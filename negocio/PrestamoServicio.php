<?php

declare(strict_types=1);
defined('ENTRYPOINT') || (http_response_code(404) && exit);
class PrestamoServicio
{
    private PrestamoRepository $prestamos;
    private EquipoRepository $equipos;

    public function __construct(?PrestamoRepository $prestamos = null, ?EquipoRepository $equipos = null)
    {
        $this->prestamos = $prestamos ?? new PrestamoRepository();
        $this->equipos = $equipos ?? new EquipoRepository();
    }

    /** Registra el préstamo y deja el equipo como "Prestado". */
    public function registrar(Prestamo $prestamo): bool
    {
        // Primero se "reserva" el equipo: solo funciona si sigue Disponible.
        if (!$this->equipos->marcarPrestadoSiDisponible($prestamo->getIdEquipo())) {
            return false;
        }

        if (!$this->prestamos->guardar($prestamo)) {
            // No se pudo guardar el préstamo: el equipo vuelve a quedar libre.
            $this->equipos->cambiarEstado($prestamo->getIdEquipo(), 'Disponible');
            return false;
        }

        return true;
    }

    /** Marca el préstamo como devuelto y el equipo vuelve a "Disponible". */
    public function devolver(int $idPrestamo): bool
    {
        $idEquipo = $this->prestamos->obtenerIdEquipo($idPrestamo);

        // Si el préstamo no existe o ya fue devuelto no se toca el equipo
        // (podría estar prestado a otra persona).
        if ($idEquipo === null || !$this->prestamos->marcarDevuelto($idPrestamo)) {
            return false;
        }

        $this->equipos->cambiarEstado($idEquipo, 'Disponible');
        return true;
    }
}
