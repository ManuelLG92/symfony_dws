<?php

namespace App\Controller;


use App\Repository\ArticuloRepository;
use App\Repository\DetalleRepository;
use App\Repository\FacturaRepository;
use App\Repository\UsuarioRepository;
use App\Repository\ValoracionRepository;
use App\Service\SecurityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FacturaController extends AbstractController
{
    /**
     * @Route("/factura/{id<\d+>}/{usuario<\d+>}", name="factura"
     *     )
     */
    public function FacturaDetalle(int $id, int $usuario, SecurityManager $securityManager,
                   FacturaRepository $facturaRepository, DetalleRepository $detalleRepository,
                   UsuarioRepository $usuarioRepository, ArticuloRepository $articuloRepository,
                   ValoracionRepository $valoracionRepository, Request $request ): Response
    {
        if ($securityManager->chequeaUsuarioSolicitud($request,$usuario)){
            if($facturaSolicitada = $facturaRepository->find($id)){
                $detallesFactura = [];
                $detalles = $detalleRepository->findByFacturaId($facturaSolicitada->getId());
                foreach ($detalles as $detalle){
                    $vendedor = $usuarioRepository->findOneByIdVendedor($detalle->getIdVendedor());
                    $articulo = $articuloRepository->find($detalle->getIdArticulo());
                    $nombreVendedor = ucwords($vendedor->getNombre()." " . $vendedor->getApellido());
                    $valoracionArticuloValor = 0;
                    $idValoracion = 0 ;

                    if ($valoracionArticulo = $valoracionRepository->
                    findValoracionByClientArticuloAndFacturaId($usuario,$articulo->getId(),$facturaSolicitada->getId())){
                        $valoracionArticuloValor = $valoracionArticulo->getValor();
                        $idValoracion = $valoracionArticulo->getId();
                    }

                    $detallesFactura[] =
                        ['cantidad'=>$detalle->getCantidad(),'articulo'=> ucfirst($articulo->getNombre()),
                        'articuloId'=> $articulo->getId(),'precio' => $detalle->getPrecio(),
                        'total'=>$detalle->getTotal(),  'idVendedor'=> $vendedor->getId(),
                        'vendedor'=>$nombreVendedor, 'idDetalle'=>$detalle->getId(),
                        'valor' => $valoracionArticuloValor, 'idValoracion' => $idValoracion
                        ];
                }
                $valoracionesUsuario = [];
                $valoraciones = $valoracionRepository->findByIdComprador($this->getUser()->getId());
                if (count($valoraciones)>0){
                    foreach ($valoraciones as $valoracionEach){
                        $valoracionesUsuario[] = $valoracionEach;
                    }

                }

            }

        }

        return $this->render('factura/detalle.html.twig', [
            'factura' => $facturaSolicitada,
            'detalles' => $detallesFactura,
            'valoraciones'=>$valoraciones,
        ]);
    }
}
