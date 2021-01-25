<?php

namespace App\Controller;


use App\Entity\Articulo;
use App\Entity\Banco;
use App\Entity\Carro;
use App\Entity\Detalle;
use App\Entity\Factura;
use App\Entity\Items;
use App\Repository\ArticuloRepository;
use App\Repository\BancoRepository;
use App\Repository\CarroRepository;
use App\Repository\ItemsRepository;
use App\Repository\UsuarioRepository;
use App\Repository\VendedorRepository;
use App\Service\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CarroController extends AbstractController
{
    /**
     * @Route("/carro", name="carro", methods={"POST"})
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

                                $agregarArticuloAlCarro = new Items();
                                $agregarArticuloAlCarro->setIdArticulo($idArticuloSolicitud);
                                $agregarArticuloAlCarro->setIdCarro($carroCompraUsuario->getId());
                                $em->persist($agregarArticuloAlCarro);
                                $em->flush();
                                $numeroDeItemsEnCarro = $itemsRepository->ItemsPorUsuario($carroCompraUsuario->getId());
                                //$numeroDeItemsEnCarro++;
                                $carroCompraUsuario->
                                setCantidad($numeroDeItemsEnCarro++);
                                $em->persist($carroCompraUsuario);
                                $em->flush();
                                $request->getSession()->set('carro',$carroCompraUsuario->getCantidad());
                                return $this->json (['respuesta' => "Articulo agregado al carro existente",
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
                                    return $this->json(['respuesta' => "Carro creado y Articulo agregado",
                                                        'cantidad' => 1,]);
                                } else {
                                    $em->remove($nuevoCarro);
                                    return $this->json(['respuesta' => "Ha habido un error creando el carro"]);
                                }

                            }
                        } else {
                            return $this->json(['respuesta' => "no hay unidades disponibles"]);
                        }
                       // return $this->json(['respuesta' => "Valido. articulo encontrado"]);
                    } else {
                        return $this->json(['respuesta' => "articulo no encontrado"]);
                    }
                //return $this->json(['respuesta' => "si"]);
                } else {
                    return $this->json(['respuesta' => "no es ajax"]);
                }
        }
        else {

            return $this->json(['respuesta' => "no sesion "]);
        }

    }


    /**
     * @Route("/carro/{id}",
     *     requirements = {
     *     "id" = "\d+"},
     *     name="carro_get", methods={"GET"})
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
                            return $this->render('carro/carro.html.twig', [
                                'articulos' => $articulosEnCarro,
                            ]);
                        } else {
                            return $this->render('carro/carro.html.twig', [
                                'articulos' => 'No fue posible',
                            ]);
                        }

                }else {
                    return $this->render('carro/carro.html.twig', [
                        'articulos' => 'Este carro no existe o no te pertenece',
                    ]);
                }

        }else {
            return $this->render('carro/carro.html.twig', [
                'articulos' => 'Diferente usuario en la solicitud',
            ]);
        }
    }

    /**
     * @Route("/compra", name="compra", methods={"POST"})
     */
    public function compra(ArticuloRepository $articuloRepository,SecurityManager $securityManager,
                           CarroRepository $carroRepository ,ItemsRepository $itemsRepository,
                           BancoRepository $bancoRepository, VendedorRepository $vendedorRepository,
                           UsuarioRepository $usuarioRepository, Request $request, EntityManagerInterface $em):Response
    {
        $articulos = [];
        $i = 0;
        $parametros =  $request->request->all();
        $idArticulo = "";
        $idUsuarioCompra = $parametros["id_usuario"];
        $importeCompra = $parametros["total"];
        if($securityManager->chequeaUsuarioSolicitud($request,$idUsuarioCompra)){
            $usuarioCompra = $usuarioRepository->find($this->getUser()->getId());
            if ($importeCompra > $usuarioCompra->getBanco()->getBalance()){
                return $this->json (['respuesta' => 5 ]);
            }
            $errorOperacion = false;

            foreach ($parametros as $llave => $parametro) {
                if ($llave == "id_usuario" || $llave == "total"){
                    continue;
                }
                $i++;
                if ($i%2==0){
                    $unidades = $parametro;
                    $articulos[] = ["id" => $idArticulo, "unidades" => (int)$unidades];
                } else {
                    $idArticulo = (int)$parametro;

                }
            //TODO procesar los articulos, capturados mediante foreach en una array clave => valor = [id=unidades]

            }
            $factura = new Factura();

            $fecha = new \DateTime('now');
            $factura->setFecha($fecha);
            $factura->setIdCliente($usuarioCompra->getId());
            try {
                $em->persist($factura);
                $em->flush();
            } catch(\Exception $ex){
                $errorOperacion = true;
            }
            if (!$errorOperacion){

                $detalles = [];
                $itemsEnCarro = [];
                $articulosEnElCarro = [];
                $vendedoresArticulos = [];
                $total = 0;
                foreach ($articulos as  $articulo){
            try {
                $articuloPorId = $articuloRepository->find($articulo['id']);
                $carroActual = $carroRepository->findOneByIdUsuario($usuarioCompra->getId());
                $itemEnCarro = $itemsRepository->
                findItemsByCarroIdAndArticuloId($carroActual->getId(),$articuloPorId->getId());
                $bancoUsuario = $usuarioCompra->getBanco();
                //$vendedorArticulo = $vendedorRepository->find($usuarioCompra->getIdVendedor());

                if($articuloPorId!=null && $carroActual!=null
                    && $itemEnCarro!=null && $bancoUsuario!=null  ){
                    if ($articuloPorId->getStock() >= (int)$articulo['unidades']){
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


                    $articuloPorId->setStock(($articuloPorId->getStock()-(int)$articulo['unidades']));
                    /*$em->persist($articuloPorId);*/
                    $articulosEnElCarro[] = $articuloPorId;
                    //$em->flush();
                    $detalles[] = $detalle;
                    $vendedorArticulo->setImporteVentas($vendedorArticulo->getImporteVentas()+(int)$totalDetalle);
                    $vendedorArticulo->setNumeroVentas($vendedorArticulo->getNumeroVentas()+(int)$articulo['unidades']);
                    $em->persist($vendedorArticulo);
                    $em->flush();
                    $vendedoresArticulos [] = $vendedorArticulo;
                    $usuarioVendedor = $usuarioRepository->findOneByIdVendedor($vendedorArticulo->getId());
                    $bancoVendedor = $usuarioVendedor->getBanco();
                    $bancoVendedor->setBalance($bancoVendedor->getBalance()+$totalDetalle);
                    $carroActual->setCantidad($carroActual->getCantidad()-1);
                    $em->persist($carroActual);
                    $em->flush();
                    $total += $articulo['unidades'] * $articuloPorId->getPrecio();

                    } else {
                        return $this->json (['respuesta' => 0 ]);
                    }
                } else {
                    return $this->json (['respuesta' => 2 ]);
                }

            } catch (\Exception $e) {
                $errorOperacion = true;
            }

        }
            } else {
                return $this->json (['respuesta' => 3 ]);
            }
            /*return $this->json (['respuesta' => $total,
                            'articulos' => $articulos,
                            'usuario' => $idUsuarioCompra,
                            ]);*/
            //return $this->json (['respuesta' => "todo bien" ]);
            if (!$errorOperacion){
                foreach ($itemsEnCarro as $itemCarro){
                    $em->remove($itemCarro);
                    $em->flush();
                }


                $banco = $bancoRepository->find($usuarioCompra->getBanco()->getId());
                $banco->setBalance($banco->getBalance()-$total);
                $em->persist($banco);
                $em->flush();

                $factura->setImporte($total);
                $em->persist($factura);
                $em->flush();

                foreach ($articulosEnElCarro as $articuloPersist){
                    $em->persist($articuloPersist);
                    $em->flush();
                }

                foreach ($vendedoresArticulos as $vendedorPersist){
                    $em->persist($vendedorPersist);
                    $em->flush();
                }
                $request->getSession()->set('carro',0);
                return $this->json (['respuesta' => "todo bien Codigo 0" ]);

            } else {
                foreach ($detalles as $detallePersist){
                    $em->remove($detallePersist);
                    $em->flush();
                }
                return $this->json (['respuesta' => "Algo salio mal removing" ]);
            }/**/
        } else {
            return $this->redirectToRoute('index');
        }
    }
}

