<?php
require_once '../includes/header.php';

// Solo el ADMIN puede gestionar todas las incidencias (Ver listado completo y cambiar estado)
// (Podríamos dejar que los profesores vean sus propias incidencias, pero por simplicidad el gestor principal es el admin)
if ($rol !== 'ADMIN') {
    echo "<div class='container'><div class='alert-danger'>Acceso denegado. Solo administradores.</div></div>";
    require_once '../includes/footer.php';
    exit;
}

require_once '../conexion.php';

// Cambiar estado si se recibe petición POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $incidencia_id = (int)$_POST['incidencia_id'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    // Si se pasa a resuelta/cerrada marcamos la fecha
    $q = "UPDATE incidencias_aula SET estado = ?";
    if (in_array($nuevo_estado, ['RESUELTA', 'CERRADA'])) {
        $q .= ", resuelta_en = NOW()";
    }
    $q .= " WHERE id = ?";
    
    $stmt_upd = $conn->prepare($q);
    $stmt_upd->bind_param("si", $nuevo_estado, $incidencia_id);
    $stmt_upd->execute();
    $stmt_upd->close();
    
    // Recargar para evitar reenvío de form
    header("Location: index.php?msg=Estado actualizado");
    exit;
}

// Cargar Todas las incidencias
$query = "
    SELECT i.id, i.titulo, i.prioridad, i.estado, i.created_at, 
           a.nombre as aula, u.nombre as reportador 
    FROM incidencias_aula i
    JOIN aulas a ON i.aula_id = a.id
    JOIN usuarios u ON i.reportada_por = u.id
    ORDER BY FIELD(i.estado, 'ABIERTA', 'EN_PROCESO', 'RESUELTA', 'CERRADA'), i.created_at DESC
";
$resultado = $conn->query($query);
?>

<div class="max-w-7xl mx-auto">
    
    <!-- Top Bar: Title & Actions -->
    <div class="mb-10 text-center sm:text-left">
        <h2 class="text-3xl font-extrabold text-brand-900 tracking-tight">Gestión de Incidencias</h2>
        <p class="mt-2 text-sm text-brand-900/60 font-medium">Panel de administración de reportes y mantenimiento del instituto.</p>
    </div>

    <!-- Alert Box -->
    <?php if(isset($_GET['msg'])): ?>
        <div class="rounded-2xl bg-green-50 p-4 border border-green-200 mb-6 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($_GET['msg']); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="bg-white shadow-sm ring-1 ring-brand-900/5 rounded-[2rem] overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aula</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Asunto</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reporta</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Prioridad</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado actual</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php if ($resultado->num_rows === 0): ?>
                        <tr><td colspan="7" class="px-6 py-8 text-center text-sm font-medium text-gray-500">No hay incidencias registradas.</td></tr>
                    <?php else: ?>
                        <?php while($row = $resultado->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-500 font-mono">#<?php echo $row['id']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-brand-900"><?php echo htmlspecialchars($row['aula']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 w-48 truncate" title="<?php echo htmlspecialchars($row['titulo']); ?>"><?php echo htmlspecialchars($row['titulo']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['reportador']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold border 
                                        <?php echo $row['prioridad'] === 'URGENTE' ? 'bg-red-50 text-red-700 border-red-200' : 
                                                  ($row['prioridad'] === 'ALTA' ? 'bg-orange-50 text-orange-700 border-orange-200' : 
                                                  'bg-indigo-50 text-indigo-700 border-indigo-200'); ?>">
                                        <?php echo $row['prioridad']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-700">
                                        <?php echo str_replace('_', ' ', $row['estado']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="index.php" method="POST" class="flex justify-end gap-2 items-center">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="incidencia_id" value="<?php echo $row['id']; ?>">
                                        
                                        <div class="relative">
                                            <select name="nuevo_estado" class="block w-full pl-3 pr-8 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 font-bold text-gray-700 appearance-none">
                                                <option value="ABIERTA" <?php if($row['estado']=='ABIERTA') echo 'selected';?>>ABIERTA</option>
                                                <option value="EN_PROCESO" <?php if($row['estado']=='EN_PROCESO') echo 'selected';?>>EN PROCESO</option>
                                                <option value="RESUELTA" <?php if($row['estado']=='RESUELTA') echo 'selected';?>>RESUELTA</option>
                                                <option value="CERRADA" <?php if($row['estado']=='CERRADA') echo 'selected';?>>CERRADA</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                            </div>
                                        </div>

                                        <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent rounded-lg shadow-sm text-xs font-bold text-brand-900 bg-brand-100 hover:bg-brand-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all active:scale-95">
                                            Guardar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
