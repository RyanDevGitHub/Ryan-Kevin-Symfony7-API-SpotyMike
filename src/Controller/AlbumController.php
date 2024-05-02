<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\UserRepository;
use App\Service\AlbumUtils;
use App\Service\UserUtils;
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
use PhpParser\Node\Expr\Cast\Array_;

class AlbumController extends AbstractController

{
    private TokenVerifierService $tokenUtils;
    private ImageUtils $imageUtils;
    private PageUtils $pageUtils;
    private $repository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ImageUtils $imageUtils, TokenVerifierService $tokenUtils, PageUtils $pageUtils)
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
        $alb = [
            'id' => $id,
            'title' => 'Album ' . $id,
            'artist' => 'Artist ' . $id,
            'description' => 'This is the description for Album ' . $id,
        ];

        return $this->json([
            'alb' => $alb,
        ]);
    }

    #[Route('/album', name: 'create_album', methods: ['POST'])]
    public function createAlbum(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->tokenUtils->checkToken($request);
        if ($user === false) {
            return $this->json($this->tokenUtils->sendJsonErrorToken(null));
        }
        // Parse the JSON request body
        $requestData = json_decode($request->getContent(), true);

        // Check if all required fields are present in the request body
        $requiredFields = ['nom', 'categ', 'cover', 'visibility'];
        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field])) {
                return $this->json([
                    'error' => true,
                    'message' => "Les paramètres fournis sont invalides. Veuillez vérifier les données soumises."
                ], 400); // HTTP 400 Bad Request
            }
        }

        //verify visibilty
        $visibility = $requestData['visibility'];
        if ($visibility !== 0 && $visibility !== 1) {
            return $this->json([
                'error' => true,
                'message' => "la valeur du champ de visibility est invalide. Les valeurs autorisées sont 0 pour invisible,1 pour visible."
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
                    'message' => "Les catégorie ciblée sont invalide."
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
                'message' => "Ce titre est déjà pris. Veuillez en choisir un autre."
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
        if ($coverData === false || !$this->imageUtils->isValidImage($coverData)) {
            return $this->json($this->imageUtils->sendImageError(), 422);
        }

        // Create a new Album entity
        $alb = new Album();
        $alb->setNom($requestData['nom'])
            ->setCateg($stringCateg)
            ->setCover($requestData['cover'])
            ->setYear($requestData['annee'])
            ->setArtistUserIdUser($artist);

        // Persist the alb entity to the database
        if (isset($requestData['featuring'])) {
            $feat = $entityManager->getRepository(Artist::class)->findOneBy(['id' => $requestData['featuring']]);
            if (!$feat) {
                return $this->json([
                    'error' => true,
                    'message' => "vous devez entrer l'id d'un artiste existant "
                ], 404);
            }
            $alb->setFeaturing($feat);
        }
        $entityManager->persist($alb);
        $entityManager->flush();

        // Return a JSON response indicating success
        return $this->json([
            'error' => false,
            'message' => 'Album créé avec succès.',
            'iD' => $alb->getId()
        ], 201); // HTTP 201 Created
    }
    #[Route('/albums', name: 'get_albums', methods: ['GET'])]
    public function getAlbums(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        // token
        $user = $this->tokenUtils->checkToken($request);
        if ($user === false) {
            return $this->json($this->tokenUtils->sendJsonErrorToken(null));
        }
        $requestData = json_decode($request->getContent(), true);
        // pagination
        if (!isset($requestData['pagination'])) {
            return $this->json($this->pageUtils->sendPaginationError(), 400);
        }
        $Page = $requestData['pagination'];
        $totalAlbums = $entityManager->getRepository(Album::class)->count([]);
        if (isset($requestData['limite'])) {
            $limite = $requestData['limite'];
        } else {
            $limite = 5;
        }
        $pageinfo = $this->pageUtils->checkPagination($Page, $totalAlbums, $limite);
        if ($pageinfo === null) {
            return $this->json($this->pageUtils->sendPaginationError(), 400);
        }
        // return data
        $albums = $entityManager->getRepository(Artist::class)->findBy([], null, $limite, $pageinfo[0]);
        $serializedAlbums = [];
        foreach ($albums as $alb) {
            $serializedAlbums[] = $alb->serializer();
        }

        return $this->json([
            'error' => false,
            'albums' => $albums,
            'pagination' => $pageinfo[1]
        ], 200);
    }
    #[Route('/albums/search', name: 'app_search_albums', methods: ['GET'])]
    public function SearchAlbum(Request $request): JsonResponse
    {
        // Validation du token
        if (!$this->tokenUtils->checkToken($request)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise. Vous devez être connecté pour effectuer cette action'
            ], 401);
        }

        $requestData = $request->query->all();
        $validParams = ['nom', 'fullname', 'label', 'annee', 'featuring', 'category', 'limit'];
        foreach ($requestData as $key => $value) {
            if (!in_array($key, $validParams)) {

                return new JsonResponse([
                    'error' => true,
                    'message' => 'Les paramètres fournis sont invalides. Veuillez vérifier les données soumises.'
                ], 400);
            }
        }

        $categorie = ["rap", "r'n'b", "gospel", "soul", "country", "hip hop", "jazz", "le Mike"];
        $categ = "";
        if (isset($requestData['category'])) {
            $arrayCat = json_decode($requestData['category']);
            if (!$arrayCat || strlen($requestData['category']) <= 0) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Les catégorie ciblée sont invalide.'
                ], 400);
            }

            foreach ($arrayCat as $key => $value) {
                if (in_array($value, $categorie)) {
                    if ($key === count($arrayCat) - 1) { //check if it's the last ele ? without , : with ,
                        $categ .= $value;
                    } else {
                        $categ .= "$value,";
                    }
                } else {
                    return new JsonResponse([
                        'error' => true,
                        'message' => 'Les catégorie ciblée sont invalide.'
                    ], 400);
                }
            }
        }
        $feat = [];
        if (isset($requestData['featuring'])) {
            $feat = json_decode($requestData['featuring']);
            if (!$feat || !is_array($feat)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Les featuring ciblée sont invalide.'
                ], 400);
            }
        }
        $annee = null;
        if (isset($requestData['annee'])) {
            if (is_numeric($requestData['annee']) || intval($requestData['annee']) > 0) {
                $annee = $requestData['annee'];
            } else {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'L\'année n\'est pas valide.'
                ], 400);
            }
            $annee = $requestData['annee'];
        }
        $album = $this->repository->findAlbums();
        if ($album) {
            foreach ($album as $alb) {
                if (
                    ($annee != -1 && $alb->getYear() != $annee) ||
                    (isset($requestData['nom']) && $alb->getNom() != $requestData['nom']) ||
                    (isset($requestData['fullname']) && $alb->getArtistUserIdUser()->getFullname() != $requestData['fullname']) ||
                    (isset($requestData['labe']) && $alb->getLabel() != $requestData['labe']) ||
                    ($categ != "" && $alb->getCateg() != $categ) ||
                    ($feat != null && !in_array($alb->getArtistUserIdUser()->getFullname(), $feat))
                ) {
                    $fullname = $alb->getArtistUserIdUser()->getFullname();
                    continue;
                }


                $albumData = $alb->serializer();
                $songsData = [];
                foreach ($alb->getSongIdSong() as $song) {
                    $songsData[] = $song->serializer();
                }
                $albumData['songs'] = $songsData;

                $artistData = [
                    'firstname' => $alb->getArtistUserIdUser()->getUserIdUser()->getFirstname(),
                    'lastname' => $alb->getArtistUserIdUser()->getUserIdUser()->getLastname(),
                    'sexe' => ($alb->getArtistUserIdUser()->getUserIdUser()->getSexe() == 0) ? "Femme" : "Homme",
                    'dateBirth' => $alb->getArtistUserIdUser()->getUserIdUser()->getDateBirth()->format('d-m-Y'),
                    'createdAt' => $alb->getArtistUserIdUser()->getCreateAt()->format('Y-m-d'),
                ];
                $albumData['artist'] = $artistData;

                $serializedData[] = $albumData;
            }
        }
        $Page = 1;
        $itemsPage = 5;
        if (isset($requestData['Page'])) {
            if (!is_numeric($requestData['Page']) && !intval($requestData['Page']) > 0) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Le paramètre de pagination est invalide. Veuillez fournir un numéro de page valide.'
                ], 400);
            } else {
                $Page = $requestData['Page'];
                $itemsPage = 5;
            }
        }

        if (isset($requestData['limit'])) {
            if (!is_numeric($requestData['limit']) && !intval($requestData['limit']) > 0) {
                $itemsPage = $requestData['limit'];
            } else {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Le paramètre de pagination est invalide. Veuillez fournir un numéro de page valide.'
                ], 400);
            }
        }
        $totalPages = ceil(count($serializedData) / $itemsPage);

        if ($Page > $totalPages) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Aucun album trouvé pour la page demandée.'
            ], 404);
        }

        $startIndex = ($Page - 1) * $itemsPage;
        $dataForCurrentPage = array_slice($serializedData, $startIndex, $itemsPage);

        $pagination = [
            'Page' => (int)($Page),
            'totalPage' => $totalPages,
            'totalAlbums' => count($serializedData)
        ];

        return new JsonResponse([
            'error' => false,
            'albums' => $dataForCurrentPage,
            'pagination' => $pagination
        ],);
    }
}
