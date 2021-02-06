<?php

namespace App\Controller;

use App\Entity\Articulo;
use App\Repository\ArticuloRepository;
use App\Repository\DetalleRepository;
use App\Repository\SeccionRepository;
use App\Repository\UsuarioRepository;
use App\Repository\ValoracionRepository;
use App\Service\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/articulo")
 */
class ArticuloController extends AbstractController

{
    /**
     * @Route("/", name="articulo_index", methods={"GET"})
     * @param ArticuloRepository $articuloRepository
     * @return Response
     */
    public function index(ArticuloRepository $articuloRepository): Response
    {
        return $this->render('articulo/carro.html.twig', [
            'articulos' => $articuloRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="articulo_new", methods={"GET"})
     * @param SeccionRepository $seccionRepository
     * @param Request $request
     * @return Response
     */
    public function new(SeccionRepository $seccionRepository, Request $request): Response
    {
        if ($this->chequeaUsuarioLogueado($request)) {
            $articulo = new Articulo();
            $secciones = $seccionRepository->findAll();
            return $this->render('articulo/crearArticulo.html.twig', [
                'articulo' => $articulo,
                'secciones' => $secciones,
                //'form' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }

    }

    /**
     * @Route("/editar/{usuario<\d+>}/{id<\d+>}", name="editar_articulo_get", methods={"GET"})
     * @param int $usuario
     * @param int $id
     * @param ArticuloRepository $articuloRepository
     * @param SeccionRepository $seccionRepository
     * @param UsuarioRepository $usuarioRepository
     * @param Request $request
     * @return Response
     */
    public function editarArticuloGet(int $usuario, int $id,
      ArticuloRepository $articuloRepository,SeccionRepository $seccionRepository,
          UsuarioRepository $usuarioRepository, Request $request): Response
    {

        if ($this->chequeaUsuarioLogueado($request)){
            $usuarioSolicitud = $usuarioRepository->find($usuario);
            if ($usuarioSolicitud == null){
                $this->addFlash('fail','El usuario del articulo no ha podido ser encontrado.');
                return $this->redirectToRoute('index');
            }
            $articulo = $articuloRepository->find($id);
            if ($articulo == null){
                $this->addFlash('fail','Articulo no encontrado');
                return $this->redirectToRoute('index');
            }
            if ($articulo->getIdVendedor() != $usuarioSolicitud->getIdVendedor()){
                $this->addFlash('fail','Solo puedes editar tus articulos.');
                return $this->redirectToRoute('index');
            }
            $secciones = $seccionRepository->findAll();
            return $this->render('articulo/editarArticulo.html.twig', [
                'articulo' => $articulo,
                'secciones' => $secciones,
                //'form' => $form->createView(),
            ]);

        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/editar/{id<\d+>}", name="editar_articulo_put", methods={"PUT"})
     * @param int $id
     * @param ArticuloRepository $articuloRepository
     * @param EntityManagerInterface $em
     * @param SecurityManager $securityManager
     * @param UsuarioRepository $usuarioRepository
     * @param Request $request
     * @return Response
     */
    public function editarArticulo(
        int $id, ArticuloRepository $articuloRepository,
        EntityManagerInterface $em, SecurityManager $securityManager,
        UsuarioRepository $usuarioRepository, Request $request): Response
    {
        $usuarioId = $request->request->get('usuario_id');

        //dump($usuarioId);
        if ($securityManager->chequeaUsuarioSolicitud($request,(int)$usuarioId)){
            if($this->isCsrfTokenValid('editar'.$this->getUser()->getId(),
                $request->request->get('csrf'))){

                    $nombreArticulo = trim($request->request->get('nombre'));
                    $stockArticulo = trim($request->request->get('stock'));
                    $precioArticulo = trim($request->request->get('precio'));
                    $descripcionArticulo = trim($request->request->get('descripcion'));
                    $seccionSelected = trim($request->request->get('secciones'));
                    $imagen = $request->files->get('imagen');
                    $imagenActual = trim($request->request->get('imagenActual'));

                    if ($this->validaArticulos($nombreArticulo,
                        $stockArticulo, $precioArticulo, $descripcionArticulo,
                        $seccionSelected)){
                        $articulo = $articuloRepository->find($id);
                        if ($articulo == null){
                            $this->addFlash('fail','No se ha podido encontrar el articulo ');
                            return $this->redirectToRoute('index');
                        }
                        if(!$this->ChequeaPropiedadArticulo($articulo->getIdVendedor(),$usuarioId,$usuarioRepository)){
                            $this->addFlash('fail','Solo puedes editar los articulos que te pertenecen. ');
                            return $this->redirectToRoute('index');
                        }

                        $articulo->setIdVendedor($this->getUser()->getIdVendedor());
                        $articulo->setNombre($nombreArticulo);
                        $articulo->setDescripcion($descripcionArticulo);
                        $articulo->setStock($stockArticulo);
                        $articulo->setPrecio($precioArticulo);
                        $articulo->setIdSeccion($seccionSelected);
                        if ($imagen){
                            if ($this->compruebaTipoImagen($imagen)){
                                if ($this->compruebaTamanoImagen($imagen)){
                                    try {
                                        $directorioImagenes = $this->getParameter('directorio_foto_articulo');
                                        $nombreUnicoImagen = uniqid(). "-". $usuarioId . ".".$imagen->guessExtension();
                                        $imagen->move($directorioImagenes,$nombreUnicoImagen);
                                        $articulo->setFoto($nombreUnicoImagen);
                                        } catch (\Exception $e) {
                                        $this->addFlash('fail','Ha ocurrido un error subiendo la imagen');
                                        }

                                    try {
                                    $fs = new Filesystem();
                                    $fs->remove($this->getParameter('directorio_foto_articulo').'/'.$imagenActual);

                                    }catch (\Exception $e) {
                                        $this->addFlash('fail','Ha ocurrido un error eliminando la imagen del sistema');
                                    }
                                } else {
                                    $this->addFlash('fail','El tamaño maximo permitido es 1MB.');
                                    return  $this->redirectToRoute('index');
                                }
                            } else {
                                $this->addFlash('fail','Solo se permiten imagenes JPG, JPEG y PNG..');
                                return  $this->redirectToRoute('index');
                            }

                        }
                        try {
                            $em->persist($articulo);
                            $em->flush();
                            $this->addFlash('success','Articulo editado exitosamente.');
                            return $this->redirectToRoute('articulo_show',['id'=>$articulo->getId()]);
                        } catch (\Exception $e){
                            $this->addFlash('fail','No se ha podido actualizar el articulo.');
                            return $this->redirectToRoute('articulo_show',['id'=>$articulo->getId()]);
                        }

                    } else {
                        $this->addFlash('fail','Hay errores en los campos del articulo que quieres editar.');
                        return $this->redirectToRoute('articulo_show',['id'=> $id]);

                    }
            }else {
                $this->addFlash('fail','Ha habido un problema con el token seguridad');
                return $this->redirectToRoute('index');

            }
        }
        else {
            $this->addFlash('fail','Debes iniciar sesion para poder crear articulos');
            return $this->redirectToRoute('app_login');
        }

    }

    /**
     * @Route("/nuevo", name="articulo_nuevo_post", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param ArticuloRepository $articuloRepository
     * @return Response
     */
    public function crearNuevoArticulo(
        Request $request,
        EntityManagerInterface $em,
        ArticuloRepository $articuloRepository): Response
    {

        $usuarioId = $request->request->get('usuario_id');

        if ($this->chequeaUsuarioLogueado($request)){
            if($this->isCsrfTokenValid('crear'.$this->getUser()->getId(),
                $request->request->get('csrf'))){
                if ($this->chequeaMismoUsuarioSesion($usuarioId)){

                $nombreArticulo = trim($request->request->get('nombre'));
                $stockArticulo = trim($request->request->get('stock'));
                $precioArticulo = trim($request->request->get('precio'));
                $descripcionArticulo = trim($request->request->get('descripcion'));
                $seccionSelected = trim($request->request->get('secciones'));
                $imagen = $request->files->get('imagen');


                if ($this->validaArticulos($nombreArticulo,$stockArticulo, $precioArticulo, $descripcionArticulo, $seccionSelected)){
                    $articulo = new Articulo();
                    $articulo->setIdVendedor($this->getUser()->getIdVendedor());
                    $articulo->setNombre($nombreArticulo);
                    $articulo->setDescripcion($descripcionArticulo);
                    $articulo->setStock($stockArticulo);
                    $articulo->setPrecio($precioArticulo);
                    $articulo->setIdSeccion($seccionSelected);
                    if ($imagen){
                    $tipoImagen = $imagen->guessExtension();
                        if ($tipoImagen == "jpg" || $tipoImagen == "jpeg"
                        || $tipoImagen == "png"){
                        $tamanhoImagen = filesize($imagen);
                            if ($tamanhoImagen <= 1060000){
                                $directorioImagenes = $this->getParameter('directorio_foto_articulo');
                                $nombreUnicoImagen = uniqid(). "-". $usuarioId . ".".$imagen->guessExtension();
                                try{
                                    $imagen->move($directorioImagenes,$nombreUnicoImagen);
                                    $articulo->setFoto($nombreUnicoImagen);
                                } catch (\Exception $e){
                                    $this->addFlash('fail','Ha ocurrido un error subiendo la imagen.');
                                }
                            }else {
                                $this->addFlash('fail','El tamaño de la imagen no puede exceder de 1MB');
                                return $this->redirectToRoute('articulo_new');
                            }

                        } else {
                            $this->addFlash('fail','Solo se admiten imagenes tipo JPG, JPEG o PNG');
                            return $this->redirectToRoute('articulo_new');
                        }
                    }
                    try {
                        $em->persist($articulo);
                        $em->flush();
                        $this->addFlash('success','Articulo creado exitosamente');
                        return $this->redirectToRoute('articulo_show',['id'=>$articulo->getId()]);

                    }catch (\Exception $e) {
                        $this->addFlash('fail','No se ha podido crear el articulo, intentalo de nuevo mas tarde.');
                        return $this->redirectToRoute('index');
                    }
                } else {
                    $this->addFlash('fail','Existen campos no validos en los detalles del articulo que intentaste crear, intentalo de nuevo.');
                    return $this->redirectToRoute('articulo_new');
                }

                } else {
                    $this->addFlash('fail','Solo puedes agregar articulos tuyos.');
                    return $this->redirectToRoute('index');

                }
            } else {
                $this->addFlash('fail','Ha ocurrido un error verificando el token de seguridad.');
                return $this->redirectToRoute('index');

            }
        } else {
            $this->addFlash('fail','Debes iniciar sesion para crear articulos.');
            return $this->redirectToRoute('app_login');
        }

    }


    /**
     * @Route("/{id<\d+>}", name="articulo_show", methods={"GET"})
     * @param Articulo $articulo
     * @param ValoracionRepository $valoracionRepository
     * @param DetalleRepository $detalleRepository
     * @return Response
     */
    public function show(Articulo $articulo,
                         ValoracionRepository $valoracionRepository,
                        DetalleRepository $detalleRepository): Response
    {
        $ventasArticulo = $detalleRepository->findVentasByArticuloId($articulo->getId());
        $estaValorado = 0;
        $valoracion = 0;
        if ($valoracionRepository->findValoracionItemsById($articulo->getId())){
            $valoracion = round($valoracionRepository->findValoracionItemsById($articulo->getId()),1);
            $estaValorado = 1;
        }

        return $this->render('articulo/show.html.twig', [
            'articulo' => $articulo,
            'ventas' => $ventasArticulo,
            'estaValorado'=> $estaValorado,
            'valoracion' => $valoracion,
        ]);
    }


    /**
     * @Route(
     *     "/todos/{id<\d+>}/{pagina<\d+>}",
     *     name="articulos_usuario",
     *     defaults = {
     *     "pagina" = 1 },
     *     methods = { "GET" }
     *     )
     * @param int $id
     * @param int $pagina
     * @param ArticuloRepository $articuloRepository
     * @param UsuarioRepository $usuarioRepository
     * @param SecurityManager $securityManager
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function mostrarTodosLosArticulosPorUsuario(int $id, int $pagina,
                   ArticuloRepository $articuloRepository, UsuarioRepository $usuarioRepository, SecurityManager $securityManager,
                   Request $request)
    {
        $ELEMENTOS_POR_PAGINA = 8;
        if ($securityManager->chequeaUsuarioSolicitud($request,$id)){
            if ($usuarioSolicitud = $usuarioRepository->find($id)){
                $totalArticulosVendedor = $articuloRepository->
                NumeroArticulosPorVendedor($usuarioSolicitud->getIdVendedor());
                $numero_paginas = 1;
                $totalArticulosVendedor > 0 ? $numero_paginas=ceil($totalArticulosVendedor/$ELEMENTOS_POR_PAGINA) : $numero_paginas = 1;
                if ($pagina<1){
                    $pagina = 1;
                    return $this->redirectToRoute('articulos_usuario',['id'=>$id,'pagina'=> $pagina]);
                }
                if ($pagina>$numero_paginas){
                    return $this->redirectToRoute('articulos_usuario',['id'=>$id,'pagina'=> $numero_paginas]);
                }
                return $this->render('articulo/articulos.html.twig',[
                    'articulos' => $articuloRepository->buscarArticulosPorVendedor($usuarioSolicitud->getIdVendedor(),$pagina,$ELEMENTOS_POR_PAGINA) ,
                    'usuario' => $usuarioSolicitud,
                    'pagina_actual' => $pagina,
                    'total_elementos' => $totalArticulosVendedor,
                    'numero_paginas' => $numero_paginas,
                ]);
            } else{
                $this->addFlash('fail','No se ha encontrado el vendedor.');
                return  $this->redirectToRoute('index');
            }
        } else {
            $this->addFlash('fail','Solo puedes ver tus articulos.');
            return  $this->redirectToRoute('index');
        }
    }

    public function validaArticulos($nombre, $stock, $precio, $descripcion, $seccionSelected): bool
    {
        if ($nombre != null && $stock != null && $precio != null && $descripcion != null && $seccionSelected != null){
            if (!empty($nombre) && !empty($stock) && !empty($precio) && !empty($descripcion) && !empty($seccionSelected)){
                return true;
            }  else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function chequeaMismoUsuarioSesion($idUsuarioSolicitud)
    {
        if($this->getUser()->getId() == $idUsuarioSolicitud) {
            return true;
        } else {
            return false;
        }
    }

    public function chequeaUsuarioLogueado(Request $request): bool
    {
        if( $request->getSession() && $this->getUser() != null) {
            return true;
        }
        else {
            return false;
        }
    }

    public function compruebaTipoImagen($imagen)
    {
        $tipoImagen = $imagen->guessExtension();
        if ($tipoImagen == "jpg" || $tipoImagen == "jpeg" || $tipoImagen == "png"){
            return true;
        } else {
            return false;
        }
    }

    public function compruebaTamanoImagen($imagen)
    {
        $tamanhoImagen = filesize($imagen);
        if ($tamanhoImagen <= 1060000){
            return true;
        } else {
            return false;
        }
    }

    public function ChequeaPropiedadArticulo($idVendedorArticulo,$usuarioId, UsuarioRepository $usuarioRepository)
    {
        $usuarioActual = $usuarioRepository->find($usuarioId);
        if ($usuarioActual == null){
            $this->addFlash('fail','El sueño del articulo no ha sido encontrado');
            return $this->redirectToRoute('index');
        }
        if ((int)$idVendedorArticulo != $usuarioActual->getIdVendedor()){
            return false;
        } else {
            return true;
        }
    }



}
