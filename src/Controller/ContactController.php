<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }

    #[Route('/contact/create_messages', name: 'create_message', methods:["POST"])]
    public function createcontact(Request $request,  EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true); 
        
        if (!isset($data['email']) || !isset($data['sujet']) || !isset($data['messages'])) {
            return new JsonResponse(['error' => 'Email, sujet et message sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        $contact = new Contact();

        // Définition des valeurs pour les propriétés de l'objet $contact
        $contact->setEmail($data['email']);
        $contact->setSujet($data["sujet"]);
        $contact->setMessages($data["messages"]);
        $contact->setNom($data["nom"]);

        $em->persist($contact);
        $em->flush();

        return new JsonResponse("Message envoyer avec succès", Response::HTTP_CREATED);
    }

    #[Route('/api/contact/get_all_messages', name: 'get_all_messages', methods: ["GET"])]
    public function getAllContacts(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $contacts = $em->getRepository(Contact::class)->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($contacts as $contact) {
            $data[] = [
                'id' => $contact->getId(),
                'email' => $contact->getEmail(),
                'sujet' => $contact->getSujet(),
                'messages' => $contact->getMessages(),
                'nom' => $contact->getNom(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/contact/get_message/{id}', name: 'get_message', methods: ["GET"])]
    public function getContactById($id, EntityManagerInterface $em): JsonResponse
    {
        $contact = $em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            return new JsonResponse(['error' => 'Contact non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $contact->getId(),
            'email' => $contact->getEmail(),
            'sujet' => $contact->getSujet(),
            'messages' => $contact->getMessages(),
            'nom' => $contact->getNom(),
        ];

        return new JsonResponse($data);
    }

}
