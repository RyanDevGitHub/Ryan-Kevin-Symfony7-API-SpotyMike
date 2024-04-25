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

    public function __construct(UserUtils $userUtils)
    {
        $this->userUtils = $userUtils;
    }

    #[Route('/artist', name: 'create_artist', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        // Check if a user is authenticated
        dd($tokenStorage);
        $user = $tokenStorage->getToken()->getUser();
        if (!($user instanceof User)) {
            return $this->json([
                'error' => true,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Get the user's email from the token
        $email = $user->getEmail();

        // Find the user entity based on the email
        $userRepository = $entityManager->getRepository(User::class);
        $userEntity = $userRepository->findOneBy(['email' => $email]);

        // Calculate user's age based on date of birth
        $dateOfBirth = $userEntity->getDateOfBirth();
        $today = new \DateTime();
        $age = $today->diff($dateOfBirth)->y;

        // Check if the user meets the minimum age requirement
        if (!$this->userUtils->isValidAge($age)) {
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
        if (!$this->userUtils->isLabelValid($requestData['label'])) {
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
               ->setLabel($requestData['label'])
               ->setUserIdUse($userEntity->getUserIdUser());


        // Set optional fields if provided
        if (isset($requestData['description'])) {
            $artist->setDescription($requestData['description']);
        }

        // Set the user associated with the artist
        $artist->setUser($userEntity);

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
}
