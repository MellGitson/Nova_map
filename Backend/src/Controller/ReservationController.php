<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\ReservationRequest;
use App\Entity\Bateau;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\BateauRepository;
use App\Repository\ReservationRepository;
use App\Security\Voter\ReservationVoter;
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

#[Route('/api/reservations', name: 'api_reservations_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ReservationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ReservationRepository  $reservationRepo,
        private readonly BateauRepository       $bateauRepo,
        private readonly ValidatorInterface     $validator,
        private readonly SerializerInterface    $serializer,
    ) {}

    /** GET /api/reservations — réservations de l'utilisateur connecté */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        $reservations = $this->reservationRepo->findByLocataire($user->getId());
        return $this->json(array_map($this->serialize(...), $reservations));
    }

    /** GET /api/reservations/{id} */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Reservation $reservation): JsonResponse
    {
        $this->denyAccessUnlessGranted(ReservationVoter::VIEW, $reservation);
        return $this->json($this->serialize($reservation));
    }

    /** POST /api/reservations */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $dto    = $this->serializer->deserialize($request->getContent(), ReservationRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $bateau = $this->em->find(Bateau::class, $dto->bateauId);
        if (!$bateau) {
            return $this->json(['message' => 'Bateau introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if (!$bateau->isDisponible()) {
            return $this->json(['message' => 'Ce bateau n\'est pas disponible.'], Response::HTTP_CONFLICT);
        }

        $debut = \DateTimeImmutable::createFromFormat('Y-m-d', $dto->dateDebut);
        $fin   = \DateTimeImmutable::createFromFormat('Y-m-d', $dto->dateFin);

        if (!$this->reservationRepo->estDisponible($bateau->getId(), $debut, $fin)) {
            return $this->json(['message' => 'Le bateau est déjà réservé sur cette période.'], Response::HTTP_CONFLICT);
        }

        $reservation = (new Reservation())
            ->setBateau($bateau)
            ->setLocataire($user)
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->setCommentaire($dto->commentaire);

        $reservation->calculerMontant();

        $this->em->persist($reservation);
        $this->em->flush();

        return $this->json($this->serialize($reservation), Response::HTTP_CREATED);
    }

    /** PATCH /api/reservations/{id}/confirmer — propriétaire du bateau */
    #[Route('/{id}/confirmer', name: 'confirmer', methods: ['PATCH'])]
    public function confirmer(Reservation $reservation): JsonResponse
    {
        $this->denyAccessUnlessGranted(ReservationVoter::MANAGE, $reservation);

        if ($reservation->getStatut() !== Reservation::STATUT_EN_ATTENTE) {
            return $this->json(['message' => 'Seules les réservations en attente peuvent être confirmées.'], Response::HTTP_BAD_REQUEST);
        }

        $reservation->setStatut(Reservation::STATUT_CONFIRMEE);
        $reservation->getBateau()->setStatut(Bateau::STATUT_LOUE);
        $this->em->flush();

        return $this->json($this->serialize($reservation));
    }

    /** PATCH /api/reservations/{id}/annuler — locataire uniquement */
    #[Route('/{id}/annuler', name: 'annuler', methods: ['PATCH'])]
    public function annuler(Reservation $reservation): JsonResponse
    {
        $this->denyAccessUnlessGranted(ReservationVoter::CANCEL, $reservation);

        if (!in_array($reservation->getStatut(), [Reservation::STATUT_EN_ATTENTE, Reservation::STATUT_CONFIRMEE], true)) {
            return $this->json(['message' => 'Cette réservation ne peut plus être annulée.'], Response::HTTP_BAD_REQUEST);
        }

        $reservation->setStatut(Reservation::STATUT_ANNULEE);

        if ($reservation->getBateau()->getStatut() === Bateau::STATUT_LOUE) {
            $reservation->getBateau()->setStatut(Bateau::STATUT_DISPONIBLE);
        }

        $this->em->flush();

        return $this->json($this->serialize($reservation));
    }

    private function serialize(Reservation $r): array
    {
        return [
            'id'           => $r->getId(),
            'bateau'       => [
                'id'  => $r->getBateau()->getId(),
                'nom' => $r->getBateau()->getNom(),
            ],
            'locataire'    => [
                'id'  => $r->getLocataire()->getId(),
                'nom' => $r->getLocataire()->getNom(),
            ],
            'date_debut'   => $r->getDateDebut()->format('Y-m-d'),
            'date_fin'     => $r->getDateFin()->format('Y-m-d'),
            'nombre_jours' => $r->getNombreJours(),
            'montant_total' => $r->getMontantTotal(),
            'statut'       => $r->getStatut(),
            'commentaire'  => $r->getCommentaire(),
            'date_creation' => $r->getDateCreation()->format(\DateTimeInterface::ATOM),
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
