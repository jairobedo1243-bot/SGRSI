<?php

declare(strict_types=1);
defined('ENTRYPOINT') || (http_response_code(404) && exit);
class EquipoRepository
{
    public function guardar(Equipo $equipo): bool
    {
        return Conexion::execute(
            'INSERT INTO equipo (estado, numeroSerie, modelo, marca, tipo) VALUES (?, ?, ?, ?, ?)',
            [
                $equipo->getEstado(),
                $equipo->getNumeroSerie(),
                $equipo->getModelo(),
                $equipo->getMarca(),
                $equipo->getTipo(),
            ]
        );
    }

    public function listar(): array
    {
        return Conexion::select('SELECT * FROM equipo ORDER BY id_equipo DESC');
    }

    public function listarDisponibles(): array
    {
        return Conexion::select("SELECT * FROM equipo WHERE estado = 'Disponible' ORDER BY modelo");
    }

    public function cambiarEstado(int $idEquipo, string $estado): bool
    {
        return Conexion::execute('UPDATE equipo SET estado = ? WHERE id_equipo = ?', [$estado, $idEquipo]);
    }

    /**
     * Pasa el equipo a "Prestado" solo si estaba "Disponible".
     * Lo hace en una sola consulta, así dos préstamos simultáneos no pueden llevarse el mismo equipo.
     */
    public function marcarPrestadoSiDisponible(int $idEquipo): bool
    {
        return Conexion::filasAfectadas(
            "UPDATE equipo SET estado = 'Prestado' WHERE id_equipo = ? AND estado = 'Disponible'",
            [$idEquipo]
        ) === 1;
    }

    public function obtenerEstado(int $idEquipo): ?string
    {
        $valor = Conexion::scalar('SELECT estado FROM equipo WHERE id_equipo = ?', [$idEquipo]);
        return $valor === null ? null : (string)$valor;
    }

    public function contar(): int
    {
        return (int)Conexion::scalar('SELECT COUNT(*) AS n FROM equipo');
    }

    public function contarPorEstado(string $estado): int
    {
        return (int)Conexion::scalar('SELECT COUNT(*) AS n FROM equipo WHERE estado = ?', [$estado]);
    }
}
