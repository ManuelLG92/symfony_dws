<?php

namespace App\Controller;

use App\Entity\Banco;
use App\Entity\Direccion;
use App\Entity\Usuario;
use App\Entity\Vendedor;
use App\Repository\ArticuloRepository;
use App\Repository\DetalleRepository;
use App\Repository\DireccionRepository;
use App\Repository\FacturaRepository;
use App\Repository\RecuperacionRepository;
use App\Repository\UsuarioRepository;
use App\Repository\BancoRepository;
use App\Repository\ValoracionRepository;
use App\Repository\VendedorRepository;
use App\Security\Mailer;
use App\Service\SecurityManager;
use App\Service\UsuarioManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

/**
 * @Route("/usuario")
 */
class UsuarioController extends AbstractController
{
    const ELEMENTOS_POR_PAGINA = 5;

    /**
     * @Route(
     *     "/{pagina}",
     *     name="usuarios_index",
     *     defaults = {
     *     "pagina" = 1 },
     *     requirements = {
     *     "pagina" = "\d+"},
     *     methods = { "GET" }
     *     )
     * @param UsuarioRepository $usuarioRepository
     * @return Response
     */
    public function usuarios (int $pagina,
  UsuarioRepository $usuarioRepository, BancoRepository $banco,
  Request $request): Response
    {
        $ruta = '/usuario/';
        if ($pagina<1) {
            $pagina = 1;
           return $this->redirect($ruta.$pagina, 302);
        }

        $totalElementos = $usuarioRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $numeroPaginas = 1;
        $totalElementos > 0 ? $numeroPaginas=ceil($totalElementos/self::ELEMENTOS_POR_PAGINA) : $numeroPaginas = 1;

        if ($pagina>$numeroPaginas) {
            $pagina = $numeroPaginas;
            return $this->redirect($ruta.$pagina, 302);
        }


        return $this->render('usuario/usuarios.html.twig', [
            'usuarios' => $usuarioRepository->buscarUsuarios($pagina,self::ELEMENTOS_POR_PAGINA),
            //'banco' => $banco->findAll(),
            'pagina_actual' => $pagina,
            'total_elementos' => $totalElementos,
            'numero_paginas' => $numeroPaginas,

        ]);
    }

