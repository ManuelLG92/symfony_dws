<?php

namespace App\Controller;

use App\Repository\ArticuloRepository;

use App\Repository\CarroRepository;
use App\Repository\ItemsRepository;
use App\Repository\SeccionRepository;
use App\Repository\ValoracionRepository;
use App\Security\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    const ELEMENTOS_POR_PAGINA = 6;
    /**
     * @Route("/", name="index")
     */
    public function index(ArticuloRepository $articuloRepository,
                          CarroRepository $carroRepository, ItemsRepository $itemsRepository,
                          ValoracionRepository $valoracionRepository, Request $request): Response
    {

        $alimentacionId = 1;
        $educacionId = 2;
        $electronicaId = 3;
        $hogarId = 4;
        $motorId = 5;
        $mueblesId = 6;
        $otrosId = 7;
        $articulosAlimentacion = $articuloRepository->findBySeccionId($alimentacionId);
        $articulosEducacion = $articuloRepository->findBySeccionId($educacionId);
        $articulosElectronica = $articuloRepository->findBySeccionId($electronicaId);
        $articulosHogar = $articuloRepository->findBySeccionId($hogarId);
        $articulosMotor = $articuloRepository->findBySeccionId($motorId);
        $articulosMuebles = $articuloRepository->findBySeccionId($mueblesId);
        $articulosOtros = $articuloRepository->findBySeccionId($otrosId);
        $itemsEnCarrito = 0;
        $session = $request->getSession();

        /*$valoracionProducto = round($valoracionRepository->findValoracionItemsById(2),1);
        $valoracionV = round($valoracionRepository->findValoracionVendedorById(4),1);

        return $this->json (['producto' => $valoracionProducto, 'vendedor' => $valoracionV ]);*/

        //$session->set('carro', 5);
        //$session->remove('carro');
       // $response = new Response();


        //$session = $request->getSession();
        if($request->getSession() && $this->getUser() != null){
            if ($this->getUser()->getId()){
                if ($carroCompraUsuarioIndex = $carroRepository->
                findOneByIdUsuario($this->getUser()->getId())){
                    $carroCompraUsuarioIndex->
                    setCantidad($itemsRepository->ItemsPorUsuario($carroCompraUsuarioIndex->getId()));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($carroCompraUsuarioIndex);
                    $em->flush();
                    $session->set('carro', $carroCompraUsuarioIndex->getCantidad());
                    // $itemsEnCarrito = ;
                } else {
                    $session->set('carro', 0);
                }
            }
        }

        return $this->render('index/index.html.twig', [
           // 'controller_name' => 'IndexController',
            'alimentacion' => $articulosAlimentacion,
            'idAlimentacion' => $alimentacionId,
            'educacion' => $articulosEducacion,
            'idEducacion' => $educacionId,
            'electronica' => $articulosElectronica,
            'idElectronica' => $electronicaId,
            'hogar' => $articulosHogar,
            'idHogar' => $hogarId,
            'motor' => $articulosMotor,
            'idMotor' => $motorId,
            'muebles' => $articulosMuebles,
            'idMuebles' => $mueblesId,
            'otros' => $articulosOtros,
            'idOtros' => $otrosId,
            'carro' => $itemsEnCarrito,

        ]);
    }

    /**
     * @Route(
     *     "/{seccion}/{pagina}",
     *     name="articulos_seccion",
     *     defaults = {
     *     "pagina" = 1 },
     *     requirements = {
     *     "pagina" = "\d+",
     *     "seccion" = "\d+"},
     *     methods = { "GET" }
     *     )
     * @param int $seccion
     * @param int $pagina
     * @param ArticuloRepository $articuloRepository
     * @param SeccionRepository $seccionRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function paginacionSeccion(int $seccion,int $pagina,
                                     ArticuloRepository $articuloRepository,
                                     SeccionRepository $seccionRepository)
    {

        $numeroSecciones = $seccionRepository->findSeccionesId();
        $idSecciones = [];
        foreach ($numeroSecciones as $seccionId){
            $idSecciones[] = (int)$seccionId['id'];
        }

        if (in_array($seccion,$idSecciones)){
            $totalArticulosPorSeccion = $articuloRepository->
            NumeroArticulosPorSeccion($seccion);

            $nombreSeccion = ucfirst($seccionRepository->find($seccion)->getDescripcion());
            $numeroPaginas = 1;
            $totalArticulosPorSeccion > 0 ? $numeroPaginas=ceil($totalArticulosPorSeccion/self::ELEMENTOS_POR_PAGINA) : $numeroPaginas = 1;
            if ($pagina<1) {
                $pagina = 1;
                return $this->redirect('/'.$pagina);
            }

            if ($pagina>$numeroPaginas) {
                return $this->redirect('/'.$numeroPaginas);
            }

            return $this->render('index/articulos_seccion.html.twig', [
                'articulos' => $articuloRepository->buscarArticulosPorSeccion($seccion,$pagina,self::ELEMENTOS_POR_PAGINA),
                'nombre_seccion' => $nombreSeccion,
                'pagina_actual' => $pagina,
                'total_elementos' => $totalArticulosPorSeccion,
                'numero_paginas' => $numeroPaginas,
                'seccion' => $seccion,

            ]);



            //return $this->json (['numero por seccion' => $totalArticulosPorSeccion]);
        } else {
            return $this->redirectToRoute('index');
        }
       // return $this->json (['secciones' => $numeroSecciones]);
    }
    /**
     * @Route(
     *     "/about",
     *     name="about",
     *     methods = { "GET" }
     *     )
     */
    public function nosotros ()
    {
        return $this->render('index/about.html.twig');
    }

    /**
     * @Route(
     *     "/contacto",
     *     name="contacto",
     *     methods = { "GET" }
     *     )
     */
    public function contacto ()
    {
        return $this->render('index/contacto.html.twig');
    }

    /**
     * @Route(
     *     "/contacto-envio",
     *     name="contacto_post",
     *     methods = { "Post" }
     *     )
     */
    public function enviarFormulario (Mailer $mailer,Request $request)
    {
        $nombre = $request->request->get('nombre');
        $asunto = $request->request->get('asunto');
        $email = $request->request->get('email');
        $descripcion = $request->request->get('descripcion');
        $html = 'Nombre: '.$nombre.'. Email: '. $email .'<br>Asunto: '.$asunto.'<br>Descripcion: '. $descripcion;
       $mailer->enviarEmail($this->getParameter('appEmailParametro'),"Contacto formulario",$html);

       return $this->redirectToRoute('index');
    }



}
