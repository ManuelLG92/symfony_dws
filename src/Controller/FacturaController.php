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
     * @Route("/factura/{id<\d+>}/{usuario<\d+>}", name="factura", methods={"GET"}
     *     )
     * @param int $id
     * @param int $usuario
     * @param SecurityManager $securityManager
     * @param FacturaRepository $facturaRepository
     * @param DetalleRepository $detalleRepository
     * @param UsuarioRepository $usuarioRepository
     * @param ArticuloRepository $articuloRepository
     * @param ValoracionRepository $valoracionRepository
     * @param Request $request
     * @return Response
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
                        'valor' => $valoracionArticuloValor, 'idValoracion' => $idValoracion,
                            'perfilVendedor' => $vendedor->getIdVendedor(),
                        ];
                }
                $valoracionesUsuario = [];
                $valoraciones = $valoracionRepository->findByIdComprador($this->getUser()->getId());
                if (count($valoraciones)>0){
                    foreach ($valoraciones as $valoracionEach){
                        $valoracionesUsuario[] = $valoracionEach;
                    }

                }

                return $this->render('factura/detalle.html.twig', [
                    'factura' => $facturaSolicitada,
                    'detalles' => $detallesFactura,
                    'valoraciones'=>$valoraciones,
                ]);
            } else {
                $this->addFlash('fail','Factura'. $id .' no encontrada');
                return  $this->redirectToRoute('perfil_usuario',['id'=> $usuario]);
               // return $this->redirectToRoute('index');

            }

        } else {
            $this->addFlash('fail','Solo puedes ver tus facturas.');
            return $this->redirectToRoute('index');
        }

    }

    /**
     * @Route(
     *     "/facturas/{id<\d+>}/{pagina<\d+>}",
     *     name="facturas_usuario",
     *     defaults = {
     *     "pagina" = 1 },
     *     methods = { "GET" }
     *     )
     * @param int $id
     * @param int $pagina
     * @param UsuarioRepository $usuarioRepository
     * @param SecurityManager $securityManager
     * @param FacturaRepository $facturaRepository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function mostrarTodasLasFacturasPorCliente(int $id, int $pagina, UsuarioRepository $usuarioRepository,
                                                      SecurityManager $securityManager, FacturaRepository $facturaRepository,
                                                      Request $request)
    {
        $ELEMENTOS_POR_PAGINA = 8;
        if ($securityManager->chequeaUsuarioSolicitud($request,$id)){
            if ($usuarioSolicitud = $usuarioRepository->find($id)){
                try {
                    $totalFacturasCliente = $facturaRepository->numeroFacturasPorCliente($id);
                } catch (\Exception $e){
                    $this->addFlash('fail','Ha ocurrido un error buscando tus facturas' . $e);
                    return  $this->redirectToRoute('index');
                }
                 if ($totalFacturasCliente==0){
                     $this->addFlash('fail','No se encontraron facturas disponible' );
                     return  $this->redirectToRoute('index');
                 }
                $numero_paginas = 1;
                $totalFacturasCliente > 0 ? $numero_paginas=ceil($totalFacturasCliente/$ELEMENTOS_POR_PAGINA) : $numero_paginas = 1;
                if ($pagina<1){
                    $pagina = 1;
                    return $this->redirectToRoute('facturas_usuario',['id'=>$id,'pagina'=> $pagina]);
                }
                if ($pagina>$numero_paginas){
                    return $this->redirectToRoute('facturas_usuario',['id'=>$id,'pagina'=> $numero_paginas]);
                }
                return $this->render('factura/facturas.html.twig',[
                    'facturas' => $facturaRepository->buscarFacturasPorUsuario($id,$pagina,$ELEMENTOS_POR_PAGINA),
                    'usuario' => $usuarioSolicitud,
                    'pagina_actual' => $pagina,
                    'total_elementos' => $totalFacturasCliente,
                    'numero_paginas' => $numero_paginas,
                    'gasto' => $facturaRepository->importeGastoPorUsuario($id)
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
}
