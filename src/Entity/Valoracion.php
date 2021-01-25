<?php

namespace App\Entity;

use App\Repository\ValoracionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ValoracionRepository::class)
 */
class Valoracion
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
    private $id_cliente;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_articulo;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero_detalle;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero_factura;

    /**
     * @ORM\Column(type="integer")
     */
    private $valor;

    private  $fecha;


    public function getFecha()
    {
        return $this->fecha;
    }


    public function setFecha($fecha): self
    {
        $this->fecha = $fecha;
        return $this;
    }

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

    public function getIdCliente(): ?int
    {
        return $this->id_cliente;
    }

    public function setIdCliente(int $id_cliente): self
    {
        $this->id_cliente = $id_cliente;

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

    public function getNumeroDetalle(): ?int
    {
        return $this->numero_detalle;
    }

    public function setNumeroDetalle(int $numero_detalle): self
    {
        $this->numero_detalle = $numero_detalle;

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

    public function getValor(): ?int
    {
        return $this->valor;
    }

    public function setValor(int $valor): self
    {
        $this->valor = $valor;

        return $this;
    }
}
