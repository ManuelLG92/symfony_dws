<?php

namespace App\Entity;

use App\Repository\VendedorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VendedorRepository::class)
 */
class Vendedor
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numero_ventas;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $importe_ventas;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $valoracion;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroVentas(): ?int
    {
        return $this->numero_ventas;
    }

    public function setNumeroVentas(?int $numero_ventas): self
    {
        $this->numero_ventas = $numero_ventas;

        return $this;
    }

    public function getImporteVentas(): ?int
    {
        return $this->importe_ventas;
    }

    public function setImporteVentas(?int $importe_ventas): self
    {
        $this->importe_ventas = $importe_ventas;

        return $this;
    }

    public function getValoracion(): ?float
    {
        return $this->valoracion;
    }

    public function setValoracion(?int $valoracion): self
    {
        $this->valoracion = $valoracion;

        return $this;
    }


}
