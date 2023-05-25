<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontController extends AbstractController {
    
    #[Route('/protection-des-donnees', name: 'protection_des_donnees', methods: ['GET'])]
    public function protectionDesDonnees(Request $request): Response {
        return $this->render('/protection_des_donnees.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }
}
