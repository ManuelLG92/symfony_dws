<?php


namespace App\Service;


use App\Repository\CarroRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class SecurityManager extends AbstractController
{

    public function chequeaUsuarioSolicitud(Request $request,$idUsuarioSolicitud): bool
    {
        if( $request->getSession() && $this->getUser() != null) {

            if($this->getUser()->getId() == $idUsuarioSolicitud) {
                return true;
            } else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function chequeaPropiedadCarro(int $id, CarroRepository $carroRepository)
    {
        if($carroVista = $carroRepository->findOneByIdUsuario($id)){
            if ($carroVista->getIdUsuario() == $this->getUser()->getId()){
                return $carroVista;
            } else {
                return  null;
            }
        }else {
            return  null;
        }
    }
}