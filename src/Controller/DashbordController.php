<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\FormuleRepository;
use App\Repository\ReservationRepository;
use App\Repository\SalonPrincipalRepository;
use App\Repository\SalonPriveeRepository;
use App\Repository\UserRepository;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashbord', name: 'dashbord_')]
class DashbordController extends AbstractController
{   
    private $userRepository ;
    private  $userPasswordHasher ;
    private $em;
    private $security ;
    private $mailerService;
    private $requestStack;
    private $reservationRepository ;
    private $salonPriveeRepository ;
    private $salonPrincipalRepository;
    private $formuleRepository ;

    public function __construct(
        EntityManagerInterface $entityManagerInterface , 
        MailerService $mailerService,
        RequestStack $requestStack,
        UserRepository $userRepository,
        ReservationRepository $reservationRepository,
        SalonPrincipalRepository $salonPrincipalRepository,
        SalonPriveeRepository $salonPriveeRepository,
        FormuleRepository $formuleRepository
    )
    {
        $this->em = $entityManagerInterface ;
        $this->mailerService = $mailerService ;
        $this->requestStack = $requestStack;
        $this->reservationRepository = $reservationRepository;
        $this->userRepository = $userRepository;
        $this->salonPriveeRepository = $salonPriveeRepository;
        $this->salonPrincipalRepository = $salonPrincipalRepository;
        $this->formuleRepository = $formuleRepository ;

    }

    #[Route('/Number_of_registrations', name: 'Number_of_registrations')]
    public function getNumberOfRegistrations(): JsonResponse
    {   
        $users = $this->em->getRepository(user::class)->findAll();

        return new JsonResponse(count($users), Response::HTTP_OK, [], true);
    }

    /**
     * 
     * Affichage des statistiques :

        Nombre total de réservations effectuées.
        Taux de remplissage des réservations (pourcentage de réservations occupées).
        Revenus générés par les réservations.
        Gestion des réservations :

        Affichage des réservations en cours, y compris les détails tels que le nom du client, la date et l'heure de la réservation, le statut de paiement, etc.
        Possibilité de modifier ou d'annuler une réservation existante.
        Création de nouvelles réservations en saisissant les détails du client et les informations de réservation.
        Planification et disponibilité :

        Affichage d'un calendrier avec les disponibilités actuelles et futures.
        Visualisation des plages horaires occupées et disponibles.
        Possibilité de bloquer certaines plages horaires pour des raisons spécifiques (maintenance, congés, etc.).

        Notifications et rappels :

        Envoi de rappels aux clients pour confirmer leurs réservations et leur rappeler la date et l'heure.
        Notifications aux utilisateurs internes pour les nouvelles réservations, les annulations ou les modifications.

        Rapports et analyses :

        Génération de rapports sur les réservations effectuées, les revenus, les annulations, etc.
        Analyse des tendances de réservation pour identifier les périodes de forte ou faible activité.
    */
}
