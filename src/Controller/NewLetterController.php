<?php

namespace App\Controller;

use App\Entity\NewLetter;
use App\Repository\NewLetterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class NewLetterController extends AbstractController
{
    #[Route('/newletter', name: 'app_new_letter')]
    public function createLetter(Request $request, EntityManagerInterface $em, NewLetterRepository $newLetterRepository): JsonResponse
    {   
        $data = json_decode($request->getContent(), true); 
        $email = $data['email'] ;

        if (!isset($email)) {
            return new JsonResponse(['error' => 'Email es obligatoir requis.'], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->isValidEmail($email)) {
            return new JsonResponse(['error' => 'Email es invalide.'], Response::HTTP_BAD_REQUEST);
        } 

        $existeEmail = $newLetterRepository->findOneBy(['email' => $email]);
        
        if ($existeEmail) {
            return new JsonResponse("Merci de vous avoir abonée a notre new letter", Response::HTTP_CREATED);
        }

        $newLetter = new NewLetter() ;
        $newLetter->setEmail($email);
        $newLetter->setStatus(1) ;

        $em->persist($newLetter);
        $em->flush();

        return new JsonResponse("Merci de vous avoir abonée a notre new letter", Response::HTTP_CREATED);
    }

    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

}
