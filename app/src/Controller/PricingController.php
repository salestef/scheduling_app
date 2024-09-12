<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PricingController extends AbstractController
{
    #[Route(path: '/pricing', name: 'pricing_index', methods: ['GET'])]
    public function pricing(): Response
    {
        return $this->render('pricing/index.html.twig');
    }
}