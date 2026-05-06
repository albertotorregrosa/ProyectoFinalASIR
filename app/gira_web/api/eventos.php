<?php
session_start();
header('Content-Type: application/json');
require '../conexion.php';


if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

$eventos = [];

if ($start && $end) {
    // 1. Reservas de aulas
    $stmt = $conn->prepare("
        SELECT r.id, r.inicio, r.fin, a.nombre as nombre_item, u.nombre as usuario, 'AULA' as tipo
        FROM reservas r
        JOIN aulas a ON r.aula_id = a.id
        JOIN usuarios u ON r.usuario_id = u.id
        WHERE r.estado = 'CONFIRMADA'
          AND r.inicio >= ? AND r.fin <= ?
    ");
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $resAulas = $stmt->get_result();

    while ($row = $resAulas->fetch_assoc()) {
        $eventos[] = [
            'id' => 'A-' . $row['id'],
            'title' => '[Aula] ' . $row['nombre_item'] . ' - ' . $row['usuario'],
            'start' => $row['inicio'],
            'end' => $row['fin']
            // Dejamos que el CSS del index.php se encargue de los colores por defecto
        ];
    }
    $stmt->close();

    // 2. Reservas de dispositivos
    $stmtD = $conn->prepare("
        SELECT rd.id, rd.inicio, rd.fin, d.nombre as nombre_item, u.nombre as usuario, 'DISPOSITIVO' as tipo
        FROM reservas_dispositivos rd
        JOIN dispositivos d ON rd.dispositivo_id = d.id
        JOIN usuarios u ON rd.usuario_id = u.id
        WHERE rd.estado = 'CONFIRMADA'
          AND rd.inicio >= ? AND rd.fin <= ?
    ");
    $stmtD->bind_param("ss", $start, $end);
    $stmtD->execute();
    $resDisp = $stmtD->get_result();

    while ($row = $resDisp->fetch_assoc()) {
        $eventos[] = [
            'id' => 'D-' . $row['id'],
            'title' => '[Disp] ' . $row['nombre_item'] . ' - ' . $row['usuario'],
            'start' => $row['inicio'],
            'end' => $row['fin'],
            // Modern SaaS Emerald
            'backgroundColor' => '#ecfdf5', // emerald-50
            'borderColor' => '#6ee7b7',     // emerald-300
            'textColor' => '#064e3b'        // emerald-900
        ];
    }
    $stmtD->close();
}

echo json_encode($eventos);
$conn->close();
?>
