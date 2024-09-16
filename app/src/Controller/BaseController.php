<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Form\RegistrationFormType;

class BaseController extends AbstractController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    protected function renderTemplate(string $view, array $parameters = [], Response $response = null): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $request ? $request->getSession() : null; // Uvadi sesiju iz Request objekta

        if ($request && $request->getMethod() === 'GET') {
            $form = $this->createForm(RegistrationFormType::class);
            $parameters['registrationForm'] = $form->createView();
        }

        if (!$this->getUser() && $request) {
            $session->set('target_path', $request->getUri());
        }

        return $this->render($view, $parameters, $response);
    }
}




