<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutUsController extends BaseController
{
    #[Route(path: '/about-us', name: 'about_us', methods: ['GET'])]
    public function index(): Response
    {
        return $this->renderTemplate('about_us/index.html.twig');
    }
}