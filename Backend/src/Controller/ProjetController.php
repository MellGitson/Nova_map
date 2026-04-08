<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\JournalAudit;
use App\Entity\Membre;
use App\Entity\Organisation;
use App\Entity\Projet;
use App\Entity\User;
use App\Repository\ProjetRepository;
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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/projets', name: 'api_projet_')]
#[IsGranted('ROLE_USER')]
final class ProjetController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProjetRepository       $projetRepo,
        private readonly AuditService           $audit,
        private readonly ValidatorInterface     $validator,
    ) {}

    // -------------------------------------------------------------------------
    // GET /api/projets
    // -------------------------------------------------------------------------
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $projets = $this->projetRepo->findAll();
        } else {
            $projets = $this->projetRepo->findByUser($user);
        }

        return $this->json(array_map($this->serializeProjet(...), $projets));
    }

    // -------------------------------------------------------------------------
    // POST /api/projets
    // -------------------------------------------------------------------------
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Crée ou récupère une organisation personnelle pour l'utilisateur
        $org = $this->getOrCreatePersonalOrg($user);

        $projet = new Projet();
        $projet->setNom($data['nom'] ?? '')
               ->setDescription($data['description'] ?? null)
               ->setOrganisation($org)
               ->setCreateur($user);

        $violations = $this->validator->validate($projet);
        if (count($violations) > 0) {
            return $this->validationErrors($violations);
        }

        $this->em->persist($projet);
        $this->em->flush();

        $this->audit->log('PROJET_CREATE', 'projet', $projet->getId(), JournalAudit::NIVEAU_INFO, ['nom' => $projet->getNom()], $user);

        return $this->json($this->serializeProjet($projet), Response::HTTP_CREATED);
    }

    // -------------------------------------------------------------------------
    // GET /api/projets/{id}
    // -------------------------------------------------------------------------
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id, #[CurrentUser] User $user): JsonResponse
    {
        $projet = $this->findOr404($id);
        $this->denyAccessUnlessGranted(ProjetVoter::VIEW, $projet);

        return $this->json($this->serializeProjet($projet, true));
    }

    // -------------------------------------------------------------------------
    // PATCH /api/projets/{id}
    // -------------------------------------------------------------------------
    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(string $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $projet = $this->findOr404($id);
        $this->denyAccessUnlessGranted(ProjetVoter::EDIT, $projet);

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $projet->setNom($data['nom']);
        }
        if (array_key_exists('description', $data)) {
            $projet->setDescription($data['description']);
        }

        $violations = $this->validator->validate($projet);
        if (count($violations) > 0) {
            return $this->validationErrors($violations);
        }

        $this->em->flush();
        $this->audit->log('PROJET_UPDATE', 'projet', $projet->getId(), JournalAudit::NIVEAU_INFO, null, $user);

        return $this->json($this->serializeProjet($projet));
    }

    // -------------------------------------------------------------------------
    // DELETE /api/projets/{id}
    // -------------------------------------------------------------------------
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id, #[CurrentUser] User $user): JsonResponse
    {
        $projet = $this->findOr404($id);
        $this->denyAccessUnlessGranted(ProjetVoter::DELETE, $projet);

        $this->audit->log('PROJET_DELETE', 'projet', $projet->getId(), JournalAudit::NIVEAU_INFO, ['nom' => $projet->getNom()], $user);

        $this->em->remove($projet);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function findOr404(string $id): Projet
    {
        $projet = $this->projetRepo->find($id);
        if (!$projet) {
            throw $this->createNotFoundException("Projet $id introuvable.");
        }
        return $projet;
    }

    private function getOrCreatePersonalOrg(User $user): Organisation
    {
        $org = $this->em->getRepository(Organisation::class)->findOneBy(['proprietaire' => $user]);
        if (!$org) {
            $org = new Organisation();
            $org->setNom($user->getNom())
                ->setSlug('org-' . substr($user->getId(), 0, 8))
                ->setProprietaire($user);

            $membre = new Membre();
            $membre->setUtilisateur($user)
                   ->setOrganisation($org)
                   ->setRole(Membre::ROLE_ADMIN);

            $this->em->persist($org);
            $this->em->persist($membre);
            $this->em->flush();
        }
        return $org;
    }

    private function serializeProjet(Projet $p, bool $withComposants = false): array
    {
        $data = [
            'id'               => $p->getId(),
            'nom'              => $p->getNom(),
            'description'      => $p->getDescription(),
            'score_global'     => $p->getScoreGlobal(),
            'nb_composants'    => $p->getComposants()->count(),
            'date_creation'    => $p->getDateCreation()->format(\DateTimeInterface::ATOM),
            'date_modification'=> $p->getDateModification()->format(\DateTimeInterface::ATOM),
        ];

        if ($withComposants) {
            $data['composants'] = $p->getComposants()->map(
                fn($c) => [
                    'id'           => $c->getId(),
                    'nom'          => $c->getNom(),
                    'type'         => $c->getType(),
                    'ip_ou_domaine'=> $c->getIpOuDomaine(),
                    'score'        => $c->getScore(),
                    'position_x'   => $c->getPositionX(),
                    'position_y'   => $c->getPositionY(),
                ]
            )->toArray();
        }

        return $data;
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
