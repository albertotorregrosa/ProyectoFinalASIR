<?php
require_once 'conexion.php';

if (isset($_POST['enviar_recuperacion'])) {
    $email_alumno = $_POST['email'];
    
    // Buscar al usuario por email
    $res = $conn->query("SELECT id, ldap_uid FROM usuarios WHERE email = '$email_alumno'");
    $user = $res->fetch_assoc();

    if ($user) {
        $token = bin2hex(random_bytes(32)); // Genera un token de 64 caracteres
        $expira = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Caduca en 15 min
        $u_id = $user['id'];

        // Guardar en la base de datos
        $conn->query("INSERT INTO recuperacion_claves (usuario_id, token, expira_en) VALUES ($u_id, '$token', '$expira')");

        // URL que el alumno pulsará (Cámbiala por tu dominio real)
        $enlace_recuperacion = "https://girahub.es/restablecer.php?token=" . $token;

        // Avisar a n8n para que mande el correo
        $webhook_url = "https://n8n.girahub.es/webhook/solicitud-reset";
        $datos = [
            "email" => $email_alumno,
            "usuario" => $user['ldap_uid'],
            "enlace" => $enlace_recuperacion
        ];

        // Llamada a n8n
        $ch = curl_init($webhook_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_exec($ch);
        curl_close($ch);

        echo "✅ Enlace de recuperación enviado al alumno.";
    }
}
?>