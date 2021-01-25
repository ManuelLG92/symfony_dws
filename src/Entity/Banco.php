<?php

namespace App\Entity;

use App\Repository\BancoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BancoRepository::class)
 */
class Banco
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $cuenta_virtual;

    /**
     * @ORM\OneToMany(targetEntity=Usuario::class, mappedBy="banco")
     */
    private $id_usuario;

    /**
     * @ORM\Column(type="integer")
     */
    private $balance;

    public function __construct()
    {
        $this->id_usuario = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCuentaVirtual(): ?string
    {
        return $this->cuenta_virtual;
    }

    public function setCuentaVirtual(string $cuenta_virtual): self
    {
        $this->cuenta_virtual = $cuenta_virtual;

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
            $idUsuario->setBanco($this);
        }

        return $this;
    }

    public function removeIdUsuario(Usuario $idUsuario): self
    {
        if ($this->id_usuario->removeElement($idUsuario)) {
            // set the owning side to null (unless already changed)
            if ($idUsuario->getBanco() === $this) {
                $idUsuario->setBanco(null);
            }
        }

        return $this;
    }

    public function getBalance(): ?int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): self
    {
        $this->balance = $balance;

        return $this;
    }
}
