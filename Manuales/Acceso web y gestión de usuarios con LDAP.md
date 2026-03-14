
📖 Manual de Integración: OpenLDAP + PHP (MariaDB) con Auto-Registro (JIT Provisioning)
=======================================================================================

📌 Introducción
---------------

Este documento detalla la implementación del sistema de autenticación centralizada para **GiraHub**. El sistema utiliza un servidor **OpenLDAP** para validar las credenciales de los usuarios y aplica un modelo de **Just-In-Time (JIT) Provisioning** para registrarlos automáticamente en la base de datos **MariaDB** con su rol correspondiente según la Unidad Organizativa (OU) a la que pertenezcan.

🏗️ Arquitectura
----------------

-   **Backend:** PHP (con extensión LDAP activada) + `mysqli`.

-   **Base de Datos:** MariaDB (Contenedor Docker).

-   **Directorio Activo:** OpenLDAP (Contenedor Docker).

-   **Gestor LDAP:** phpLDAPadmin (Contenedor Docker).

* * * * *

🚀 1. Configuración de la Infraestructura (Docker)
--------------------------------------------------

Para desplegar el servicio LDAP y su panel de gestión, añadimos estos servicios a nuestro `docker-compose.yml`:

YAML

```
  ldap:
    image: osixia/openldap:1.5.0
    container_name: ldap
    restart: always
    ports:
      - "8443:389" # Puerto expuesto para la conexión PHP
    environment:
      LDAP_ORGANISATION: "GIRAHUB"
      LDAP_DOMAIN: "girahub.local"
      LDAP_ADMIN_PASSWORD: "ldap_admin_pass"

  phpldapadmin:
    image: osixia/phpldapadmin:0.9.0
    container_name: phpldapadmin
    ports:
      - "8081:80" # Mapeo crucial para acceder al panel web
    environment:
      PHPLDAPADMIN_LDAP_HOSTS: "ldap"
      PHPLDAPADMIN_HTTPS: "false"
    depends_on:
      - ldap

```

* * * * *

🧠 2. La Lógica: Auto-Registro (JIT Provisioning)
-------------------------------------------------

El flujo de inicio de sesión no requiere que el administrador duplique el trabajo creando usuarios en LDAP y en MariaDB. El proceso es:

1.  El usuario introduce credenciales en la web.

2.  PHP verifica la contraseña contra LDAP.

3.  Si es válida, PHP lee la ruta (DN) del usuario en LDAP para saber su rol (`ou=Profesores`, `ou=Alumnos`, `ou=Admin`).

4.  Si el usuario no existe en MariaDB, **el sistema lo inserta automáticamente** en ese instante.

### Código: `includes/auth_ldap.php` (El Buscador)

PHP

```
<?php
function autenticar_y_obtener_datos($username, $password) {
    $host = "31.97.157.220";
    $port = 8443;
    $base_dn = "dc=girahub,dc=local";

    $ldap_admin = "cn=admin,dc=girahub,dc=local";
    $ldap_pass = "ldap_admin_pass";

    // Conexión a LDAP usando el formato URI
    $ds = ldap_connect("ldap://$host:$port");
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

    if (!$ds) return ['success' => false, 'debug' => 'Fallo de conexión al servidor LDAP.'];

    // 1. Bind como Admin para buscar al usuario
    if (!@ldap_bind($ds, $ldap_admin, $ldap_pass)) return ['success' => false, 'debug' => 'Credenciales de Admin LDAP incorrectas.'];

    // 2. Buscar al usuario
    $search = @ldap_search($ds, $base_dn, "(uid=$username)");
    $entries = @ldap_get_entries($ds, $search);

    if ($entries['count'] == 0) return ['success' => false, 'debug' => "Usuario inexistente en LDAP."];

    // 3. Bind con las credenciales del usuario
    $user_dn = $entries[0]['dn'];
    if (!@ldap_bind($ds, $user_dn, $password)) return ['success' => false, 'debug' => 'Contraseña incorrecta.'];

    // 4. Asignación automática de rol basada en la OU
    $rol = 'ALUMNO';
    $ruta_minusculas = strtolower($user_dn);
    if (strpos($ruta_minusculas, 'ou=profesores') !== false) $rol = 'PROFESOR';
    elseif (strpos($ruta_minusculas, 'ou=admin') !== false) $rol = 'ADMIN';

    @ldap_close($ds);
    return [
        'success' => true,
        'rol' => $rol,
        'nombre' => $entries[0]['givenname'][0] ?? $username,
        'apellidos' => $entries[0]['sn'][0] ?? '',
        'email' => $entries[0]['mail'][0] ?? "$username@girahub.es"
    ];
}
?>

```

### Código: `login.php` (El Gestor de Sesiones con `mysqli`)

PHP

