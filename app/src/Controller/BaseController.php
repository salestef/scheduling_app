<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\RegistrationFormType;

class BaseController extends AbstractController
{
    protected function renderTemplate(string $view, array $parameters = [], Response $response = null): Response
    {
        // Kreiraj obrazac samo za GET zahteve, inače se koristi obrazac iz parametara
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $form = $this->createForm(RegistrationFormType::class);
            $parameters['registrationForm'] = $form->createView();
        }

        return $this->render($view, $parameters, $response);
    }
}
