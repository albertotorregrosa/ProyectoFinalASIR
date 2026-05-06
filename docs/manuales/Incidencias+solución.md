## Gestión de incidencias y resolución de errores

Durante la ejecución del despliegue del MVP se produjeron diversas incidencias técnicas.  
Estas incidencias permitieron validar el funcionamiento real de la infraestructura y aplicar procedimientos de diagnóstico y resolución.

A continuación se detallan los errores detectados y las soluciones aplicadas.

---

### Incidencia 1: Docker Compose no creaba contenedores

**Síntomas**

- `docker compose up -d` se ejecutaba sin errores críticos.  
- `docker ps -a` no mostraba ningún contenedor.  
- Aparecían avisos relacionados con la red `root_default`.

**Mensaje observado**

```text
WARN a network with name root_default exists but was not created by compose.
network root_default was found but has incorrect label
```

**Causa**

Existía una red Docker llamada `root_default` creada previamente, pero no gestionada por Docker Compose.  
Al no coincidir las etiquetas internas esperadas por Compose, el despliegue del stack quedaba bloqueado y los contenedores no llegaban a crearse.

**Solución aplicada**

1. Comprobación de contenedores:

   ```bash
   docker ps -a
   ```

2. Eliminación de la red conflictiva:

   ```bash
   docker network rm root_default
   ```

3. Despliegue limpio del stack:

   ```bash
   docker compose up -d
   ```

**Resultado**

Los contenedores se crearon correctamente y el despliegue continuó con normalidad.

---

### Incidencia 2: MariaDB entraba en bucle de reinicio (“Restarting”)

**Síntomas**

- El contenedor de MariaDB se reiniciaba continuamente.  
- No era posible acceder a la base de datos.

**Causa**

MariaDB requiere obligatoriamente la variable `MYSQL_ROOT_PASSWORD` para inicializarse.  
Al no estar definida, el contenedor fallaba durante el arranque.

**Solución aplicada**

1. Revisión de logs:

   ```bash
   docker logs <contenedor_mariadb>
   ```

2. Definición correcta de variables en `docker-compose.yml`:

   ```yaml
   environment:
     MYSQL_ROOT_PASSWORD: root123
     MYSQL_DATABASE: gira_db
     MYSQL_USER: gira_user
     MYSQL_PASSWORD: gira_pass
   ```

3. Reinicio del stack:

   ```bash
   docker compose down
   docker compose up -d
   ```

**Resultado**

MariaDB se inició correctamente y permaneció en estado operativo.

---

### Incidencia 3: Error “No such container” al usar `docker exec`

**Síntomas**

Al intentar acceder a MariaDB mediante:

```bash
docker exec -it mariaDB mysql -u root -p
```

aparecía:

```text
No such container: mariaDB
```

**Causa**

`mariaDB` era el nombre del **servicio** en Docker Compose, pero el contenedor tenía un nombre real automático (por ejemplo, `proyecto-mariaDB-1`).

**Solución aplicada**

1. Identificación del nombre real:

   ```bash
   docker ps
   ```

2. Acceso usando el nombre real:

   ```bash
   docker exec -it <nombre_real_contenedor> mysql -u root -p
   ```

**Resultado**

Se pudo acceder correctamente a la base de datos.

---

### Incidencia 4: n8n no podía conectarse a MariaDB

**Síntomas**

En n8n aparecía:

```text
Access denied for user 'gira_user'@'172.18.0.4'
```

**Causa**

n8n y MariaDB estaban en contenedores distintos.  
MariaDB recibía la conexión desde una IP interna de Docker (`172.18.0.x`), pero el usuario solo tenía permisos desde `localhost`.

**Solución aplicada**

1. Acceso al contenedor MariaDB:

   ```bash
   docker exec -it <contenedor_mariadb> mysql -u root -p
   ```

2. Creación del usuario con permisos desde cualquier host:

   ```sql
   CREATE USER 'gira_user'@'%' IDENTIFIED BY 'gira_pass';
   GRANT ALL PRIVILEGES ON gira_db.* TO 'gira_user'@'%';
   FLUSH PRIVILEGES;
   ```

3. Configuración correcta en n8n:

   - Host: `mariaDB` (nombre del servicio Docker)  
   - Puerto: `3306`  

**Resultado**

n8n se conectó correctamente a MariaDB y pudo listar/consultar datos.

---

### Incidencia 5: Error SSL al acceder a LDAP por HTTPS (`curl` error 60)

**Síntomas**

Al ejecutar:

```bash
curl -I https://ldap.girahub.es
```

