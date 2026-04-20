<?php
require_once '../includes/header.php';

if ($rol !== 'ADMIN') {
    echo "<div class='container'><div class='alert-danger'>Acceso denegado. Solo administradores.</div></div>";
    require_once '../includes/footer.php';
    exit;
}

require_once '../conexion.php';

// Procesar Formulario (Crear/Editar)
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $tipo = $_POST['tipo'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if ($_POST['action'] === 'create') {
        $stmt = $conn->prepare("INSERT INTO dispositivos (codigo, nombre, tipo, activo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $codigo, $nombre, $tipo, $activo);
        if ($stmt->execute()) {
            $msg = "Dispositivo registrado éxito.";
        } else {
            $msg = "Error al registrar: " . $conn->error;
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'edit') {
        $id = (int)$_POST['dispositivo_id'];
        $stmt = $conn->prepare("UPDATE dispositivos SET codigo=?, nombre=?, tipo=?, activo=? WHERE id=?");
        $stmt->bind_param("sssii", $codigo, $nombre, $tipo, $activo, $id);
        if ($stmt->execute()) {
            $msg = "Dispositivo actualizado.";
        } else {
            $msg = "Error al actualizar.";
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'delete') {
         $id = (int)$_POST['dispositivo_id'];
         $stmt = $conn->prepare("UPDATE dispositivos SET activo=0 WHERE id=?");
         $stmt->bind_param("i", $id);
         $stmt->execute();
         $stmt->close();
         $msg = "Dispositivo dado de baja.";
    }
}

$dispositivos = $conn->query("SELECT * FROM dispositivos ORDER BY codigo");
?>

<div class="max-w-7xl mx-auto">
    
    <!-- Top Bar: Title & Actions -->
    <div class="md:flex md:items-center md:justify-between mb-10">
        <div class="flex-1 min-w-0">
            <h2 class="text-3xl font-extrabold text-brand-900 tracking-tight">Inventario de Dispositivos</h2>
            <p class="mt-2 text-sm text-brand-900/60 font-medium">Gestiona el inventario de recursos prestables para alumnos o profesores.</p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
            <button onclick="showModal()" class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-full shadow-sm text-sm font-bold text-brand-900 bg-brand-100 hover:bg-brand-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all active:scale-95">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Nuevo Equipo
            </button>
        </div>
    </div>

    <!-- Alert Box -->
    <?php if($msg): ?>
        <div class="rounded-2xl bg-green-50 p-4 border border-green-200 mb-6 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($msg); ?></p>
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
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">S/N (Código)</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre de Equipo</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php while($row = $dispositivos->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-brand-900 font-mono"><?php echo htmlspecialchars($row['codigo']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nombre']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    <?php echo htmlspecialchars($row['tipo']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($row['activo']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold text-green-700 bg-green-100">
                                        OPERATIVO
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold text-red-700 bg-red-100">
                                        BAJA / TALLER
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                                <button onclick='editDisp(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>)' class="text-brand-500 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 p-2 rounded-xl transition-colors" title="Editar Equipo">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                
                                <form method="POST" onsubmit="return confirm('¿Seguro de dar de baja este dispositivo?');" class="inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="dispositivo_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-800 bg-red-50 hover:bg-red-100 p-2 rounded-xl transition-colors" title="Dar de Baja">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Formulario -->
<div id="modalDisp" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-brand-900/30 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('modalDisp').style.display='none'"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-brand-900/5">
            <div class="bg-white px-6 pt-6 pb-6 sm:p-8 sm:pb-6 text-gray-800">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-2xl leading-6 font-extrabold text-brand-900 mb-6" id="modalTitle">
                            Registrar Equipo
                        </h3>
                        <div>
                            <form method="POST" id="formDisp" class="space-y-5">
                                <input type="hidden" name="action" id="formAction" value="create">
                                <input type="hidden" name="dispositivo_id" id="dispositivo_id" value="">
                                
                                <div class="grid grid-cols-1 gap-5">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Número de Serie / Inventario</label>
                                        <input type="text" name="codigo" id="codigo" required placeholder="Ej: LAP-001" class="block w-full px-5 py-3 bg-white border border-gray-200 rounded-full shadow-sm text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Nombre Visible</label>
                                        <input type="text" name="nombre" id="nombre" required placeholder="Ej: Portátil Alumnos 01" class="block w-full px-5 py-3 bg-white border border-gray-200 rounded-full shadow-sm text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Categoría</label>
                                        <select name="tipo" id="tipo" class="block w-full px-5 py-3 bg-white border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 appearance-none">
                                            <option value="PORTATIL">PORTATIL / LAPTOP</option>
                                            <option value="TABLET">TABLETA / IPAD</option>
                                            <option value="PROYECTOR">PROYECTOR PORTÁTIL</option>
                                            <option value="CAMARA">CAMARA</option>
                                            <option value="OTRO">OTRO PERIFÉRICO</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 pl-4 pt-2">
                                    <input type="checkbox" name="activo" id="activo" checked class="w-5 h-5 text-brand-500 bg-gray-100 border-gray-300 rounded focus:ring-brand-500 focus:ring-2">
                                    <label for="activo" class="text-sm font-medium text-gray-700">Operativo (Disponible para reservar)</label>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-brand-50/50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse border-t border-brand-900/5 gap-3">
                <button type="submit" form="formDisp" class="w-full inline-flex justify-center rounded-full border border-transparent shadow-sm px-6 py-3 bg-brand-200 text-sm font-bold text-brand-900 hover:bg-brand-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 sm:w-auto transition-all active:scale-95">
                    Guardar Equipo
                </button>
                <button type="button" onclick="document.getElementById('modalDisp').style.display='none'" class="mt-3 w-full inline-flex justify-center rounded-full border border-gray-200 shadow-sm px-6 py-3 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 sm:mt-0 sm:w-auto transition-all active:scale-95">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showModal() {
    document.getElementById('modalTitle').innerText = 'Registrar Equipo';
    document.getElementById('formAction').value = 'create';
    document.getElementById('dispositivo_id').value = '';
    document.getElementById('formDisp').reset();
    document.getElementById('modalDisp').style.display = 'block';
}

function editDisp(data) {
    document.getElementById('modalTitle').innerText = 'Editar Equipo';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('dispositivo_id').value = data.id;
    document.getElementById('codigo').value = data.codigo;
    document.getElementById('nombre').value = data.nombre;
    document.getElementById('tipo').value = data.tipo;
    document.getElementById('activo').checked = data.activo == 1;
    document.getElementById('modalDisp').style.display = 'block';
}

document.getElementById('modalDisp').addEventListener('click', function(e) {
    if(e.target === this) this.style.display = 'none';
});
</script>

<?php require_once '../includes/footer.php'; ?>
