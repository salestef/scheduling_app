<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends BaseController
{
    #[Route(path: '/contact', name: 'contact', methods: ['GET'])]
    public function index(): Response
    {
        return $this->renderTemplate('contact/index.html.twig');
    }
}