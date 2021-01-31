<?php

namespace App\Entity;

use App\Repository\ArticuloRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticuloRepository::class)
 */
class Articulo
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
     * @ORM\Column(type="string", length=100)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="integer")
     */
    private $stock;

    /**
     * @ORM\Column(type="integer")
     */
    private $precio;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private  $id_seccion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $foto;

    /**
     * @ORM\Column(type="float", )
     */
    private $valoracion;


    public function getValoracion(): ? float
    {
        return $this->valoracion;
    }


    public function setValoracion($valoracion):  self
    {
        $this->valoracion = $valoracion;
        return $this;
    }

    public function getFoto() : ?string
    {
        return $this->foto;
    }


    public function setFoto($foto): self
    {
        $this->foto = $foto;
        return $this;
    }

    private  $fecha;

    public function getFecha() : \DateTime
    {
        return $this->fecha;
    }


    public function setFecha($fecha): self
    {
        $this->fecha = $fecha;
        return $this;
    }


    public function getIdSeccion(): ?int
    {
        return $this->id_seccion;
    }


    public function setIdSeccion($id_seccion): self
    {
        $this->id_seccion = $id_seccion;
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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

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
}
