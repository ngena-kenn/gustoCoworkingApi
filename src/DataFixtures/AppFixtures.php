<?php
// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\EspaceDeTravail;
use App\Entity\Formule;
use App\Entity\SalonPrincipal;
use App\Entity\SalonPrivee;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    private $em;
    private $userRepository ;

    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $EntityManagerInterface , 
        // UserRepository $userRepository,

    )
    {
        $this->userPasswordHasher = $userPasswordHasher ;
        $this->em = $EntityManagerInterface ;
    }
    
    public function load(ObjectManager $manager)
    {   
        //create user
        $usersData = [
            [
                'email' => 'user1@gmail.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password',
                'status' => true,
            ],
            [
                'email' => 'user2@gmail.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password',
                'status' => true,
            ],
            [
                'email' => 'user3@gmail.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password',
                'status' => true,
            ],
        ];

        foreach ($usersData as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
            $user->setStatus($userData['status']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $userData['password']));

            $manager->persist($user);
        }

        //create formule
        $formulesData = [
            [
                'nom_formule' => '...',
                'descriptionFormule' => "..",
                'description2' => '...',
                'prix' => "...",
            ],
            [
                'nom_formule' => '...',
                'descriptionFormule' => "..",
                'description2' => '...',
                'prix' => "...",
            ],
            [
                'nom_formule' => '...',
                'descriptionFormule' => "..",
                'description2' => '...',
                'prix' => "...",
            ],
        ];

        foreach ($formulesData as $formuleData) {
            $formule = new Formule();

            $formule->setNomFormule($formuleData['nom_formule']);
            $formule->setDescriptionFormule($formuleData['descriptionFormule']);
            $formule->setDescription2($formuleData['description2']);
            $formule->setPrix($formuleData['prix']);

            $manager->persist($formule);
        }

        // create espace de travail salon principal
        $espaceDeTravailData = [];
        for ($i = 1; $i <= 120; $i++) {
            $espaceDeTravailData[] = [
                'type' => 'principal_room',
                'nom' => 'A' . $i,
                'capacite' => 1,
            ];
        }

        foreach ($espaceDeTravailData as $data) {
            $type = $data["type"];
            $nomEspaceDeTravail = $data["nom"];
            $capacite = (int) $data['capacite'];

            $espaceDeTravail = new EspaceDeTravail();
            $espaceDeTravail->setType($type);
            $manager->persist($espaceDeTravail);

            if ($type === 'principal_room') {
                $existingNamePrincipalRoom = $manager->getRepository(SalonPrincipal::class)->findOneBy(['nomSalonPrincipal' => $nomEspaceDeTravail]);

                if ($existingNamePrincipalRoom) {
                    continue;
                }

                $salonPrincipal = new SalonPrincipal();
                $salonPrincipal->setNomSalonPrincipal($nomEspaceDeTravail);
                $salonPrincipal->setEspacedetravail($espaceDeTravail);
                $manager->persist($salonPrincipal);
            }
        }

        // create espace de travail salon privé
        $espaceDeTravailDataPrivate_room = [
            [
                'type' => 'private_room',
                'nom' => 'Salle Privée 1',
                'capacite' => 4,
            ],
            [
                'type' => 'principal_room',
                'nom' => 'Salle Principale 1',
                'capacite' => 4,
            ],
            [
                'type' => 'private_room',
                'nom' => 'Salle Privée 1',
                'capacite' => 4,
            ],
            [
                'type' => 'principal_room',
                'nom' => 'Salle Principale 1',
                'capacite' => 4,
            ],
            [
                'type' => 'private_room',
                'nom' => 'Salle Privée 1',
                'capacite' => 5,
            ],
            [
                'type' => 'principal_room',
                'nom' => 'Salle Principale 1',
                'capacite' => 5,
            ],
            [
                'type' => 'private_room',
                'nom' => 'Salle Privée 1',
                'capacite' => 5,
            ],
            [
                'type' => 'principal_room',
                'nom' => 'Salle Principale 1',
                'capacite' => 5,
            ],
        ];

        foreach ($espaceDeTravailDataPrivate_room as $data) {
            $espaceDeTravail = new EspaceDeTravail();
            $espaceDeTravail->setType($data['type']); 
            $manager->persist($espaceDeTravail);

            if ($data['type'] === 'private_room') {
                $salonPrive = new SalonPrivee();
                $salonPrive->setCapacite($data['capacite']);
                $salonPrive->setNomSalonPrivee($data['nom']);
                $salonPrive->setEspacedetravail($espaceDeTravail);
                $manager->persist($salonPrive);
            } 
        }

        $manager->flush();
    }
}
