<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\BateauRequest;
use App\Entity\Bateau;
use App\Entity\Port;
use App\Entity\User;
use App\Repository\BateauRepository;
use App\Security\Voter\BateauVoter;
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

#[Route('/api/bateaux', name: 'api_bateaux_')]
final class BateauController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly BateauRepository       $bateauRepo,
        private readonly ValidatorInterface     $validator,
        private readonly SerializerInterface    $serializer,
    ) {}

    /** GET /api/bateaux?latMin=&latMax=&lngMin=&lngMax= */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $latMin = $request->query->get('latMin');
        $latMax = $request->query->get('latMax');
        $lngMin = $request->query->get('lngMin');
        $lngMax = $request->query->get('lngMax');

        if ($latMin !== null && $latMax !== null && $lngMin !== null && $lngMax !== null) {
            $bateaux = $this->bateauRepo->findDisponiblesDansBbox(
                (float) $latMin, (float) $latMax, (float) $lngMin, (float) $lngMax
            );
        } else {
            $bateaux = $this->bateauRepo->findBy(['statut' => Bateau::STATUT_DISPONIBLE], ['dateCreation' => 'DESC']);
        }

        return $this->json(array_map($this->serialize(...), $bateaux));
    }

    /** GET /api/bateaux/mes-bateaux */
    #[Route('/mes-bateaux', name: 'mes_bateaux', methods: ['GET'])]
    #[IsGranted(User::ROLE_OWNER)]
    public function mesBateaux(#[CurrentUser] User $user): JsonResponse
    {
        $bateaux = $this->bateauRepo->findByProprietaire($user->getId());
        return $this->json(array_map($this->serialize(...), $bateaux));
    }

    /** GET /api/bateaux/{id} */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Bateau $bateau): JsonResponse
    {
        $this->denyAccessUnlessGranted(BateauVoter::VIEW, $bateau);
        return $this->json($this->serialize($bateau));
    }

    /** POST /api/bateaux */
    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted(User::ROLE_OWNER)]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $dto    = $this->serializer->deserialize($request->getContent(), BateauRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $bateau = $this->hydrate(new Bateau(), $dto, $user);
        $this->em->persist($bateau);
        $this->em->flush();

        return $this->json($this->serialize($bateau), Response::HTTP_CREATED);
    }

    /** PUT /api/bateaux/{id} */
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(Bateau $bateau, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(BateauVoter::EDIT, $bateau);

        $dto    = $this->serializer->deserialize($request->getContent(), BateauRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $this->hydrate($bateau, $dto, $user);
        $this->em->flush();

        return $this->json($this->serialize($bateau));
    }

    /** DELETE /api/bateaux/{id} */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Bateau $bateau): JsonResponse
    {
        $this->denyAccessUnlessGranted(BateauVoter::DELETE, $bateau);
        $this->em->remove($bateau);
        $this->em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function hydrate(Bateau $bateau, BateauRequest $dto, User $user): Bateau
    {
        $bateau
            ->setNom($dto->nom)
            ->setType($dto->type)
            ->setMarque($dto->marque)
            ->setAnnee($dto->annee)
            ->setLongueur($dto->longueur)
            ->setCapacitePersonnes($dto->capacitePersonnes)
            ->setPrixParJour($dto->prixParJour)
            ->setStatut($dto->statut)
            ->setDescription($dto->description)
            ->setProprietaire($user);

        if ($dto->portId) {
            $port = $this->em->find(Port::class, $dto->portId);
            $bateau->setPort($port);
        }

        return $bateau;
    }

    private function serialize(Bateau $b): array
    {
        return [
            'id'                => $b->getId(),
            'nom'               => $b->getNom(),
            'type'              => $b->getType(),
            'marque'            => $b->getMarque(),
            'annee'             => $b->getAnnee(),
            'longueur'          => $b->getLongueur(),
            'capacite_personnes' => $b->getCapacitePersonnes(),
            'prix_par_jour'     => $b->getPrixParJour(),
            'statut'            => $b->getStatut(),
            'description'       => $b->getDescription(),
            'photos'            => $b->getPhotos(),
            'proprietaire'      => [
                'id'  => $b->getProprietaire()->getId(),
                'nom' => $b->getProprietaire()->getNom(),
            ],
            'port'              => $b->getPort() ? [
                'id'        => $b->getPort()->getId(),
                'nom'       => $b->getPort()->getNom(),
                'latitude'  => $b->getPort()->getLatitude(),
                'longitude' => $b->getPort()->getLongitude(),
            ] : null,
            'date_creation'     => $b->getDateCreation()->format(\DateTimeInterface::ATOM),
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
