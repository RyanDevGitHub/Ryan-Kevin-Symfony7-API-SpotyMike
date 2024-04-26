<?php
namespace App\Controller;

use App\Entity\Artist;
use App\Entity\User;
use App\Service\UserUtils;
use App\Controller\TokenVerifierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ArtistController extends AbstractController
{
    private UserUtils $userUtils;
    private TokenVerifierService $tokenUtils ;

    public function __construct(UserUtils $userUtils, TokenVerifierService $tokenUtils)
    {
        $this->userUtils = $userUtils;
        $this->tokenUtils = $tokenUtils;
    }

    #[Route('/artist', name: 'create_artist', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        // Check if a user is authenticated
        $user = $this->tokenUtils->checkToken($request);
        if($user === false){
            return $this->json($this->tokenUtils->sendJsonErrorToken(null));
        }

        // Get the user's email from the token
        $email = $user->getEmail();

        // Find the user entity based on the email
        $userRepository = $entityManager->getRepository(User::class);
        $userEntity = $userRepository->findOneBy(['email' => $email]);

        // Calculate user's age based on date of birth
        $dateOfBirth = $userEntity->getDateBirth();
        // Check if the user meets the minimum age requirement
        if (!$this->userUtils->isValidAge($dateOfBirth)) {
            return $this->json([
                'error' => true,
                'message' => 'Vous devez avoir au moins 16 ans pour être artiste.',
            ]);
        }

        // Parse JSON request body
        $requestData = json_decode($request->getContent(), true);

        // Check if all required fields are present
        $requiredFields = ['fullname', 'label'];
        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field])) {
                return $this->json([
                    'error' => true,
                    'message' => "L'id du label et le fullname sont obligatoires.",
                ]);
            }
        }

        // Check if the label ID format is valid
        if (!$this->userUtils->isValidLabel($requestData['label'])) {
            return $this->json([
                'error' => true,
                'message' => "Le format de l'id du label est invalide.",
            ]);
        }

        // Check if the full name is already used in the database
        $artistRepository = $entityManager->getRepository(Artist::class);
        $existingArtist = $artistRepository->findOneBy(['fullname' => $requestData['fullname']]);
        if ($existingArtist !== null) {
            return $this->json([
                'error' => true,
                'message' => "Ce nom d'artiste est déjà pris. Veuillez en choisir un autre.",
            ]);
        }

        // Create a new Artist entity
        $artist = new Artist();
        $artist->setFullname($requestData['fullname'])
               ->setLabel($requestData['label']);


        // Set optional fields if provided
        if (isset($requestData['description'])) {
            $artist->setDescription($requestData['description']);
        }

        // Set the user associated with the artist
        $artist->setUserIdUser($userEntity);

        // Persist the entity to the database
        $entityManager->persist($artist);
        $entityManager->flush();

        // Return the newly created artist as JSON response
        return $this->json([
            'success' => true,
            'message' => "Votre compte artiste a ete cree avec succes. Bienvenue dans notre communaute d'artistes ! ",
            'artist_id' => $artist->serializer(),
        ]);
    }
    #[Route('/artists', name: 'get_artists', methods: ['GET'])]
    public function getArtists(Request $request, EntityManagerInterface $entityManager): JsonResponse
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

        // Serialize the artist data (example assuming you have a serializer method)
        $serializedArtists = array_map(function ($artist) {
            return $artist->serialize(); // Implement your serialization logic here
        }, $artists);

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
    #[Route('/artists/{fullname}', name: 'get_artist_by_fullname', methods: ['GET'])]
    public function getArtistByFullName(string $fullname, EntityManagerInterface $entityManager): JsonResponse
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
}
