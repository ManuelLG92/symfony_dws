<?php

namespace App\Controller;


use App\Entity\Recuperacion;
use App\Entity\Usuario;
use App\Repository\RecuperacionRepository;
use App\Repository\UsuarioRepository;
use App\Security\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class RecuperarClaveController extends AbstractController
{
    /**
     * @Route("/recuperar-clave", name="recuperar_clave", methods = { "GET" })
     */
    public function enviarEmail(): Response
    {
        return $this->render('recuperar_clave/recuperar_clave.html.twig');
    }

    /**
     * @Route("/datos-recuperar-clave", name="datos_recuperar_clave", methods = { "POST"})
     */
    public function datosRecuperarClave(Mailer $mailer, RecuperacionRepository $recuperacionRepository,
                                        UsuarioRepository $usuarioRepository,
                                        Request $request ): Response
    {

        $email = trim($request->request->get('email'));
        if($usuario = $usuarioRepository->findByEmail($email)){

            $recuperacionClave = $this->generaRecuperacion($usuario, $recuperacionRepository);

            $urlToken = $mailer->generarUrlActivacionUsuario($recuperacionClave->getToken());
            $htmlContenido = '<p>Este es el link para reestablecer tu contraseña: ' . $urlToken . ' Tiene 24 horas de validez</p>';
            $mailer->enviarEmail($email,"Recuperacion contraseña",$htmlContenido);

            $this->addFlash('success',
                'Hemos enviado un correo al email "'. $email .'" con un link para reestablecer
                su contraseña, es valido por un dia. Revise la bandeja de correos no deseados o spam.');
            return $this->
            redirectToRoute('recuperar_clave');
        } else {
            $this->addFlash('fail',
                'El email "'. $email .'" no ha sido encontrado como usuario. Verifique
                y vuelva a intentarlo.');
            return $this->
            redirectToRoute('recuperar_clave');

        }
    }

    public function generaRecuperacion(Usuario $usuario ,
                              RecuperacionRepository $recuperacionRepository): ?Recuperacion
    {
        $entityManager = $this->getDoctrine()->getManager();
        if ($recuperacionClave = $recuperacionRepository->findByIdUsuario($usuario->getId())){
            $fechaMaxima = new \DateTime('now');
            $fechaMaxima->modify('+1 day');
            $recuperacionClave->setFecha($fechaMaxima);
            $token = $usuario->getId() . uniqid();
            $recuperacionClave->setToken($token);
            $entityManager->persist($recuperacionClave);
            $entityManager->flush();
            return $recuperacionClave;
        } else {
            $nuevaRecuperacionClave = new Recuperacion();
            $nuevaRecuperacionClave->setIdUsuario($usuario->getId());
            $fechaMaxima = new \DateTime('now');
            $fechaMaxima->modify('+1 day');
            $nuevaRecuperacionClave->setFecha($fechaMaxima);
            $token = $usuario->getId() . uniqid();
            $nuevaRecuperacionClave->setToken($token);
            $entityManager->persist($nuevaRecuperacionClave);
            $entityManager->flush();
            return $nuevaRecuperacionClave;
        }
    }



    /**
     * @Route("/clave-token/{token}", name="token_clave", methods = { "GET" })
     */
    public function compruebaToken(string $token,RecuperacionRepository $recuperacionRepository
        , UsuarioRepository $usuarioRepository): Response
    {
        if ($this->getUser()){
            $this->addFlash('fail', 'Es necesario cerrar sesion para reestablecer una contraseña.');
            return $this->redirectToRoute('index');
        } else {
        if($token = $recuperacionRepository->findByToken($token)){
            $fechaActual = new \DateTime();
            if ($fechaActual < $token->getFecha()) {
                $emailRecuperacion = $usuarioRepository->findById($token->getIdUsuario())->getEmail();
                return $this->render('recuperar_clave/reestablecer_clave.html.twig', [
                    'token' => $token,
                    'email' => $emailRecuperacion
                ]);
            } else {
                $this->addFlash('fail',
                    'Han pasado mas de 24 horas desde que se genero el token, genelo aqui nuevamente.');
                return $this->
                redirectToRoute('recuperar_clave');
            }

        } else {
            $this->addFlash('fail',
                'Token no valido, genere uno nuevo en este formulario.');
            return $this->
            redirectToRoute('recuperar_clave');
        }
       // return $this->json(['token'=> $token, 'usuario'=> $emailRecuperacion]);

    }
    }
}

