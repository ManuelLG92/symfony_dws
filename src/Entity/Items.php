<?php

namespace App\Entity;

use App\Repository\ItemsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItemsRepository::class)
 */
class Items
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
    private $id_carro;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_articulo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCarro(): ?int
    {
        return $this->id_carro;
    }

    public function setIdCarro(int $id_carro): self
    {
        $this->id_carro = $id_carro;

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
}
