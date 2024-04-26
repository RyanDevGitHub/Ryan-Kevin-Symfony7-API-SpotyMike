<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;

class AlbumController extends AbstractController
{
    private $repository;
    private $tokenVerifier;
    private $entityManager;
    private $userUtils;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, TokenVerifierService $tokenVerifier, UserUtils $userUtils,UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->tokenVerifier = $tokenVerifier;
        $this->repository = $entityManager->getRepository(User::class);
        $this->userUtils = $userUtils;
        $this->userRepository = $userRepository;
    }

    #[Route('/album/{id}', name: 'app_album', methods: 'GET')]
    public function index(Request $request,$id ,JWSProviderInterface $jwtProvider): JsonResponse
    {

        if(!$this->tokenVerifier->checkToken($request)){
            return $this->json([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
            ]);
        }
        return $this->json([
            'message' => 'Welcome to your new controller!'.$id,
            'path' => 'src/Controller/AlbumController.php',
        ]);
    }
}