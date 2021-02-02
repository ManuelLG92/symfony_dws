<?php

namespace App\Controller;

use App\Repository\CarroRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityLoginController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils,
                          CarroRepository $carroRepository, Request $request): Response
    {
        if ($this->getUser()) {
        return $this->redirectToRoute('index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(Request $request)
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $session = $request->getSession();
        $session->remove('carro');
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
