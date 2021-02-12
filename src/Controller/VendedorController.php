<?php

namespace App\Controller;

use App\Repository\ArticuloRepository;
use App\Repository\DetalleRepository;
use App\Repository\DireccionRepository;
use App\Repository\UsuarioRepository;
use App\Repository\VendedorRepository;
use App\Service\SecurityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class  VendedorController extends AbstractController
{
    /**
     * @Route(
     *     "/ventas/{id<\d+>}/{pagina<\d+>}",
     *     name="ventas_usuario",
     *     defaults = {
     *     "pagina" = 1 },
     *     methods = { "GET" }
     *     )
     * @param int $id
     * @param int $pagina
     * @param UsuarioRepository $usuarioRepository
     * @param SecurityManager $securityManager
     * @param DetalleRepository $detalleRepository
     * @param VendedorRepository $vendedorRepository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function mostrarTodasLasFacturasPorCliente(int $id, int $pagina, UsuarioRepository $usuarioRepository,
                                                      SecurityManager $securityManager, DetalleRepository $detalleRepository,
                                                      VendedorRepository $vendedorRepository, Request $request)
    {
        $ELEMENTOS_POR_PAGINA = 8;
        if ($securityManager->chequeaUsuarioSolicitud($request,$id)){
            if ($usuarioSolicitud = $usuarioRepository->find($id)){
                try {
                    $totalVentasArticuloVendedor = $detalleRepository->CantidadVentasPorVendedor($usuarioSolicitud->getIdVendedor());
                } catch (\Exception $e){
                    $this->addFlash('fail','Ha ocurrido un error la cantidad de ventas');
                    return  $this->redirectToRoute('index');
                }
                if ($totalVentasArticuloVendedor==0){
                    $this->addFlash('fail','No se encontro ninguna venta.' );
                    return  $this->redirectToRoute('index');
                }
                $numero_paginas = 1;
                $totalVentasArticuloVendedor > 0 ? $numero_paginas=ceil($totalVentasArticuloVendedor/$ELEMENTOS_POR_PAGINA) : $numero_paginas = 1;
                if ($pagina<1){
                    $pagina = 1;
                    return $this->redirectToRoute('ventas_usuario',['id'=>$id,'pagina'=> $pagina]);
                }
                if ($pagina>$numero_paginas){
                    return $this->redirectToRoute('ventas_usuario',['id'=>$id,'pagina'=> $numero_paginas]);
                }
                return $this->render('usuario/ventas.html.twig',[
                    'ventas' => $detalleRepository->ArticulosPorVendedorPaginacion($usuarioSolicitud->getIdVendedor(),$pagina,$ELEMENTOS_POR_PAGINA),
                    'usuario' => $usuarioSolicitud,
                    'vendedor'=> $vendedorRepository->find($usuarioSolicitud->getIdVendedor()) ,
                    'pagina_actual' => $pagina,
                    'total_elementos' => $totalVentasArticuloVendedor,
                    'numero_paginas' => $numero_paginas,

                ]);
            } else{
                $this->addFlash('fail','No se ha encontrado el vendedor.');
                return  $this->redirectToRoute('index');
            }
        } else {
            $this->addFlash('fail','Solo puedes ver tus ventas.');
            return  $this->redirectToRoute('index');
        }
    }

    /**
     * @Route(
     *     "/vendedor/{id<\d+>}",
     *     name="perfil_vendedor",
     *     methods = { "GET" } )
     * @param int $id
     * @param Request $request
     * @param ArticuloRepository $articuloRepository
     * @param VendedorRepository $vendedorRepository
     * @param UsuarioRepository $usuarioRepository
     * @param DireccionRepository $direccionRepository
     * @param DetalleRepository $detalleRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function perfilVendedor(int $id, Request $request,
                                   ArticuloRepository $articuloRepository,
                                   VendedorRepository $vendedorRepository,
                                   UsuarioRepository $usuarioRepository,
                                    DireccionRepository $direccionRepository,
                                    DetalleRepository $detalleRepository)
    {
        if ($usuarioVendedor = $usuarioRepository->findOneByIdVendedor($id)){
        $vendedor = $vendedorRepository->find($usuarioVendedor->getIdVendedor());
        $articulosVendedor = $articuloRepository->findByIdVendedor($vendedor->getId());
        $direccionVendedor = $direccionRepository->find($usuarioVendedor->getDireccion());
        $numeroVentas = $detalleRepository->CantidadVentasPorVendedor($vendedor->getId());
        return $this->render('vendedor/perfilVendedor.html.twig',[
                'usuario' => $usuarioVendedor,
                'vendedor' => $vendedor,
                'articulos' => $articulosVendedor,
                'direccion' => $direccionVendedor,
                'ventas' => $numeroVentas,

            ]);
        }
        $this->addFlash('fail','No se ha podido encontrar el vendedor.');
        return $this->redirectToRoute('index');
    }

}
