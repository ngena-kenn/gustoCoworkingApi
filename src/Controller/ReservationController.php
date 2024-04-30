<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\SalonPrivee;
use App\Repository\EspaceDeTravailRepository;
use App\Repository\FormuleRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Repository\SalonPriveeRepository;
use App\Repository\SalonPrincipalRepository;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Type;

#[Route('/api/reservation', name: 'reservation_')]
class ReservationController extends AbstractController
{   
    private  $userPasswordHasher ;
    private $em;
    private $userRepository ;
    private $security ;
    private $mailerService;
    private $requestStack;
    private $reservationRepository ;
    private $salonPriveeRepository ;
    private $salonPrincipalRepository;
    private $formuleRepository ;
    private $espaceDeTravailRepository ;

    public function __construct(
        EntityManagerInterface $EntityManagerInterface , 
        MailerService $mailerService,
        RequestStack $requestStack,
        UserRepository $userRepository,
        ReservationRepository $reservationRepository,
        SalonPrincipalRepository $salonPrincipalRepository,
        SalonPriveeRepository $salonPriveeRepository,
        FormuleRepository $formuleRepository,
        EspaceDeTravailRepository $espaceDeTravailRepository 
    )
    {
        $this->em = $EntityManagerInterface ;
        $this->mailerService = $mailerService ;
        $this->requestStack = $requestStack;
        $this->reservationRepository = $reservationRepository;
        $this->userRepository = $userRepository;
        $this->salonPriveeRepository = $salonPriveeRepository;
        $this->salonPrincipalRepository = $salonPrincipalRepository;
        $this->formuleRepository = $formuleRepository ;
        $this->espaceDeTravailRepository = $espaceDeTravailRepository ;
    }

