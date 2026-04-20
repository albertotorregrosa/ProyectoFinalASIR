<?php
require_once '../includes/header.php';

// Los alumnos NO pueden reportar incidencias de aulas
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
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $prioridad = $_POST['prioridad'];
    $usuario_id = $_SESSION['usuario_id']; // Quien reporta

    if (empty($titulo) || empty($descripcion)) {
        $mensaje = 'Por favor, completa todos los campos requeridos.';
        $tipo_mensaje = 'alert-danger';
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO incidencias_aula (aula_id, reportada_por, titulo, descripcion, prioridad, estado) VALUES (?, ?, ?, ?, ?, 'ABIERTA')");
        $stmt_insert->bind_param("iisss", $aula_id, $usuario_id, $titulo, $descripcion, $prioridad);
        
        if ($stmt_insert->execute()) {
            $mensaje = 'Incidencia reportada con éxito. El equipo de mantenimiento la revisará pronto.';
            $tipo_mensaje = 'alert-success; background-color: #D1FAE5; color: #065F46; border-color: #10B981;';
        } else {
            $mensaje = 'Error al registrar la incidencia.';
            $tipo_mensaje = 'alert-danger';
        }
        $stmt_insert->close();
    }
}

// Cargar Aulas
$res_aulas = $conn->query("SELECT id, nombre FROM aulas WHERE activa = 1 ORDER BY nombre");
?>

<div class="max-w-3xl mx-auto">
    
    <div class="mb-10 text-center sm:text-left">
        <h2 class="text-3xl font-extrabold text-brand-900 tracking-tight">Reportar Incidencia</h2>
        <p class="mt-2 text-sm text-brand-900/60 font-medium">¿Has encontrado un problema en un aula? Repórtalo aquí para que mantenimiento lo solucione.</p>
    </div>

    <div class="bg-white shadow-xl shadow-brand-900/5 ring-1 ring-brand-900/5 rounded-[2.5rem] p-8 sm:p-10 mb-8 max-w-2xl mx-auto">
        
        <?php if ($mensaje): ?>
            <?php if (strpos($tipo_mensaje, 'success') !== false || $tipo_mensaje === 'alert-success'): ?>
                <div class="rounded-2xl bg-green-50 p-4 border border-green-200 mb-8 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0"><svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg></div>
                        <div class="ml-3"><p class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($mensaje); ?></p></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="rounded-2xl bg-red-50 p-4 border border-red-200 mb-8 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0"><svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></div>
                        <div class="ml-3"><p class="text-sm font-bold text-red-800"><?php echo htmlspecialchars($mensaje); ?></p></div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
            
            <div>
                <label for="aula_id" class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Aula Afectada</label>
                <div class="relative">
                    <select name="aula_id" id="aula_id" class="block w-full px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 appearance-none font-medium" required>
                        <option value="">-- Selecciona el Aula --</option>
                        <?php while($aula = $res_aulas->fetch_assoc()): ?>
                            <option value="<?php echo $aula['id']; ?>"><?php echo htmlspecialchars($aula['nombre']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-5 text-gray-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="titulo" class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Título / Asunto Breve</label>
                <input type="text" name="titulo" id="titulo" class="block w-full px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 font-medium placeholder-gray-400" placeholder="Ej: Proyector no enciende" required maxlength="120">
            </div>

            <div>
                <label for="prioridad" class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Prioridad</label>
                <div class="relative">
                    <select name="prioridad" id="prioridad" class="block w-full px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 appearance-none font-medium" required>
                        <option value="BAJA">Baja (No afecta al desarrollo de la clase)</option>
                        <option value="MEDIA" selected>Media (Molesto pero permite continuar)</option>
                        <option value="ALTA">Alta (Afecta gravemente la clase)</option>
                        <option value="URGENTE">Urgente (Peligro o clase completamente paralizada)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-5 text-gray-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="descripcion" class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Descripción detallada del problema</label>
                <textarea name="descripcion" id="descripcion" class="block w-full px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-[2rem] shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 font-medium placeholder-gray-400 resize-y min-h-[120px]" rows="4" placeholder="Detalla qué está ocurriendo..." required></textarea>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-full shadow-md text-sm font-bold text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all active:scale-95">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Reportar Incidencia
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
