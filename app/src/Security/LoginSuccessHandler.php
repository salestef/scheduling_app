<?php
// src/Security/LoginSuccessHandler.php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $session = $request->getSession();

        // Proveri da li je korisnik admin i ako jeste, preusmeri ga na backoffice
        if ($token instanceof UsernamePasswordToken) {
            $roles = $token->getUser()->getRoles();
            foreach ($roles as $role) {
                if ($role === 'ROLE_ADMIN') {
                    $url = $this->urlGenerator->generate('backoffice_index');
                    return new RedirectResponse($url);
                }
            }
        }

        // Proveri da li postoji zabeleženi target_path u sesiji
        if ($session->has('target_path')) {
            $targetPath = $session->get('target_path');
            $session->remove('target_path');  // Ukloni iz sesije nakon preusmeravanja
            return new RedirectResponse($targetPath);
        }

        // Ako nema target_path-a, preusmeri na default korisničku stranicu
        $url = $this->urlGenerator->generate('home_index');
        return new RedirectResponse($url);
    }
}



