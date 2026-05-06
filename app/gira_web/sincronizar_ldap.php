<?php
header('Content-Type: application/json');

$token_esperado = "SuperSecreto123"; 

if (!isset($_GET['token']) || $_GET['token'] !== $token_esperado) {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado. Token invalido."]);
    exit;
}

require_once 'conexion.php';

$ldap_host = "ldap://31.97.157.220:8443"; 
$ldap_dn = "cn=admin,dc=girahub,dc=local"; 
$ldap_pass = "ldap_admin_pass";
$base_dn = "dc=girahub,dc=local";

$estadisticas = [
    "usuarios_eliminados" => 0,
    "lista_eliminados" => []
];

$conn_ldap = ldap_connect($ldap_host);
ldap_set_option($conn_ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

if ($conn_ldap && @ldap_bind($conn_ldap, $ldap_dn, $ldap_pass)) {
    $busqueda = ldap_search($conn_ldap, $base_dn, "(uid=*)", ["uid"]);
    $entradas_ldap = ldap_get_entries($conn_ldap, $busqueda);
    
    $uids_ldap = [];
    for ($i = 0; $i < $entradas_ldap["count"]; $i++) {
        if (isset($entradas_ldap[$i]["uid"][0])) {
            $uids_ldap[] = $entradas_ldap[$i]["uid"][0];
        }
    }
    
    // Consultamos todos los usuarios para ver quién ya no está en LDAP
    $sql = "SELECT id, ldap_uid FROM usuarios";
    $resultado_db = $conn->query($sql);
    
    if ($resultado_db && $resultado_db->num_rows > 0) {
        
        while ($usuario_db = $resultado_db->fetch_assoc()) {
            
            // Si el usuario de la DB NO existe en LDAP... ¡A limpiar!
            if (!in_array($usuario_db['ldap_uid'], $uids_ldap)) {
                $u_id = $usuario_db['id'];

                // Iniciamos transacción para no dejar datos a medias
                $conn->begin_transaction();

                try {
                    // Borramos en orden para que las Foreign Keys no den error
                    $conn->query("DELETE FROM reservas_dispositivos WHERE usuario_id = $u_id");
                    $conn->query("DELETE FROM reservas WHERE usuario_id = $u_id");
                    $conn->query("DELETE FROM incidencias_aula WHERE reportada_por = $u_id OR asignada_a = $u_id");
                    $conn->query("DELETE FROM sesiones WHERE usuario_id = $u_id");
                    $conn->query("DELETE FROM usuarios WHERE id = $u_id");

                    $conn->commit();

                    $estadisticas["usuarios_eliminados"]++;
                    $estadisticas["lista_eliminados"][] = $usuario_db['ldap_uid'];

                } catch (Exception $e) {
                    $conn->rollback(); // Si algo falla en uno, no borra nada
                }
            }
        }
    }
    
    echo json_encode([
        "status" => "success", 
        "mensaje" => "Sincronizacion y limpieza completada",
        "datos" => $estadisticas
    ]);

} else {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo conectar a LDAP."]);
}
?>