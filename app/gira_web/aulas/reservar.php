<?php
require_once '../includes/header.php';

// Los alumnos NO pueden reservar aulas
if ($rol === 'ALUMNO') {
    echo "<div class='container'><div class='alert-danger'>No tienes permisos para acceder a esta sección.</div></div>";
    require_once '../includes/footer.php';
    exit;
}

require_once '../conexion.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aula_id = (int)$_POST['aula_id'];
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $usuario_id = $_SESSION['usuario_id'];

    if ($hora_inicio < '08:00' || $hora_fin > '21:00' || $hora_inicio >= $hora_fin) {
        $mensaje = 'El horario debe estar entre las 08:00 y las 21:00 y ser válido.';
        $tipo_mensaje = 'alert-danger';
    } else {
        $inicio_dt = $fecha . ' ' . $hora_inicio;
        $fin_dt = $fecha . ' ' . $hora_fin;

        // Comprobar solapamiento
        $stmt_check = $conn->prepare("
            SELECT id FROM reservas 
            WHERE aula_id = ? AND estado = 'CONFIRMADA'
            AND ((inicio < ? AND fin > ?) OR (inicio < ? AND fin > ?))
        ");
        $stmt_check->bind_param("issss", $aula_id, $fin_dt, $inicio_dt, $inicio_dt, $fin_dt);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows > 0) {
            $mensaje = 'El aula ya está reservada en ese horario.';
            $tipo_mensaje = 'alert-danger';
        } else {
            // Insertar reserva
            $stmt_insert = $conn->prepare("INSERT INTO reservas (aula_id, usuario_id, inicio, fin, estado) VALUES (?, ?, ?, ?, 'CONFIRMADA')");
            $stmt_insert->bind_param("iiss", $aula_id, $usuario_id, $inicio_dt, $fin_dt);
            
            if ($stmt_insert->execute()) {
                $mensaje = 'Reserva confirmada con éxito.';
                $tipo_mensaje = 'alert-success; background-color: #D1FAE5; color: #065F46; border-color: #10B981;';
            } else {
                $mensaje = 'Error al crear la reserva.';
                $tipo_mensaje = 'alert-danger';
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

// Cargar Aulas Activas
$res_aulas = $conn->query("SELECT id, nombre, codigo, capacidad FROM aulas WHERE activa = 1 ORDER BY nombre");
?>

<div class="max-w-3xl mx-auto">
    
    <div class="mb-10 text-center sm:text-left">
        <h2 class="text-3xl font-extrabold text-brand-900 tracking-tight">Reservar Aula</h2>
        <p class="mt-2 text-sm text-brand-900/60 font-medium">Selecciona el aula, la fecha y el bloque horario (08:00 - 21:00).</p>
    </div>

    <?php if ($mensaje && strpos($tipo_mensaje, 'success') !== false): ?>
        <!-- PANTALLA DE ÉXITO -->
        <div class="bg-white shadow-xl shadow-brand-900/5 ring-1 ring-brand-900/5 rounded-[2.5rem] p-8 sm:p-12 mb-8 max-w-lg mx-auto text-center">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-50 mb-6 ring-8 ring-green-50/50">
                <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-2xl font-extrabold text-brand-900 mb-2">¡Reserva Confirmada!</h3>
            <p class="text-brand-900/60 font-medium mb-8 text-sm leading-relaxed">Tu reserva del aula ha sido registrada correctamente en el sistema y ya es visible en el calendario institucional.</p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="reservar.php" class="inline-flex justify-center items-center px-6 py-3.5 border border-transparent text-sm font-bold rounded-full shadow-sm text-brand-900 bg-brand-200 hover:bg-brand-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all active:scale-95">
                    Nueva Reserva
                </a>
                <a href="../index.php" class="inline-flex justify-center items-center px-6 py-3.5 border border-gray-200 text-sm font-bold rounded-full text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all active:scale-95 shadow-sm">
                    Ir al Calendario
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- FORMULARIO DE RESERVA / ERRORES -->
        <div class="bg-white shadow-xl shadow-brand-900/5 ring-1 ring-brand-900/5 rounded-[2.5rem] p-8 sm:p-10 mb-8 max-w-2xl mx-auto">
            
            <?php if ($mensaje && strpos($tipo_mensaje, 'danger') !== false): ?>
                <div class="rounded-2xl bg-red-50 p-4 border border-red-200 mb-8 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0"><svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></div>
                        <div class="ml-3"><p class="text-sm font-bold text-red-800"><?php echo htmlspecialchars($mensaje); ?></p></div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
                
                <div>
                    <label for="aula_id" class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Aula Seleccionada</label>
                    <div class="relative">
                        <select name="aula_id" id="aula_id" class="block w-full px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 appearance-none font-medium" required>
                            <option value="">-- Selecciona un Aula --</option>
                            <?php while($aula = $res_aulas->fetch_assoc()): ?>
                                <option value="<?php echo $aula['id']; ?>"><?php echo htmlspecialchars($aula['nombre'] . ' (' . $aula['capacidad'] . ' pax)'); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-5 text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="fecha" class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Fecha de Uso</label>
                    <input type="date" name="fecha" id="fecha" class="block w-full px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 font-medium" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="hora_inicio" class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Hora Inicio</label>
                        <div class="relative">
                            <select name="hora_inicio" id="hora_inicio" class="block w-full px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 appearance-none font-medium" required>
                                <option value="">-- Inicio --</option>
                                <?php
                                $start_time = strtotime('08:00');
                                $end_time = strtotime('20:30'); // Hasta 20:30 como inicio máximo
                                while ($start_time <= $end_time) {
                                    $time_display = date('H:i', $start_time);
                                    $time_value = date('H:i:s', $start_time);
                                    echo "<option value=\"$time_value\">$time_display</option>";
                                    $start_time = strtotime('+30 minutes', $start_time);
                                }
                                ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-5 text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="hora_fin" class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Hora Fin</label>
                        <div class="relative">
                            <select name="hora_fin" id="hora_fin" class="block w-full px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 appearance-none font-medium" required>
                                <option value="">-- Fin --</option>
                                <?php
                                $start_time = strtotime('08:30'); // Desde 08:30 como fin mínimo
                                $end_time = strtotime('21:00');
                                while ($start_time <= $end_time) {
                                    $time_display = date('H:i', $start_time);
                                    $time_value = date('H:i:s', $start_time);
                                    echo "<option value=\"$time_value\">$time_display</option>";
                                    $start_time = strtotime('+30 minutes', $start_time);
                                }
                                ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-5 text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-full shadow-md text-sm font-bold text-brand-900 bg-brand-200 hover:bg-brand-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all active:scale-95">
                        Confirmar Reserva
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