    /**
     * @Route("/nuevo", methods={"POST"}, name="nuevo_usuario")
     */
    public function crearUsuarios(
        Request $request,
        EntityManagerInterface $em,
        UsuarioManager $usuarioManager
       ): Response
    {
        $direccionError = false;
        $bancoError = false;
        $usuarioError = false;
        $vendedorError = false;

        $usuario = new Usuario();
        $nombre = $request->request->get('nombre');
        $apellido = $request->request->get('apellido');
        $telefono = $request->request->get('telefono');
        $email = $request->request->get('email');
        $clave = $request->request->get('clave');

        if ($emailRegistrado = $usuarioManager->compruebaEmail(trim($email))){
            $this->addFlash('fail','El email '. $emailRegistrado->getEmail() .' ya esta registrado, puedes recuperar la clave si no la recuerdas.');
            return $this->redirectToRoute('index');
        } /*else {
            $this->addFlash('success','El email disponible.');
            return $this->redirectToRoute('index');
        }*/

        $direccion = new Direccion();
        $via = $request->request->get('via');
        $nombre_via = $request->request->get('nombre_via');
        $numero = $request->request->get('numero');
        $piso = $request->request->get('piso');
        $puerta = $request->request->get('puerta');
        $ciudad = $request->request->get('ciudad');
        $estado = $request->request->get('estado');
        $cp = $request->request->get('cp');
        $pais = $request->request->get('pais');

        $banco = new Banco();
        $banco->setBalance(2000);

        $vendedor = new Vendedor();

        $compruebaUsuario = $usuarioManager->compruebaCamposUsuario($nombre,$apellido,$email,$clave);
        $compruebaDireccion =$usuarioManager->compruebaCamposDireccion($via,$nombre_via,$numero,$ciudad,$estado,$cp,$pais);

        if($compruebaUsuario && $compruebaDireccion){
                $direccion->setVia(trim($via));
                $direccion->setNombreVia(trim($nombre_via));
                $direccion->setNumero(trim($numero));
                $direccion->setCp(trim($cp));
                $direccion->setCiudad(trim($ciudad));
                $direccion->setEstado(trim($estado));
                $direccion->setPais(trim($pais));
                if ($piso != null && !empty($piso) ) {
                    $direccion->setPiso(trim($piso));
                }
                if ($puerta != null && !empty($puerta) ) {
                    $direccion->setPuerta(trim($puerta));
                }
                /*$errores = $usuarioManager->
                compruebaCamposDireccionValidate($direccion);*/

            try {
                $usuarioManager->crearDireccion($direccion);
            } catch (\Exception $e) {
                $direccionError = true;
            } catch (\TypeError $error){
                $direccionError = true;
            }

            try {
                $cuentaVirtual = $direccion->getId() . substr($nombre, 0,2) .
                    substr($apellido,0,2) . mt_rand();
                $banco->setCuentaVirtual($cuentaVirtual);
                $usuarioManager->crearBanco($banco);
            } catch (\Exception $e) {
                $bancoError = true;
            }
            catch (\TypeError $e) {
                $bancoError = true;
            }

               // $usuarioManager->crearBanco($banco);

               /* $em->persist($banco);
                $em->flush();*/
            try {
                //$vendedor->setIdUsuario($usuario->getId());
                $vendedor->setImporteVentas(0);
                $vendedor->setNumeroVentas(0);
                $vendedor->setValoracion(0);
                $em->persist($vendedor);
                $em->flush();
            }catch (\Exception $e) {
                $vendedorError = true;

            } catch (\TypeError $error){
                $vendedorError = true;


            }

            try {
                $usuario->setNombre(trim($nombre));
                $usuario->setApellido(trim($apellido));
                $usuario->setEmail(trim($email));
                $usuario->setDireccion($direccion);
                $usuario->setIdVendedor($vendedor->getId());

                $claveEncriptada = $usuarioManager->encriptarClave(trim($clave));
                //dump($claveEncriptada);
//                    $encoder->encodePassword($usuario,$clave);
                $usuario->setClave($claveEncriptada);
                if ($telefono != null && !empty($telefono)){
                    $usuario->setTelefono(trim($telefono));
                }
                $usuario->setBanco($banco);

                $usuarioManager->crearUsuario($usuario);
            }catch (\Exception $e) {
                $usuarioError = true;
            }catch (\TypeError $e) {
                $usuarioError = true;
            }
               // $usuarioManager->crearUsuario($usuario);


            if ($direccionError || $bancoError || $usuarioError || $vendedorError){
                //$respuestaErrores = "";

                if (!$usuarioError) {
                  //  $respuestaErrores .= "Error creando el usuario\n.";
                    $em->remove($usuario);
                    $em->flush();
                }
                if (!$direccionError){
                   // $respuestaErrores .= "Error creando la direccion\n.";
                    /**/$em->remove($direccion);
                    $em->flush();
                }
                if (!$bancoError) {
                    //$respuestaErrores .= "Error creando el banco\n.";
                    /**/ $em->remove($banco);
                    $em->flush();
                }

                if (!$vendedorError) {
                    //$respuestaErrores .= "Error creando el vendedor\n.";
                    $em->remove($vendedor);
                    $em->flush();
                }
                $this->addFlash('fail','Ha ocurrido un error, no se pudo crear el usuario. Vuelve a intentarlo.');
                return $this->redirectToRoute('nuevo_usuario_get');
                /*$response = new Response(
                    $respuestaErrores,
                    Response::HTTP_OK,
                    ['content-type' => 'text/html']
                );
                return $response;*/

            } else {
                $this->addFlash('success','Usuario creado satisfactoriamente. Ya puedes iniciar sesion..');
                return $this->redirectToRoute('app_login');
               /* $respuesta = 'Usuario creado';
                $response = new Response(
                    $respuesta,
                    Response::HTTP_OK,
                    ['content-type' => 'text/html']
                );
                return $response;*/
            }
            /* $em->persist($vendedor);
             $em->flush();

             /*$em-ersist($usuario);
             $em->flush();*/



        } else {
            $this->addFlash('fail','No se pudo crear el usuario, se encontraron errores en los campos. Vuelve a intentarlo');

            /*$respuesta2 = 'No creado, comprueba usuario:  ' .$compruebaUsuario . ",Direccion " . $compruebaDireccion;
            var_dump($compruebaUsuario);
            var_dump($compruebaDireccion);
            $response = new Response(
                $respuesta2,
                Response::HTTP_NOT_FOUND,
                ['content-type' => 'text/html']
            );*/
            return $this->redirectToRoute('nuevo_usuario_get');

        }

    }

