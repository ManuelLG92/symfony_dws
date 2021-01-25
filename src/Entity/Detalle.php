<?php

namespace App\Entity;

use App\Repository\DetalleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DetalleRepository::class)
 */
class Detalle
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_vendedor;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero_factura;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_articulo;

    /**
     * @ORM\Column(type="integer")
     */
    private $cantidad;

    /**
     * @ORM\Column(type="integer")
     */
    private $precio;

    /**
     * @ORM\Column(type="integer")
     */
    private $total;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdVendedor(): ?int
    {
        return $this->id_vendedor;
    }

    public function setIdVendedor(int $id_vendedor): self
    {
        $this->id_vendedor = $id_vendedor;

        return $this;
    }

    public function getNumeroFactura(): ?int
    {
        return $this->numero_factura;
    }

    public function setNumeroFactura(int $numero_factura): self
    {
        $this->numero_factura = $numero_factura;

        return $this;
    }

    public function getIdArticulo(): ?int
    {
        return $this->id_articulo;
    }

    public function setIdArticulo(int $id_articulo): self
    {
        $this->id_articulo = $id_articulo;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getPrecio(): ?int
    {
        return $this->precio;
    }

    public function setPrecio(int $precio): self
    {
        $this->precio = $precio;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }
}
