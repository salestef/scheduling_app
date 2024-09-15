<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    #[Route(
        path: '/',
        name: 'home',
        methods: ['GET']
    )]
    public function index(): Response
    {
        // Render the 'home.html.twig' template
        return $this->renderTemplate('home.html.twig');
    }

    #[Route(
        path: '/home',
        name: 'home_index',
        methods: ['GET']
    )]
    public function home(): Response
    {
        // Render the 'home.html.twig' template
        return $this->renderTemplate('home.html.twig');
    }
}
