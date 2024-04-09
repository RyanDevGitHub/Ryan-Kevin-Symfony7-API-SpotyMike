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
                'message' => "Des champs obligatoires sont manquants.1",
            ]);
        }
        if (empty($request->get('lastname'))) {
            return $this->json([
                'error' => (true),
                'message' => "Des champs obligatoires sont manquants.2",
            ]);
        }
        if (empty($request->get('email'))) {
            return $this->json([
                'error' => (true),
                'message' => "Des champs obligatoires sont manquants.3",
            ]);
        }
        if (empty($request->get('dateBirth'))) {
            return $this->json([
                'error' => (true),
                'message' => "Des champs obligatoires sont manquants.4",
            ]);
        }
        if (empty($request->get('password'))) {
            return $this->json([
                'error' => (true),
                'message' => "Des champs obligatoires sont manquants.5",
            ]);
        }

        if (!$this->userUtils->isValidEmail($request->get('email'))) {
            return $this->json([
                'error' => (true),
                'message' => "Le format de l'email est invalide.",
            ]);
        }
        if (!$this->userUtils->isValidPassword($request->get('password'))) {
            return $this->json([
                'error' => (true),
                'message' => "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre, un caractère spécial et avoir 8 caractères minimum.",
            ]);
        }
        if (!$this->userUtils->DateOfBirthFormatIsValid($request->get('dateBirth'))) {
            return $this->json([
                'error' => (true),
                'message' => "Le format de la date de naissance est invalid. Le format attendu est JJ/MM/AAAA",
            ]);
        }
        if (!$this->userUtils->isValidAge($request->get('dateBirth'))) {
            return $this->json([
                'error' => (true),
                'message' => "L'utilisateur doit avoir avoir au moins 12 ans.",
            ]);
        }
        if (!is_null($request->get('tel')) && !$this->userUtils->isValidPhoneNumber($request->get('tel'))) {
            return $this->json([
                'error' => (true),
                'message' => "Le format du numéros de téléphone est invalide.",
            ]);
        }

        if (!is_null($request->get('sexe')) && !$this->userUtils->isValidSex($request->get('sexe'))) {
            return $this->json([
                'error' => (true),
                'message' => "La valeur du champ sexe est invalide.Les valeurs autorisées sont 0 pour Femme,1 pour Homme.",
            ]);
        }

        if (!$this->userUtils->IsAvailableEmail($request->get('email'))) {
            return $this->json([
                'error' => (true),
                'message' => "Cet email est déjà utilisé par un autre compte.",
            ]);
        }




        $user->setFirstName($request->get('firstname'));
        $user->setName($request->get('firstname'));
        $user->setLastName($request->get('lastname'));
        $user->setEmail($request->get('email'));
        $user->setIdUser(strval(mt_rand(1000, 9999)));
        $user->setDateBirth(new DateTime($request->get('dateBirth')));
        $user->setCreateAt(new DateTimeImmutable());
        $user->setUpdateAt(new DateTimeImmutable());


        $password = $request->get('password');
        $hash = $passwordHash->hashPassword($user, $password); // Hash le password envoyez par l'utilisateur
        $user->setPassword($hash);
        if (!is_null($request->get('tel'))) {
            $user->setTel($request->get('tel'));
        }
        if (!is_null($request->get('sexe'))) {
            $user->setSexe($request->get('sexe'));
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'error' => (false),
            'message' => "l'utilisateur a bien été créé avec succès",
            'user' => $user->serializer(),
        ]);
    }

    // use Symfony\Component\HttpFoundation\Request;
    #[Route('/login', name: 'app_login_post', methods: ['POST', 'PUT'])]
    public function login(Request $request, JWTTokenManagerInterface $JWTManager): JsonResponse
    {

        $user = $this->repository->findOneBy(["email" => "mike.sylvestre@lyknowledge.io"]);

        $parameters = json_decode($request->getContent(), true);


        return $this->json([
            'token' => $JWTManager->create($user),
            'data' => $request->getContent(),
            'message' => 'Welcome to MikeLand',
            'path' => 'src/Controller/LoginController.php',
        ]);
    }
}