    #[Route('/create_reservation', name: 'create_reservation_', methods:['POST'])]
    public function createReservation(Request $request): JsonResponse
    {   
        $data = json_decode($request->getContent(), true);
        $date = $data["date"];
        $heure_de_debut = $data["heure_de_debut"]; 
        $heure_de_fin = $data["heure_de_fin"];
        $id_formule = (int)$data["id_formule"];
        $id_user = (int)$data["id_user"];
        $effectif = $data["effectif"];
        $id_espace_de_travail = (int)$data["id_espace_de_travail"];
        $type_espace_de_travail = $data["type_espace_de_travail"];
        $type_de_Formule = "" ;
        $espace_de_travail = "";
        $id_espace_de_travail_salon_principal = "" ;
        $id_espace_de_travail_salon_privee = "" ;

        if (!in_array($type_espace_de_travail,  ["private_room", "principal_room"])) {
            return new JsonResponse(['error' => " le type d'espace de travail doit être l'un de ces deux éléments private_room ou principal_room"], 401);
        }

        $user = $this->userRepository->findOneBy(['id' => $id_user]);
        if (!$user) {
            return new JsonResponse(['error' => "L'ID de l'utilisateur ne correspond à aucun utilisateur"], 401);
        }

        // si l'éffectif es supérieur a deux et que c'est un saloon principal alors 
        $formule = $this->formuleRepository->findOneBy(['id' => $id_formule]);
        if (!$formule) {
            return new JsonResponse(['error' => "L'ID de la formule ne correspond à aucune formule"], 401);
        }

        /*         
        if ($id_espace_de_travail) {
            
            if ($type_espace_de_travail == "principal_room") {
                $id_espace_de_travail_salon_principal = $this->salonPrincipalRepository->findOneBy(['id' => $id_espace_de_travail]);
            }
            if ($type_espace_de_travail == "private_room" ) {
                $id_espace_de_travail_salon_privee = $this->salonPriveeRepository->findOneBy(['id' => $id_espace_de_travail]);
            }
            // dump($id_espace_de_travail) ;
            // dump($id_espace_de_travail_salon_principal); die ;
            
            if (!$id_espace_de_travail_salon_principal && !$id_espace_de_travail_salon_privee) {
                return new JsonResponse(['error' => "L'espace de travail n'existe pas"], 401);
            }

            // $espace_de_travail = $id_espace_de_travail_salon_principal ?: $id_espace_de_travail_salon_privee;
            $espace_de_travail_id = $id_espace_de_travail_salon_principal ?: $id_espace_de_travail_salon_privee;
            $id_search = $espace_de_travail_id->getEspaceDeTravail();
            $espace_de_travail = $this->espaceDeTravailRepository->find($id_search) ;
        } 
        */

        $espace_de_travail = $this->espaceDeTravailRepository->find($id_espace_de_travail) ;
        if (!$espace_de_travail) {
            return new JsonResponse(['error' => "L'espace de travail n'existe pas"], 401);
        }
        
        $validator = Validation::createValidator();
        $errors = [];
        
        $constraints = [
            'date' => new DateTime(['format' => 'Y-m-d']),
            'heure_de_debut' => new DateTime(['format' => 'H:i']),
            'heure_de_fin' => new DateTime(['format' => 'H:i']),
        ];
        
        foreach ($constraints as $key => $constraint) {
            $value = $$key;
            if ($validator->validate($value, $constraint)->count()) {
                $errors[] = "Le format de {$key} est invalide.";
            }
        }
        
        if (!empty($errors)) {
            return new JsonResponse(implode(' ', $errors), Response::HTTP_BAD_REQUEST);
        }

        $existingReservation = $this->reservationRepository->createQueryBuilder('r')
        ->where('r.date = :date')
        ->andWhere('r.espacedetravail = :espaceDeTravail')
        ->andWhere(
            ':heureDeDebut BETWEEN r.heureDeDebut AND r.heureDeFin OR ' .
            ':heureDeFin BETWEEN r.heureDeDebut AND r.heureDeFin OR ' .
            'r.heureDeDebut BETWEEN :heureDeDebut AND :heureDeFin OR ' .
            'r.heureDeFin BETWEEN :heureDeDebut AND :heureDeFin OR ' .
            'r.heureDeDebut = :heureDeFin OR ' .
            ':heureDeDebut = r.heureDeFin'
        )
        ->setParameters([
            'date' => $date,
            'espaceDeTravail' => $espace_de_travail,
            'heureDeDebut' => $heure_de_debut,
            'heureDeFin' => $heure_de_fin,
        ])
        ->getQuery()
        ->getResult();

        if ($existingReservation) {
            return new JsonResponse(['error' => 'Une réservation existe déjà à cette date et heures pour cet espace de travail.'], Response::HTTP_CONFLICT);
        }

        $prix_de_la_reservation = 15 ;
        $reservation = new Reservation(); 
        $reservation->setUser($user) ;
        // $reservation->setEspacedetravail($espace_de_travail->getEspaceDeTravail());
        $reservation->setEspacedetravail($espace_de_travail);
        $reservation->setFormule($formule);
        $reservation->setDate($date);
        $reservation->setHeureDeDebut($heure_de_debut);
        $reservation->setHeureDeFin($heure_de_fin);
        $reservation->setStatus(0);
        $reservation->setEffectif($effectif ?? 1);
        //Gokan doit me dire si il y a un calcul pour le prix de la reservation ou si c'est en fonction du forfait
        // $reservation->setPrixReservation((($heure_de_debut - $heure_de_fin)/30) * $prix_de_la_reservation);
        $reservation->setPrixReservation($prix_de_la_reservation + intval($formule->getPrix()));

        $this->em->persist($reservation); 
        $this->em->flush();
        //Je procède au paiement et je mets le statut à un
        //Je notifie l'utilisateur par email
    
        return new JsonResponse('Reservation enregistré veillez proceder au paiement', Response::HTTP_CREATED);
    }
    
