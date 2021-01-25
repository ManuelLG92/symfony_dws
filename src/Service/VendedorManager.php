<?php


namespace App\Service;


use App\Entity\Vendedor;

class VendedorManager
{

    public function crearDireccion(Vendedor $vendedor)
    {
        $this->em->persist($vendedor);
        $this->em->flush();
    }
}