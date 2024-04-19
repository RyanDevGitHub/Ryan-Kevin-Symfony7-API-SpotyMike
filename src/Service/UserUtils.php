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
        $date = DateTime::createFromFormat('d/m/Y', $dateString);

        // Vérifier si la date est valide et si elle est d'au moins 12 ans
        if ($date && $date->diff(new DateTime())->y >= 12) {
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

    function logFailedLoginAttempt($user)
    {
        /**
         * Vérifie si l'email n'est est disponible (0 ou 1).
         *
         * @param string $user email à vérifier.
         * @return bool|int false si user peut tenter de ce connecter, timeless sinon.
         */
        global $dataDir;
        $failedLoginFile = $dataDir . $user . '.txt';
    
        // Vérifie si le fichier existe déjà
        if (file_exists($failedLoginFile)) {
            // Le fichier existe, lire les données
            $data = file_get_contents($failedLoginFile);
            list($failedAttempts, $firstAttemptTime) = explode('|', $data);
    
            // Vérifie si le premier essai était il y a plus de 5 minutes
            $currentTime = time();
            if ($currentTime - $firstAttemptTime > 300) {
                // Réinitialiser les tentatives si le premier essai était il y a plus de 5 minutes
                $failedAttempts = 1;
                $firstAttemptTime = $currentTime;
            } else {
                // Vérifier si le nombre de tentatives dépasse le seuil
                if ($failedAttempts >= 5) {
                    // Vérifie si le délai entre la première et la dernière tentative est inférieur à 5 minutes
                    if ($currentTime - $firstAttemptTime <= 60) {
                        
                        // L'utilisateur a dépassé le seuil et le délai, donc on l'empêche de se connecter pendant 5 minutes
                        return 60 - (time() - $firstAttemptTime) ;
                    } else {
                        // Réinitialiser les tentatives si le délai entre la première et la dernière tentative est supérieur à 5 minutes
                        $failedAttempts = 1;
                        $firstAttemptTime = $currentTime;
                    }
                } else {
                    // Incrémenter les tentatives infructueuses
                    $failedAttempts++;
                }
            }
        } else {
            // Le fichier n'existe pas, c'est la première tentative
            $failedAttempts = 1;
            $firstAttemptTime = time();
        }
    
        // Écrire les données dans le fichier
        $data = "$failedAttempts|$firstAttemptTime";
        file_put_contents($failedLoginFile, $data);
        return false;
       
    }

    function isValidName($name)
{
    /**
     * Vérifie si un nom est valide.
     *
     * @param string $name Le nom à vérifier.
     * @return bool True si le nom est valide, False sinon.
     */
    $length = mb_strlen($name, 'utf8');
    if ($length >= 1 && $length <= 60) {
        return true;
    } else {
        return false;
    }
}
function isTelAvailable($tel)
{
    /**
     * Vérifie si le numéro de téléphone est disponible.
     *
     * @param string $tel Le numéro de téléphone à vérifier.
     * @return bool True si le numéro de téléphone est disponible, False sinon.
     */

    // Supposons que $this->telRepository->findByTel($tel) récupère les numéros de téléphone depuis la base de données
    $telExist = $this->userRepository->findByTel($tel);
    return empty($telExist); // Si le tableau est vide, le numéro de téléphone est disponible
}

}
