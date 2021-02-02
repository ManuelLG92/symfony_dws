<?php


namespace App\Security;

use App\Entity\Usuario;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mailer
{
    private $emailAplicacion;
    private $emailManager;
    private $router;


    public function __construct($appEmail , UrlGeneratorInterface $router, MailerInterface $emailManager)
    {
        $this->emailAplicacion = $appEmail;
        $this->emailManager = $emailManager;
        $this->router = $router;
    }



    public function generarUrlActivacionUsuario(String $token)
    {
        return $this->router->generate('token_clave', [
            'token' => $token
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function enviarEmail(String $para, string $titulo, string $mensaje)
    {
        $email = (new Email())
            ->from($this->emailAplicacion)
            ->to($para)
            ->subject($titulo)
            ->html($mensaje);
        $this->emailManager->send($email);
    }

    public function RecibirEmail(String $de, string $titulo, string $mensaje)
    {
        $email = (new Email())
            ->from($this->emailAplicacion)
            ->to($this->emailAplicacion)
            ->subject($titulo)
            ->html($mensaje);
        $this->emailManager->send($email);
    }

}