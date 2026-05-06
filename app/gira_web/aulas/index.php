<?php
require_once '../includes/header.php';

if ($rol !== 'ADMIN') {
    echo "<div class='container'><div class='alert-danger'>Acceso denegado. Solo administradores.</div></div>";
    require_once '../includes/footer.php';
    exit;
}

require_once '../conexion.php';

// Procesar Formulario (Crear/Editar Aula)
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $tipo = $_POST['tipo'];
    $capacidad = (int)$_POST['capacidad'];
    $ubicacion = trim($_POST['ubicacion']);
    $activa = isset($_POST['activa']) ? 1 : 0;
    
    if ($_POST['action'] === 'create') {
        $stmt = $conn->prepare("INSERT INTO aulas (codigo, nombre, tipo, capacidad, ubicacion, activa) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisi", $codigo, $nombre, $tipo, $capacidad, $ubicacion, $activa);
        if ($stmt->execute()) {
            $msg = "Aula creada éxito.";
        } else {
            $msg = "Error al crear: " . $conn->error;
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'edit') {
        $id = (int)$_POST['aula_id'];
        $stmt = $conn->prepare("UPDATE aulas SET codigo=?, nombre=?, tipo=?, capacidad=?, ubicacion=?, activa=? WHERE id=?");
        $stmt->bind_param("sssisii", $codigo, $nombre, $tipo, $capacidad, $ubicacion, $activa, $id);
        if ($stmt->execute()) {
            $msg = "Aula actualizada.";
        } else {
            $msg = "Error al actualizar.";
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'delete') {
         $id = (int)$_POST['aula_id'];
         // (Opcional) Borrado lógico. Por la bd desactivaremos para no romper FK
         $stmt = $conn->prepare("UPDATE aulas SET activa=0 WHERE id=?");
         $stmt->bind_param("i", $id);
         $stmt->execute();
         $stmt->close();
         $msg = "Aula desactivada.";
    }
}

$aulas = $conn->query("SELECT * FROM aulas ORDER BY codigo");
?>

<div class="max-w-7xl mx-auto">
    
    <!-- Top Bar: Title & Actions -->
    <div class="md:flex md:items-center md:justify-between mb-10">
        <div class="flex-1 min-w-0">
            <h2 class="text-3xl font-extrabold text-brand-900 tracking-tight">Gestión de Aulas</h2>
            <p class="mt-2 text-sm text-brand-900/60 font-medium">Añade, edita o desactiva (eliminación lógica) los espacios del centro.</p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
            <button onclick="document.getElementById('modalAula').style.display='block'" class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-full shadow-sm text-sm font-bold text-brand-900 bg-brand-100 hover:bg-brand-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all active:scale-95">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Nueva Aula
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
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Código</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Capacidad</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php while($row = $aulas->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-brand-900"><?php echo htmlspecialchars($row['codigo']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nombre']); ?></div>
                                <div class="text-xs text-gray-500 mt-0.5"><?php echo htmlspecialchars($row['ubicacion']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    <?php echo htmlspecialchars($row['tipo']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <strong><?php echo $row['capacidad']; ?></strong> pax.
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($row['activa']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold text-green-700 bg-green-100">
                                        ACTIVO
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold text-red-700 bg-red-100">
                                        INACTIVO
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                                <button onclick='editAula(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>)' class="text-brand-500 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 p-2 rounded-xl transition-colors" title="Editar Aula">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                
                                <form method="POST" onsubmit="return confirm('¿Seguro de desactivar esta aula?');" class="inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="aula_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-800 bg-red-50 hover:bg-red-100 p-2 rounded-xl transition-colors" title="Eliminar Aula">
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
<div id="modalAula" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-brand-900/30 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('modalAula').style.display='none'"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-brand-900/5">
            <div class="bg-white px-6 pt-6 pb-6 sm:p-8 sm:pb-6 text-gray-800">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-2xl leading-6 font-extrabold text-brand-900 mb-6" id="modalTitle">
                            Registrar Aula
                        </h3>
                        <div>
                            <form method="POST" id="formAula" class="space-y-5">
                                <input type="hidden" name="action" id="formAction" value="create">
                                <input type="hidden" name="aula_id" id="aula_id" value="">
                                
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Código</label>
                                        <input type="text" name="codigo" id="codigo" required placeholder="Ej: A-101" class="block w-full px-5 py-3 bg-white border border-gray-200 rounded-full shadow-sm text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Nombre</label>
                                        <input type="text" name="nombre" id="nombre" required placeholder="Ej: Aula de Informática" class="block w-full px-5 py-3 bg-white border border-gray-200 rounded-full shadow-sm text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Tipo</label>
                                        <select name="tipo" id="tipo" class="block w-full px-5 py-3 bg-white border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800 appearance-none">
                                            <option value="AULA">AULA NORMAL</option>
                                            <option value="LAB">LABORATORIO</option>
                                            <option value="TALLER">TALLER</option>
                                            <option value="OTRA">OTRA</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Capacidad (Pax)</label>
                                        <input type="number" name="capacidad" id="capacidad" required min="1" class="block w-full px-5 py-3 bg-white border border-gray-200 rounded-full shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Ubicación</label>
                                    <input type="text" name="ubicacion" id="ubicacion" placeholder="Ej: Planta Baja, Edificio A" class="block w-full px-5 py-3 bg-white border border-gray-200 rounded-full shadow-sm text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-gray-800">
                                </div>

                                <div class="flex items-center gap-3 pl-4 pt-2">
                                    <input type="checkbox" name="activa" id="activa" checked class="w-5 h-5 text-brand-500 bg-gray-100 border-gray-300 rounded focus:ring-brand-500 focus:ring-2">
                                    <label for="activa" class="text-sm font-medium text-gray-700">Activa (Disponible para reservas)</label>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-brand-50/50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse border-t border-brand-900/5 gap-3">
                <button type="submit" form="formAula" class="w-full inline-flex justify-center rounded-full border border-transparent shadow-sm px-6 py-3 bg-brand-200 text-sm font-bold text-brand-900 hover:bg-brand-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 sm:w-auto transition-all active:scale-95">
                    Guardar Aula
                </button>
                <button type="button" onclick="document.getElementById('modalAula').style.display='none'" class="mt-3 w-full inline-flex justify-center rounded-full border border-gray-200 shadow-sm px-6 py-3 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 sm:mt-0 sm:w-auto transition-all active:scale-95">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function editAula(data) {
    document.getElementById('modalTitle').innerText = 'Editar Aula';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('aula_id').value = data.id;
    document.getElementById('codigo').value = data.codigo;
    document.getElementById('nombre').value = data.nombre;
    document.getElementById('tipo').value = data.tipo;
    document.getElementById('capacidad').value = data.capacidad;
    document.getElementById('ubicacion').value = data.ubicacion;
    document.getElementById('activa').checked = data.activa == 1;
    document.getElementById('modalAula').style.display = 'block';
}

// Ocultar modal al hacer click fuera
document.getElementById('modalAula').addEventListener('click', function(e) {
    if(e.target === this) this.style.display = 'none';
});
</script>

<?php require_once '../includes/footer.php'; ?>
