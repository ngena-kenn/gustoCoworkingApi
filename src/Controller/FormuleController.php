<?php

namespace App\Controller;

use App\Entity\Formule;
use App\Repository\FormuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

// #[Route('/api/formule', name: 'formule_')]
#[Route('/formule', name: 'formule_')]
class FormuleController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    #[Route('/create_formule', name: 'create_formule', methods: ['POST'])]
    public function createFormule(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManagerInterface, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $formule = $serializer->deserialize($request->getContent(), Formule::class, 'json');
        
        $entityManagerInterface->persist($formule);
        $entityManagerInterface->flush();
    
        $jsonFormule = $serializer->serialize($formule, 'json', ['groups' => 'formule']);

        return new JsonResponse($jsonFormule, Response::HTTP_CREATED, [], true);
    }

    #[Route('/update_formule/{id}', name: 'update_formule', methods: ['PUT'])]
    public function updateFormule(int $id, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $formule = $entityManagerInterface->getRepository(Formule::class)->find($id);

        if (!$formule) {
            return new JsonResponse(['message' => 'Formule not found'], Response::HTTP_NOT_FOUND);
        }

        // Deserialize data from the request
        $updatedFormule = $serializer->deserialize($request->getContent(), Formule::class, 'json');

        $formule->setNomFormule($updatedFormule->getNomFormule());
        $formule->setDescriptionFormule($updatedFormule->getDescriptionFormule());
        $formule->setPrix($updatedFormule->getPrix());
        $formule->setDescription2($updatedFormule->getDescription2());

        $entityManagerInterface->flush();

        $jsonFormule = $serializer->serialize($formule, 'json', ['groups' => 'formule']);

        return new JsonResponse($jsonFormule, Response::HTTP_OK, [], true);
    }


   #[Route('/all', name: 'get_all_formules', methods: ['GET'])]
   public function getAllFormules(EntityManagerInterface $entityManagerInterface, SerializerInterface $serializer): JsonResponse
   {
       $formules = $entityManagerInterface->getRepository(Formule::class)->findAll();
       $jsonFormules = $serializer->serialize($formules, 'json', ['groups' => 'formule']);
       
       return new JsonResponse($jsonFormules, Response::HTTP_OK, [], true);
   }

    #[Route('/{id}', name: 'get_formule', methods: ['GET'])]
    public function getFormule(int $id, EntityManagerInterface $entityManagerInterface, SerializerInterface $serializer): JsonResponse
    {
        $formule = $entityManagerInterface->getRepository(Formule::class)->find($id);

        if (!$formule) {
            throw new EntityNotFoundException("cette formule n'existe pas");
        }

        $jsonFormule = $serializer->serialize($formule, 'json', ['groups' => 'formule']);

        return new JsonResponse($jsonFormule, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'delete_formule', methods: ['DELETE'])]
    public function deleteFormule(EntityManagerInterface $entityManagerInterface, Formule $formule): JsonResponse
    {   
        $entityManagerInterface->remove($formule);
        $entityManagerInterface->flush();

        return new JsonResponse('Formule deleted successfully', Response::HTTP_OK, [], true);
    }
}
