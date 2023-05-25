<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontApiController extends AbstractController {
    
    #[Route('/api/connexion', name: 'api_connexion', methods: ['POST'])]
    public function apiConnexion(Request $request): Response {
        $result = [
            "status" => 200,
            "message" => "Succès",
        ];

        return $this->json($result);
    }
}
