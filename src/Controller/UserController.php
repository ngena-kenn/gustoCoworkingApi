<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\VerificationTokens;
use App\Repository\UserRepository;
use App\Services\AllServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Services\MailerService;

class UserController extends AbstractController
{
    private $userPasswordHasher;
    private $em;
    private $userRepository ;
    private $security ;
    private $mailerService;
    private $requestStack;

    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $EntityManagerInterface , 
        UserRepository $userRepository,
        Security $security,
        RequestStack $requestStack,
        MailerService $mailerService
    )
    {
        $this->userPasswordHasher = $userPasswordHasher ;
        $this->em = $EntityManagerInterface ;
        $this->userRepository = $userRepository ;
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    #[Route('user/login', name: 'login_user', methods:['POST'])]
    public function login(Request $request, Security $security, JWTTokenManagerInterface $JWTManager, ValidatorInterface $validator): JsonResponse{
        $data = json_decode($request->getContent(), true);
        $login = $data["email"] ;
        $password = $data["password"] ;

        if (!isset($login) || !isset($password)) {
            return new JsonResponse("Veuillez remplir tous les champs", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->userRepository->findOneBy(['email' => $login]);
        
        if (!$user) {
            return new JsonResponse(['error' => 'Invalid credentials.'], 401);
        }

        if (!$user->isStatus()) {
            return new JsonResponse(['error' => "Votre compte est inactif"], Response::HTTP_UNAUTHORIZED);
        }

        if (!password_verify($password, $user->getPassword())) {
            return new JsonResponse(['error' => 'Bad credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $JWTManager->create($user);
        return $this->json([
            'user'  => $user->getUserIdentifier(),
            "id" => $user->getId(),
            'token' => $token,
        ]);
    } 
    
    #[Route('/user/create', name: 'create_user', methods:['POST', 'GET'])]
    public function createUser(Request $request,  EntityManagerInterface $em, ValidatorInterface $validator, AllServices $allServices,  MailerService $mailerService): JsonResponse
    {
        $data = json_decode($request->getContent(), true); 
        
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['roles'])) {
            return new JsonResponse(['error' => 'Email et password sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Cet utilisateur existe déjà.'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();

        // Définition des valeurs pour les propriétés de l'objet $user
        $user->setEmail($data['email']);
        $user->setRoles($data["roles"]);
        $user->setStatus(true);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $data['password']));
        
        // Validation de l'objet $user après avoir défini les valeurs
        $errors = $validator->validate($user);
        
        if ($errors->count() > 0) {
            return $this->json(['error' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $token = $allServices->generateCode() ;

        // Insertion dans la table de vérification des codes
       $verificationTokens = new VerificationTokens();
        $verificationTokens->setUserId($user);
        $verificationTokens->setToken($token);

        // Envoi du code d'authentification
        $mailerService->sendMail(
            'nagorfonkoua@gmail.com',
            $data['email'],
            "Code de vérification", 
            "table/index.html",
            [
                'codeTwoFactor' => $token,
            ]
        ); 

        $em->persist($user);
        // $em->persist($verificationTokens);
        $em->flush();

        return new JsonResponse("Utilisateur crée avec succes", Response::HTTP_CREATED);
    }

    #[Route('/user/active', name: 'user_active_account', methods:['GET'])]
    public function activeAcount(Request $request){ 
        $request = $this->requestStack->getCurrentRequest();
        $url = $request->getUri();
        
        echo $url ;
        dump($url); die ; 

        $data = json_decode($request->getContent(), true);

        $enteredCode = $data["code"];
        
        if (!isset($enteredCode)){
            return new JsonResponse(['error' => 'Le code es obligatoire'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        dump($user) ;

        $isValid = $this->validateTwoFactorCode($enteredCode, $user);
        
        if ($isValid) {
            return new JsonResponse('Code de vérification valide. Utilisateur Crée avec succès.', Response::HTTP_CREATED);
        }
        
        return new JsonResponse('Code de vérification invalide.', Response::HTTP_BAD_REQUEST);
    }
    
    public function validateTwoFactorCode($enteredCode, $user)
    {
        dump($user);
        // $verificationTokensRepository = $this->em->getRepository(VerificationTokens::class);
        // $verificationToken = $verificationTokensRepository->findBy(['user_id' => $user]);
        $verificationTokensRepository = $this->em->getRepository(VerificationTokens::class);
    
        $entityB = $verificationTokensRepository->findOneBy(['user' => $user]);
        dump($entityB); die; 
        if ($enteredCode === $user->getCode()) {
   
            $user->setStatus(true) ;
            $this->em->persist($user);
            $this->em->flush();

            return true;
        }
        
        throw new AuthenticationException('Code à deux facteurs invalide');
    }

    #[Route('user/reset-password/request', name: 'reset_password_request', methods:['POST'])]
    public function fogotPassword(UserRepository $userRepository , 
    AllServices $allServices, SerializerInterface $serializer, Request $request) {

        $data = json_decode($request->getContent(), true);

        $existingEmail = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
  
        if (!isset($existingEmail)) {
            return $this->json(["error" => "Cet email n'existe pas."], Response::HTTP_BAD_REQUEST);
        }
        
        $generateCode = $allServices->generateCode() ;


        //envoie d'un mail avec le code 

        //insertion du code dans la base de donnée

        //récuperation du code et verification avec le code envoyé

        //recuperation du mots de passe et mise a jour. 


        dump($data) ;
        die ;


        $listUser = $userRepository->findAll();
        $jsonListUser = $serializer->serialize($listUser, 'json');
        
    
        return new JsonResponse($jsonListUser, Response::HTTP_OK, [], true);
    }

    #[Route('/api/user/listes_user', name: 'listes_user', methods: ['GET'])]
    public function listUser(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $listUser = $userRepository->findAll();
        $jsonListUser = $serializer->serialize($listUser, 'json', ['groups' => 'listUser']);

        return new JsonResponse($jsonListUser, Response::HTTP_OK, [], true);
    }

    #[Route('data_user/{userId}', name: 'data_user_', methods: ['GET'])]
    public function getDataUser(int $userId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'listUser']);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('api/user/update_password', name: 'update_password', methods:['PUT'])]
    public function update_password(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $passwordProtection = $data["password-protection"] ?? null;
        $newPassword = $data["new-password"] ?? null;
    
        if ($passwordProtection === null || $newPassword === null) {
            return new JsonResponse("Les champs requis sont manquants", Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        if (!isset($passwordProtection) || empty($passwordProtection) || !isset($newPassword) || empty($newPassword)) {
            return new JsonResponse("Veuillez remplir tous les champs", Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $user = $this->security->getUser();
       
        if ($user instanceof User) {
            if (!password_verify($passwordProtection, $user->getPassword())) {
                return new JsonResponse("L'ancien mot de passe n'est pas correct", Response::HTTP_UNAUTHORIZED);
            }
    
            $hashedPassword = $this->userPasswordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $this->em->flush();
            
            $this->mailerService->sendMail(
                'nagorfonkoua@gmail.com',
                $user->getEmail(),
                "Code de vérification", 
                "update_password/index.html",
                [
                    'message' => "Votre mots de passe a été mise a jour avec succès",
                ]
            );

            return new JsonResponse("Mise à jour effectuée avec succès", Response::HTTP_OK);
        }
    
        return new JsonResponse("Utilisateur non connecté", Response::HTTP_UNAUTHORIZED);
    }

    #[Route('api/user/update_user_profil/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em, int $id): JsonResponse
    {   
        $existingUser = $this->userRepository->find($id);
        if (!$existingUser) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }
    
        if ($currentUser->getId() !== $id) {
            throw new AccessDeniedHttpException('Vous n\'avez pas l\'autorisation de modifier cet utilisateur.');
        }
    
        $updatedFormule = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
        );
    
        $em->persist($updatedFormule);
        $em->flush();

        return new JsonResponse('Mise à jour du profil effectuée avec succès', Response::HTTP_OK, [], true);
    }    

    #[Route('api/user/disable_user_account/{id}', name: 'disable_user_account', methods:['PUT'])]
    public function disableUserAccount(EntityManagerInterface $em, $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur introuvable');
        }
        $user->setStatus(false);
        $em->flush();

        return new JsonResponse("Compte de l'utilisateur désactivé avec succès", Response::HTTP_OK);
    }

    #[Route('api/user/enable_user_account/{id}', name: 'enable_user_account', methods:['PUT'])]
    public function enableUserAccount( EntityManagerInterface $em, $id): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($id);
    
        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur introuvable');
        }

        $user->setStatus(true);
        $em->flush();
    
        return new JsonResponse("Compte de l'utilisateur réactivé avec succès", Response::HTTP_OK);
    }

    #[Route('api/user/delete_user_account/{id}', name: 'admin_delete_user_account', methods:['DELETE'])]
    public function delete_user_account(Request $request, EntityManagerInterface $em, $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user instanceof User) {
            return new JsonResponse("Utilisateur introuvable", Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse("Compte de l'utilisateur supprimé avec succès", Response::HTTP_OK);
    }
}