<?php

namespace App\Controller;

use App\Entity\Emplacement;
use App\Constants;
use App\Repository\EmplacementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/emplacement', name:'emplacement_')]
class EmplacementController extends AbstractController
{   
    private $security;
    private $serializer;
    private $entityManagerInterface;
    private $emplacementRepository;

    public function __construct(Security $security, SerializerInterface $serializer,
    EntityManagerInterface $entityManagerInterface, EmplacementRepository $emplacementRepository)
    {
        $this->security = $security;
        $this->serializer = $serializer;
        $this->entityManagerInterface = $entityManagerInterface ;
        $this->emplacementRepository = $emplacementRepository;
    }

    #[Route('/create_emplacement', name: 'create_emplacement', methods: ['POST'])]
    public function createEmplacement(Request $request): JsonResponse
    {
        $emplacement = $this->serializer->deserialize($request->getContent(), Emplacement::class, 'json');
        $description = $emplacement->getDescription();
        $typeEmplacement = $emplacement->getTypeEmplacement();
        $capacity = intval($emplacement->getCapacite());
    
        switch ($typeEmplacement) {
            case 'public_room':
                if ($capacity !== Constants::PUBLIC_ROOM) {
                    return new JsonResponse(['error' => 'La capacité du salon public doit être de ' . Constants::PUBLIC_ROOM], 400);
                }
    
                $existingEmplacement = $this->entityManagerInterface->getRepository(Emplacement::class)->findOneBy([
                    'typeEmplacement' => 'public_room',
                    'capacite' => $capacity,
                ]);
    
                if ($existingEmplacement) {
                    return new JsonResponse(['error' => 'La capacité spécifiée existe déjà pour le salon public.'], 400);
                }
    
                $emplacement = new Emplacement();
                $emplacement->setDescription($description);
                $emplacement->setTypeEmplacement('public_room');
                $emplacement->setCapacite($capacity);
                $this->entityManagerInterface->persist($emplacement);
                break;
    
            case 'private_room':
                if ($capacity !== 4 && $capacity !== 6) {
                    return new JsonResponse(['error' => 'Le salon privé doit avoir une capacité de 4 ou de 6'], 400);
                }
    
                $existingEmplacement = $this->entityManagerInterface->getRepository(Emplacement::class)->findOneBy([
                    'typeEmplacement' => 'private_room',
                    'capacite' => $capacity * Constants::PRIVATE_ROOM_FOUR_SEATS,
                ]);
    
                if ($existingEmplacement) {
                    return new JsonResponse(['error' => 'La capacité spécifiée existe déjà pour les salons privés.'], 400);
                }
    
                $emplacement = new Emplacement();
                $emplacement->setDescription($description);
                $emplacement->setTypeEmplacement('private_room');
                $emplacement->setCapacite($capacity * Constants::PRIVATE_ROOM_FOUR_SEATS);
                $this->entityManagerInterface->persist($emplacement);
                break;
    
            default:
                return new JsonResponse(['error' => 'Type d\'emplacement invalide'], 400);
        }
    
        $this->entityManagerInterface->flush();
        return new JsonResponse('Emplacement créé avec succès', Response::HTTP_CREATED);
    }
    

    #[Route('/get_all_emplacements', name: 'get_all_emplacements', methods: ['GET'])]
    public function getAllEmplacements(EmplacementRepository $emplacementRepository): JsonResponse
    {
        $emplacements = $emplacementRepository->findAll();
    
        $emplacementsData = $this->serializer->serialize($emplacements, 'json', ['groups' => 'getCapacite']);
    
        return new JsonResponse($emplacementsData, 200, [], true);
    }


    #[Route('/read_emplacement/{id}', name: 'read_emplacement', methods: ['GET'])]
    public function readEmplacement(int $id): JsonResponse
    {
        $emplacement = $this->emplacementRepository->find($id);

        if (!$emplacement) {
            return new JsonResponse(['error' => 'Emplacement non trouvé'], 404);
        }

        $emplacementData = $this->serializer->serialize($emplacement, 'json');

        return new JsonResponse($emplacementData, Response::HTTP_OK, [], true);
    }

    #[Route('/update_emplacement/{id}', name: 'update_emplacement', methods: ['PUT', 'PATCH'])]
    public function updateEmplacement(int $id, Request $request): JsonResponse
    {
        $emplacement = $this->emplacementRepository->find($id);

        if (!$emplacement) {
            return new JsonResponse(['error' => 'Emplacement non trouvé'], 404);
        }

        $updatedEmplacement = $this->serializer->deserialize($request->getContent(), Emplacement::class, 'json', [
            'object_to_populate' => $emplacement,
        ]);

        $this->entityManagerInterface->flush();

        return new JsonResponse('Emplacement mis à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/delete_emplacement/{id}', name: 'delete_emplacement', methods: ['DELETE'])]
    public function deleteEmplacement(int $id): JsonResponse
    {
        $emplacement = $this->emplacementRepository->find($id);

        if (!$emplacement) {
            return new JsonResponse(['error' => 'Emplacement non trouvé'], 404);
        }

        $this->entityManagerInterface->remove($emplacement);
        $this->entityManagerInterface->flush();

        return new JsonResponse('Emplacement supprimé avec succès', Response::HTTP_OK);
    }

}
