<?php

namespace App\Controller;

use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{

    private $repository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(User::class);
    }

    #[Route('/register', name: 'app_register_post', methods: ['POST', 'PUT'])]
    public function create(Request $request, UserPasswordHasherInterface $passwordHash): JsonResponse
    {

        $user = new User();
        $user->setFirstName($request->get('firstname'));
        $user->setName($request->get('firstname'));
        $user->setLastName($request->get('lastname'));
        $user->setEmail($request->get('email'));
        $user->setIdUser($request->get('email'));
        $user->setDateBirth(DateTime::createFromFormat('Y-m-d', $request->get('dateBirth')));
        $user->setCreateAt(new DateTimeImmutable());
        $user->setUpdateAt(new DateTimeImmutable());
        $password = $request->get('password');

        $hash = $passwordHash->hashPassword($user, $password); // Hash le password envoyez par l'utilisateur
        $user->setPassword($hash);
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