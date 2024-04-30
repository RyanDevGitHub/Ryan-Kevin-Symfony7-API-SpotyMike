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

    public function isBinarySong($song){
    // Créez un objet FileInfo
    $song = str_replace(' ', '', $song);
    $song = pack('H*', $song);
    $song = base64_encode($song);
    $song_binary = base64_encode($song);
    //dd($song_binary);
    $finfo = new \finfo(FILEINFO_MIME_TYPE);

    // Obtenez le type MIME à partir du contenu binaire
    $mime_type = $finfo->buffer($song_binary);
    //dd($song);
    // Types MIME courants pour les fichiers audio
    $audio_mime_types = array(
        'audio/mpeg', // MP3
        'audio/x-wav' // WAV
    );

    dd($mime_type);
    // Vérifiez si le type MIME correspond à un type audio valide
    if (in_array($mime_type, $audio_mime_types)) {
        return true;
    } else {
        return false;
    }
    }

    public function IsValidFileSize($song,){
       // Taille maximale autorisée en Mo
    $max_size_mb = 7;
    // Taille minimale autorisée en Mo
    $min_size_mb = 1;

    // Convertir les Mo en octets
    $max_size_bytes = $max_size_mb * 1024 * 1024; // 1 Mo = 1024 Ko = 1024 * 1024 octets
    $min_size_bytes = $min_size_mb * 1024 * 1024;

    // Obtenez la taille du fichier en octets
    $file_size = filesize($song);

    // Vérifiez si la taille du fichier est comprise entre la taille minimale et maximale
    if ($file_size >= $min_size_bytes && $file_size <= $max_size_bytes) {
        return true; // La taille du fichier est valide
    } else {
        return false; // La taille du fichier n'est pas valide
    }
    }
}
