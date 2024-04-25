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
        $data = json_decode($request->getContent(), true);
    
        $requiredFields = ['firstname', 'lastname', 'email', 'dateBirth', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->json([
                    'error' => true,
                    'message' => "Des champs obligatoires sont manquants.",
                ]);
            }
        }
    
        if (!$this->userUtils->isValidEmail($data['email'])) {
            return $this->json([
                'error' => true,
                'message' => "Le format de l'email est invalide.",
            ]);
        }
    
        if (!$this->userUtils->isValidPassword($data['password'])) {
            return $this->json([
                'error' => true,
                'message' => "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre, un caractère spécial et avoir 8 caractères minimum.",
            ]);
        }
    
        if (!$this->userUtils->DateOfBirthFormatIsValid($data['dateBirth'])) {
            return $this->json([
                'error' => true,
                'message' => "Le format de la date de naissance est invalide. Le format attendu est JJ/MM/AAAA",
            ]);
        }
    
        if (!$this->userUtils->isValidAge($data['dateBirth'])) {
            return $this->json([
                'error' => true,
                'message' => "L'utilisateur doit avoir avoir au moins 12 ans.",
            ]);
        }
    
        $tel = $data['tel'] ?? null;
        if (!is_null($tel) && !$this->userUtils->isValidPhoneNumber($tel)) {
            return $this->json([
                'error' => true,
                'message' => "Le format du numéro de téléphone est invalide.",
            ]);
        }
    
        $sexe = $data['sexe'] ?? null;
        if (!is_null($sexe) && !$this->userUtils->isValidSex($sexe)) {
            return $this->json([
                'error' => true,
                'message' => "La valeur du champ sexe est invalide. Les valeurs autorisées sont 0 pour Femme, 1 pour Homme.",
            ]);
        }
    
        if (!$this->userUtils->IsAvailableEmail($data['email'])) {
            return $this->json([
                'error' => true,
                'message' => "Cet email est déjà utilisé par un autre compte.",
            ]);
        }
    
        $user = new User();
        $user->setFirstName($data['firstname'])
             ->setName($data['firstname'])
             ->setLastName($data['lastname'])
             ->setEmail($data['email'])
             ->setIdUser(strval(mt_rand(1000, 9999)))
             ->setDateBirth(new DateTime($data['dateBirth']))
             ->setCreateAt(new DateTimeImmutable())
             ->setUpdateAt(new DateTimeImmutable())
             ->setPassword($passwordHash->hashPassword($user, $data['password']));
    
        if (!is_null($tel)) {
            $user->setTel($tel);
        }
        if (!is_null($sexe)) {
            $user->setSexe($sexe);
        }
        $user->setDisable(0);
    
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    
        return $this->json([
            'error' => false,
            'message' => "L'utilisateur a bien été créé avec succès",
            'user' => $user->serializer(),
        ]);
    }
    
    
    // use Symfony\Component\HttpFoundation\Request;
    #[Route('/login2', name: 'app_login_post', methods: ['POST', 'PUT'])]
    public function login(Request $request, JWTTokenManagerInterface $JWTManager, UserPasswordHasherInterface $passwordHash): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
    
        if (empty($requestData['password']) || empty($requestData['email'])) {
            return $this->json([
                'error' => true,
                'message' => "Email/password manquants.",
            ]);
        }
    
        $email = $requestData['email'];
        $password = $requestData['password'];
    
        if (!$this->userUtils->isValidEmail($email)) {
            return $this->json([
                'error' => true,
                'message' => "Le format de l'email est invalide.",
            ]);
        }
    
        if (!$this->userUtils->isValidPassword($password)) {
            return $this->json([
                'error' => true,
                'message' => "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre, un caractère spécial et avoir 8 caractères minimum.",
            ]);
        }
    
        /** @var User|null $user */
        $user = $this->repository->findOneBy(["email" => $email]);
    
        if (!$user) {
            return $this->json([
                'error' => true,
                'message' => "Aucun compte existe avec cette adresse email.",
            ]);
        }
    
        if (!$passwordHash->isPasswordValid($user, $password)) {
            return $this->json([
                'error' => true,
                'message' => "Mot de passe incorrect.",
            ]);
        }
    
        if ($this->userUtils->IsDisableAccount($user)) {
            return $this->json([
                'error' => true,
                'message' => "Le compte n'est plus actif ou est suspendu.",
            ]);
        }
    
        return $this->json([
            'error' => false,
            'message' => "L'utilisateur a été authentifié avec succès",
            'user' => $user->serializer(),
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

            ]);
        } else {
            if ($this->userUtils->IsDisableAccount($token)) {
                return $this->json([
                    'error' => (true),
                    'message' => "Le compte est déjà désactivé.",
                ]);
            } else {
                $token->setDisable(1);
                $this->entityManager->persist($token);
                $this->entityManager->flush();
                return $this->json([
                    'success' => (true),
                    'message' => "Votre compte a été désactivé avec succès.Nous sommes désolés de vous voir partir.",
                ]);
            }
        }
    }
}
