<?php
function autenticar_y_obtener_datos($username, $password) {
    $host = "31.97.157.220";
    $port = 8443;
    $base_dn = "dc=girahub,dc=local";
    
    // Credenciales del Admin de LDAP
    $ldap_admin = "cn=admin,dc=girahub,dc=local";
    $ldap_pass = "ldap_admin_pass"; 

    $ds = ldap_connect("ldap://$host:$port");
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

    if (!$ds) {
        return ['success' => false, 'debug' => 'Fallo al conectar con el servidor LDAP.'];
    }

    // 1. Autenticación del Admin
    if (!@ldap_bind($ds, $ldap_admin, $ldap_pass)) {
        return ['success' => false, 'debug' => 'Fallo de Admin: Conexión LDAP rechazada.'];
    }
            
    // 2. Búsqueda del usuario por UID
    $search = @ldap_search($ds, $base_dn, "(uid=$username)");
    $entries = @ldap_get_entries($ds, $search);

    if ($entries['count'] == 0) {
        return ['success' => false, 'debug' => "El usuario '$username' no existe en LDAP."];
    }

    // 3. Verificación de la contraseña del usuario
    $user_dn = $entries[0]['dn'];
    if (!@ldap_bind($ds, $user_dn, $password)) {
        return ['success' => false, 'debug' => 'Usuario encontrado, pero contraseña incorrecta.'];
    }

    // 4. Determinación del ROL basado en la Unidad Organizativa (OU)
    $rol = 'ALUMNO'; 
    $ruta_minusculas = strtolower($user_dn);
    
    if (strpos($ruta_minusculas, 'ou=profesores') !== false) {
        $rol = 'PROFESOR';
    } elseif (strpos($ruta_minusculas, 'ou=admin') !== false) {
        $rol = 'ADMIN';
    }

    @ldap_close($ds);

    // Retorno de datos (Email real o NULL, sin inventos de @girahub.es)
    return [
        'success' => true,
        'rol' => $rol,
        'nombre' => $entries[0]['givenname'][0] ?? $username,
        'apellidos' => $entries[0]['sn'][0] ?? '',
        'email' => $entries[0]['mail'][0] ?? null
    ];
}
?>