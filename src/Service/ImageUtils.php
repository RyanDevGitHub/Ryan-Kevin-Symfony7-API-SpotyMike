<?php

// src/Service/Utils/UserUtils.php

namespace App\Service;

class ImageUtils{
    function isValidImage($imageData)
        {
            // Attempt to get image information from the binary data
            $imageInfo = @getimagesizefromstring($imageData);

            if (!$imageInfo) {
                return false; // Failed to get image information
            }

            // Check the MIME type to determine if it's JPEG or PNG
            $mimeType = $imageInfo['mime'];

            // Check if the MIME type corresponds to JPEG or PNG
            if ($mimeType === 'image/jpeg' || $mimeType === 'image/png') {
                return true; // Valid JPEG or PNG image
            }

            // Return false for any other MIME type
            return false;
        }
}