```markdown
# Manual de Despliegue y Configuración del Servicio n8n

## 1. Objetivo del servicio n8n

El objetivo de n8n es actuar como motor de lógica y automatización del sistema de reservas.  
Este servicio se encarga de:

- Recibir peticiones externas (webhooks)
- Procesar la lógica de negocio
- Interactuar con la base de datos MariaDB
- Devolver respuestas a la aplicación web

n8n se despliega como contenedor Docker dentro del VPS y se expone mediante Traefik con HTTPS.

---

## 2. Tecnologías utilizadas

- n8n  
- Docker  
- Docker Compose  
- Traefik  
- MariaDB  
- VPS con Ubuntu Server  

---

## 3. Despliegue del servicio n8n

### 3.1 Definición del servicio en Docker Compose

Dentro del archivo `docker-compose.yml` se define el servicio n8n:

```yaml
services:
  n8n:
    image: docker.n8n.io/n8nio/n8n
    restart: always
    labels:
      - traefik.enable=true
      - traefik.http.routers.n8n.rule=Host(`${SUBDOMAIN}.${DOMAIN_NAME}`)
      - traefik.http.routers.n8n.tls=true
      - traefik.http.routers.n8n.entrypoints=web,websecure
      - traefik.http.routers.n8n.tls.certresolver=mytlschallenge
    environment:
      - N8N_HOST=${SUBDOMAIN}.${DOMAIN_NAME}
      - N8N_PORT=5678
      - N8N_PROTOCOL=https
      - NODE_ENV=production
      - WEBHOOK_URL=https://${SUBDOMAIN}.${DOMAIN_NAME}/
      - GENERIC_TIMEZONE=Europe/Madrid
    ports:
      - 127.0.0.1:5678:5678
    volumes:
      - n8n_data:/home/node/.n8n
```

Este servicio:

- Ejecuta n8n en modo producción  
- Expone la interfaz web de forma segura mediante Traefik  
- Utiliza volúmenes Docker para persistir la configuración  

---

## 4. Puesta en marcha del servicio

Arranque del stack:

```bash
docker compose up -d
```

Comprobación del estado:

```bash
docker ps
```

Se verifica que el contenedor de n8n se encuentra en estado `Running`.

---

## 5. Acceso a la interfaz de n8n

El acceso a n8n se realiza desde el navegador mediante HTTPS:

```text
https://subdominio.dominio
```

En el primer acceso se crea el usuario administrador de n8n.

---

## 6. Configuración de credenciales MariaDB en n8n

Para permitir que n8n interactúe con la base de datos, se crean credenciales de tipo MySQL / MariaDB.

### 6.1 Creación de credenciales

En n8n:

1. Ir a **Credentials**  
2. Pulsar **New Credential**  
3. Tipo: **MySQL**  

Parámetros:

- Host: `mariaDB`  
- Port: `3306`  
- Database: `gira_db`  
- User: `gira_user`  
- Password: `contraseña_user`  
- SSL: desactivado  

Se realiza la prueba de conexión para verificar el acceso correcto.

---

## 7. Creación de workflows básicos

### 7.1 Workflow de prueba de funcionamiento

Workflow utilizado para verificar que n8n funciona correctamente.

**Nodos:**

- `Manual Trigger`  
- `Set`  

El nodo **Set** devuelve un mensaje de prueba, confirmando la ejecución correcta del flujo.

### 7.2 Workflow de prueba de conexión con la base de datos

Workflow utilizado para comprobar la conexión entre n8n y MariaDB.

**Nodos:**

- `Manual Trigger`  
- `MySQL (Execute Query)`  

Consulta utilizada:

```sql
SELECT 1 AS conexion_ok;
```

La respuesta correcta confirma la comunicación entre servicios.

### 7.3 Workflow de creación de reservas

Este workflow representa la base funcional del sistema.

**Nodo Webhook**

- Método HTTP: `POST`  
- Ruta: `crear-reserva`  
- Autenticación: `None`  

Este nodo simula las peticiones que realizará la aplicación web.

**Nodo Set**

Se utilizan campos simulados para pruebas iniciales:

- `aula`  
- `dispositivo`  
- `fecha`  
- `hora_inicio`  
- `hora_fin`  
- `curso`  

**Nodo MySQL**

- Operación: `Insert`  
- Tabla: `reservas`  

Columnas:

- `aula`  
- `dispositivo`  
- `fecha`  
- `hora_inicio`  
- `hora_fin`  
- `curso`  

Este nodo inserta la reserva en la base de datos.

---

## 8. Pruebas del webhook

En modo test:

- Se activa el botón **“Listen for test event”**  
- Se accede a la URL:

```text
https://subdominio.dominio/webhook-test/crear-reserva
```

En modo producción:

- Se activa el workflow  
- Se utiliza la URL:

```text
https://subdominio.dominio/webhook/crear-reserva
```

La inserción correcta de datos en la tabla `reservas` confirma el funcionamiento del workflow.

---

## 9. Resultado final

Tras completar estos pasos se dispone de:

- Servicio n8n desplegado y accesible mediante HTTPS  
- Conexión funcional con MariaDB  
- Workflows básicos operativos  
- Webhooks preparados para su consumo desde la aplicación web  
- Infraestructura lista para la integración con el frontend  
```

Si quieres, luego te hago un índice enlazado para los dos manuales (MariaDB + n8n) juntos.