<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Composant;
use App\Entity\JournalAudit;
use App\Entity\LienComposant;
use App\Entity\Projet;
use App\Entity\User;
use App\Repository\ComposantRepository;
use App\Repository\LienComposantRepository;
use App\Repository\ProjetRepository;
use App\Security\Voter\ComposantVoter;
use App\Security\Voter\ProjetVoter;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/projets/{projetId}/composants', name: 'api_composant_')]
#[IsGranted('ROLE_USER')]
final class ComposantController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly ProjetRepository         $projetRepo,
        private readonly ComposantRepository      $composantRepo,
        private readonly LienComposantRepository  $lienRepo,
        private readonly AuditService             $audit,
        private readonly ValidatorInterface       $validator,
    ) {}

    // -------------------------------------------------------------------------
    // GET /api/projets/{projetId}/composants
    // -------------------------------------------------------------------------
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(string $projetId): JsonResponse
    {
        $projet = $this->findProjet($projetId);
        $this->denyAccessUnlessGranted(ProjetVoter::VIEW, $projet);

        $composants = $this->composantRepo->findBy(['projet' => $projet]);
        $liens      = $this->lienRepo->findByProjet($projet);

        return $this->json([
            'nodes' => array_map($this->serializeComposant(...), $composants),
            'edges' => array_map($this->serializeLien(...), $liens),
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /api/projets/{projetId}/composants
    // -------------------------------------------------------------------------
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(string $projetId, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $projet = $this->findProjet($projetId);
        $this->denyAccessUnlessGranted(ProjetVoter::EDIT, $projet);

        $data = json_decode($request->getContent(), true);

        $composant = new Composant();
        $composant->setProjet($projet)
                  ->setNom($data['nom'] ?? '')
                  ->setType($data['type'] ?? Composant::TYPE_SERVEUR)
                  ->setIpOuDomaine($data['ip_ou_domaine'] ?? null)
                  ->setVersionLogicielle($data['version_logicielle'] ?? null)
                  ->setEnvironnement($data['environnement'] ?? Composant::ENV_PROD)
                  ->setPort($data['port'] ?? null)
                  ->setPositionX((float) ($data['position_x'] ?? 0))
                  ->setPositionY((float) ($data['position_y'] ?? 0));

        $violations = $this->validator->validate($composant);
        if (count($violations) > 0) {
            return $this->validationErrors($violations);
        }

        $this->em->persist($composant);
        $this->em->flush();

        $this->audit->log('COMPOSANT_CREATE', 'composant', $composant->getId(), JournalAudit::NIVEAU_INFO, null, $user);

        return $this->json($this->serializeComposant($composant), Response::HTTP_CREATED);
    }

    // -------------------------------------------------------------------------
    // GET /api/projets/{projetId}/composants/{id}
    // -------------------------------------------------------------------------
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $projetId, string $id): JsonResponse
    {
        $composant = $this->findComposant($projetId, $id);
        $this->denyAccessUnlessGranted(ComposantVoter::VIEW, $composant);

        return $this->json($this->serializeComposant($composant, true));
    }

    // -------------------------------------------------------------------------
    // PATCH /api/projets/{projetId}/composants/{id}
    // -------------------------------------------------------------------------
    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(string $projetId, string $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $composant = $this->findComposant($projetId, $id);
        $this->denyAccessUnlessGranted(ComposantVoter::EDIT, $composant);

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom']))               $composant->setNom($data['nom']);
        if (isset($data['type']))              $composant->setType($data['type']);
        if (array_key_exists('ip_ou_domaine', $data)) $composant->setIpOuDomaine($data['ip_ou_domaine']);
        if (array_key_exists('version_logicielle', $data)) $composant->setVersionLogicielle($data['version_logicielle']);
        if (isset($data['environnement']))     $composant->setEnvironnement($data['environnement']);
        if (array_key_exists('port', $data))  $composant->setPort($data['port']);
        if (isset($data['position_x']))        $composant->setPositionX((float) $data['position_x']);
        if (isset($data['position_y']))        $composant->setPositionY((float) $data['position_y']);

        $violations = $this->validator->validate($composant);
        if (count($violations) > 0) {
            return $this->validationErrors($violations);
        }

        $this->em->flush();
        $this->audit->log('COMPOSANT_UPDATE', 'composant', $composant->getId(), JournalAudit::NIVEAU_INFO, null, $user);

        return $this->json($this->serializeComposant($composant));
    }

    // -------------------------------------------------------------------------
    // DELETE /api/projets/{projetId}/composants/{id}
    // -------------------------------------------------------------------------
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $projetId, string $id, #[CurrentUser] User $user): JsonResponse
    {
        $composant = $this->findComposant($projetId, $id);
        $this->denyAccessUnlessGranted(ComposantVoter::DELETE, $composant);

        $this->audit->log('COMPOSANT_DELETE', 'composant', $composant->getId(), JournalAudit::NIVEAU_INFO, null, $user);
        $this->em->remove($composant);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // -------------------------------------------------------------------------
    // POST /api/projets/{projetId}/composants/liens
    // -------------------------------------------------------------------------
    #[Route('/liens', name: 'lien_create', methods: ['POST'])]
    public function createLien(string $projetId, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $projet = $this->findProjet($projetId);
        $this->denyAccessUnlessGranted(ProjetVoter::EDIT, $projet);

        $data    = json_decode($request->getContent(), true);
        $source  = $this->findComposant($projetId, $data['source_id'] ?? '');
        $cible   = $this->findComposant($projetId, $data['cible_id'] ?? '');

        if ($source->getId() === $cible->getId()) {
            return $this->json(['message' => 'Un composant ne peut pas se lier à lui-même.'], Response::HTTP_BAD_REQUEST);
        }

        $lien = new LienComposant();
        $lien->setSource($source)
             ->setCible($cible)
             ->setTypeLien($data['type_lien'] ?? LienComposant::TYPE_HTTP)
             ->setDescription($data['description'] ?? null);

        $this->em->persist($lien);
        $this->em->flush();

        return $this->json($this->serializeLien($lien), Response::HTTP_CREATED);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/projets/{projetId}/composants/liens/{lienId}
    // -------------------------------------------------------------------------
    #[Route('/liens/{lienId}', name: 'lien_delete', methods: ['DELETE'])]
    public function deleteLien(string $projetId, string $lienId, #[CurrentUser] User $user): JsonResponse
    {
        $projet = $this->findProjet($projetId);
        $this->denyAccessUnlessGranted(ProjetVoter::EDIT, $projet);

        $lien = $this->lienRepo->find($lienId);
        if (!$lien) {
            throw $this->createNotFoundException("Lien $lienId introuvable.");
        }

        $this->em->remove($lien);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function findProjet(string $id): Projet
    {
        $projet = $this->projetRepo->find($id);
        if (!$projet) throw $this->createNotFoundException("Projet $id introuvable.");
        return $projet;
    }

    private function findComposant(string $projetId, string $id): Composant
    {
        $c = $this->composantRepo->findOneBy(['id' => $id, 'projet' => $projetId]);
        if (!$c) throw $this->createNotFoundException("Composant $id introuvable.");
        return $c;
    }

    private function serializeComposant(Composant $c, bool $withAnalyses = false): array
    {
        $data = [
            'id'                  => $c->getId(),
            'nom'                 => $c->getNom(),
            'type'                => $c->getType(),
            'ip_ou_domaine'       => $c->getIpOuDomaine(),
            'version_logicielle'  => $c->getVersionLogicielle(),
            'environnement'       => $c->getEnvironnement(),
            'port'                => $c->getPort(),
            'score'               => $c->getScore(),
            'derniere_analyse'    => $c->getDerniereAnalyse()?->format(\DateTimeInterface::ATOM),
            'position_x'          => $c->getPositionX(),
            'position_y'          => $c->getPositionY(),
            'date_creation'       => $c->getDateCreation()->format(\DateTimeInterface::ATOM),
        ];

        if ($withAnalyses) {
            $data['cves_actives'] = $c->getComposantCves()
                ->filter(fn($cv) => $cv->getStatut() === 'active')
                ->map(fn($cv) => [
                    'cve_id'   => $cv->getCve()->getCveId(),
                    'severite' => $cv->getCve()->getSeverite(),
                    'cvss'     => $cv->getCve()->getScoreCvss(),
                ])
                ->toArray();
        }

        return $data;
    }

    private function serializeLien(LienComposant $l): array
    {
        return [
            'id'          => $l->getId(),
            'source_id'   => $l->getSource()->getId(),
            'cible_id'    => $l->getCible()->getId(),
            'type_lien'   => $l->getTypeLien(),
            'description' => $l->getDescription(),
        ];
    }

    private function validationErrors(mixed $errors): JsonResponse
    {
        $messages = [];
        foreach ($errors as $e) {
            $messages[$e->getPropertyPath()] = $e->getMessage();
        }
        return $this->json(['errors' => $messages], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
