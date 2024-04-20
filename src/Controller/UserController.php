<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserUtils;
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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserController extends AbstractController
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

    // #[Route('/user', name: 'user_post', methods: 'POST')]
    // public function create(Request $request, UserPasswordHasherInterface $passwordHash): JsonResponse
    // {

    //     $user = new User();
    //     $user->setName("Mike");
    //     $user->setEmail("Mike");
    //     $user->setIdUser("Mike");
    //     $user->setCreateAt(new DateTimeImmutable());
    //     $user->setUpdateAt(new DateTimeImmutable());
    //     $password = "Mike";

    //     $hash = $passwordHash->hashPassword($user, $password);
    //     $user->setPassword($hash);
    //     $this->entityManager->persist($user);
    //     $this->entityManager->flush();

    //     return $this->json([
    //         'isNotGoodPassword' => ($passwordHash->isPasswordValid($user, 'Zoubida')),
    //         'isGoodPassword' => ($passwordHash->isPasswordValid($user, $password)),
    //         'user' => $user->serializer(),
    //         'path' => 'src/Controller/UserController.php',
    //     ]);
    // }

    // #[Route('/user', name: 'user_put', methods: 'PUT')]
    // public function update(Request $request): JsonResponse
    // {

    //     $dataMiddellware = $this->tokenVerifier->checkToken($request);
    //     if (gettype($dataMiddellware) == 'boolean') {
    //         return $this->json($this->tokenVerifier->sendJsonErrorToken($dataMiddellware));
    //     }
    //     $user = $dataMiddellware;

    //     dd($user);
    //     $phone = "0668000000";
    //     if (preg_match("/^[0-9]{10}$/", $phone)) {
    //         $old = $user->getTel();
    //         $user->setTel($phone);
    //         $this->entityManager->flush();
    //         return $this->json([
    //             "New_tel" => $user->getTel(),
    //             "Old_tel" => $old,
    //             "user" => $user->serializer(),
    //         ]);
    //     }
    // }

    // #[Route('/user', name: 'user_delete', methods: 'DELETE')]
    // public function delete(): JsonResponse
    // {
    //     $this->entityManager->remove($this->repository->findOneBy(["id" => 1]));
    //     $this->entityManager->flush();
    //     return $this->json([
    //         'message' => 'Welcome to your new controller!',
    //         'path' => 'src/Controller/UserController.php',
    //     ]);
    // }

    #[Route('/user', name: 'user_get', methods: 'POST')]
    public function updateUser(Request $request): JsonResponse
    {

        // Récupérer le beer token depuis la requête et le verifie
        $user = $this->tokenVerifier->checkToken($request);
        $bodyContent = $request->getContent();
        // Si le contenu est en JSON, vous pouvez le décoder en tableau associatif
        parse_str($bodyContent,$data);
        

        // Vérifier si le décodage JSON a réussi
        if ($data == null) {
            
            return new JsonResponse([
                'error' => true,
                'message' => 'Les données fournies sont invalide ou incompletes']);
        }

        // Parcourir les données du corps de la requête
        foreach ($data as $key => $value) {
            if($key == 'lastname'|| $key == 'firstname'||$key == 'tel'|| $key == 'sexe'){
              
            }else{
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Les données fournies sont invalide ou incompletes']);
            }
        }

        // Trouver l'utilisateur avec la même adresse e-mail que dans le beer token
        if($user){
            if($request->get('firstname') != null){
                if(!$this->userUtils->isValidName($request->get('firstname'))){
                    return new JsonResponse([
                        'error' => true,
                        'message' => 'Erreur de validation des données.']);
                }
                $user->setFirstName($request->get('firstname'));
            }
            if($request->get('lastname') != null){
                if(!$this->userUtils->isValidName($request->get('lastname'))){
                    return new JsonResponse([
                        'error' => true,
                        'message' => 'Erreur de validation des données.']);
                }
                $user->setLastName($request->get('lastname'));
            }
            if(!$this->userUtils->isValidPhoneNumber($request->get('tel'))){
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Le format du numéros de telephone est invalide']);
            }
            if(!$this->userUtils->isTelAvailable($request->get('tel'))){
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Conflit de données. Le numeros de télephone est dejà utilisé par un autre utilisateur']);
            }
            $user->setTel($request->get('tel'));
            if(!$this->userUtils->isValidSex($request->get('sexe'))){
                return new JsonResponse([
                    'error' => true,
                    'message' => 'La valeur du champ sexe est invalide. Les valeurs autorisée sont 0 pour Femme et 1 pour Homme']);
            }
            $user->setSexe($request->get('sexe'));
            $this->entityManager->persist($user);
            $this->entityManager->flush($user);
            return new JsonResponse([
                'error' => false,
                'message' => 'Votre inscription a bien été prise en compte.']);

        }else{
            return new JsonResponse([
                'error' => true,
                'message' => 'Authentification requise.Vous devez etre connecté pour effectuer cette action.']);
        }

       
    }

    #[Route('/password-lost', name: 'user_pasword_lost', methods: 'POST')]
    public function passwordLost(Request $request ,JWTTokenManagerInterface $JWTManager,JWSProviderInterface $jwtProvider): JsonResponse
    {
        if(!$request->get('email')){
            return new JsonResponse([
                'error' => true,
                'message' => 'Email manquant Veuiller fournir votre email pour la récuperation du mot de passe.']);
        }else{
            if(!$this->userUtils->isValidEmail($request->get('email'))){
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le format de l'email est invalide. Veuller entrer un mail valide."]);
            }else{
                if($this->userUtils->IsAvailableEmail($request->get('email'))){
                    $time = $this->userUtils->logFailedLoginAttempt($request->get('email'));
                    if ($time == false) {
                        return new JsonResponse([
                        'error' => true,
                        'message' => "Aucun compte n'est associer à cette email .Veuller verifier est reésayer."]);
                    }else{
                        return $this->json([
                            'error' => (true),
                            'message' => "Trop de tentative de connexion (5 max).Veuillez réessayer ultèrieurement - $time min d'attente.",
                        ]);
                    }
                }else{
                    $user = $this->repository->findOneBy(["email" => $request->get('email')]);
                    $token = $jwtProvider->create(['email'=> $request->get('email'),'iat' => time(),'exp' => time()+120]);
                    return $this->json([
                        'error' => (false),
                        'token' => $token,
                        'message' => "Un email de reinitialisation a été envoyé à votre adresse email. Veullez suivre les instruction contenues dans l'email pour reinitialisé votre mot de passe",
                    ]);
                }
            }
        }

    }
    #[Route('/reset-password/{token}', name: 'user_pasword_lost2', methods: ['POST','GET'])]
    public function resetPassword(Request $request ,JWTTokenManagerInterface $JWTManager,$token, UserPasswordHasherInterface $passwordHash): JsonResponse
    {   
        if (!$token) {
            return $this->json([
                'error' => (true),
                'message' => "Token de reinitialisation manquand ou invalide.Veullez utiliser le lien fournie dans l'email de reinitialisation de lot de passe.",
            ]);
        }else{
            if(!$request->get('password')){
                return $this->json([
                    'error' => (true),
                    'message' => "Veullez fournir un nouveaux mot de passe.",
                ]);   
            }else{
                if(!$this->userUtils->isValidPassword($request->get('password'))){
                    return $this->json([
                        'error' => (true),
                        'message' => "Le nouveaux mot de passe ne respecte pas les critère requis. Il doit contenir au moins une majuscule,une linuscule,un chiffre,un caractère soecial est etre composer de 8 caracterers .",
                    ]);  
                }
               
                if($user = $this->tokenVerifier->checkStringToken($token)){  
                    if($this->tokenVerifier->isExpiredStringToken($token)){   
                        return $this->json([
                            'error' => (true),
                            'message' => "Votre token de reinitialisation de mot de passe à expirer.Veullez refaire une demande de reinitialisation de mot de passe.",
                        ]);
                    }
                    $password = $request->get('password');
                    $hash = $passwordHash->hashPassword($user, $password); // Hash le password envoyez par l'utilisateur
                    $user->setPassword($hash);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    return $this->json([
                        'error' => (true),
                        'message' => "Votre mot de passe à été réinitialiser avec succès. Vous pouvez maintenantvous connecter avec votre nouveaux mot de passe.",
                    ]); 
                }else{
                    return $this->json([
                        'error' => (true),
                        'message' => "Votre token de reinitialisation de mot de passe à expirer.Veullez refaire une demande de reinitialisation de mot de passe.",
                    ]);
                }
            }  
            
        }
        
    }

}