aparecía el error:

```text
curl: (60) SSL certificate problem: self-signed certificate
```

**Causa**

El sistema no confiaba todavía en la cadena de certificados emitida por Let’s Encrypt, ya que el paquete de certificados raíz no estaba correctamente instalado o actualizado en la VPS.

**Solución aplicada**

1. Reparación del sistema de paquetes:

   ```bash
   dpkg --configure -a
   ```

2. Instalación del paquete de certificados:

   ```bash
   apt install -y ca-certificates
   ```

3. Actualización de certificados:

   ```bash
   update-ca-certificates
   ```

**Resultado**

El acceso HTTPS a `https://ldap.girahub.es` se realizó correctamente sin errores SSL.

---

### Incidencia 6: Let’s Encrypt no emitía certificados (ACME error 400)

**Síntomas**

En los logs de Traefik aparecía:

```text
invalid authorization: acme:error:connection :: Connection refused
```

y para otros subdominios:

```text
DNS problem: NXDOMAIN
```

**Causa**

- El subdominio no apuntaba correctamente a la IP de la VPS.  
- Let’s Encrypt no podía validar el dominio al no resolverse en DNS.

**Solución aplicada**

1. Revisión de registros DNS del dominio.  
2. Creación del registro `A`:

   ```text
   ldap.girahub.es → IP de la VPS
   ```

3. Espera de propagación DNS.  
4. Reinicio de Traefik:

   ```bash
   docker compose restart traefik
   ```

**Resultado**

Traefik obtuvo correctamente los certificados SSL y el acceso HTTPS quedó operativo.

---

### Incidencia 7: Error “Invalid credentials (49)” al autenticar usuarios LDAP

**Síntomas**

Al intentar autenticar un usuario:

```text
ldap_bind: Invalid credentials (49)
```

**Causa**

Se estaba utilizando un DN incorrecto para el usuario.  
En LDAP no es válido autenticarse únicamente con `uid`, sino con el **Distinguished Name** completo.

**Solución aplicada**

1. Búsqueda del DN real del usuario:

   ```bash
   ldapsearch -x -b "dc=girahub,dc=local" "(uid=dgranados)"
   ```

2. Uso del DN correcto en la autenticación:

   ```text
   cn=David Granados,ou=usuarios,dc=girahub,dc=local
   ```

**Resultado**

La autenticación se realizó correctamente usando el DN completo.

---

### Incidencia 8: Error “No such object (32)” al realizar búsquedas LDAP

**Síntomas**

Al ejecutar búsquedas LDAP:

```text
result: 32 No such object
```

**Causa**

Se estaba utilizando como base de búsqueda (`-b`) una rama que no existía como objeto contenedor en el directorio LDAP.

**Solución aplicada**

1. Comprobación del contexto raíz:

   ```bash
   ldapsearch -x -s base -b "" namingContexts
   ```

2. Uso correcto del base DN existente:

   ```text
   dc=girahub,dc=local
   ```

**Resultado**

Las búsquedas LDAP funcionaron correctamente al usar una base válida.

---

### Incidencia 9: Usuarios creados en una rama incorrecta

**Síntomas**

- El usuario aparecía directamente bajo `dc=girahub,dc=local`.  
- No seguía una estructura LDAP estándar.

**Causa**

phpLDAPadmin permite crear entradas sin OU si no se selecciona correctamente el contenedor antes de crear el usuario.

**Solución aplicada**

1. Creación de unidades organizativas:

   - `ou=usuarios`  
   - `ou=grupos`  

2. Recreación del usuario dentro de `ou=usuarios`.  
3. Eliminación del usuario antiguo mal ubicado.

**Resultado**

La estructura LDAP quedó normalizada y organizada correctamente.

---

### Incidencia 10: Confusión entre `cn`, `uid` y `ou`

**Síntomas**

- Dudas al autenticar y buscar usuarios:
  - No quedaba claro cuándo usar `cn` o `uid`.  
  - Fallos de autenticación por DN mal formado.

**Causa**

LDAP diferencia entre:

- `cn` → *Common Name* (nombre descriptivo)  
- `uid` → Identificador único de usuario  
- `ou` → Unidad organizativa  

Cada uno cumple una función distinta dentro del DN.

**Solución aplicada**

- Uso de `uid` como identificador del usuario.  
- Uso de `cn` como nombre descriptivo.  
- Uso de `ou` para organizar usuarios y grupos.  
- Documentación del DN completo para autenticación.

**Resultado**

Autenticaciones y búsquedas LDAP realizadas sin errores.
