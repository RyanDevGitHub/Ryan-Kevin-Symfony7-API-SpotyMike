<?php

// src/Service/Utils/UserUtils.php

namespace App\Service;

use DateTime;
use App\Entity\User;
use Namshi\JOSE\JWT;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;

class UserUtils
{

    private $userRepository;
    private $jwtManager;
    private $jwtProvider;

    public function __construct(UserRepository $userRepository, JWTTokenManagerInterface $jwtManager, JWSProviderInterface $jwtProvider)
    {
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
        $this->jwtProvider = $jwtProvider;
    }

    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isValidPassword(string $password): bool
    {
        // Vérifier la longueur du mot de passe
        if (strlen($password) < 8) {
            return false;
        }

        // Vérifier s'il contient une majuscule, une minuscule, un chiffre et un caractère spécial
        if (
            !preg_match('/[A-Z]/', $password) ||    // au moins une majuscule
            !preg_match('/[a-z]/', $password) ||    // au moins une minuscule
            !preg_match('/[0-9]/', $password) ||    // au moins un chiffre
            !preg_match('/[^A-Za-z0-9]/', $password) // au moins un caractère spécial
        ) {
            return false;
        }

        // Si toutes les conditions sont remplies, le mot de passe est valide
        return true;
    }

    function DateOfBirthFormatIsValid($dateOfBirth)
    {
        /**
         * Vérifie si la date de naissance est au format "jj/mm/aaaa".
         *
         * @param string $dateOfBirth La date de naissance à vérifier.
         * @return bool True si le format est valide, False sinon.
         */
        $pattern = "/^\d{2}\/\d{2}\/\d{4}$/";
        if (preg_match($pattern, $dateOfBirth)) {
            return true;
        } else {
            return false;
        }
    }

    function isValidAge($dateString)
    {
        // Convertir la date en un objet DateTime
        $date = new DateTime($dateString);

        // Vérifier si la date est valide et si elle est d'au moins 12 ans
        if ($date && $date->diff(new DateTime())->y >= 16) {
            return true;
        } else {
            return false;
        }
    }


    function isValidPhoneNumber($phoneNumber)
    {
        /**
         * Vérifie si un numéro de téléphone est valide.
         *
         * @param string $phoneNumber Le numéro de téléphone à vérifier.
         * @return bool True si le numéro est valide, False sinon.
         */
        $pattern = "/^[0-9]{10}$/"; // Format accepté : 10 chiffres
        if (preg_match($pattern, $phoneNumber)) {
            return true;
        } else {
            return false;
        }
    }

    function isValidSex($sex)
    {
        /**
         * Vérifie si le champ sexe est valide (0 ou 1).
         *
         * @param string $sex Le champ sexe à vérifier.
         * @return bool True si le champ est valide, False sinon.
         */
        if ($sex === '0' || $sex === '1') {
            return true;
        } else {
            return false;
        }
    }

    function IsAvailableEmail($email)
    {
        /**
         * Vérifie si l'email n'est est disponible (0 ou 1).
         *
         * @param string $email L'email à vérifier.
         * @return bool True si l'email est disponible, False sinon.
         */

        $users = $this->userRepository->findByEmail($email);
        return count($users) == 0;
    }

    function IsDisableAccount($user)
    {
        /**
         * Vérifie si l'email n'est est disponible (0 ou 1).
         *
         * @param User $user à vérifier.
         * @return bool True si user account est désactiver, False sinon.
         */

        if ($user->getDisable() == 1) {
            return true;
        }
        return false;
    }
    function IsValidLabel($label){
        return true;
    }
}
