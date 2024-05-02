<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\TokenVerifierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ImageUtils;
use App\Service\PageUtils;
use App\Entity\Album;
use App\Entity\Artist;




class AlbumController extends AbstractController

{
    private TokenVerifierService $tokenUtils ;
    private ImageUtils $imageUtils;
    private PageUtils $pageUtils;
    
    public function __construct(EntityManagerInterface $entityManager, ImageUtils $imageUtils,TokenVerifierService $tokenUtils, PageUtils $pageUtils)
    {
        $this->entityManager = $entityManager;
        $this->pageUtils = $pageUtils;
        $this->repository = $entityManager->getRepository(Album::class);
        $this->tokenUtils = $tokenUtils;
        $this->imageUtils = $imageUtils;



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
        $validCategories = ['RNB', 'RAP']; // Example list of valid categories
        $categories = $requestData['categ'];

        foreach ($categories as $category) {
            if (!in_array($category, $validCategories)) {
                return $this->json([
                    'error' => true,
                    'message' => "Les catégories ciblées sont invalides."
                ], 400); // HTTP 400 Bad Request
            }
        }

        $stringCateg = implode(', ', $categories);
        //verify if name isnt already taken 
        $nom = $requestData['nom'];

        // Check if an artist with the same fullname already exists in the database
        $existingAlbums = $this->entityManager->getRepository(Album::class)->findOneBy(['nom' => $nom]);

        if ($existingAlbums  !== null) {
            return $this->json([
                'error' => true,
                'message' => "ce titre est deja pris veillez en choisir un autre."
            ], 409); // HTTP 409 Conflict
        }
        // Retrieve the artist based on the provided artist_id
        $artistId = $user->getId();

        $artist = $entityManager->getRepository(Artist::class)->findOneBy(['User_idUser' => $artistId]);

        if (!$artist) {
            return $this->json([
                'error' => true,
                'message' => "vous devez etre un artiste pour creer un albums "
            ], 404); // HTTP 404 Not Found
        }
        // Check image

        $coverBase64 = $requestData['cover'];
        $coverData = base64_decode($coverBase64);
        // Check if the decoding was successful
            if ($coverData === false || !$this->imageUtils-> isValidImage($coverData)) {
                return $this->json($this->imageUtils->sendImageError(), 422);
            } 

        // Create a new Album entity
        $album = new Album();
        $album->
             setNom($requestData['nom'])
            ->setCateg($stringCateg)
            ->setCover($requestData['cover'])
            ->setYear($requestData['year'])
            ->setArtistUserIdUser($artist);

        // Persist the album entity to the database
        if(isset($requestData['featuring'])){
            $feat = $entityManager->getRepository(Artist::class)->findOneBy(['id' => $requestData['featuring']]);
            if(!$feat){
                return $this->json([
                    'error' => true,
                    'message' => "vous devez entrer l'id d'un artiste existant "
                ], 404);
            }
            $album->setFeaturing($feat);
        }
        $entityManager->persist($album);
        $entityManager->flush();

        // Return a JSON response indicating success
        return $this->json([
            'error' => false,
            'message' => 'Album créé avec succès.',
            'album' => $album->getId()
        ], 201); // HTTP 201 Created
    }
    #[Route('/albums', name: 'get_albums', methods: ['GET'])]
    public function getAlbums(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        
        // token
        $user = $this->tokenUtils->checkToken($request);
        if($user === false){
            return $this->json($this->tokenUtils->sendJsonErrorToken(null));
        }
        $requestData = json_decode($request->getContent(), true);
        // pagination
        if (!isset($requestData['pagination'])){
            return $this->json($this->pageUtils->sendPaginationError(), 400);
        }
        $currentPage = $requestData['pagination'];
        $totalAlbums = $entityManager->getRepository(Album::class)->count([]);
        if (isset($requestData['limite']))
        {
            $limite = $requestData['limite'];
        }
        else
        {
            $limite = 5;
        }
        $pageinfo = $this->pageUtils->checkPagination($currentPage, $totalAlbums, $limite);
        if($pageinfo === null){
            return $this->json($this->pageUtils->sendPaginationError(), 400);
        }
        // return data
        $albums = $entityManager->getRepository(Artist::class)->findBy([], null, $limite, $pageinfo[0]);
        $serializedAlbums = [];
        foreach ($albums as $album) {
            $serializedAlbums[] = $album->serializer();
        }
        
        return $this->json([
            'error' => false,
            'albums' => $albums,
            'pagination' => $pageinfo[1]
        ], 200);
    }

}
