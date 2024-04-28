<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\UserRepository;
use App\Service\AlbumUtils;
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
    private $albumUtils;
    private $albumRepository;

    public function __construct(EntityManagerInterface $entityManager, TokenVerifierService $tokenVerifier, UserUtils $userUtils,AlbumRepository $albumRepository,AlbumUtils $albumUtils)
    {
        $this->entityManager = $entityManager;
        $this->tokenVerifier = $tokenVerifier;
        $this->repository = $entityManager->getRepository(User::class);
        $this->userUtils = $userUtils;
        $this->albumUtils = $albumUtils;
        $this->albumRepository = $albumRepository;
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

    #[Route('/album/{id}/song', name: 'app_album_created_song', methods: 'POST')]
    public function createSong  (Request $request,$id ,JWSProviderInterface $jwtProvider): JsonResponse
    {
        if(!$request->get("song")){
            return $this->json([
                'error' => true,
                'message' => 'Erreur sur le format du fichier qui n\'est pas pris en compte.',
            ],422);
        }
        
        $user = $this->tokenVerifier->checkToken($request) ;
        if(!$user){
            
            return $this->json([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action.',
            ],401);
        }
         //check user Id  = artist_user_id_user_id in album
         if(!$this->albumUtils->userCanAccesToAlbum($user , $id)){
            $album = $this->albumRepository->findById($id);
            if(!$album){
                return $this->json([
                    'error' => true,
                    'message' => "Aucun album trouvé correspondant au nom fourni.",
                ],404);
            }
            return $this->json([
                'error' => true,
                'message' => "Vous n'avez pas l'autorisation pour accéder à cet album.",
            ],403);
         }
         if(!$this->albumUtils->isBinarySong( $request->get('song'))){
            return $this->json([
                'error' => true,
                'message' => "Erreur sur le format du fichier qui n'est pas pris en compte.",
            ],422);
         }
         if(!$this->albumUtils->IsValidFileSize($request->get('song'))){
            return $this->json([
                'error' => true,
                'message' => "Le fichier envoyé est trop ou pas assez volumineux. Vous devez respecter la taille entre 1Mb et 7Mb.",
            ],422);
         }  

        return $this->json([
            'error' => false,
            'message' => 'Album mis à jour avec succès.',
            'idSong:'=>"xxx",
        ]);
    }
}