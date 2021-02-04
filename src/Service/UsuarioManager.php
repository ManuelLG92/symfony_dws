<?php


namespace App\Service;


use App\Entity\Banco;
use App\Entity\Direccion;
use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UsuarioManager
{
    private $em;
    private $usuarioRepository;
    private $validator;
    public function __construct(
        EntityManagerInterface $em,
        UsuarioRepository $usuarioRepository,
        ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->usuarioRepository = $usuarioRepository;
        $this->validator = $validator;
    }

    public function compruebaCamposUsuario
    ($nombre,$apellido,$email, $clave) : bool
    {
        if ($nombre != null && $apellido != null && $email!= null && $clave!= null  ){
            if (!empty($nombre) && !empty($apellido) && !empty($email) && !empty($clave)){
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }
    public function compruebaCamposDireccion ($via, $nombre_via,  $numero, $ciudad, $estado, $cp,$pais) : bool
    {
        if ($via != null && $nombre_via != null && $numero != null && $ciudad!= null && $estado != null && $cp != null && $pais != null) {
            if(!empty($via) && (!empty($nombre_via)) && (!empty($numero)) && (!empty($ciudad)) && (!empty($estado)) &&  (!empty($cp)) && (!empty($pais)) ){
                return true;
            }   else {
                return false;
            }
        }
        return false;

    }

    public function compruebaCamposUsuarioEditar($nombre,$apellido)
    {
        if ($nombre != null && $apellido != null){
            if (!empty($nombre) && !empty($apellido)){
                return true;
            } else {
                return false;
            }
        }else {
            return false;
        }
    }
    public function compruebaCamposDireccionValidate(Direccion $direccion) {
    $errores = $this->validator->validate($direccion);
    return $errores;
    }

    public function crearUsuario(Usuario $usuario)
    {
        $this->em->persist($usuario);
        $this->em->flush();
    }
    public function crearDireccion(Direccion $direccion)
    {
        $this->em->persist($direccion);
        $this->em->flush();
    }
    public function crearBanco(Banco $banco)
    {
        $this->em->persist($banco);
        $this->em->flush();
    }

    public function comparaNuevaclave($clave, $claveVerificacion)
    {
        return strcmp($clave,$claveVerificacion);
    }

    public function ChequeaClave($clave, $claveUsuarioHashed)
    {
        return password_verify($clave, $claveUsuarioHashed);

    }


    public function encriptarClave (string $clave) : string
    {
        return password_hash($clave, PASSWORD_DEFAULT);

    }

    public function compruebaEmail(string $email)
    {
        return $this->usuarioRepository->findByEmail($email);
    }

}