    /**
     * @Route("/nuevo", methods={"GET"}, name="nuevo_usuario_get")
     */
    public function crearUsuarioGet()
    {
        if ($this->getUser()){
        $this->addFlash('fail','Debes cerrar sesion para crear un usuario.');
            return $this->redirectToRoute('index');
        }
        $usuario = new Usuario();
        $direccion = new Direccion();
        $banco = new Banco();
        $modo = 'crear';

        return $this->render('usuario/nuevo.html.twig', [
            'usuario' => $usuario,
            'direccion' => $direccion,
            'banco' => $banco,
            'modo' => $modo,
        ]);


    }

    /**
     * @Route("/editar/{id<\d+>}", methods={"GET"}, name="editar_usuario_get")
     */
    public function EditarUsuarios(int $id,
   UsuarioRepository $usuarioRepository, DireccionRepository $direccionRepository,
    BancoRepository $bancoRepository, Request $request): Response
    {
        if ($this->chequeaUsuarioEnSesion($request,$id)){
        $usuario = new Usuario();
        $direccion = new Direccion();
        $usuario = $usuarioRepository->find($id);
        $direccion = $direccionRepository->find($usuario->getDireccion());
        $modo = 'editar';
        //dump($usuario->getPassword());
        return $this->render('usuario/editar.html.twig', [
            'usuario' => $usuario,
            'direccion' => $direccion,
            'modo' => $modo,
        ]);
    } else {
            $this->addFlash('fail','Si no has iniciado sesion, inicia sesion para editar tu perfil. Si ya has iniciado sesion, solo puedes editar tu perfil.');
            return $this->redirectToRoute('index');
        }
    }


    /**
     * @Route("/editar/{id<\d+>}", methods={"POST"}, name="editar_usuario_post")
     */
    public function actualizarUsuario(int $id, UsuarioRepository $usuarioRepository,
                                      DireccionRepository $direccionRepository,
                                      UsuarioManager $usuarioManager,Request $request)
    {
        if ($this->chequeaUsuarioEnSesion($request,$id)){
            $idUsuarioSolicitud = trim($request->request->get('idUsuario'));
            if ($this->getUser()->getId() == (int)$idUsuarioSolicitud){
                $em = $this->getDoctrine()->getManager();
                $usuarioModificar = $usuarioRepository->find($idUsuarioSolicitud);
                $nombre = trim($request->request->get('nombre'));
                $apellido = trim($request->request->get('apellido'));
                $telefono = trim($request->request->get('telefono'));
                $via = trim($request->request->get('via'));
                $nombre_via = trim($request->request->get('nombre_via'));
                $numero = trim($request->request->get('numero'));
                $piso = trim($request->request->get('piso'));
                $puerta = trim($request->request->get('puerta'));
                $ciudad = trim($request->request->get('ciudad'));
                $estado = trim($request->request->get('estado'));
                $cp = trim($request->request->get('cp'));
                $pais = trim($request->request->get('pais'));

                $verificaDatosPersonales = $usuarioManager->
                compruebaCamposUsuarioEditar($nombre, $apellido);

                $verificaDatosDireccion = $usuarioManager->
                compruebaCamposDireccion($via,$nombre_via,$numero,$ciudad,$estado,$cp,$pais);
                if ($verificaDatosPersonales && $verificaDatosDireccion){
                    $usuarioModificar->setNombre($nombre);
                    $usuarioModificar->setApellido($apellido);
                    $usuarioModificar->setTelefono($telefono);

                    $direccionModificar = $usuarioModificar->getDireccion();
                    $direccionModificar->setVia($via);
                    $direccionModificar->setNombreVia($nombre_via);
                    $direccionModificar->setNumero($numero);
                    if ($piso != null && !empty($piso) ) {
                        $direccionModificar->setPiso($piso);
                    }
                    if ($puerta != null && !empty($puerta) ) {
                        $direccionModificar->setPuerta($puerta);
                    }
                    $direccionModificar->setCiudad($ciudad);
                    $direccionModificar->setEstado($estado);
                    $direccionModificar->setCp($cp);
                    $direccionModificar->setPais($pais);
                    $em->persist($direccionModificar);
                    $em->flush();
                    $em->persist($usuarioModificar);
                    $em->flush();
                    $this->addFlash('success','Usuario editado correctamente.');
                    return  $this->redirectToRoute('index');
                }
                $this->addFlash('fail','Hemos encontrado datos no validos en el formulario, Intentalo de nuevo.');
                return  $this->redirectToRoute('index');

            } else {
                $this->addFlash('fail',"Solo puedes editar tu perfil");
                return $this->redirectToRoute('index');
            }
        } else {
            $this->addFlash('fail',"Debes iniciar sesion para editar tu perfil");
            return $this->redirectToRoute('app_login');
        }
    }


