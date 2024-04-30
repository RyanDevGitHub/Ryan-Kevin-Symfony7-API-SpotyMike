<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    #[Route('/albums', name: 'get_albums', methods: ['GET'])]
    public function getAlbums(): JsonResponse
    {
        

        return $this->json([
            'albums' => $albums,
        ]);
    }

    #[Route('/album/{id}', name: 'get_album_by_id', methods: ['GET'])]
    public function getAlbumById(int $id): JsonResponse
    {
        // Dummy data for demonstration
        $album = [
            'id' => $id,
            'title' => 'Album ' . $id,
            'artist' => 'Artist ' . $id,
            'description' => 'This is the description for Album ' . $id,
        ];

        return $this->json([
            'album' => $album,
        ]);
    }

    #[Route('/albums', name: 'create_album', methods: ['POST'])]
    public function createAlbum(): JsonResponse
    {
        // Dummy data for demonstration
        $album = [
            'id' => 3,
            'title' => 'New Album',
            'artist' => 'New Artist',
        ];

        return $this->json([
            'message' => 'Album created successfully',
            'album' => $album,
        ]);
    }
}
