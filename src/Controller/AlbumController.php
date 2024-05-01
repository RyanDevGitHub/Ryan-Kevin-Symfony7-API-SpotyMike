<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\TokenVerifierService;
use Doctrine\ORM\EntityManagerInterface;

class AlbumController extends AbstractController

{
    private TokenVerifierService $tokenUtils ;
    
    public function __construct(EntityManagerInterface $entityManager, UserUtils $userUtils)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Album::class);
        $this->userUtils = $userUtils;
    }
    #[Route('/albums', name: 'get_albums', methods: ['GET'])]
    public function getAlbums(): JsonResponse
    {
        

        return $this->json([
            'albums' => $albums,
        ]);
    }

    #[Route('/album/{id}', name: 'get_album_by_id', methods: ['GET'])]
    public function getAlbumById(int $id): JsonResponse
    {
        // Dummy data for demonstration
        $album = [
            'id' => $id,
            'title' => 'Album ' . $id,
            'artist' => 'Artist ' . $id,
            'description' => 'This is the description for Album ' . $id,
        ];

        return $this->json([
            'album' => $album,
        ]);
    }

    #[Route('/albums', name: 'create_album', methods: ['POST'])]
    public function createAlbum(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->tokenUtils->checkToken($request);
        if($user === false){
            return $this->json($this->tokenUtils->sendJsonErrorToken(null));
        }
        // Parse the JSON request body
        $requestData = json_decode($request->getContent(), true);

        // Check if all required fields are present in the request body
        $requiredFields = [ 'nom', 'categ', 'cover', 'visibility'];
        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field])) {
                return $this->json([
                    'error' => true,
                    'message' => "les parmaetre soumins son invalide veillez verifier les donnees soumise."
                ], 400); // HTTP 400 Bad Request
            }
        }

        //verify visibilty
        $visibility = $requestData['visibility'];
        if($visibility !== 0 && $visibility !== 1){
            return $this->json([
                'error'=> true,
                'message' => "la valeur de la champ de visibility est invalide. les valeurs autoriser sont 0 pour invisible 1 visible"
            ], 400);
        }
        $validCategories = ['RNB', 'RAP'];
        // verify category
        $category = $requestData['categ'];

        if (!in_array($category, $validCategories)) {
            return $this->json([
                'error' => true,
                'message' => "La catégorie cible sont invalide"
            ], 400); // HTTP 400 Bad Request
        }

        //verify if name isnt already taken 
        $validCategories = $
        // Retrieve the artist based on the provided artist_id
        $artistId = $requestData['artist_id'];
        $artist = $entityManager->getRepository(Artist::class)->find($artistId);

        if (!$artist) {
            return $this->json([
                'error' => true,
                'message' => "L'artiste avec l'identifiant '$artistId' n'existe pas."
            ], 404); // HTTP 404 Not Found
        }

        // Create a new Album entity
        $album = new Album();
        $album->
            ->setNom($requestData['nom'])
            ->setCateg($requestData['categ'])
            ->setCover($requestData['cover'])
            ->setYear($requestData['year'])
            ->setArtistUserIdUser($artist);

        // Persist the album entity to the database
        $entityManager->persist($album);
        $entityManager->flush();

        // Return a JSON response indicating success
        return $this->json([
            'error' => false,
            'message' => 'Album créé avec succès.',
            'album' => $album->getId()
        ], 201); // HTTP 201 Created
    }
}
