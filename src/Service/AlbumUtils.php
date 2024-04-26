<?php

// src/Service/Utils/UserUtils.php

namespace App\Service;

use DateTime;
use App\Entity\Album;
use App\Entity\User;
use Namshi\JOSE\JWT;
use App\Repository\AlbumRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;

class AlbumUtils
{

    private $albumRepository;
    private $jwtManager;
    private $jwtProvider;
    private $userRepository;

    public function __construct(AlbumRepository $albumRepository, UserRepository $userRepository,JWTTokenManagerInterface $jwtManager, JWSProviderInterface $jwtProvider)
    {
        $this->albumRepository = $albumRepository;
        $this->albumRepository = $userRepository;
        $this->jwtManager = $jwtManager;
        $this->jwtProvider = $jwtProvider;
    }
 
    public function userCanAccesToAlbum($user, $idAlbum){
            if($user->getId() == $idAlbum){
                return true;
            }{
                return false;
            }
    }
}
