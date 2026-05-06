<?php
session_start();
require_once 'conexion.php';
require_once 'includes/auth_ldap.php';

$mensaje_recuperacion = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- BLOQUE 1: PROCESAR RECUPERACIÓN DE CONTRASEÑA (SISTEMA SEGURO POR TOKEN) ---
    if (isset($_POST['accion_recuperar'])) {
        $email_usuario = $_POST['email_recuperar'];

        // Buscamos si el usuario existe
        $stmt_check = $conn->prepare("SELECT id, ldap_uid FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email_usuario);
        $stmt_check->execute();
        $resultado_check = $stmt_check->get_result();
        $user = $resultado_check->fetch_assoc();

        if ($user) {
            // 1. Generamos el Token de seguridad
            $token = bin2hex(random_bytes(32)); 
            $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $u_id = $user['id'];

            // 2. Lo guardamos en la tabla (Asegúrate de haber creado la tabla 'recuperacion_claves')
            $conn->query("INSERT INTO recuperacion_claves (usuario_id, token, expira_en) VALUES ($u_id, '$token', '$expira')");

            // 3. Preparamos el enlace que irá en el correo
            $enlace_recuperacion = "https://girahub.es/restablecer.php?token=" . $token;

            // 4. Llamamos al NUEVO Webhook de n8n
            $webhook_url = 'https://n8n.girahub.es/webhook/solicitud-reset'; 
            
            $data = array(
                'email'   => $email_usuario, 
                'usuario' => $user['ldap_uid'], 
                'enlace'  => $enlace_recuperacion
            );

            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/json\r\n",
                    'method'  => 'POST',
                    'content' => json_encode($data),
                    'timeout' => 5
                ),
            );
            $context  = stream_context_create($options);
            $result = @file_get_contents($webhook_url, false, $context);
            
            $mensaje_recuperacion = ($result !== FALSE) ? "Si el correo es correcto, recibirás un enlace para cambiar tu contraseña en unos minutos." : "Error: El sistema de envío no está disponible.";
        } else {
            // Por seguridad, mostramos el mismo mensaje aunque el email no exista
            $mensaje_recuperacion = "Si el correo es correcto, recibirás un enlace para cambiar tu contraseña en unos minutos.";
        }
    }
    // BLOQUE 2: LOGIN TRADICIONAL
    if (isset($_POST['usuario']) && isset($_POST['password'])) {
        $user = $_POST['usuario'];
        $pass = $_POST['password'];
        $datos_ldap = autenticar_y_obtener_datos($user, $pass);

        if ($datos_ldap['success'] === true) {
            $stmt = $conn->prepare("SELECT id, nombre, rol, estado FROM usuarios WHERE ldap_uid = ?");
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $usuario_db = $stmt->get_result()->fetch_assoc();

            if (!$usuario_db) {
                $email_ldap = $datos_ldap['email'];
                if (empty($email_ldap) || !filter_var($email_ldap, FILTER_VALIDATE_EMAIL)) {
                    $error = "Error: Tu cuenta de LDAP no tiene un email válido asignado.";
                } else {
                    $stmt_insert = $conn->prepare("INSERT INTO usuarios (ldap_uid, nombre, apellidos, email, rol, estado) VALUES (?, ?, ?, ?, ?, 'ACTIVO')");
                    $stmt_insert->bind_param("sssss", $user, $datos_ldap['nombre'], $datos_ldap['apellidos'], $email_ldap, $datos_ldap['rol']);
                    $stmt_insert->execute();
                    $stmt->execute();
                    $usuario_db = $stmt->get_result()->fetch_assoc();
                }
            }

            if ($usuario_db) {
                if ($usuario_db['estado'] !== 'ACTIVO') {
                    $error = "Acceso Denegado: Usuario bloqueado.";
                } else {
                    $_SESSION['usuario_id'] = $usuario_db['id'];
                    $_SESSION['nombre'] = $usuario_db['nombre'];
                    $_SESSION['rol'] = $usuario_db['rol'];
                    $_SESSION['ldap_uid'] = $user;
                    header("Location: index.php");
                    exit;
                }
            }
        } else {
            $error = "Aviso: " . ($datos_ldap['debug'] ?? "Credenciales incorrectas");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiraHub - Inicio de Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { brand: { 50: '#FDF6F0', 100: '#FCE7D8', 200: '#F8DAC6', 500: '#E89060', 800: '#8A4831', 900: '#4F2516' } }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        @keyframes customFloat { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
        .float-anim { animation: customFloat 6s ease-in-out infinite; }
    </style>
</head>
<body class="bg-brand-50 min-h-screen text-gray-900 font-sans antialiased selection:bg-brand-500 selection:text-white" x-data="{ modalOlvido: false }">

    <div class="min-h-screen flex flex-col md:flex-row">
        
        <div class="flex-1 flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-24 xl:px-32 relative z-10 w-full md:w-1/2 bg-brand-50">
            <div class="mx-auto w-full max-w-sm lg:max-w-md">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 mb-2">Bienvenido!!</h1>
                <p class="text-gray-500 font-medium mb-10 text-sm">GiraHub: Gestión Institucional.</p>

                <?php if ($mensaje_recuperacion): ?>
                    <div class="mb-6 rounded-3xl p-4 border shadow-sm text-sm font-medium <?php echo (strpos($mensaje_recuperacion, 'Error') !== false) ? 'bg-red-50 border-red-100 text-red-700' : 'bg-green-50 border-green-100 text-green-700'; ?>">
        <?php echo $mensaje_recuperacion; ?>
    </div>
<?php endif; ?>
                <?php if ($error): ?>
                    <div class="mb-6 rounded-3xl bg-red-50 p-4 border border-red-100 shadow-sm text-sm text-red-700 font-medium animate-pulse"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form class="space-y-6" action="login.php" method="POST" x-data="{ showPass: false }">
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Usuario Institucional</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <input type="text" name="usuario" required autofocus placeholder="Usuario LDAP" class="appearance-none block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 text-sm transition-all text-gray-800">
                        </div>
                    </div>

                    <div class="space-y-1 mt-6">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest pl-4 mb-2">Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" name="password" required placeholder="Contraseña" class="appearance-none block w-full pl-12 pr-12 py-4 bg-white border border-gray-200 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-500 text-sm transition-all text-gray-800">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400">
                                <svg x-show="!showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></button>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-3 mb-6 pr-2">
                        <button type="button" @click="modalOlvido = true" class="text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors">¿Olvidaste tu contraseña?</button>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-full shadow-sm text-sm font-bold text-brand-900 bg-brand-100 hover:bg-brand-200 focus:outline-none transition-all hover:shadow-md active:scale-[0.98]">Login</button>
                </form>
            </div>
        </div>

        <div class="hidden md:flex flex-1 relative bg-brand-50 overflow-hidden items-center justify-center p-8 lg:p-12">
            <div class="absolute -top-10 left-0 w-64 h-64 bg-brand-200 rounded-full filter blur-3xl opacity-50 animate-pulse"></div>
            <div class="relative w-full max-w-[400px] lg:max-w-[480px] aspect-square bg-brand-100 rounded-[3rem] shadow-2xl float-anim overflow-hidden z-10 mx-auto">
                <img src="/img/login.png" alt="Login Image" class="absolute inset-0 w-full h-full object-cover">
            </div>
        </div>
    </div>

    <div x-show="modalOlvido" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" x-transition x-cloak>
        <div @click.away="modalOlvido = false" class="bg-white rounded-[2rem] p-8 max-w-sm w-full shadow-2xl">
            <h2 class="text-xl font-extrabold text-gray-900 mb-2 text-center">Recuperar Acceso</h2>
            <p class="text-xs text-gray-500 mb-8 text-center px-2">Introduce tu email institucional y avisaremos al administrador para resetear tu clave.</p>
            <form action="login.php" method="POST" class="space-y-5">
                <input type="hidden" name="accion_recuperar" value="1">
                <div class="relative">
                    <input type="email" name="email_recuperar" required placeholder="ejemplo@girahub.es" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-full focus:ring-2 focus:ring-brand-200 outline-none text-sm">
                </div>
                <div class="flex space-x-3 pt-2">
                    <button type="button" @click="modalOlvido = false" class="flex-1 py-4 bg-gray-100 text-gray-600 rounded-full font-bold text-sm">Cancelar</button>
                    <button type="submit" class="flex-1 py-4 bg-brand-500 text-white rounded-full font-bold shadow-lg shadow-brand-500/30 text-sm">Enviar</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>