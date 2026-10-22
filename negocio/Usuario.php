<?php

declare(strict_types=1);
defined('ENTRYPOINT') || (http_response_code(404) && exit);
class Usuario
{
    private int $ci;
    private string $nom;
    private string $ape;
    private string $email;
    private string $rol;
    private string $contrasenaHash;
    private string $contrasenaInicial;

    public function __construct(int $ci, string $nom, string $ape, string $email, string $rol)
    {
        $this->ci = $ci;
        $this->nom = $nom;
        $this->ape = $ape;
        $this->email = $email;
        $this->rol = $rol;
        // Clave inicial distinta para cada usuario (no todos con la misma).
        $this->contrasenaInicial = bin2hex(random_bytes(5));
        $this->contrasenaHash = password_hash($this->contrasenaInicial, PASSWORD_DEFAULT);
    }

    /**
     * Arma un usuario a partir de los datos del formulario.
     * Devuelve null si la cédula no es válida.
     */
    public static function crearDesdeFormulario(string $cedula, string $nombreCompleto, string $email, string $rol): ?self
    {
        if (!self::validarCedula($cedula)) {
            return null;
        }

        $ci = (int)preg_replace('/[^0-9]/', '', $cedula);
        // Separa nombre y apellido aunque haya varios espacios entre las palabras.
        $partes = preg_split('/\s+/u', trim($nombreCompleto), 2) ?: [''];

        // La base guarda máximo 20 letras en nombre y apellido.
        return new self($ci, mb_substr($partes[0], 0, 20), mb_substr($partes[1] ?? '', 0, 20), $email, $rol);
    }

    public static function validarCedula(string $cedula): bool
    {
        $ci = preg_replace('/[^0-9]/', '', $cedula);
        if (strlen($ci) !== 8) {
            return false;
        }

        $pesos = [2, 9, 8, 7, 6, 3, 4];
        $suma = 0;
        for ($i = 0; $i < 7; $i++) {
            $suma += (int)$ci[$i] * $pesos[$i];
        }

        $verificador = (10 - $suma % 10) % 10;
        return $verificador === (int)$ci[7];
    }

    public function getCi(): int
    {
        return $this->ci;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getApe(): string
    {
        return $this->ape;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRol(): string
    {
        return $this->rol;
    }

    public function getContrasenaInicial(): string
    {
        return $this->contrasenaInicial;
    }

    public function getContrasenaHash(): string
    {
        return $this->contrasenaHash;
    }
}
