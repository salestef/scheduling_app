<?php
// src/Security/LoginSuccessHandler.php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        if ($token instanceof UsernamePasswordToken) {
            $roles = $token->getUser()->getRoles();
            foreach ($roles as $role) {
                if ($role === 'ROLE_ADMIN') {
                    $url = $this->urlGenerator->generate('backoffice_index');
                    return new RedirectResponse($url);
                }
            }
        }

        // Default redirection for non-admin users
        $url = $this->urlGenerator->generate('home_index');
        return new RedirectResponse($url);
    }
}

