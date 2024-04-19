<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private $repository;
    private $tokenVerifier;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, TokenVerifierService $tokenVerifier)
    {
        $this->entityManager = $entityManager;
        $this->tokenVerifier = $tokenVerifier;
        $this->repository = $entityManager->getRepository(User::class);
    }

    #[Route('/user', name: 'user_post', methods: 'POST')]
    public function create(Request $request, UserPasswordHasherInterface $passwordHash): JsonResponse
    {

        $user = new User();
        $user->setName("Mike");
        $user->setEmail("Mike");
        $user->setIdUser("Mike");
        $user->setCreateAt(new DateTimeImmutable());
        $user->setUpdateAt(new DateTimeImmutable());
        $password = "Mike";

        $hash = $passwordHash->hashPassword($user, $password);
        $user->setPassword($hash);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'isNotGoodPassword' => ($passwordHash->isPasswordValid($user, 'Zoubida')),
            'isGoodPassword' => ($passwordHash->isPasswordValid($user, $password)),
            'user' => $user->serializer(),
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route('/user', name: 'user_put', methods: 'PUT')]
    public function update(Request $request): JsonResponse
    {

        $dataMiddellware = $this->tokenVerifier->checkToken($request);
        if (gettype($dataMiddellware) == 'boolean') {
            return $this->json($this->tokenVerifier->sendJsonErrorToken($dataMiddellware));
        }
        $user = $dataMiddellware;

        dd($user);
        $phone = "0668000000";
        if (preg_match("/^[0-9]{10}$/", $phone)) {
            $old = $user->getTel();
            $user->setTel($phone);
            $this->entityManager->flush();
            return $this->json([
                "New_tel" => $user->getTel(),
                "Old_tel" => $old,
                "user" => $user->serializer(),
            ]);
        }
    }

    #[Route('/user', name: 'user_delete', methods: 'DELETE')]
    public function delete(): JsonResponse
    {
        $this->entityManager->remove($this->repository->findOneBy(["id" => 1]));
        $this->entityManager->flush();
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route('/user', name: 'user_get', methods: 'POST')]
    public function updateUser(Request $request): JsonResponse
    {

        // Récupérer le beer token depuis la requête et le verifie
        $user = $this->tokenVerifier->checkToken($request);

        // Trouver l'utilisateur avec la même adresse e-mail que dans le beer token
        if($user){
            $user->setFirstName($request->get('firstname'));
            $user->setFirstName($request->get('lastname'));
            $user->setFirstName($request->get('tel'));
            $user->setFirstName($request->get('sexe'));
        }else{
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise.Vous devez etre connecté pour effectuer cette action.']);
        }

        if (!$utilisateur) {
            throw $this->createNotFoundException('Aucun utilisateur trouvé pour cette adresse e-mail.');
        }

      
        $utilisateur->setNom($nouveauNom);

        // Enregistrer l'utilisateur modifié dans la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        return new JsonResponse('Nom utilisateur changé avec succès !');
    }

    #[Route('/user/all', name: 'user_get_all', methods: 'GET')]
    public function readAll(): JsonResponse
    {
        $result = [];

        try {
            if (count($users = $this->repository->findAll()) > 0)
                foreach ($users as $user) {
                    array_push($result, $user->serializer());
                }
            return new JsonResponse([
                'data' => $result,
                'message' => 'Successful'
            ], 400);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage()
            ], 404);
        }
    }
}
