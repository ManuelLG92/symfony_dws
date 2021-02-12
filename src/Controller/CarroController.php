<?php

namespace App\Controller;


use App\Entity\Articulo;
use App\Entity\Banco;
use App\Entity\Carro;
use App\Entity\Detalle;
use App\Entity\Factura;
use App\Entity\Items;
use App\Entity\Valoracion;
use App\Repository\ArticuloRepository;
use App\Repository\BancoRepository;
use App\Repository\CarroRepository;
use App\Repository\FacturaRepository;
use App\Repository\ItemsRepository;
use App\Repository\UsuarioRepository;
use App\Repository\ValoracionRepository;
use App\Repository\VendedorRepository;
use App\Service\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CarroController extends AbstractController
{
    /**
     * @Route("/carro", name="carro", methods={"POST"})
     * @param ArticuloRepository $articuloRepository
     * @param CarroRepository $carroRepository
     * @param ItemsRepository $itemsRepository
     * @param SecurityManager $securityManager
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * //Se realiza mediante AJAX
     * Codigos de respuesta:
     * 1 => articulo agregado al carro
     * 2 => no hay unidades disponibles
     * 3 => articulo existente en el carro
     * 4 => articulo no encontrado
     * 5 => Error creando el carro/No es peticion AJAX/No hay session
     *
     */
    public function AgregaItems(ArticuloRepository $articuloRepository,
                     CarroRepository $carroRepository, ItemsRepository $itemsRepository,
                    SecurityManager $securityManager ,Request $request,
                    EntityManagerInterface $em): Response
    {
        $usuarioSolicitud = $request->request->get('usuarioId');
        $idArticuloSolicitud = $request->request->get('articuloId');
        if( $securityManager->chequeaUsuarioSolicitud($request,$usuarioSolicitud)) {
                if($request->isXmlHttpRequest()){
                    if ($articulo = $articuloRepository->find($idArticuloSolicitud)){
                        if ($articulo->getStock()>0){
                          //  $carroCompraUsuario = $carroRepository->findOneByIdUsuario($usuarioSolicitud);
                            if ($carroCompraUsuario = $carroRepository->
                            findOneByIdUsuario($usuarioSolicitud)){
                                if ($itemsRepository->findItemByCarroIdAndArticuloId($carroCompraUsuario->getId(),$articulo->getId())){
                                    return $this->json(['respuesta' => 3]);
                                }
                                $agregarArticuloAlCarro = new Items();
                                $agregarArticuloAlCarro->setIdArticulo($idArticuloSolicitud);
                                $agregarArticuloAlCarro->setIdCarro($carroCompraUsuario->getId());
                                $em->persist($agregarArticuloAlCarro);
                                $em->flush();
                                $numeroDeItemsEnCarro = $itemsRepository->ItemsPorUsuario($carroCompraUsuario->getId());
                                //$numeroDeItemsEnCarro++;
                                $carroCompraUsuario->
                                setCantidad($numeroDeItemsEnCarro);
                                $em->persist($carroCompraUsuario);
                                $em->flush();
                                $request->getSession()->set('carro',$carroCompraUsuario->getCantidad());
                                return $this->json (['respuesta' => 1,
                                    'cantidad' => $carroCompraUsuario->getCantidad(),
                                                    ]);

                            } else {
                                $errorNuevoCarro = false;
                                $nuevoCarro = new Carro();
                                $nuevoCarro->setIdUsuario($usuarioSolicitud);
                                $nuevoCarro->setCantidad(1);
                                try {
                                    $em->persist($nuevoCarro);
                                    $em->flush();
                                } catch (\Exception $e) {
                                    $errorNuevoCarro = true;
                                }
                                if (!$errorNuevoCarro){
                                    $articuloNuevoCarro = new Items();
                                    $articuloNuevoCarro->setIdArticulo($idArticuloSolicitud);
                                    $articuloNuevoCarro->setIdCarro($nuevoCarro->getId());
                                    $em->persist($articuloNuevoCarro);
                                    $em->flush();
                                    $request->getSession()->set('carro',1);
                                    return $this->json(['respuesta' => 1,
                                                        'cantidad' => 1,]);
                                } else {
                                    $em->remove($nuevoCarro);
                                    return $this->json(['respuesta' => 5]);
                                }

                            }
                        } else {
                            return $this->json(['respuesta' => 2]);
                        }

                    } else {
                        return $this->json(['respuesta' => 4]);
                    }
                }
        }

            return $this->json(['respuesta' => 5]);

    }

    /**
     * @Route("/carro/{id}",
     *     requirements = {
     *     "id" = "\d+"},
     *     name="carro_get", methods={"GET"})
     * @param int $id
     * @param CarroRepository $carroRepository
     * @param ItemsRepository $itemsRepository
     * @param ArticuloRepository $articuloRepository
     * @param SecurityManager $securityManager
     * @param Request $request
     * @return Response
     */
    public function articulosEnCarro(int $id, CarroRepository $carroRepository,
                                     ItemsRepository $itemsRepository,
                                     ArticuloRepository $articuloRepository,
                                     SecurityManager $securityManager ,Request $request): Response
    {
        $articulosEnCarro = [];
        if($securityManager->chequeaUsuarioSolicitud($request,$id)){

            if($carroVista = $securityManager->
            chequeaPropiedadCarro($id, $carroRepository)){
                        if ($itemsEnCarro =
                            $itemsRepository->findItemsByCarroId($carroVista->getId())){
                            foreach ($itemsEnCarro as $item){
                              $articulo = $articuloRepository->find($item->getIdArticulo());
                                $articulosEnCarro[] = $articulo;
                            }
                            $session = $request->getSession();
                            $session->set('carro', $carroVista->getCantidad());
                            return $this->render('carro/carro.html.twig', [
                                'articulos' => $articulosEnCarro,
                            ]);
                        } else {
                            return $this->render('carro/carro.html.twig', [
                                'articulos' => null,
                            ]);
                        }

                }else {
                $this->addFlash('fail','Aun no hay elementos en tu carro, agrega algun articulo.');
                   return  $this->redirectToRoute('index');
                }

        }else {
            $this->addFlash('fail','Este carro no te pertenece');
            return  $this->redirectToRoute('index');
        }
    }

    /**
     * @Route("/compra", name="compra", methods={"POST"})
     * @param ArticuloRepository $articuloRepository
     * @param SecurityManager $securityManager
     * @param CarroRepository $carroRepository
     * @param ItemsRepository $itemsRepository
     * @param BancoRepository $bancoRepository
     * @param VendedorRepository $vendedorRepository
     * @param UsuarioRepository $usuarioRepository
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function compra(ArticuloRepository $articuloRepository,SecurityManager $securityManager,
                           CarroRepository $carroRepository ,ItemsRepository $itemsRepository,
                           BancoRepository $bancoRepository, VendedorRepository $vendedorRepository,
                           UsuarioRepository $usuarioRepository, Request $request,
                            EntityManagerInterface $em):Response
    {
        $articulos = [];
        $i = 0;
        $parametros =  $request->request->all();
        $idArticulo = "";
        $idUsuarioCompra = $parametros["id_usuario"];
        $importeCompra = $parametros["total"];

        if($securityManager->chequeaUsuarioSolicitud($request,$idUsuarioCompra)) {

            $usuarioCompra = $usuarioRepository->find($this->getUser()->getId());
            if ($importeCompra > $usuarioCompra->getBanco()->getBalance()) {
                $this->addFlash('fail', 'No tienes BC suficientes para esta compra. Tienes ' . $usuarioCompra->getBanco()->getBalance() . ' BC disponibles.');
                return $this->redirectToRoute('carro_get', ['id' => $idUsuarioCompra]);
            }

            $errorOperacion = false;

            foreach ($parametros as $llave => $parametro) {
                if ($llave == "id_usuario" || $llave == "total"
                    || str_starts_with($llave, 'h') || $llave == "comprar") {
                    continue;
                }
                $i++;
                if ($i % 2 == 0) {
                    $unidades = $parametro;
                    $articulos[] = ["id" => $idArticulo, "unidades" => (int)$unidades];
                } else {
                    $idArticulo = (int)$parametro;

                }

            }


            $factura = new Factura();

            $fecha = new \DateTime('now');
            $factura->setFecha($fecha);
            $factura->setIdCliente($usuarioCompra->getId());
            try {
                $em->persist($factura);
                $em->flush();
            } catch (\Exception $ex) {
                $errorOperacion = true;
            }
            if (!$errorOperacion) {

                $detalles = [];
                $itemsEnCarro = [];
                $articulosEnElCarro = [];
                $vendedoresArticulos = [];
                $total = 0;
                $carroActual = $carroRepository->findOneByIdUsuario($usuarioCompra->getId());
                foreach ($articulos as $articulo) {
                    try {
                        $articuloPorId = $articuloRepository->find($articulo['id']);
                        $itemEnCarro = $itemsRepository->
                        findItemByCarroIdAndArticuloId($carroActual->getId(), $articuloPorId->getId());
                        $bancoUsuario = $usuarioCompra->getBanco();


                        if ($articuloPorId != null && $carroActual != null
                            && $itemEnCarro != null && $bancoUsuario != null) {
                            if ($articuloPorId->getStock() >= (int)$articulo['unidades']) {
                                $detalle = new Detalle();
                                $vendedorArticulo = $vendedorRepository->find($articuloPorId->getIdVendedor());
                                $detalle->setIdVendedor($vendedorArticulo->getId());
                                $detalle->setNumeroFactura($factura->getId());
                                $detalle->setIdArticulo($articuloPorId->getId());
                                $detalle->setCantidad((int)$articulo['unidades']);
                                $detalle->setPrecio($articuloPorId->getPrecio());
                                $totalDetalle = (int)$articulo['unidades'] * $articuloPorId->getPrecio();
                                $detalle->setTotal((int)$totalDetalle);
                                $em->persist($detalle);
                                $em->flush();

                                $itemsEnCarro [] = $itemEnCarro;


                                $articuloPorId->setStock(($articuloPorId->getStock() - (int)$articulo['unidades']));
                                $articulosEnElCarro[] = $articuloPorId;
                                $detalles[] = $detalle;
                                $vendedorArticulo->setImporteVentas($vendedorArticulo->getImporteVentas() + (int)$totalDetalle);
                                $vendedorArticulo->setNumeroVentas($vendedorArticulo->getNumeroVentas() + (int)$articulo['unidades']);
                                $em->persist($vendedorArticulo);
                                $em->flush();
                                $vendedoresArticulos [] = $vendedorArticulo;
                                $usuarioVendedor = $usuarioRepository->findOneByIdVendedor($vendedorArticulo->getId());
                                $bancoVendedor = $usuarioVendedor->getBanco();
                                $bancoVendedor->setBalance($bancoVendedor->getBalance() + $totalDetalle);
                                $carroActual->setCantidad($carroActual->getCantidad() - 1);
                                $em->persist($carroActual);
                                $em->flush();
                                $total += $articulo['unidades'] * $articuloPorId->getPrecio();

                            } else {
                                $this->addFlash('fail', 'Solo quedan ' . $articuloPorId->getStock() . " unidades del articulo " . $articuloPorId->getNombre() . ' y has solicitado ' . (int)$articulo['unidades'] . '.Vuelve a intentarlo.');

                            }
                        } else {
                            $this->addFlash('fail', 'Ha ocurrido un error. No se ha encontado el articulo ' . $articuloPorId->getNombre() . '.');

                        }
                    } catch (\Exception $e) {
                        $errorOperacion = true;
                    }
                }
            } else {
                $this->addFlash('fail', 'Ha habido un error creando la factura. ');
                return $this->redirectToRoute('carro_get', ['id' => $idUsuarioCompra]);

            }

            if (!$errorOperacion) {
                foreach ($itemsEnCarro as $itemCarro) {
                    $em->remove($itemCarro);
                    $em->flush();
                }

                $banco = $bancoRepository->find($usuarioCompra->getBanco()->getId());
                $banco->setBalance($banco->getBalance() - $total);
                $em->persist($banco);
                $em->flush();

                $factura->setImporte($total);
                $em->persist($factura);
                $em->flush();

                foreach ($articulosEnElCarro as $articuloPersist) {
                    $em->persist($articuloPersist);
                    $em->flush();
                }

                foreach ($vendedoresArticulos as $vendedorPersist) {
                    $em->persist($vendedorPersist);
                    $em->flush();
                }

                $request->getSession()->set('carro', $itemsRepository->findItemsByCarroId($carroActual->getId()));
                $this->addFlash('success', 'Compra realizada exitosamente.');
                return $this->redirectToRoute('factura', ['id' => $factura->getId(), 'usuario' => $idUsuarioCompra]);



            } else {
                foreach ($detalles as $detallePersist) {
                    $em->remove($detallePersist);
                    $em->flush();
                }
                $this->addFlash('fail', 'Ha habido un error al realizar la compra. Intentalo en unos minutos.');
                return $this->redirectToRoute('carro_get', ['id' => $idUsuarioCompra]);

                // return $this->json (['respuesta' => "Algo salio mal removing" ]);
            }/**/

        } else {
            $this->addFlash('fail', 'Solo puedes realizar operaciones de tu carro de compra.');
            return $this->redirectToRoute('index');
        }
    }

    /**
     * @Route("/carro/{id<\d+>}/{usuario<\d+>}",
     *     name="item_delete", methods={"GET"})
     * @param int $id
     * @param int $usuario
     * @param CarroRepository $carroRepository
     * @param ItemsRepository $itemsRepository
     * @param SecurityManager $securityManager
     * @param UsuarioRepository $usuarioRepository
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function eliminarItemCarrito(int $id,int $usuario,
                    CarroRepository $carroRepository,ItemsRepository $itemsRepository,
                    SecurityManager $securityManager, UsuarioRepository $usuarioRepository,
                    EntityManagerInterface $em, Request $request)
    {
        if($securityManager->chequeaUsuarioSolicitud($request,$usuario)){
            $carroItemEliminar = $securityManager->
            chequeaPropiedadCarro($usuario, $carroRepository);
            if($carroItemEliminar) {
                if($itemParaEliminar = $itemsRepository->
                findItemByCarroIdAndArticuloId($carroItemEliminar->getId(),$id)){
                    $em->remove($itemParaEliminar);
                    $em->flush();
                    $itemsDelCarro = $itemsRepository->ItemsPorUsuario($carroItemEliminar->getId());
                    $carroItemEliminar->setCantidad($itemsDelCarro);
                    $em->persist($carroItemEliminar);
                    $em->flush();
                    $session = $request->getSession();
                    $session->set('carro', $carroItemEliminar->getCantidad());
                    $this->addFlash('success','Articulo eliminado de la cesta exitosamente');
                    return $this->redirectToRoute('carro_get', ['id' => $usuario]);
                }
                else {$this->addFlash('fail','No ha se ha podido eliminar el articulo, intentalo de nuevo.');
                    return $this->redirectToRoute('carro_get', ['id' => $usuario]);
                }

            }else {
                $this->addFlash('fail','No se pudo verificar que la cesta de la solicitud te pertenezca.');
                return $this->redirectToRoute('index');
            }
        }else {
            $this->addFlash('fail','Debes estar autenticado para eliminar items de tu cesta de compra');
            return $this->redirectToRoute('index');
        }

    }

    /**
     * @Route("/valoracion",
     *     name="valoracion", methods={"POST"})
     * @param UsuarioRepository $usuarioRepository
     * @param FacturaRepository $facturaRepository
     * @param SecurityManager $securityManager
     * @param EntityManagerInterface $em
     * @param ArticuloRepository $articuloRepository
     * @param VendedorRepository $vendedorRepository
     * @param ValoracionRepository $valoracionRepository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ValoracionArticulos(UsuarioRepository $usuarioRepository,
                    FacturaRepository $facturaRepository,SecurityManager $securityManager,
                    EntityManagerInterface $em, ArticuloRepository $articuloRepository,
                    VendedorRepository $vendedorRepository, ValoracionRepository $valoracionRepository,
                    Request $request)
    {
    $parametros = $request->request->all();
    $idFactura = $parametros['idFactura'];
    $idUsuario = $parametros['idUsuario'];

        if($securityManager->chequeaUsuarioSolicitud($request,$idUsuario)){
           if( $FacturaValoracion = $facturaRepository->find($idFactura)){
                if ($FacturaValoracion->getIdCliente() != (int)$idUsuario){
                    $this->addFlash('fail','Solo puedes valorar tus facturas');
                    return $this->redirectToRoute('perfil_usuario', ['id' => $idUsuario]);
                }

                $contador = 0;
                $valoracionForm = 0;
                $idVendedor = 0;
                $idDetalle = 0;
                $idArticulo = 0;
                $idValoracion = 0;
                $valoraciones = [];
               $idVendedores = [];
               $idArticulos = [];


               foreach ($parametros as $llave => $parametro){
                   if ($llave == "idFactura" || $llave == "idUsuario"){
                       continue;
                   }
                   $contador++;
                   if ($contador == 1){
                       $valoracionForm = $parametro;
                       continue;
                   }
                   if ($contador == 2){
                       $idValoracion = $parametro;
                       continue;
                   }

                   if ($contador == 3){
                       $idArticulo = $parametro;
                       continue;
                   }

                   if ($contador == 4){
                       $idVendedor = $parametro;
                       continue;
                   } if ($contador == 5) {
                       $idDetalle = $parametro;

                       $valoraciones[] =
                           ['idVendedor' => $idVendedor,'idCliente' => $idUsuario,
                           'idArticulo' => $idArticulo, 'idDetalle' =>$idDetalle,
                           'idFactura' => $FacturaValoracion->getId(), 'valor' => $valoracionForm,
                           'idValoracion' => $idValoracion];
                       $contador = 0;
                       continue;
                   }
               }
                $errorValoracion = false;
               $valoracionesPersist = [];

               try {
                  foreach ($valoraciones as $val){
                      if ($val['idValoracion'] == 0) {
                      $valoracion = new Valoracion();
                      $vendedor = $usuarioRepository->find($val['idVendedor']);
                      $valoracion->setIdVendedor($vendedor->getIdVendedor());
                      $valoracion->setIdCliente($val['idCliente']);
                      $valoracion->setIdArticulo($val['idArticulo']);
                      $valoracion->setNumeroDetalle($val['idDetalle']);
                      $valoracion->setNumeroFactura($val['idFactura']);
                      $valoracion->setValor($val['valor']);
                      $fechaActual = new \DateTime('now');
                      $valoracion->setFecha($fechaActual);
                      $valoracionesPersist[] = $valoracion;
                      $idVendedores[] = $vendedor->getIdVendedor();
                      $idArticulos[] = $val['idArticulo'];
                      } else {
                        $valoracionExistente = $valoracionRepository->find((int)$val['idValoracion']);
                          $valoracionExistente->setValor($val['valor']);
                          $idVendedores[] = $valoracionExistente->getIdVendedor();
                          $idArticulos[] = $valoracionExistente->getIdArticulo();
                          $valoracionesPersist[] = $valoracionExistente;
                      }
                  }
               } catch (\Exception $exception){
                   $errorValoracion = true;
               }


               if ($errorValoracion){
                   $this->addFlash('fail','Ha ocurrido un error durante la valoracion, intentalo en unos minutos.');
                   return $this->redirectToRoute('perfil_usuario', ['id' => $idUsuario]);
               } else {
                   foreach ($valoracionesPersist as $valoracionCrear){
                       $em->persist($valoracionCrear);
                       $em->flush();
                   }
                   $arrayVendedorUnicos = array_unique($idVendedores);
                   $arrayArticulosUnicos = array_unique($idArticulos);

                   foreach ($arrayVendedorUnicos as $vendedorId){
                       $vendedorActual = $vendedorRepository->find($vendedorId);
                       $valoracionVendedor = round($valoracionRepository->
                       findValoracionVendedorById($vendedorActual->getId()),1);
                       $vendedorActual->setValoracion($valoracionVendedor);
                       $em->persist($vendedorActual);
                       $em->flush();
                   }

                   foreach ($arrayArticulosUnicos as $articuloId){
                       $articuloActual = $articuloRepository->find($articuloId);
                       $valoracionArticulo = round($valoracionRepository->
                       findValoracionItemsById($articuloActual->getId()),1);
                       $articuloActual->setValoracion($valoracionArticulo);
                       $em->persist($articuloActual);
                       $em->flush();
                   }

                   $this->addFlash('success','Valoracion exitosa.');
                   return $this->redirectToRoute('factura', ['id' => $idFactura,'usuario'=> $idUsuario]);

               }

           } else {
               $this->addFlash('fail','Factura no encontrada');
               return $this->redirectToRoute('perfil_usuario', ['id' => $idUsuario]);
           }

        } else {
            $this->addFlash('fail','Debe iniciar sesion para ver alguna factura');
            return $this->redirectToRoute('app_login');
        }

       // return $this->json (['respuesta' => $parametros, $idFactura, $idUsuario]);
    }
}



