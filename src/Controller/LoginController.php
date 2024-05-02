<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use DateTimeImmutable;
use App\Service\UserUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class LoginController extends AbstractController
{

    private $repository;
    private $entityManager;
    private $userUtils;

    public function __construct(EntityManagerInterface $entityManager, UserUtils $userUtils)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(User::class);
        $this->userUtils = $userUtils;
    }
    
    
    #[Route('/register', name: 'app_register_post', methods: ['POST', 'PUT'])]
    public function create(Request $request, UserPasswordHasherInterface $passwordHash): JsonResponse
    {
        $user = new User();
        if (empty($request->get('firstname'))) {
            return $this->json([
                'error' => (true),
                'message' => "Des champs obligatoires sont manquants.",
            ],400);
        }
        if (empty($request->get('lastname'))) {
            return $this->json([
                'error' => (true),
                'message' => "Des champs obligatoires sont manquants.",
            ],400);
        }
        if (empty($request->get('email'))) {
            return $this->json([
                'error' => (true),
                'message' => "Des champs obligatoires sont manquants.",
            ],400);
        }
        if (empty($request->get('dateBirth'))) {
            return $this->json([
                'error' => (true),
                'message' => "Des champs obligatoires sont manquants.",
            ],400);
        }
        if (empty($request->get('password'))) {
            return $this->json([
                'error' => (true),
                'message' => "Des champs obligatoires sont manquants.",
            ],400);
        }
    
        if (!$this->userUtils->isValidEmail($data['email'])) {
            return $this->json([
                'error' => true,
                'message' => "Le format de l'email est invalide.",
            ],400);
        }
    
        if (!$this->userUtils->isValidPassword($data['password'])) {
            return $this->json([
                'error' => true,
                'message' => "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre, un caractère spécial et avoir 8 caractères minimum.",
            ],400);
        }   
        if (!$this->userUtils->DateOfBirthFormatIsValid($request->get('dateBirth'))) {
            return $this->json([
                'error' => (true),
                'message' => "Le format de la date de naissance est invalide. Le format attendu est JJ/MM/AAAA.",
            ],400);
        }
    
        if (!$this->userUtils->isValidAge($data['dateBirth'])) {
            return $this->json([
                'error' => (true),
                'message' => "L'utilisateur doit avoir au moins 12 ans.",
            ],400);
        }
    
        $tel = $data['tel'] ?? null;
        if (!is_null($tel) && !$this->userUtils->isValidPhoneNumber($tel)) {
            return $this->json([
                'error' => (true),
                'message' => "Le format du numéro de téléphone est invalide.",
            ],400);
        }
    
        $sexe = $data['sexe'] ?? null;
        if (!is_null($sexe) && !$this->userUtils->isValidSex($sexe)) {
            return $this->json([
                'error' => (true),
                'message' => "La valeur du champ sexe est invalide. Les valeurs autorisées sont 0 pour Femme, 1 pour Homme.",
            ],400);
        }else{
            $user->setSexe(1);
        }
    
        if (!$this->userUtils->IsAvailableEmail($data['email'])) {
            return $this->json([
                'error' => true,
                'message' => "Cet email est déjà utilisé par un autre compte.",
            ],409);
        }

        $user->setFirstName($request->get('firstname'));
        $user->setName($request->get('firstname'));
        $user->setLastName($request->get('lastname'));
        $user->setEmail($request->get('email'));
        $user->setIdUser(strval(mt_rand(1000, 9999)));
        $dateOfBirth = DateTimeImmutable::createFromFormat('d/m/Y', $request->get('dateBirth'));
        $dateOfBirth->format('dd/mm/yyyy');
        $user->setDateBirth($dateOfBirth);
        $user->setCreateAt(new DateTimeImmutable());
        $user->setUpdateAt(new DateTimeImmutable());

        $password = $request->get('password');
        $hash = $passwordHash->hashPassword($user, $password); // Hash le password envoyez par l'utilisateur
        $user->setPassword($hash);
        if (!is_null($request->get('tel'))) {
            $user->setTel($request->get('tel'));
        }
        if (!is_null($sexe)) {
            $user->setSexe($sexe);
        }
        $user->setDisable(0);
    
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    
        return $this->json([
            'error' => (false),
            'message' => "l'utilisateur a bien été créé avec succès",
            'user' => $user->serializerRegister(),
        ],201);
    }
    
    
    // use Symfony\Component\HttpFoundation\Request;

    #[Route('/login', name: 'app_login_post', methods: ['POST', 'PUT'])]
    public function login(Request $request, JWTTokenManagerInterface $JWTManager, UserPasswordHasherInterface $passwordHash): JsonResponse
    {

        if (empty($request->get('password')) || empty($request->get('email'))) {
            return $this->json([
                'error' => (true),
                'message' => "Email/password manquants.",
            ],400);
        }
        if (!$this->userUtils->isValidEmail($request->get('email'))) {
            return $this->json([
                'error' => (true),
                'message' => "Le format de l'email est invalide.",
            ],400);
        }

        if (!$this->userUtils->isValidPassword($request->get('password'))) {
            return $this->json([
                'error' => (true),
                'message' => "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre, un caractère spécial et avoir 8 caractères minimum.",
            ],400);
        }
        /** @var User|null $user */
        $user = $this->repository->findOneBy(["email" => $request->get('email')]);
        if (!$user) {
            return $this->json([
                'error' => (true),
                'message' => "Aucun compte existe avec cette adresse email.",
            ]);
        }
        if(!$passwordHash->isPasswordValid($user,$request->get('password'))){
            $time = $this->userUtils->logFailedLoginAttempt($request->get('email'));
            if ($time == false) {
                return $this->json([
                    'error' => (true),
                    'message' => "Mot de passe incorrecte.",
                ]);
            }else{
                return $this->json([
                    'error' => (true),
                    'message' => "Trop de tentatives de connexion (5 max). Veuillez réessayer ultérieurement - 2 min d'attente.",
                ],429);
            }
        }
        if ($this->userUtils->IsDisableAccount($user)) {
            return $this->json([
                'error' => (true),
                'message' => "Le compte n'est plus actif ou est suspendu.",
            ],403);
        }

        $parameters = json_decode($request->getContent(), true);


        return $this->json([
            'error' => false,
            'message' => "L'utilisateur a été authentifié succès",
            'user' => $user->serializerLogin(),
            'token' => $JWTManager->create($user),
        ]);
    }

    #[Route('/account-deactivation', name: 'app_disabled_account', methods: 'DELETE')]
    public function disabledAccount(Request $request, JWTTokenManagerInterface $JWTManager, TokenVerifierService $tokenVerifierService): JsonResponse
    {
        $token = $tokenVerifierService->checkToken($request);
        
        if (!$token) {
            return $this->json([
                'error' => (true),
                'message' => "Authentification requise. Vous devez être connecté pour effectuer cette action.",

            ],401);
        } else {
            if ($this->userUtils->IsDisableAccount($token)) {
                return $this->json([
                    'error' => (true),
                    'message' => "Le compte est déjà désactivé.",
                ],409);
            } else {
                $token->setDisable(1);
                $this->entityManager->persist($token);
                $this->entityManager->flush();
                return $this->json([
                    'success' => (true),
                    'message' => "Votre compte a été désactivé avec succès. Nous sommes désolés de vous voir partir.",
                ]);
            }
        }
    }
}
