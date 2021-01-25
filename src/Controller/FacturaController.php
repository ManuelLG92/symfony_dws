<?php

namespace App\Controller;


use App\Repository\ArticuloRepository;
use App\Repository\DetalleRepository;
use App\Repository\FacturaRepository;
use App\Repository\UsuarioRepository;
use App\Service\SecurityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FacturaController extends AbstractController
{
    /**
     * @Route("/factura/{id}", name="factura" ,
     *     requirements = {
     *     "id" = "\d+"})
     */
    public function FacturaDetalle(int $id, SecurityManager $securityManager,
                   FacturaRepository $facturaRepository, DetalleRepository $detalleRepository,
                   UsuarioRepository $usuarioRepository, ArticuloRepository $articuloRepository,
                   Request $request ): Response
    {
        if ($securityManager->chequeaUsuarioSolicitud($request,$this->getUser()->getId())){
            if($facturaSolicitada = $facturaRepository->find($id)){
                $detallesFactura = [];
                $detalles = $detalleRepository->findByFacturaId($facturaSolicitada->getId());
                foreach ($detalles as $detalle){
                    $vendedor = $usuarioRepository->findOneByIdVendedor($detalle->getIdVendedor());
                    $articulo = $articuloRepository->find($detalle->getIdArticulo());
                    $nombreVendedor = ucwords($vendedor->getNombre()." " . $vendedor->getApellido());
                    $detallesFactura[] =
                        ['cantidad'=>$detalle->getCantidad(),'articulo'=> ucfirst($articulo->getNombre()),
                        'articuloId'=> $articulo->getId(),'precio' => $detalle->getPrecio(),
                        'total'=>$detalle->getTotal(),  'idVendedor'=> $vendedor->getId(),
                        'vendedor'=>$nombreVendedor,
                        ];
                }

            }

        }

        return $this->render('factura/detalle.html.twig', [
            'factura' => $facturaSolicitada,
            'detalles' => $detallesFactura,
        ]);
    }
}
