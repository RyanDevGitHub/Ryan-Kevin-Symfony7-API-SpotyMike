<?php

// src/Service/Utils/UserUtils.php

namespace App\Service;

use App\Entity\User;

class UserUtils
{
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
}
