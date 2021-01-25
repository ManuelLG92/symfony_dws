<?php

namespace App\Entity;

use App\Repository\DireccionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DireccionRepository::class)
 */
class Direccion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $via;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $piso;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $puerta;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $cp;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $ciudad;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $pais;

    /**
     * @ORM\OneToMany(targetEntity=Usuario::class, mappedBy="direccion", cascade={"persist"})
     */
    private $id_usuario;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombre_via;

    public function __construct()
    {
        $this->id_usuario = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVia(): ?string
    {
        return $this->via;
    }

    public function setVia(string $via): self
    {
        $this->via = $via;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getPiso(): ?string
    {
        return $this->piso;
    }

    public function setPiso(?string $piso): self
    {
        $this->piso = $piso;

        return $this;
    }

    public function getPuerta(): ?string
    {
        return $this->puerta;
    }

    public function setPuerta(?string $puerta): self
    {
        $this->puerta = $puerta;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(string $cp): self
    {
        $this->cp = $cp;

        return $this;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(string $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(string $pais): self
    {
        $this->pais = $pais;

        return $this;
    }

    /**
     * @return Collection|Usuario[]
     */
    public function getIdUsuario(): Collection
    {
        return $this->id_usuario;
    }

    public function addIdUsuario(Usuario $idUsuario): self
    {
        if (!$this->id_usuario->contains($idUsuario)) {
            $this->id_usuario[] = $idUsuario;
            $idUsuario->setDireccion($this);
        }

        return $this;
    }

    public function removeIdUsuario(Usuario $idUsuario): self
    {
        if ($this->id_usuario->removeElement($idUsuario)) {
            // set the owning side to null (unless already changed)
            if ($idUsuario->getDireccion() === $this) {
                $idUsuario->setDireccion(null);
            }
        }

        return $this;
    }

    public function getNombreVia(): ?string
    {
        return $this->nombre_via;
    }

    public function setNombreVia(string $nombre_via): self
    {
        $this->nombre_via = $nombre_via;

        return $this;
    }
}
