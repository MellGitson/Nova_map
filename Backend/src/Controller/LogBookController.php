<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\TrajetRequest;
use App\Entity\Bateau;
use App\Entity\Trajet;
use App\Entity\User;
use App\Repository\TrajetRepository;
use App\Security\Voter\TrajetVoter;
use App\Service\RecommendationEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/logbook', name: 'api_logbook_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class LogBookController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TrajetRepository       $trajetRepo,
        private readonly ValidatorInterface     $validator,
        private readonly SerializerInterface    $serializer,
        private readonly RecommendationEngine   $recommandation,
    ) {}

    /** GET /api/logbook — trajets du capitaine connecté */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        $trajets = $this->trajetRepo->findByCapitaine($user->getId());
        return $this->json(array_map($this->serialize(...), $trajets));
    }

    /** GET /api/logbook/recommandations — suggestions basées sur les patterns */
    #[Route('/recommandations', name: 'recommandations', methods: ['GET'])]
    public function recommandations(#[CurrentUser] User $user): JsonResponse
    {
        $suggestions = $this->recommandation->generer($user);
        return $this->json($suggestions);
    }

    /** GET /api/logbook/{id} */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Trajet $trajet): JsonResponse
    {
        $this->denyAccessUnlessGranted(TrajetVoter::VIEW, $trajet);
        return $this->json($this->serialize($trajet));
    }

    /** POST /api/logbook */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $dto    = $this->serializer->deserialize($request->getContent(), TrajetRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $bateau = $this->em->find(Bateau::class, $dto->bateauId);
        if (!$bateau) {
            return $this->json(['message' => 'Bateau introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $trajet = $this->hydrate(new Trajet(), $dto, $user, $bateau);
        $this->em->persist($trajet);
        $this->em->flush();

        return $this->json($this->serialize($trajet), Response::HTTP_CREATED);
    }

    /** PUT /api/logbook/{id} */
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(Trajet $trajet, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(TrajetVoter::EDIT, $trajet);

        $dto    = $this->serializer->deserialize($request->getContent(), TrajetRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $bateau = $this->em->find(Bateau::class, $dto->bateauId);
        $this->hydrate($trajet, $dto, $user, $bateau ?? $trajet->getBateau());
        $this->em->flush();

        return $this->json($this->serialize($trajet));
    }

    /** DELETE /api/logbook/{id} */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Trajet $trajet): JsonResponse
    {
        $this->denyAccessUnlessGranted(TrajetVoter::DELETE, $trajet);
        $this->em->remove($trajet);
        $this->em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function hydrate(Trajet $trajet, TrajetRequest $dto, User $user, Bateau $bateau): Trajet
    {
        $depart  = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $dto->dateDepart)
            ?: \DateTimeImmutable::createFromFormat('Y-m-d', $dto->dateDepart);

        $arrivee = null;
        if ($dto->dateArrivee) {
            $arrivee = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $dto->dateArrivee)
                ?: \DateTimeImmutable::createFromFormat('Y-m-d', $dto->dateArrivee);
        }

        return $trajet
            ->setBateau($bateau)
            ->setCapitaine($user)
            ->setPortDepart($dto->portDepart)
            ->setPortArrivee($dto->portArrivee)
            ->setDateDepart($depart)
            ->setDateArrivee($arrivee ?: null)
            ->setDistanceMilles($dto->distanceMilles)
            ->setNombrePassagers($dto->nombrePassagers)
            ->setNotes($dto->notes)
            ->setConditionsMeteo($dto->conditionsMeteo);
    }

    private function serialize(Trajet $t): array
    {
        return [
            'id'               => $t->getId(),
            'bateau'           => [
                'id'  => $t->getBateau()->getId(),
                'nom' => $t->getBateau()->getNom(),
            ],
            'port_depart'      => $t->getPortDepart(),
            'port_arrivee'     => $t->getPortArrivee(),
            'date_depart'      => $t->getDateDepart()->format(\DateTimeInterface::ATOM),
            'date_arrivee'     => $t->getDateArrivee()?->format(\DateTimeInterface::ATOM),
            'duree_heures'     => $t->getDureeHeures(),
            'distance_milles'  => $t->getDistanceMilles(),
            'nombre_passagers' => $t->getNombrePassagers(),
            'notes'            => $t->getNotes(),
            'conditions_meteo' => $t->getConditionsMeteo(),
            'est_weekend'      => $t->isEstWeekend(),
            'date_creation'    => $t->getDateCreation()->format(\DateTimeInterface::ATOM),
        ];
    }

    private function validationErrors(mixed $errors): JsonResponse
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()] = $error->getMessage();
        }
        return $this->json(['errors' => $messages], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
