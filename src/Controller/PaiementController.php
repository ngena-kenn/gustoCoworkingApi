<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\FormuleRepository;
use App\Repository\ReservationRepository;
use App\Repository\SalonPrincipalRepository;
use App\Repository\SalonPriveeRepository;
use App\Repository\UserRepository;
use App\Service\StripeConfigService as ServiceStripeConfigService;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\LineItem;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request ;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaiementController extends AbstractController
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
    private $stripeConfigService;

    public function __construct(
        EntityManagerInterface $EntityManagerInterface , 
        MailerService $mailerService,
        RequestStack $requestStack,
        UserRepository $userRepository,
        ReservationRepository $reservationRepository,
        SalonPrincipalRepository $salonPrincipalRepository,
        SalonPriveeRepository $salonPriveeRepository,
        FormuleRepository $formuleRepository,
        ServiceStripeConfigService $stripeConfigService
        // readonly private string $clientSecret
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
        // Stripe::setApiKey("sk_test_51NViAtBaiI39BkXpoGFgFoPTVIhHsnNhlQCwSNfGLSTavsCcD7bu5bj7UAaOLe3sVeBtEnj3Q49M3c5MKVYPtS7L008bqji2Cy");
        // Stripe::setApiKey($this->clientSecret);
        Stripe::setApiVersion('2020-08-27');
        $this->stripeConfigService = $stripeConfigService;
    }
    
    #[Route('/showpaiement', name: 'app_paypal')]
    public function index(): Response
    {
        return $this->render('ddkdikd/index.html', [
            'controller_name' => 'PaypalController',
        ]);
    }

    #[Route('/paiement_success/{id_reservation}', name: 'paiement_stripe', methods:["GET"])]
    public function startPayment(int $id_reservation, UrlGeneratorInterface $urlGenerator)
    {   
        $reservation = $this->reservationRepository->findOneBy(['id' => $id_reservation]);
        if (!$reservation) {
            throw $this->createNotFoundException("La réservation avec l'ID $id_reservation n'a pas été trouvée.");
        }
   
        $reservationId = $reservation->getId();
        $itemPrice =  $reservation->getPrixReservation() ;

        // Récupérer les informations de l'article que le client achète (par exemple, le prix et la description)
        $itemPrice = 2000; // Remplacez cette valeur par le prix de l'article
        $formule = $reservation->getFormule()->getNomFormule();

        $itemDescription = "Réservez dès maintenant votre espace de travail avec notre " ;


        // Configurer la clé d'API Stripe dans la méthode
        Stripe::setApiKey($this->stripeConfigService->getStripeSecretKey());
    
        try {
            // Créer une nouvelle Session avec les informations du paiement
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'eur', 
                            'product_data' => [
                                'name' => $itemDescription,  
                            ],
                            'unit_amount' => $itemPrice, 
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => "http://localhost:8000/verifyPaiement",
                'cancel_url' => "http://localhost:8000/",
                'metadata' => [
                    'reservation_id' => $reservationId, 
                ],
            ]);
            
            $paymentUrl = $session->url;
            file_put_contents(__DIR__ . '/test.txt', $session->id);

            return new JsonResponse(['payment_url' => $paymentUrl]);
        } catch (\Stripe\Exception\ApiErrorException $e) {

            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/verifyPaiement', name: 'verifyPaiement_', methods:["GET", "POST"])]
    public function verifyPaiement(Request $request)
    {   
        $id = file_get_contents(__DIR__ .'/test.txt') ;
        
        $stripe = new \Stripe\StripeClient($this->stripeConfigService->getStripeSecretKey());
        
        $metadata = $stripe->checkout->sessions->retrieve(
            $id,
            []
        );
        
        $reservationId = $metadata->metadata->reservation_id ;
        $status = $metadata->status ;

        if (!($status === "complete")) {
            return new JsonResponse("Reservation éffectuer avec succès", Response::HTTP_OK);
        }

        $reservation = $this->reservationRepository->findOneBy(['id' => $reservationId]);
        $reservation->setStatus(1);
        $this->em->flush();
        //
        /**
         * Je dois notifier l'utilisateur par email 
         */
        // dd($metadata->metadata->reservation_id);
        // $data = json_decode($request->getContent(), true); 
        return new JsonResponse("Reservation éffectuer avec succès", Response::HTTP_OK);
    }

}
