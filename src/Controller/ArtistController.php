<?php
namespace App\Controller;

use App\Entity\Artist;
use App\Entity\User;
use App\Service\UserUtils;
use App\Service\ImageUtils;
use App\Controller\TokenVerifierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;



class ArtistController extends AbstractController
{
    private UserUtils $userUtils;
    private TokenVerifierService $tokenUtils ;
    private ImageUtils $imageUtils;

    public function __construct(UserUtils $userUtils, TokenVerifierService $tokenUtils, ImageUtils $imageUtils )
    {
        $this->userUtils = $userUtils;
        $this->tokenUtils = $tokenUtils;
        $this->imageUtils = $imageUtils;
    }

    #[Route('/artist', name: 'create_artist', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        // Check if a user is authenticated
        $user = $this->tokenUtils->checkToken($request);
        if ($user === false) {
            return $this->json($this->tokenUtils->sendJsonErrorToken(null), 401);
        }

        // Parse JSON request body
        $requestData = $request->request->all();

        // Check if all required fields are present
        $requiredFields = ['fullname', 'label']; // Ensure 'avatar' field is provided
        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field])) {
                return $this->json([
                    'error' => true,
                    'message' => "Les champs fullname et le label et  sont obligatoires.",
                ], 400);
            }
        };
        // date verify
        $date = $user->getDateBirth();

        // Formater la date en une chaîne au format 'Y-m-d H:i:s'
        $formattedDate = $date->format('Y-m-d H:i:s');

        if (!$this->userUtils->isValidAge($formattedDate, 16)) {
            return $this->json([
                'error' => (true),
                'message' => "vous devez avoir au moins 16 ans pour etre artiste.",
            ],403);
        }
        dd($requestData['fullname']);
        $existingArtist = $entityManager->getRepository(Artist::class)->findOneBy(['fullname' => $requestData['fullname']]);
        if($exisitingArtist){
            return $this->json([
                'error' => (true),
                'message' => "ce nom d'artiste est deja pris. Velliez en choisir un autre.",
            ],403);
        }
        // Validate the avatar field
        $coverBase64 = $requestData['avatar'];
        $coverData = base64_decode($coverBase64);
        // Check if the decoding was successful
            if ($coverData === false || !$this->imageUtils-> isValidImage($coverData)) {
                return $this->json($this->imageUtils->sendImageError(), 422);
            } 

        else{
            $avatarBase64 = '';
        }
    ;


        // Get the user entity based on the authenticated user
        $email = $user->getEmail();
        $userRepository = $entityManager->getRepository(User::class);
        $userEntity = $userRepository->findOneBy(['email' => $email]);

        // Create a new Artist entity
        dd('ici');
        $artist = new Artist();
        $artist->setFullname($requestData['fullname'])
            ->setLabel($requestData['label'])
            ->setAvatar($avatarBase64) // Store
            ->setCreatedAt(new \DateTimeImmutable())
            ->setDateBegin(new \DateTimeImmutable())
            ->setDateEnd(new \DateTimeImmutable())
            ->setUserIdUser($userEntity);

        // Set optional description field if provided
        if (isset($requestData['description'])) {
            $artist->setDescription($requestData['description']);
        }

        // Persist the entity to the database
        $entityManager->persist($artist);
        $entityManager->flush();

        // Return the newly created artist as JSON response
        return $this->json([
            'success' => true,
            'message' => "Votre compte artiste a été créé avec succès. Bienvenue dans notre communauté d'artistes ! ",
            'artist_id' => $artist->getId(),
        ]);
    }
    #[Route('/artist', name: 'get_artist', methods: ['GET'])]
    public function getArtists(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        // Retrieve the current page number from the query parameters (default to 1 if not provided)
        $currentPage = $request->query->getInt('currentPage', 1);

        // Ensure currentPage is at least 1
        $currentPage = max(1, $currentPage);

        // Define the number of artists per page
        $pageSize = 5;

        // Calculate the offset based on the current page
        $offset = ($currentPage - 1) * $pageSize;

        // Retrieve the artists for the current page
        $artists = $entityManager->getRepository(Artist::class)->findBy([], null, $pageSize, $offset);
        $serializedArtists = [];
        // Serialize the artist data (example assuming you have a serializer method)
        foreach ($artists as $artist) {
            $serializedArtists[] = $artist->serializer();
        }
        // Calculate total number of artists for pagination info
        $totalArtists = $entityManager->getRepository(Artist::class)->count([]);

        // Calculate total number of pages
        $totalPages = ceil($totalArtists / $pageSize);

        // Construct pagination information
        $pagination = [
            'currentPage' => $currentPage,
            'pageSize' => $pageSize,
            'totalPages' => $totalPages,
            'totalArtists' => $totalArtists,
        ];

        // Return the response with the artists and pagination details
        return $this->json([
            'error' => false,
            'message' => 'Information des artistes récupérés avec succès',
            'pagination' => $pagination,
            'artists' => $serializedArtists,
        ]);
    }
    #[Route('/artist/{fullname}', name: 'get_artist_by_fullname', methods: ['GET'])]
    public function getArtistByFullName(string $fullname, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
    
        $user = $this->tokenUtils->checkToken($request);
        if($user === false){
            return $this->json($this->tokenUtils->sendJsonErrorToken(null));
        }
        // Find the artist by full name
        $artistRepository = $entityManager->getRepository(Artist::class);
        $artist = $artistRepository->findOneBy(['fullname' => $fullname]);

        // If artist not found, return 404
        if (!$artist) {
            return $this->json(['error' => true, 'message' => 'Artist not found'], 404);
        }

        // Serialize artist data
        $serializedArtist = $artist->serializer();

        return $this->json([
            'artist' => $serializedArtist,
        ]);
    }
    #[Route('/artist', name: 'update_artist', methods: ['PUT'])]
    public function updateArtist(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->tokenUtils->checkToken($request);
        if($user === false){
            return $this->json($this->tokenUtils->sendJsonErrorToken(null));
        }
        // Retrieve artist associated with the authenticated user
        $artist = $user->getArtist();
        if (!$artist) {
            $redirectUrl = $this->generateUrl('create_artist');
            $redirectRequest = $this->createRedirectRequest($request, $requestData);
            return $this->redirect($redirectUrl, JsonResponse::HTTP_FOUND, $redirectRequest);
        }

        // Parse JSON request body
        $requestData = json_decode($request->getContent(), true);

        // Validate new fullname (if provided)
        if (isset($requestData['fullname']) && $requestData['fullname'] !== $artist->getFullname()) {
            // Check if the new fullname is already taken
            $existingArtist = $entityManager->getRepository(Artist::class)->findOneBy(['fullname' => $requestData['fullname']]);
            if ($existingArtist) {
                return $this->json(['error' => true, 'message' => 'Ce nom d\'artiste est déjà pris. Veuillez en choisir un autre.'], 400);
            }
            // Update artist's fullname
            $artist->setFullname($requestData['fullname']);
        }

        // Update other fields if provided
        if (isset($requestData['label'])) {
            $artist->setLabel($requestData['label']);
        }
        if (isset($requestData['description'])) {
            $artist->setDescription($requestData['description']);
        }
        if (isset($requestData['avatar'])) {
            $avatarBase64 = $requestData['avatar'];
            $avatarData = base64_decode($avatarBase64);
        // Check if the decoding was successful
            if ($avatarData === false || !$this->imageUtils-> isValidImage($avatarData)) {
                return $this->json([
                    'error' => true,
                    'message' => "Le contenu fourni n'est pas une image JPEG ou PNG valide.",
                ]);
            }
            $artist->setAvatar($avatarBase64); 
        }
        $entityManager->persist($artist);
        $entityManager->flush();
            // Handle updating avatar if needed
            // Validate and update avatar logic here if required
        return $this->json([
            'error'=> false,
            'message' => "les informations de l'artiste ont ete mise jour avec succes."
        ]);
    }
    #[Route('/artist', name: 'delete_artist', methods: ['DELETE'])]
public function deleteArtist(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $user = $this->tokenUtils->checkToken($request);
    if ($user === false) {
        return $this->json($this->tokenUtils->sendJsonErrorToken(null));
    }

    // Retrieve artist associated with the authenticated user
    $artist = $user->getArtist();
    if (!$artist) {
        return $this->json([
            'error' => true,
            'message' => 'Compte artiste non trouvé. Aucune suppression n\'est nécessaire.'
        ], 404);
    }

    try {
        // Remove the artist entity
        $entityManager->remove($artist);
        $entityManager->flush();

        return $this->json([
            'error' => false,
            'message' => 'Compte artiste supprimé avec succès.'
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'error' => true,
            'message' => 'Une erreur est survenue lors de la suppression du compte artiste.'
        ], 500);
    }
}
}
