<?php

namespace App\Controller;

use App\Repository\ArticuloRepository;

use App\Repository\CarroRepository;
use App\Repository\ItemsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(ArticuloRepository $articuloRepository,
                          CarroRepository $carroRepository, ItemsRepository $itemsRepository,
                          Request $request): Response
    {
        $articulosAlimentacion = $articuloRepository->findBySeccionId(1);
        $articulosEducacion = $articuloRepository->findBySeccionId(2);
        $articulosElectronica = $articuloRepository->findBySeccionId(3);
        $articulosHogar = $articuloRepository->findBySeccionId(4);
        $articulosMotor = $articuloRepository->findBySeccionId(5);
        $articulosMuebles = $articuloRepository->findBySeccionId(6);
        $articulosOtros = $articuloRepository->findBySeccionId(7);
        $itemsEnCarrito = 0;
        $session = $request->getSession();
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
            'educacion' => $articulosEducacion,
            'electronica' => $articulosElectronica,
            'hogar' => $articulosHogar,
            'motor' => $articulosMotor,
            'muebles' => $articulosMuebles,
            'otros' => $articulosOtros,
            'carro' => $itemsEnCarrito,
        ]);
    }
}
