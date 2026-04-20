<?php
require_once '../includes/header.php';

if ($rol !== 'ADMIN') {
    echo '<div class="max-w-7xl mx-auto"><div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md shadow-sm mt-6"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></div><div class="ml-3"><p class="text-sm text-red-700">Acceso denegado. Solo administradores.</p></div></div></div></div>';
    require_once '../includes/footer.php';
    exit;
}

require_once '../conexion.php';

// Procesar Formulario (Status Toggle)
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'status') {
         $id = (int)$_POST['usuario_id'];
         $nuevo = $_POST['nuevo_estado'];
         $stmt = $conn->prepare("UPDATE usuarios SET estado=? WHERE id=?");
         $stmt->bind_param("si", $nuevo, $id);
         $stmt->execute();
         $stmt->close();
         $msg = "Estado de acceso modificado.";
    }
}

$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY nombre, apellidos");
?>

<div class="max-w-7xl mx-auto">
    
    <!-- Top Bar: Title & Actions -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate tracking-tight">Directorio de Usuarios</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los accesos, roles (Alumno, Profesor, Admin) y bloqueos disciplinarios.</p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
            <div class="relative w-64">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <!-- ID busquedaUsuario para poder usar js en un futuro script -->
                <input type="text" id="busquedaUsuario" placeholder="Buscar usuario..." class="block w-full pl-11 pr-4 py-2.5 bg-white border border-brand-900/10 rounded-full shadow-sm text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 transition-all text-brand-900 font-medium">
            </div>
        </div>
    </div>

    <!-- Alert Box -->
    <?php if($msg): ?>
        <div class="rounded-md bg-green-50 p-4 border border-green-200 mb-6 shadow-sm">
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
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario (LDAP)</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acceso</th>
                        <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php while($row = $usuarios->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 font-mono">@<?php echo htmlspecialchars($row['ldap_uid']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($row['rol'] === 'ADMIN'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ADMIN
                                    </span>
                                <?php elseif($row['rol'] === 'PROFESOR'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        PROFESOR
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        ALUMNO
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($row['estado'] === 'ACTIVO'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <svg class="mr-1.5 h-2 w-2 text-green-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        ACTIVO
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                        <svg class="mr-1.5 h-2 w-2 text-red-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        BLOQUEADO
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                                <form method="POST" onsubmit="return confirm('¿Confirmas el cambio de acceso para este usuario?');" class="inline">
                                    <input type="hidden" name="action" value="status">
                                    <input type="hidden" name="usuario_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="nuevo_estado" value="<?php echo $row['estado']==='ACTIVO' ? 'BLOQUEADO' : 'ACTIVO'; ?>">
                                    <?php if($row['estado']==='ACTIVO'): ?>
                                        <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Bloquear Usuario">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 p-2 rounded-lg transition-colors" title="Desbloquear Usuario">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