    #[Route('/matrix_reservation/{id_espace_de_travail}/{date}', name: 'matrix_reservation_', methods:['GET'])]
    public function getReservationMatrix(string $id_espace_de_travail, string $date): JsonResponse
    {  
        //tester les deux paramètre de la route
        $espace_de_travail = $this->espaceDeTravailRepository->find($id_espace_de_travail);

        if (!$espace_de_travail) {
            return new JsonResponse(['error' => 'Espace de travail non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Obtenez les heures disponibles en fonction de la logique de vérification des conflits
        $availableHours = $this->calculateAvailableHours($date, $espace_de_travail);

        return new JsonResponse(['available_hours' => $availableHours], JsonResponse::HTTP_OK);
    }

    private function calculateAvailableHours($date, $espace_de_travail)
    {  
        $allHours = [];
        $currentHour = 9;
        while ($currentHour <= 23) {
            $allHours[] = sprintf('%02d:00', $currentHour);
            $currentHour++;
        }

        $existingReservations = $this->reservationRepository->createQueryBuilder('r')
        ->where('r.date = :date')
        ->andWhere('r.espacedetravail = :espaceDeTravail')
        ->setParameters([
            'date' => $date,
            'espaceDeTravail' => $espace_de_travail,
        ])
        ->getQuery()
        ->getResult();
        
        $availableHours = [];
         foreach ($allHours as $hour) {
            $isAvailable = true;
            foreach ($existingReservations as $reservation) {
                if ($hour >= $reservation->getHeureDeDebut() && $hour < $reservation->getHeureDeFin()) {
                    $isAvailable = false;
                    break; 
                }
            }
            if ($isAvailable) {
                $availableHours[] = $hour;
            }
        } 

        return $availableHours;
    }

    #[Route('/reservation/{id}', name: 'update_reservation', methods:['PUT'])]
    public function updateReservation(Request $request, int $id): JsonResponse
    {
        $reservation = $this->reservationRepository->findOneBy(['id' => $id]);

        if (!$reservation) {
            return new JsonResponse(['error' => 'La réservation n\'existe pas'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $date = $data["date"];
        $heure_de_debut = $data["heure_de_debut"]; 
        $heure_de_fin = $data["heure_de_fin"];
        $id_formule = (int)$data["id_formule"];
        $effectif = $data["effectif"];
        $id_espace_de_travail = (int)$data["id_espace_de_travail"];
        $type_espace_de_travail = $data["type_espace_de_travail"];

        $type_de_Formule = "" ;
        $espace_de_travail = "";
        $id_espace_de_travail_salon_principal = "" ;
        $id_espace_de_travail_salon_privee = "" ;

        if (!in_array($type_espace_de_travail,  ["private_room", "principal_room"])) {
            return new JsonResponse(['error' => "Le type d'espace de travail doit être l'un de ces deux éléments private_room ou principal_room"], 401);
        }

        // Vérifier si la réservation existe déjà à cette date

        $user = $this->userRepository->findOneBy(['id' => $id]);
        if (!$user) {
            return new JsonResponse(['error' => "L'ID de l'utilisateur ne correspond à aucun utilisateur"], 401);
        }

        // Vérifier l'existence de la formule
        $formule = $this->formuleRepository->findOneBy(['id' => $id_formule]);
        if (!$formule) {
            return new JsonResponse(['error' => "L'ID de la formule ne correspond à aucune formule"], 401);
        }

        if ($id_espace_de_travail) {

            if ($type_espace_de_travail == "principal_room") {
                $id_espace_de_travail_salon_principal = $this->salonPrincipalRepository->findOneBy(['espaceDeTravail' => $id_espace_de_travail]);
            }
            
            if ($type_espace_de_travail == "private_room" ) {
                $id_espace_de_travail_salon_privee = $this->salonPriveeRepository->findOneBy(['espaceDeTravail' => $id_espace_de_travail]);
            }
            
            if (!$id_espace_de_travail_salon_principal && !$id_espace_de_travail_salon_privee) {
                return new JsonResponse(['error' => "L'espace de travail n'existe pas"], 401);
            }
            $espace_de_travail = $id_espace_de_travail_salon_principal ?: $id_espace_de_travail_salon_privee;
        }

        $validator = Validation::createValidator();
        $errors = [];

        $constraints = [
            'date' => new DateTime(['format' => 'Y-m-d']),
            'heure_de_debut' => new DateTime(['format' => 'H:i']),
            'heure_de_fin' => new DateTime(['format' => 'H:i']),
        ];

        foreach ($constraints as $key => $constraint) {
            $value = $$key;
            if ($validator->validate($value, $constraint)->count()) {
                $errors[] = "Le format de {$key} est invalide.";
            }
        }

        if (!empty($errors)) {
            return new JsonResponse(implode(' ', $errors), Response::HTTP_BAD_REQUEST);
        }

        $reservation->setDate(\DateTime::createFromFormat('Y-m-d', $date));
        $reservation->setHeureDeDebut($heure_de_debut);
        $reservation->setHeureDeFin($heure_de_fin);
        $reservation->setFormule($formule);
        $reservation->setEspacedetravail($espace_de_travail->getEspaceDeTravail());
        $reservation->setStatus(0);
        $reservation->setEffectif($effectif ?? 1);
        $reservation->setPrixReservation((($heure_de_debut - $heure_de_fin)/30) * $prix_de_la_reservation = 1);

        $this->em->flush();

        return new JsonResponse('Réservation mise à jour avec succès');
    }

    #[Route('/reservation/{id}', name: 'get_reservation', methods:['GET'])]
    public function getReservation(int $id): JsonResponse
    {
        $reservation = $this->reservationRepository->findOneBy(['id' => $id]);

        if (!$reservation) {
            return new JsonResponse(['error' => 'La réservation n\'existe pas'], 404);
        }

        // Retourner les détails de la réservation au format JSON
        return new JsonResponse($reservation);
    }

    #[Route('/reservation/{id}', name: 'delete_reservation', methods:['DELETE'])]
    public function deleteReservation(int $id): JsonResponse
    {
        $reservation = $this->reservationRepository->findOneBy(['id' => $id]);

        if (!$reservation) {
            return new JsonResponse(['error' => 'La réservation n\'existe pas'], 404);
        }

        $this->em->remove($reservation);
        $this->em->flush();

        return new JsonResponse('Réservation supprimée avec succès');
    }

    #[Route('/reservations', name: 'list_reservations', methods:['GET'])]
    public function listReservations(EntityManagerInterface $entityManagerInterface, SerializerInterface $serializer): JsonResponse
    {
        $reservations = $entityManagerInterface->getRepository(Reservation::class)->findAll();
        $jsonReservations = $serializer->serialize($reservations, 'json', ['groups' => 'reservation']);
        // dump($jsonReservations); die ;
        
        // $reservations = $this->reservationRepository->findAll();

        return new JsonResponse($jsonReservations, Response::HTTP_OK, [], true);
    }

    #[Route('/user_reservation/{id_user}', name: 'user_reservation_', methods:['GET'])]
    public function getReservationUser(int $id_user, SerializerInterface $serializer): JsonResponse
    {   
        if (!is_int($id_user)) {
            throw new InvalidArgumentException('L\'ID utilisateur doit être un entier.');
        }
        
        $existingUser = $this->userRepository->find($id_user);
        if (!$existingUser) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        $reservations = $this->reservationRepository->findBy(['User' => $existingUser]);
        $jsonReservations = $serializer->serialize($reservations, 'json', ['groups' => 'reservation']);
        
        // Vérifier si des réservations existent
        if (empty($reservations)) {
            return new JsonResponse(['message' => 'Aucune réservation trouvée pour cet utilisateur.'], 200);
        }
    
        return new JsonResponse( $jsonReservations, Response::HTTP_OK, [], true);
    }

    #[Route('/cancel_reservation/{id_reservation}/{id_user}', name: 'cancel_reservation', methods: ['GET'])]
    public function cancelReservation(int $id_reservation, int $id_user): JsonResponse
    {
        $reservation = $this->reservationRepository->find($id_reservation);

        if (!$reservation) {
            return new JsonResponse(['error' => "La réservation avec l'ID {$id_reservation} n'existe pas."], Response::HTTP_NOT_FOUND);
        }

        // Effectuer ici les étapes nécessaires pour annuler la réservation
        // Par exemple, mettre à jour le statut de la réservation et envoyer une notification à l'utilisateur

        $reservation->setStatus(-1);
        $this->em->persist($reservation);
        $this->em->flush();

        // Envoyer une notification à l'utilisateur, par exemple, par e-mail
        return new JsonResponse('Réservation annulée avec succès.', Response::HTTP_OK);
    }   

    //Je dois écrire un autre programme qui teste si la durée de la reservation expire et là ,
    //je vais notifier le client a moin de 30 minutes

    //Je peux desactiver une reservation ( dans la cas ou une résevation es désactive quesque je dois faire ? )

    //Je dois écrire un programme permetant d'anulez une reservation et de proceder au remboursement ()

    // Je dois faire quelque graphe ( qui montre les tables qui son reserver, qui montre le plan de la salle)
    // ainsi le nombre de compte crée
    // les heures les plus saturé
    // 
}
