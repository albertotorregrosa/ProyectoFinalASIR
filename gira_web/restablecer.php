<?php
require_once 'conexion.php';

// 1. VALIDAR QUE EL TOKEN EXISTA Y SEA VÁLIDO
if (!isset($_GET['token'])) {
    die("Acceso denegado: Token no proporcionado.");
}

$token = $_GET['token'];
$ahora = date('Y-m-d H:i:s');

// Buscamos el token en la BD
$sql = "SELECT r.usuario_id, u.ldap_uid, u.rol 
        FROM recuperacion_claves r 
        JOIN usuarios u ON r.usuario_id = u.id 
        WHERE r.token = ? AND r.expira_en > ? AND r.usado = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $token, $ahora);
$stmt->execute();
$resultado = $stmt->get_result();
$datos = $resultado->fetch_assoc();

if (!$datos) {
    die("El enlace es inválido, ya ha sido usado o ha caducado (15 min). Solicita uno nuevo.");
}

$mensaje = "";

// 2. PROCESAR EL CAMBIO DE CONTRASEÑA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_pass'])) {
    $nueva_pass = $_POST['nueva_pass'];
    $uid = $datos['ldap_uid'];

    // CONECTAR A LDAP
    $ldap_host = "ldap://31.97.157.220:8443";
    $ldap_dn = "cn=admin,dc=girahub,dc=local";
    $ldap_pass = "ldap_admin_pass"; 
    $ds = ldap_connect($ldap_host);
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

    if ($ds && @ldap_bind($ds, $ldap_dn, $ldap_pass)) {
        // Búsqueda del usuario en todo el árbol de GiraHub
        $sr = ldap_search($ds, "dc=girahub,dc=local", "(uid=$uid)");
        $info = ldap_get_entries($ds, $sr);
        
        if ($info["count"] > 0) {
            $user_dn = $info[0]["dn"];
            
            // Encriptación compatible con tu OpenLDAP (SHA)
            $encoded_pass = "{SHA}" . base64_encode(pack("H*", sha1($nueva_pass)));
            $entry["userPassword"] = $encoded_pass;

            if (ldap_modify($ds, $user_dn, $entry)) {
                // Invalidad el token para que no se use dos veces
                $conn->query("UPDATE recuperacion_claves SET usado = 1 WHERE token = '$token'");
                $mensaje = "✅ Contraseña actualizada correctamente. Ya puedes <a href='login.php' style='color:blue;text-decoration:underline;'>iniciar sesión</a>.";
            } else {
                $mensaje = "❌ Error al actualizar la contraseña en el directorio.";
            }
        }
    } else {
        $mensaje = "❌ No se pudo conectar con el servidor de identidades.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>GiraHub - Restablecer Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-3xl shadow-xl max-w-md w-full border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Nueva Contraseña</h2>
        <p class="text-gray-500 text-sm mb-6">Usuario: <span class="font-bold text-orange-600"><?php echo htmlspecialchars($datos['ldap_uid']); ?></span></p>

        <?php if ($mensaje): ?>
            <div class="p-4 mb-4 rounded-xl text-sm font-medium <?php echo strpos($mensaje, '✅') !== false ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Escribe tu nueva contraseña</label>
                    <input type="password" name="nueva_pass" required minlength="8" 
                           class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-orange-200 outline-none transition-all">
                </div>
                <button type="submit" 
                        class="w-full py-4 bg-gray-900 text-white rounded-2xl font-bold hover:bg-black transition-all active:scale-95">
                    Guardar Cambios
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>