```
<?php
session_start();
require_once 'conexion.php';
require_once 'includes/auth_ldap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['usuario'];
    $pass = $_POST['password'];

    $datos_ldap = autenticar_y_obtener_datos($user, $pass);

    if ($datos_ldap['success'] === true) {

        // Buscar en MariaDB
        $stmt = $conn->prepare("SELECT id, nombre, rol FROM usuarios WHERE ldap_uid = ? AND estado = 'ACTIVO'");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $usuario_db = $stmt->get_result()->fetch_assoc();

        // JIT Provisioning: Si no existe, lo creamos
        if (!$usuario_db) {
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (ldap_uid, nombre, apellidos, email, rol, estado) VALUES (?, ?, ?, ?, ?, 'ACTIVO')");
            $stmt_insert->bind_param("sssss", $user, $datos_ldap['nombre'], $datos_ldap['apellidos'], $datos_ldap['email'], $datos_ldap['rol']);
            $stmt_insert->execute();

            $stmt->execute();
            $usuario_db = $stmt->get_result()->fetch_assoc();
        }

        // Iniciar Sesión
        $_SESSION['usuario_id'] = $usuario_db['id'];
        $_SESSION['nombre'] = $usuario_db['nombre'];
        $_SESSION['rol'] = $usuario_db['rol'];
        header("Location: index.php");
        exit;

    } else {
        $error = "Error: " . ($datos_ldap['debug'] ?? "Credenciales incorrectas.");
    }
}
?>

```

* * * * *

🐛 3. Diario de Errores y Soluciones (Troubleshooting)
------------------------------------------------------

Durante el desarrollo nos encontramos con varios bloqueos técnicos que solucionamos así:

1.  **Error:** `phpLDAPadmin` inaccesible desde el navegador.

    -   **Causa:** El contenedor funcionaba en los puertos 80/443 internamente, pero no estaban expuestos al VPS.

    -   **Solución:** Modificar `docker-compose.yml` para mapear el puerto (`8081:80`) y abrir el puerto 8081 en el Firewall del VPS.

2.  **Error:** Al crear un usuario en phpLDAPadmin salta la alerta `This attribute is required: GID Number`, pero el desplegable está vacío.

    -   **Causa:** Una cuenta `posixAccount` necesita pertenecer a un grupo Posix obligatoriamente.

    -   **Solución:** Crear primero un "Generic: Posix Group" (ej: `UsuariosBase`). Esto pobló el desplegable.

3.  **Error:** `HTTP ERROR 500` al intentar hacer login en la web.

    -   **Causa:** La función `ldap_connect()` no existía porque la extensión LDAP de PHP estaba apagada en el hosting.

    -   **Solución:** Entrar al panel (hPanel de Hostinger) > Configuración de PHP > Extensiones PHP > Activar `ldap`.

4.  **Error:** `Deprecated: Usage of ldap_connect with two arguments`.

    -   **Causa:** PHP 8.x considera obsoleta la sintaxis `$ds = ldap_connect($host, $port)`.

    -   **Solución:** Cambiar al formato URI: `$ds = ldap_connect("ldap://$host:$port")`.

5.  **Error:** `Call to a member function prepare() on null`.

    -   **Causa:** Discrepancia de librerías. El código original usaba `PDO` (`$pdo->prepare`), pero el archivo `conexion.php` del proyecto estaba instanciado con `mysqli` (`$conn`).

    -   **Solución:** Refactorizar las sentencias preparadas en `login.php` para usar la sintaxis de `mysqli` (`bind_param`, `get_result`, etc.).

6.  **Error:** `Cannot truncate a table referenced in a foreign key constraint` al intentar limpiar la BD.

    -   **Causa:** Medida de seguridad de MariaDB por tener claves foráneas apuntando a la tabla `usuarios`.

    -   **Solución:** Desactivar temporalmente los chequeos:

        SQL

        ```
        SET FOREIGN_KEY_CHECKS = 0;
        TRUNCATE TABLE usuarios;
        SET FOREIGN_KEY_CHECKS = 1;

        ```

* * * * *

👥 4. Manual de Uso: Cómo añadir nuevos usuarios al sistema
-----------------------------------------------------------

Gracias a la arquitectura implementada, **no es necesario tocar la base de datos MariaDB para dar de alta a un usuario**.

**Pasos para el Administrador:**

1.  Entrar al panel de **phpLDAPadmin** (`http://[IP_DEL_VPS]:8081`).

2.  Desplegar el árbol de directorio (la bola del mundo).

3.  Seleccionar la **Unidad Organizativa (OU)** adecuada según el rol deseado (`ou=Admin`, `ou=Profesores` u `ou=Alumnos`).

4.  Clic en *Create new entry here* -> *Generic: User Account*.

5.  Rellenar los campos clave:

    -   **First Name** y **Last Name**.

    -   **User ID**: Será el nombre de usuario para el login (ej: `npons`).

    -   **GID Number**: Seleccionar el grupo base del desplegable.

    -   **Password**: Asignar una contraseña inicial.

6.  Guardar.

**¡Eso es todo!** La primera vez que ese usuario inicie sesión en GiraHub, el sistema lo validará, copiará sus datos y le asignará los permisos de su OU automáticamente en MariaDB.
