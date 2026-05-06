# Manual (solo web): creación, modificación e integración con n8n/MariaDB

1\. Arquitectura del Sistema (Híbrida)
--------------------------------------

El sistema utiliza una arquitectura distribuida para maximizar la seguridad y el rendimiento:

-   **Frontend y Lógica de Aplicación:** Alojado en **Hostinger** (Hosting Compartido). Desarrollado en **PHP Vanilla**, HTML5 y CSS3 moderno.

-   **Base de Datos:** Motor **MariaDB 11** corriendo en un contenedor **Docker** dentro de un **VPS Ubuntu** (IP: 31.97.157.220).

-   **Automatización:** **n8n** (en proceso) alojado en el VPS para notificaciones e integraciones externas.

* * * * *

2\. Conexión de Datos (El Puente)
---------------------------------

A diferencia del manual anterior, la web **SÍ accede directamente a la base de datos** mediante el archivo `conexion.php`.

-   **Entorno Local:** Conecta a `localhost`.

-   **Entorno Producción:** Conecta vía TCP/IP al VPS remoto por el puerto **3306**.

-   **Seguridad:** El VPS solo acepta conexiones entrantes desde la IP específica de Hostinger (configurado en UFW).

* * * * *

3\. Estructura de Archivos en Hostinger
---------------------------------------

Los archivos se encuentran en la raíz `public_html/`. **Es crítico usar rutas absolutas desde la raíz (`/`)** para evitar errores 404 al navegar entre carpetas.

Plaintext

```
public_html/
├── index.php          # Dashboard principal (Calendario)
├── login.php          # Acceso al sistema (Simulación LDAP/Demo)
├── conexion.php       # Gestión de conexión dual (Local/Producción) - [GITIGNORE]
├── includes/          # Componentes reutilizables (Header, Footer)
├── api/               # Endpoints que devuelven JSON (ej: eventos.php para el calendario)
├── css/               # Estilos (style.css)
├── js/                # Lógica del lado del cliente
└── [carpetas_roles]/ # aulas/, dispositivos/, usuarios/ (Lógica específica)

```

* * * * *

4\. Gestión de Roles y Permisos
-------------------------------

El sistema identifica al usuario mediante `$_SESSION['rol']` tras validar su `ldap_uid`:

1.  **ADMIN:** Acceso total a CRUD de usuarios, aulas y dispositivos. Borrado lógico (soft-delete).

2.  **PROFESOR:** Reserva de aulas y reporte de incidencias.

3.  **ALUMNO:** Únicamente reserva de dispositivos.

* * * * *

5\. Reglas de Negocio Estrictas
-------------------------------

-   **Horario Institucional:** Reservas permitidas únicamente de **08:00 a 21:00**.

-   **Validación de Solapamiento:** El sistema comprueba en MariaDB que el recurso no esté ocupado antes de confirmar la reserva.

-   **Integración n8n:** n8n actuará como oyente (listener) de la base de datos para disparar alertas (Telegram/Email) cuando se cree una reserva o incidencia.

* * * * *

6\. Despliegue y Mantenimiento
------------------------------

1.  **Repositorio:** El código se sube a GitHub ocultando las credenciales reales mediante un archivo `.gitignore`.

2.  **Cambios en Caliente:** Se realizan en el Administrador de Archivos de Hostinger o vía FTP.

3.  **Base de Datos:** Se gestiona vía terminal SSH en el VPS o mediante herramientas como DBeaver/HeidiSQL conectadas a la IP del VPS.
