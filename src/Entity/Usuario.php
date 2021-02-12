<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UsuarioRepository::class)
 */
class Usuario implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $Nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Apellido;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $Telefono;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $email;


    /**
     * @ORM\ManyToOne(targetEntity=Direccion::class, inversedBy="id_usuario")
     * @ORM\JoinColumn(nullable=false)
     */
    private $direccion;

    /**
     * @ORM\ManyToOne(targetEntity=Banco::class, inversedBy="id_usuario")
     * @ORM\JoinColumn(nullable=false)
     */
    private $banco;



    /**
     * @ORM\Column(type="string", length=150)
     */
    private $clave;

    /**
     * @ORM\Column(type="integer")
     */
    private $vendedor_id;



    public function getIdVendedor(): ?int
    {
        return $this->vendedor_id;
    }


    public function setIdVendedor($id_vendedor): self
    {
        $this->vendedor_id = $id_vendedor;
        return $this;
    }

    public function getClave(): ?string
    {
        return $this->clave;
    }


    public function setClave(string $clave): self
    {
        $this->clave = $clave;

        return $this;
    }


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

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): self
    {
        $this->Nombre = $Nombre;

        return $this;
    }

    public function getApellido(): ?string
    {
        return $this->Apellido;
    }

    public function setApellido(string $Apellido): self
    {
        $this->Apellido = $Apellido;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->Telefono;
    }

    public function setTelefono(?string $Telefono): self
    {
        $this->Telefono = $Telefono;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDireccion(): ?Direccion
    {
        return $this->direccion;
    }

    public function setDireccion(?Direccion $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getBanco(): ?Banco
    {
        return $this->banco;
    }

    public function setBanco(?Banco $banco): self
    {
        $this->banco = $banco;

        return $this;
    }

    /*public function getIdVendedor(): ?Vendedor
    {
        return $this->id_vendedor;
    }

    public function setIdVendedor(Vendedor $id_vendedor): self
    {
        // set the owning side of the relation if necessary
        if ($id_vendedor->getIdUsuario() !== $this) {
            $id_vendedor->setIdUsuario($this);
        }

        $this->id_vendedor = $id_vendedor;

        return $this;
    }*/


    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
        return $this->getClave();
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->getNombre();
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