    /**
     * @Route("/perfil/{id<\d+>}", methods={"GET"}, name="perfil_usuario")
     */
    public function PerfilUsuario (
        int $id,
        UsuarioRepository $usuarioRepository,
        DireccionRepository $direccionRepository,
        BancoRepository $bancoRepository,
        ArticuloRepository $articuloRepository,
        VendedorRepository $vendedorRepository,
        FacturaRepository $facturaRepository,
        DetalleRepository $detalleRepository,
        Request $request): Response
    {
        if ($this->chequeaUsuarioEnSesion($request,$id)){

        try {
        $usuario = $usuarioRepository->find($id);
        $direccion = $direccionRepository->find($usuario->getDireccion());
        $banco = $bancoRepository->find($usuario->getBanco());
        $articulos = $articuloRepository->findByIdVendedor($usuario->getIdVendedor());
        $vendedor = $vendedorRepository->find($usuario->getIdVendedor());
        $facturas = $facturaRepository->findByUsuarioId($usuario->getId());
        $cantidadGastada = $facturaRepository->importeGastoPorUsuario($id);
         } catch (\Exception $e){
            $this->addFlash('fail','Ha ocurrido un error cargando tu informacion, intentalo mas tarde.');
            return  $this->redirectToRoute('index');
        }

        $ventasDetalle=[];
        /*$detalle = [];
        if ($facturas != null) {
            foreach ($facturas as $factura){
                $detalle[] = [ $factura->getId() => $detalleRepository->find($factura->getId())];
            }
            //$detalle = $detalleRepository->find($facturas->getId());
        } else {
            $detalle = null;
        }*/
            $VentasVendedor = $detalleRepository->
            ArticulosPorVendedor($vendedor->getId());


        if ($usuario != null && $direccion != null && $banco != null) {
            return $this->render('usuario/perfil.html.twig', [
                'usuario' => $usuario,
                'direccion' => $direccion,
                'banco' => $banco,
                'articulos' => $articulos,
                'vendedor' => $vendedor,
                'facturas' => $facturas,
                'ventas' => $VentasVendedor,
                'gasto' => $cantidadGastada,
            ]);
        }
        else {
            $this->addFlash('fail','Ha ocurrido un error buscando este usuario, intentalo en unos minutos.');
            return $this->redirectToRoute('index');
        }
        } else {
            $this->addFlash('fail','Si no has iniciado sesion, inicia sesion para ver tu perfil. Si ya has iniciado sesion, solo puedes ver tu perfil.');

            return $this->redirectToRoute('index');
        }

    }

    /**
     * @Route("/nueva-clave", methods={"POST"}, name="nueva_clave")
     */
    public function reestablecerClave(Request $request, UsuarioRepository $usuarioRepository
                                    ,RecuperacionRepository $recuperacionRepository,
                                    UsuarioManager $usuarioManager)
    {
        if (!$this->getUser()) {
        $idUsuario = trim($request->request->get('idUsuario'));
        $idToken = trim($request->request->get('idToken'));
        $token = trim($request->request->get('Token'));
        $clave = trim($request->request->get('clave'));
        $clave2 = trim($request->request->get('clave2'));
        if (!$this->compruebaLongitudClave($clave) || !$this->compruebaLongitudClave($clave2)){
            $this->addFlash('fail','Los campos deben de la nueva clave deben ser iguales');
            return $this->redirectToRoute('token_clave',['token' => $token]);
        }

        if ($usuarioManager->comparaNuevaclave($clave,$clave2) == 0){
            $entityManager = $this->getDoctrine()->getManager();
            $usuario = $usuarioRepository->find($idUsuario);
            $usuario->setClave($usuarioManager->encriptarClave($clave));
            $recuperacionActual = $recuperacionRepository->find($idToken);
            try {
                $entityManager->persist($usuario);
                $entityManager->flush();
                $entityManager->remove($recuperacionActual);
                $entityManager->flush();
                 } catch(\Exception $e){
                $this->addFlash('fail','Ha ocurrido un eror, intentalo en unos minutos');
                return $this->redirectToRoute('index');
            }
            $this->addFlash('success',"Clave cambiada exitosamente");
            return $this->redirectToRoute('app_login');
            /*return $this->json (['respuesta' => $usuario->getClave(),
                'recuperacion' => $recuperacionActual]);*/

        } else {
            $this->addFlash('fail', 'Las claves no coinciden.');
            return $this->redirectToRoute('token_clave',['token' => $token]);
        }
        } else {
            $this->addFlash('fail', 'Debes cerrar sesion para reestablecer uan contraseña.');
            return $this->redirectToRoute('index');
        }
        
    }

