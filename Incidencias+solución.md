Te dejo tu texto reformateado y mejorado (copia/pega tal cual):

## 🛠️ Gestión de Incidencias y Resolución de Errores

Durante la ejecución del despliegue del MVP se produjeron diversas incidencias técnicas.  
Estas incidencias permitieron validar el funcionamiento real de la infraestructura y aplicar procedimientos de diagnóstico y resolución.

A continuación se detallan los errores detectados y las soluciones aplicadas.

---

### ❌ Incidencia 1: Docker Compose no creaba contenedores

**Síntomas**
- `docker compose up -d` se ejecutaba sin errores críticos.
- `docker ps -a` no mostraba ningún contenedor.
- Aparecían avisos relacionados con la red `root_default`.

**Mensaje observado**
```text
WARN a network with name root_default exists but was not created by compose.
network root_default was found but has incorrect label
```
Causa
Existía una red Docker llamada root_default creada previamente, pero no gestionada por Docker Compose.
Al no coincidir las etiquetas internas esperadas por Compose, el despliegue del stack quedaba bloqueado y los contenedores no llegaban a crearse.

Solución aplicada

Comprobación de contenedores:

docker ps -a
Eliminación de la red conflictiva:

docker network rm root_default
Despliegue limpio del stack:

docker compose up -d
Resultado
Los contenedores se crearon correctamente y el despliegue continuó con normalidad.

❌ Incidencia 2: MariaDB entraba en bucle de reinicio (“Restarting”)
Síntomas

El contenedor de MariaDB se reiniciaba continuamente.

No era posible acceder a la base de datos.

Causa
MariaDB requiere obligatoriamente la variable MYSQL_ROOT_PASSWORD para inicializarse.
Al no estar definida, el contenedor fallaba durante el arranque.

Solución aplicada

Revisión de logs:

docker logs <contenedor_mariadb>
Definición correcta de variables en docker-compose.yml:

environment:
  MYSQL_ROOT_PASSWORD: root123
  MYSQL_DATABASE: gira_db
  MYSQL_USER: gira_user
  MYSQL_PASSWORD: gira_pass
Reinicio del stack:

docker compose down
docker compose up -d
Resultado
MariaDB se inició correctamente y permaneció en estado operativo.

❌ Incidencia 3: Error “No such container” al usar docker exec
Síntomas
Al intentar acceder a MariaDB mediante:

docker exec -it mariaDB mysql -u root -p
aparecía:

No such container: mariaDB
Causa
mariaDB era el nombre del servicio en Docker Compose, pero el contenedor tenía un nombre real automático (ej.: proyecto-mariaDB-1).

Solución aplicada

Identificación del nombre real:

docker ps
Acceso usando el nombre real:

docker exec -it <nombre_real_contenedor> mysql -u root -p
Resultado
Se pudo acceder correctamente a la base de datos.

❌ Incidencia 4: n8n no podía conectarse a MariaDB
Síntomas
En n8n aparecía:

Access denied for user 'gira_user'@'172.18.0.4'
Causa
n8n y MariaDB estaban en contenedores distintos. MariaDB recibía la conexión desde una IP interna de Docker (172.18.0.x), pero el usuario solo tenía permisos desde localhost.

Solución aplicada

Acceso al contenedor MariaDB:

docker exec -it <contenedor_mariadb> mysql -u root -p
Creación del usuario con permisos desde cualquier host:

CREATE USER 'gira_user'@'%' IDENTIFIED BY 'gira_pass';
GRANT ALL PRIVILEGES ON gira_db.* TO 'gira_user'@'%';
FLUSH PRIVILEGES;
Configuración correcta en n8n:

Host: mariaDB (nombre del servicio Docker)

Puerto: 3306

Resultado
n8n se conectó correctamente a MariaDB y pudo listar/consultar datos.
