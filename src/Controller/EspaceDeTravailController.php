<?php

namespace App\Controller;

use App\Entity\EspaceDeTravail;
use App\Entity\SalonPrincipal;
use App\Entity\SalonPrive;
use App\Entity\SalonPrivee;
use App\Repository\EspaceDeTravailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

// #[Route('/api/espaceDeTravail', name:'espaceDeTravail_')]
class EspaceDeTravailController extends AbstractController
{

    private $entityManagerInterface;
    private $espaceDeTravailRepository;
    private $serializer;

    public function __construct(Security $security, SerializerInterface $serializer,
    EntityManagerInterface $entityManagerInterface, EspaceDeTravailRepository $espaceDeTravailRepository)
    {
 
        $this->entityManagerInterface = $entityManagerInterface ;
        $this->espaceDeTravailRepository = $espaceDeTravailRepository;
        $this->serializer = $serializer;
    }

    #[Route('/api/espaceDeTravail/create_espacce_de_travail', name: 'create_espacce_de_travail', methods: ['POST'])]
    public function createEspaceDeTravail(Request $request): JsonResponse
    {
        $espaceDeTravailData = json_decode($request->getContent(), true);
        $type = $espaceDeTravailData["type"];
        $nomEspaceDeTravail = $espaceDeTravailData["nom"];
        $capacite = (int) $espaceDeTravailData['capacite'];

        
        // Enregistrement dans l'espace de travail
        $espaceDeTravail = new EspaceDeTravail();  
        $espaceDeTravail->setType($type);
        $this->entityManagerInterface->persist($espaceDeTravail);

        switch ($type) {
            case 'private_room':
                $existingNamePrivateRoom = $this->entityManagerInterface->getRepository(SalonPrivee::class)->findOneBy(['nomSalonPrivee' => $nomEspaceDeTravail]);
                
                if ($existingNamePrivateRoom) {
                    return new JsonResponse(['error' => "L'emplacement enregistré existe déjà pour le salon privé"], 400);
                }

                // Vérification du nombre d'enregistrements définis pour les salons privés
                $numberPrivateRoom = $this->entityManagerInterface->getRepository(SalonPrivee::class)->count(['capacite' => $capacite]);
                if (!in_array($capacite, [4, 6])) {
                    return new JsonResponse(['error' => 'Le salon privé doit avoir une capacité de 4 ou de 6'], 400);
                }
                
                if ($numberPrivateRoom >= 5) {
                    return new JsonResponse(['error' => "Vous avez atteint le nombre d'enregistrements définis pour les salons privés de " . $capacite . ' places'], 400);
                }
                
                // Création et enregistrement du salon privé
                $salonPrive = new SalonPrivee();
                $salonPrive->setCapacite($capacite === 4 ? 4 : 6);
                $salonPrive->setNomSalonPrivee($nomEspaceDeTravail);
                $salonPrive->setEspacedetravail($espaceDeTravail);
                $this->entityManagerInterface->persist($salonPrive);
                break;

            case 'principal_room':
                // Vérification du nom existant
                $existingNamePrincipalRoom = $this->entityManagerInterface->getRepository(SalonPrincipal::class)->findOneBy(['nomSalonPrincipal' => $nomEspaceDeTravail]);
                if ($existingNamePrincipalRoom) {
                    return new JsonResponse(['error' => "L'emplacement enregistré existe déjà pour le salon principal"], 400);
                }
                
                // Vérification du nombre d'enregistrements définis pour le salon principal
                $numberPrincipalRoom = $this->entityManagerInterface->getRepository(SalonPrincipal::class)->count([]);
                if ($numberPrincipalRoom >= 120) {
                    return new JsonResponse(['error' => "Vous avez atteint le nombre d'enregistrements définis pour le salon principal"], 400);
                }
                
                // Création et enregistrement du salon principal
                $salonPrincipal = new SalonPrincipal();
                $salonPrincipal->setNomSalonPrincipal($nomEspaceDeTravail);
                $salonPrincipal->setEspacedetravail($espaceDeTravail);
                $this->entityManagerInterface->persist($salonPrincipal);
                break;

            default:
                return new JsonResponse(['error' => 'Type d\'espace de travail invalide'], 400);
        }

        $this->entityManagerInterface->flush();

        // Vérification si aucun enregistrement dans les tables enfants
        if ($espaceDeTravail->getSalonPrivees()->isEmpty() && $espaceDeTravail->getSalonPrincipals() === null) {
            $this->entityManagerInterface->remove($espaceDeTravail);
            $this->entityManagerInterface->flush();
            return new JsonResponse('Aucun enregistrement n\'a été effectué pour les tables enfants, l\'enregistrement parent a été supprimé', Response::HTTP_OK);
        }

        return new JsonResponse('Emplacement créé avec succès', Response::HTTP_CREATED);
    }

    #[Route('/espaces_de_travail_prives', name: 'espaces_de_travail_prives', methods: ['GET'])]
    public function getEspacesDeTravailPrives(): JsonResponse
    {
        $salonPrivees = $this->entityManagerInterface->getRepository(SalonPrivee::class)->findAll();

        $formattedSalonPrivees = [];
        foreach ($salonPrivees as $salonPrivee) {
            $espaceDeTravail = $salonPrivee->getEspaceDeTravail();
            $formattedSalonPrivees[] = [
                'id' => $espaceDeTravail->getId(),
                'nomSalonPrivee' => $salonPrivee->getNomSalonPrivee(),
                'capacite' => $salonPrivee->getCapacite(),
            ];
        }

        return new JsonResponse($formattedSalonPrivees, Response::HTTP_OK);
    }

    #[Route('/espaces_de_travail_salon_principal', name: 'espaces_de_travail_salon_principal', methods: ['GET'])]
    public function getEspacesDeTravailSalonPrincipal(): JsonResponse
    {   

        $espacesDeTravailSalonPrincipals = $this->entityManagerInterface->getRepository(SalonPrincipal::class)->findAll();
        
        $formattedSalonPrincipal = [];
        foreach ($espacesDeTravailSalonPrincipals as $espacesDeTravailSalonPrincipals) {
            $espaceDeTravail = $espacesDeTravailSalonPrincipals->getEspaceDeTravail();
            $formattedSalonPrincipal[] = [
                'id' => $espaceDeTravail->getId(),
                'nomSalonPrincipal' => $espacesDeTravailSalonPrincipals->getNomSalonPrincipal(),
            ];
        }

        return new JsonResponse($formattedSalonPrincipal, Response::HTTP_OK);
    }
}