    /**
     * @Route("/cambiar-clave", methods={"GET"}, name="cambiar_clave_get")
     */
    public function cambiarClaveGet()
    {
        if (!$this->getUser()){
            $this->addFlash('fail','Debes iniciar sesion para cambiar tu clave');
            return $this->redirectToRoute('index');
        }
        return $this->render('usuario/cambiar_clave.html.twig',['id'=>$this->getUser()->getId()]);
    }


    /**
     * @Route("/cambiar-clave", methods={"POST"}, name="cambiar_clave_post")
     * @param UsuarioManager $usuarioManager
     * @param UsuarioRepository $usuarioRepository
     * @param SecurityManager $securityManager
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cambiarClavePost(UsuarioManager $usuarioManager,
                                     UsuarioRepository $usuarioRepository,
                                     SecurityManager $securityManager,
                                     Request $request, EntityManagerInterface $em)
    {
        $claveActual = $request->request->get('claveActual');
        $claveNueva = $request->request->get('claveNueva');
        $claveVerificacion = $request->request->get('claveVerificacion');
        $idUsuarioSolicitud = $request->request->get('idUsuario');
        if ($securityManager->chequeaUsuarioSolicitud($request,$idUsuarioSolicitud)){
            if ($usuarioSolicitud = $usuarioRepository->find($idUsuarioSolicitud)){
                if ($usuarioManager->ChequeaClave($claveActual,$usuarioSolicitud->getPassword())){
                    if ($usuarioManager->comparaNuevaclave($claveNueva,$claveVerificacion) == 0){
                       $usuarioSolicitud->setClave($usuarioManager->encriptarClave($claveNueva));
                       $em->persist($usuarioSolicitud);
                       $em->flush();
                       $this->addFlash('success','Clave cambiada correctamente. Recuerda la clave para la proximna vez que inicies sesion.');
                        //return $this->redirectToRoute('app_logout');
                        return $this->redirectToRoute('perfil_usuario',['id'=> (int)$idUsuarioSolicitud ]);
                    } else {
                        $this->addFlash('fail','La nueva clave debe ser igual a la verificacion, es sensible a mayusculas y minusculas');
                        return $this->redirectToRoute('perfil_usuario',['id'=> (int)$idUsuarioSolicitud ]);
                    }
                } else {
                    $this->addFlash('fail','La clave actual no es correcta. Si no la sabes, cierra sesion y recuperala desde el login.');
                    return $this->redirectToRoute('perfil_usuario',['id'=> (int)$idUsuarioSolicitud ]);
                }
            }  else {
                $this->addFlash('fail','No se pudo encontrar tu usuario, intentalo de nuevo mas tarde.');
                return $this->redirectToRoute('perfil_usuario',['id'=> (int)$idUsuarioSolicitud ]);
            }
        } else {
            $this->addFlash('fail','Tienes que iniciar sesion para cambiar tu clave, si no la sabes puedes recuperarla desde el login.');
            return $this->redirectToRoute('index');
        }

    }

    /**
     * @Route("/cambiar-email", methods={"GET"}, name="cambiar_email_get")
     */
    public function cambiarEmailGet(Request $request)
    {
        if (!$this->getUser()){
            $this->addFlash('fail','Debes iniciar sesion para cambiar tu email.');
            return  $this->redirectToRoute('app_login');
        }
       return $this->render('usuario/cambiar_email.html.twig');
    }
    /**
     * @Route("/cambiar-email", methods={"POST"}, name="cambiar_email_post")
     */
    public function cambiarEmailPost(SecurityManager $securityManager,UsuarioRepository $usuarioRepository,
                                     UsuarioManager $usuarioManager, Mailer $mailer,
                                     EntityManagerInterface $em, Request $request)
    {
        $idUsuarioSesion = trim($request->request->get('idUsuario'));
        $claveUsuario = trim($request->request->get('claveActual'));
        $emailActual = trim($request->request->get('emailActual'));
        $emailNuevo = trim($request->request->get('emailNuevo'));

        if (!$usuarioManager->compruebaEmailBienFormado($emailActual)){
            $this->addFlash('fail','El email actual no tiene formato valido.');
            return  $this->redirectToRoute('cambiar_email_get');
        }
        if (!$usuarioManager->compruebaEmailBienFormado($emailNuevo)){
            $this->addFlash('fail','El email nuevo no tiene formato valido.');
            return  $this->redirectToRoute('cambiar_email_get');
        }
        if ($usuarioManager->comparaEmails($emailActual,$emailNuevo)){
            $this->addFlash('fail','El email actual es el mismo que es que quieres cambiar.');
            return  $this->redirectToRoute('cambiar_email_get');
        }
        if ($usuarioRepository->findByEmail($emailNuevo)){
            $this->addFlash('fail','El email nuevo ya esta registrado en buy-m3.');
            return  $this->redirectToRoute('cambiar_email_get');
        }
        if ($securityManager->chequeaUsuarioSolicitud($request,$idUsuarioSesion)){
            if ($usuarioSolicitud = $usuarioRepository->find($idUsuarioSesion)){
                    if ($usuarioManager->comparaEmails($emailActual,$usuarioSolicitud->getEmail())){
                        if ($usuarioManager->ChequeaClave($claveUsuario,$usuarioSolicitud->getPassword())){
                            $usuarioSolicitud->setEmail($emailNuevo);
                            try {
                                $em->persist($usuarioSolicitud);
                                $em->flush();
                                $htmlContenido = '<p>Haz solicitado cambiar tu email de tu cuenta en buy-m3 a ' . $emailNuevo . ' .Si no has sido tu, ponte en contacto con nosotros para recuperar tu cuenta mediante este link: https://buy-m3.000webhostapp.com/</p>';
                                $mailer->enviarEmail($emailActual,"Cambio Email",$htmlContenido);
                                $htmlContenido = '<p>Haz solicitado el cambio el email de tu cuenta en buy-m3 a este correo. Si no has pedido este cambio, tu informacion personal a quedado expuesta en algun lugar, por favor ponte en contacto con nosotros mediante este link: https://buy-m3.000webhostapp.com/</p>';
                                $mailer->enviarEmail($emailNuevo,"Cambio Email",$htmlContenido);
                                $this->addFlash('success','Email cambiado exitosamente');
                                $this->addFlash('success','Hemos enviado un email informativo a tu direccion de email anterior y a tu nueva direccion de email. Revisa la carpeta Spam o no deseado.');
                                return $this->redirectToRoute('perfil_usuario',['id'=> (int)$idUsuarioSesion ]); } catch (\Exception $e){
                            }

                            }else {
                            $this->addFlash('fail','La clave que has indicado no coincide con la de tu usuario.');
                            return  $this->redirectToRoute('cambiar_email_get');
                        }
                    } else {
                        $this->addFlash('fail','El email actual no coincide con el de tu usuario.');
                        return  $this->redirectToRoute('cambiar_email_get');
                    }

            } else {
                $this->addFlash('fail','Usuario no encontrado.');
                //return $this->redirectToRoute('index');
            }
        } else {
            $this->addFlash('fail','Tienes que iniciar sesion para cambiar tu email.');
           // return $this->redirectToRoute('index');
        }
        return $this->redirectToRoute('index');
    }



    public function compruebaLongitudClave(string $clave)
    {
        strlen($clave) > 7 && strlen($clave)<33 ? $validacionLongitud = true : $validacionLongitud = false;
        return $validacionLongitud;
    }


    public function chequeaUsuarioEnSesion(Request $request, int $idUsuario): bool
    {
        $usuarioEnSesion = $this->getUser();
        $sesion = $request->getSession();
        if( $sesion && $usuarioEnSesion!=null) {
            if ($usuarioEnSesion->getId() == $idUsuario){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }






}
