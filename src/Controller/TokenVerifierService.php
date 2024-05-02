<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenVerifierService
{

    private $jwtManager;
    private $jwtProvider;
    private $userRepository;

    public function __construct(JWTTokenManagerInterface $jwtManager, JWSProviderInterface $jwtProvider, UserRepository $userRepository)
    {
        $this->jwtManager = $jwtManager;
        $this->jwtProvider = $jwtProvider;
        $this->userRepository = $userRepository;
    }

    /**
     * @return User | Boolean - false if token is not avalaible | null is not send
     */
    public function checkToken(Request $request)
    {
        
        if ($request->headers->has('Authorization')) {
            $data =  explode(" ", $request->headers->get('Authorization'));
            if (count($data) == 2) {
                $token = $data[1];
                try {
                    $dataToken = $this->jwtProvider->load($token);
                    // dd($dataToken->isVerified());
                    if ($dataToken->isVerified()) {
                        $payload = $dataToken->getPayload();
                        if(array_key_exists("email", $payload)){
                            $user = $this->userRepository->findOneBy(["email" => $dataToken->getPayload()["email"]]);
                        }else if(array_key_exists("username", $payload)){
                            $user = $this->userRepository->findOneBy(["email" => $dataToken->getPayload()["username"]]);
                        }
                        
                        return ($user) ? $user : false;
                    }
                } catch (\Throwable $th) {
                    return false;
                }
            }
        } else {
            return false;
        }
        return false;
    }


    public function checkStringToken(string $token )
    { 
        try {
            $dataToken = $this->jwtProvider->load($token);
            if ($dataToken->isVerified()) {
                $user = $this->userRepository->findOneBy(["email" => $dataToken->getPayload()["email"]]);
                return ($user) ? $user : false;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }





    public function sendJsonErrorToken($nullToken): array
    {
        return [
            'error' => true,
            'message' => ($nullToken) ? "Authentification requise. Vous devez être connecté pour effectuer cette action." : "Vous n'êtes pas autorisé à accéder aux informations de cet artiste.",
        ];
    }

    public function isExpiredToken ($request){
        if ($request->headers->has('Authorization')) {
            $data =  explode(" ", $request->headers->get('Authorization'));
            if (count($data) == 2) {
                $token = $data[1];
                try {
                    $dataToken = $this->jwtProvider->load($token);
                    if (!$dataToken->isExpired($token)) {
                        return false;
                    }
                } catch (\Throwable $th) {
                    return false;
                }
            }
        } else {
            return true;
        }
        return true;
    }

    public function isExpiredStringToken ($token){
        try {
            $dataToken = $this->jwtProvider->load($token);
            if (!$dataToken->isExpired($token)) {
                return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }
}
