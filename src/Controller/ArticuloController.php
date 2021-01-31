<?php

namespace App\Controller;

use App\Entity\Articulo;
use App\Entity\Usuario;
use App\Form\ArticuloType;
use App\Repository\ArticuloRepository;
use App\Repository\SeccionRepository;
use App\Repository\UsuarioRepository;
use App\Service\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @Route("/articulo")
 */
class ArticuloController extends AbstractController

{
    /**
     * @Route("/", name="articulo_index", methods={"GET"})
     */
    public function index(ArticuloRepository $articuloRepository): Response
    {
        return $this->render('articulo/carro.html.twig', [
            'articulos' => $articuloRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="articulo_new", methods={"GET"})
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
     * @Route("/editar/{id<\d+>}", name="editar_articulo_get", methods={"GET"})
     */
    public function editarArticuloGet(int $id,
      ArticuloRepository $articuloRepository,SeccionRepository $seccionRepository,
        Request $request): Response
    {
        $usuarioId = $request->request->get('usuario_id');
        if ($this->chequeaUsuarioLogueado($request)){

                    $articulo = $articuloRepository->find($id);
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
     */
    public function editarArticulo(
        int $id, ArticuloRepository $articuloRepository,
        EntityManagerInterface $em, Request $request): Response
    {
        $usuarioId = $request->request->get('usuario_id');

        //dump($usuarioId);
        if ($this->chequeaUsuarioLogueado($request)){
            if($this->isCsrfTokenValid('editar'.$this->getUser()->getId(),
                $request->request->get('csrf'))){
                if ($this->chequeaMismoUsuarioSesion($usuarioId)){

                    $nombreArticulo = trim($request->request->get('nombre'));
                    $stockArticulo = trim($request->request->get('stock'));
                    $precioArticulo = trim($request->request->get('precio'));
                    $descripcionArticulo = trim($request->request->get('descripcion'));
                    $seccionSelected = trim($request->request->get('secciones'));

                    if ($this->validaArticulos($nombreArticulo,
                        $stockArticulo, $precioArticulo, $descripcionArticulo,
                        $seccionSelected)){
                        $articulo = $articuloRepository->find($id);
                        $articulo->setIdVendedor($this->getUser()->getIdVendedor());
                        $articulo->setNombre($nombreArticulo);
                        $articulo->setDescripcion($descripcionArticulo);
                        $articulo->setStock($stockArticulo);
                        $articulo->setPrecio($precioArticulo);
                        $articulo->setIdSeccion($seccionSelected);
                        try {
                            $em->persist($articulo);
                            $em->flush();
                            $response = new Response(
                                'Articulo Editada' ,
                                Response::HTTP_NOT_FOUND,
                                ['content-type' => 'text/html']
                            );
                            return $response;
                        }catch (\Exception $e) {
                            $articuloError = true;
                        }
                    } else {
                        $response = new Response(
                            'Campos no validos' ,
                            Response::HTTP_NOT_FOUND,
                            ['content-type' => 'text/html']
                        );
                        return $response;
                    }
                }else {
                    $response = new Response(
                        'Diferente usuario' ,
                        Response::HTTP_NOT_FOUND,
                        ['content-type' => 'text/html']
                    );
                    return $response;
                }
            }else {
                $response = new Response(
                    'Token no valido' ,
                    Response::HTTP_NOT_FOUND,
                    ['content-type' => 'text/html']
                );
                return $response;
            }
        }
        else {
            return $this->redirectToRoute('app_login');
        }

    }

    /**
     * @Route("/nuevo", name="articulo_nuevo_post", methods={"POST"})
     */
    public function crearNuevoArticulo(
        Request $request,
        EntityManagerInterface $em,
        ArticuloRepository $articuloRepository): Response
    {

        $usuarioId = $request->request->get('usuario_id');

        dump($usuarioId);
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
                                    $response = new Response(
                                        'Imagen no subida y articulo no creado: ' . $e ,
                                        Response::HTTP_NOT_FOUND,
                                        ['content-type' => 'text/html']
                                    );
                                    return $response;
                                }
                            }

                        }
                    }
                    try {
                        $em->persist($articulo);
                        $em->flush();
                        $response = new Response(
                            'Articulo creado' ,
                            Response::HTTP_NOT_FOUND,
                            ['content-type' => 'text/html']
                        );
                        return $response;
                    }catch (\Exception $e) {
                         $articuloError = true;
                    }
                } else {
                    $response = new Response(
                        'Campos articulo no validos' ,
                        Response::HTTP_NOT_FOUND,
                        ['content-type' => 'text/html']
                    );
                    return $response;
                }
                $respuesta = 'Token valido' . $usuarioId;
                $response = new Response(
                    $respuesta,
                    Response::HTTP_NOT_FOUND,
                    ['content-type' => 'text/html']
                );
                return $response;

                } else {
                    $response = new Response(
                        'id usuario diferente',
                        Response::HTTP_NOT_FOUND,
                        ['content-type' => 'text/html']
                    );
                    return $response;
                }
            } else {
                $response = new Response(
                    'Token invalido',
                    Response::HTTP_NOT_FOUND,
                    ['content-type' => 'text/html']
                );
                return $response;
            }
        } else {
            return $this->redirectToRoute('app_login');
        }

       /* return $this->render('articulo/show.html.twig', [
            'articulo' => $articulo,
        ]);*/
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
                /*if ($this->getUser()->getId() == $idUsuarioSolicitud){
            } else {
                return false;
            }*/
        }
             else {
                return false;
            }
    }

    /**
     * @Route("/{id<\d+>}", name="articulo_show", methods={"GET"})
     */
    public function show(Articulo $articulo): Response
    {
        return $this->render('articulo/show.html.twig', [
            'articulo' => $articulo,
        ]);
    }

    /**
     * @Route("/{id<\d+>}/edit", name="articulo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Articulo $articulo): Response
    {
        $form = $this->createForm(ArticuloType::class, $articulo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('articulo_index');
        }

        return $this->render('articulo/edit.html.twig', [
            'articulo' => $articulo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id<\d+>}", name="articulo_delete", methods={"DELETE"})
     */
 /*   public function delete(Request $request, Articulo $articulo,
                           SecurityManager $securityManager): Response
    {
        $idUsuarioSolicitud = (int)$request->request->get('idUsuario');
        if ($securityManager->chequeaUsuarioSolicitud($request,$idUsuarioSolicitud)){
            if ($this->isCsrfTokenValid('delete'.$articulo->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($articulo);
                $entityManager->flush();
                return $this->redirectToRoute('perfil_usuario',['id'=> $idUsuarioSolicitud]);
            } else {
                return $this->redirectToRoute('perfil_usuario',['id'=> $this->getUser()->getId()]);
            }
        } else {
            return $this->redirectToRoute('index');
        }


    }*/


}
