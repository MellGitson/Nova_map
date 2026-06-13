<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\PortRequest;
use App\Entity\Port;
use App\Entity\User;
use App\Repository\PortRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/ports', name: 'api_ports_')]
final class PortController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PortRepository         $portRepo,
        private readonly ValidatorInterface     $validator,
        private readonly SerializerInterface    $serializer,
    ) {}

    /**
     * GET /api/ports?latMin=&latMax=&lngMin=&lngMax=
     * Route publique — alimentée par la carte Leaflet (bounding box).
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $latMin = $request->query->get('latMin');
        $latMax = $request->query->get('latMax');
        $lngMin = $request->query->get('lngMin');
        $lngMax = $request->query->get('lngMax');

        if ($latMin !== null && $latMax !== null && $lngMin !== null && $lngMax !== null) {
            $ports = $this->portRepo->findActifsDansBbox(
                (float) $latMin, (float) $latMax, (float) $lngMin, (float) $lngMax
            );
        } else {
            $ports = $this->portRepo->findBy(['actif' => true], ['nom' => 'ASC']);
        }

        return $this->json(array_map($this->serialize(...), $ports));
    }

    /** GET /api/ports/{id} */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Port $port): JsonResponse
    {
        return $this->json($this->serialize($port));
    }

    /** POST /api/ports — ADMIN only */
    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function create(Request $request): JsonResponse
    {
        $dto    = $this->serializer->deserialize($request->getContent(), PortRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $port = $this->hydrate(new Port(), $dto);
        $this->em->persist($port);
        $this->em->flush();

        return $this->json($this->serialize($port), Response::HTTP_CREATED);
    }

    /** PUT /api/ports/{id} — ADMIN only */
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function update(Port $port, Request $request): JsonResponse
    {
        $dto    = $this->serializer->deserialize($request->getContent(), PortRequest::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->validationErrors($errors);
        }

        $this->hydrate($port, $dto);
        $this->em->flush();

        return $this->json($this->serialize($port));
    }

    /** DELETE /api/ports/{id} — ADMIN only */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function delete(Port $port): JsonResponse
    {
        $port->setActif(false);
        $this->em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function hydrate(Port $port, PortRequest $dto): Port
    {
        return $port
            ->setNom($dto->nom)
            ->setVille($dto->ville)
            ->setPays($dto->pays)
            ->setLatitude($dto->latitude)
            ->setLongitude($dto->longitude)
            ->setCapacite($dto->capacite)
            ->setDescription($dto->description);
    }

    private function serialize(Port $p): array
    {
        $bateauxDispo = $p->getBateaux()->filter(
            fn ($b) => $b->getStatut() === \App\Entity\Bateau::STATUT_DISPONIBLE
        )->count();

        return [
            'id'               => $p->getId(),
            'nom'              => $p->getNom(),
            'ville'            => $p->getVille(),
            'pays'             => $p->getPays(),
            'latitude'         => $p->getLatitude(),
            'longitude'        => $p->getLongitude(),
            'capacite'         => $p->getCapacite(),
            'description'      => $p->getDescription(),
            'bateaux_disponibles' => $bateauxDispo,
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
