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
use App\Repository\UsuarioRepository;
use App\Repository\BancoRepository;
use App\Repository\VendedorRepository;
use App\Service\UsuarioManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        /*if ($totalElementos > 1) {
            $numeroPaginas = ceil($totalElementos/self::ELEMENTOS_POR_PAGINA);
        }*/
        if ($pagina>$numeroPaginas) {
            $pagina = $numeroPaginas;
            return $this->redirect($ruta.$pagina, 302);
        }
        /*session_start();
        dump(session_id());
        session_destroy();
        dump(session_id());*/

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
                $respuestaErrores = "";

                if (!$usuarioError) {
                    $respuestaErrores .= "Error creando el usuario\n.";
                    $em->remove($usuario);
                    $em->flush();
                }
                if (!$direccionError){
                    $respuestaErrores .= "Error creando la direccion\n.";
                    /**/$em->remove($direccion);
                    $em->flush();
                }
                if (!$bancoError) {
                    $respuestaErrores .= "Error creando el banco\n.";
                    /**/ $em->remove($banco);
                    $em->flush();
                }

                if (!$vendedorError) {
                    $respuestaErrores .= "Error creando el vendedor\n.";
                    $em->remove($vendedor);
                    $em->flush();
                }

                $response = new Response(
                    $respuestaErrores,
                    Response::HTTP_OK,
                    ['content-type' => 'text/html']
                );
                return $response;

            } else {
                $respuesta = 'Usuario creado';
                $response = new Response(
                    $respuesta,
                    Response::HTTP_OK,
                    ['content-type' => 'text/html']
                );
                return $response;
            }
            /* $em->persist($vendedor);
             $em->flush();

             /*$em-ersist($usuario);
             $em->flush();*/



        } else {
            $respuesta2 = 'No creado, comprueba usuario:  ' .$compruebaUsuario . ",Direccion " . $compruebaDireccion;
            var_dump($compruebaUsuario);
            var_dump($compruebaDireccion);
            $response = new Response(
                $respuesta2,
                Response::HTTP_NOT_FOUND,
                ['content-type' => 'text/html']
            );
            return $response;

        }

    }

    /**
     * @Route("/nuevo", methods={"GET"}, name="nuevo_usuario_get")
     */
    public function crearUsuarioGet()
    {
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
     * @Route("/editar/{id}", methods={"GET"}, name="editar_usuario_get")
     */
    public function EditarUsuarios(int $id,
   UsuarioRepository $usuarioRepository, DireccionRepository $direccionRepository,
    BancoRepository $bancoRepository): Response
    {
        $usuario = new Usuario();
        $direccion = new Direccion();
        $usuario = $usuarioRepository->find($id);
        $direccion = $direccionRepository->find($usuario->getDireccion());
        $modo = 'editar';
        dump($usuario->getPassword());
        return $this->render('usuario/editar.html.twig', [
            'usuario' => $usuario,
            'direccion' => $direccion,
            'modo' => $modo,
        ]);
    }


    /**
     * @Route("/perfil/{id}", methods={"GET"}, name="perfil_usuario")
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

        $usuario = $usuarioRepository->find($id);
        $direccion = $direccionRepository->find($usuario->getDireccion());
        $banco = $bancoRepository->find($usuario->getBanco());
        $articulos = $articuloRepository->findByIdVendedor($usuario->getIdVendedor());
        $vendedor = $vendedorRepository->find($usuario->getIdVendedor());
        $facturas = $facturaRepository->findByUsuarioId($usuario->getId());
        /*$detalle = [];
        if ($facturas != null) {
            foreach ($facturas as $factura){
                $detalle[] = [ $factura->getId() => $detalleRepository->find($factura->getId())];
            }
            //$detalle = $detalleRepository->find($facturas->getId());
        } else {
            $detalle = null;
        }*/



        if ($usuario != null && $direccion != null && $banco != null) {
            return $this->render('usuario/perfil.html.twig', [
                'usuario' => $usuario,
                'direccion' => $direccion,
                'banco' => $banco,
                'articulos' => $articulos,
                'vendedor' => $vendedor,
                'facturas' => $facturas,
                //'detalle' => $detalle,
            ]);
        }
        } else {
            return $this->redirectToRoute('index');
        }

    }


    /**
     * @Route("/login", methods={"GET"}, name="usuario_login_form")
     */
    public function login ()
    {
        $auth_rute = "/usuario/auth";
        return $this->render('usuario/login/login.html.twig', [
            'auth_ruta' => $auth_rute,
        ]);
    }

    /**
     * @Route("/auth", methods={"POST"}, name="auth_user")
     */
    public function autenticacion (
        Request $request,
        UsuarioRepository $usuarioRepository,
        UsuarioManager $usuarioManager): Response
    {
        $email = $request->request->get('email');
        $clave = $request->request->get('clave');
        if ($email != null && $clave != null){
            if (!empty($email) && !empty($clave)){
                $criteria = ['Email' => $email];
                $usuario = $usuarioRepository->findOneBy($criteria);
                if($usuario) {
                    if($usuarioManager->ChequeaClave($clave, $usuario->getPassword())){

                        $respuesta = 'Clave valida';
                        $response = new Response(
                            $respuesta,
                            Response::HTTP_OK,
                            ['content-type' => 'text/html']
                        );
                        return $response;
                    } else {
                        $respuesta = 'Clave no valida';
                        $response = new Response(
                            $respuesta,
                            Response::HTTP_OK,
                            ['content-type' => 'text/html']
                        );
                        return $response;
                    }/**/

                } /**/else {
                    $respuesta = 'Email Invalido';
                    $response = new Response(
                        $respuesta,
                        Response::HTTP_OK,
                        ['content-type' => 'text/html']
                    );
                    return $response;
                }
            }
        }/**/ else {
            $response = new Response(
                'Los campos no pueden ser nulos',
                Response::HTTP_OK,
                ['content-type' => 'text/html']
            );
            return $response;
        }
        $clave2 = $usuarioManager->ChequeaClave('test', '$2y$10$.7PuF/1vBK1s.n6dLkk0eeQn3OdFxltAx0aLWsPuDCwawqR/gtNh2');
        dump($clave2);
        return new Response(
            $clave2,
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );


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
