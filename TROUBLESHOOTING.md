# 🚀 Proyecto de Automatización de Reservas – Fase 3 (Despliegue MVP)

## Centro / Módulo
Proyecto de Infraestructura y Servicios en Red

## Integrantes del equipo
- Nazareth Pons Martínez  
- Alberto Torregrosa Montes  

---

## 1️⃣ Contexto y Objetivos

Una vez diseñado el sistema y definida la infraestructura en fases anteriores, en esta **Fase 3**
se ha llevado a cabo la **ejecución del despliegue mínimo viable (MVP)**.

El objetivo principal de esta entrega es demostrar la **viabilidad técnica** del proyecto mediante
la puesta en marcha de una infraestructura base operativa, sin necesidad de implementar todavía
todas las funcionalidades finales del sistema.

El MVP permite validar:
- La correcta instalación de los servicios base
- La conectividad entre componentes
- La resolución de incidencias reales durante el despliegue

---

## 2️⃣ Planificación de la Intervención

### 🔹 Secuenciación de actividades
1. Acceso y configuración inicial del VPS en Hostinger
2. Instalación de Docker y Docker Compose
3. Preparación del archivo `docker-compose.yml`
4. Despliegue de servicios base (Traefik, n8n y MariaDB)
5. Resolución de incidencias detectadas
6. Verificación del funcionamiento de los servicios

### 🔹 Recursos humanos
- Equipo formado por 2 personas
- Trabajo colaborativo y remoto

### 🔹 Recursos materiales
- VPS Hostinger (Ubuntu Server)
- Hosting compartido (web)
- Software libre: Docker, MariaDB, n8n, Traefik

---

## 3️⃣ Ejecución Técnica del Despliegue

### 🔹 Infraestructura desplegada

- **VPS en Hostinger**
  - Docker
  - Docker Compose
  - Traefik (proxy inverso)
  - n8n (automatización)
  - MariaDB (base de datos)

- **Hosting compartido**
  - Preparado para la futura web del proyecto

### 🔹 Comandos principales utilizados

```bash
docker compose up -d
docker ps
docker logs
docker exec


🔹 Evidencias de funcionamiento
Contenedores activos (docker ps)

Acceso a n8n vía navegador

Acceso correcto a MariaDB desde consola

Conexión funcional entre n8n y MariaDB

4️⃣ Procedimientos Operativos
▶️ Arranque de servicios
docker compose up -d
⏹️ Parada de servicios
docker compose down
🔍 Comprobación de estado
docker ps
🗄️ Acceso a MariaDB
docker exec -it <contenedor_mariadb> mysql -u root -p
5️⃣ Seguridad Inicial Implementada
Uso de usuarios y contraseñas en MariaDB

Separación de servicios por contenedores

Comunicación interna mediante red Docker

No exposición innecesaria de la base de datos

Acceso al VPS únicamente por SSH

6️⃣ Gestión de Incidencias (Troubleshooting)
❌ Incidencia 1: Docker Compose no creaba contenedores
Síntomas

docker compose up -d no creaba contenedores

docker ps -a aparecía vacío

Advertencias relacionadas con la red root_default

Causa
Existía una red Docker creada previamente que no había sido gestionada por Docker Compose,
provocando un conflicto de etiquetas.

Solución

docker network rm root_default
docker compose up -d
Resultado
Los contenedores se crearon correctamente.

❌ Incidencia 2: MariaDB en bucle “Restarting”
Causa
Falta de la variable obligatoria MYSQL_ROOT_PASSWORD.

Solución
Definición correcta de variables de entorno en docker-compose.yml y reinicio del stack.

❌ Incidencia 3: Error “No such container” al usar docker exec
Causa
Se utilizaba el nombre del servicio en lugar del nombre real del contenedor.

Solución

docker ps
docker exec -it <nombre_real_contenedor> mysql -u root -p
❌ Incidencia 4: n8n no podía conectarse a MariaDB
Error

Access denied for user 'gira_user'@'172.18.0.4'
Causa
El usuario de base de datos no tenía permisos desde la IP interna del contenedor n8n.

Solución

CREATE USER 'gira_user'@'%' IDENTIFIED BY 'gira_pass';
GRANT ALL PRIVILEGES ON gira_db.* TO 'gira_user'@'%';
FLUSH PRIVILEGES;
Configuración correcta del nodo MySQL en n8n usando el nombre del servicio Docker como host.

7️⃣ Gestión Económica (Fase 3)
💰 Costes reales aproximados
VPS Hostinger: coste mensual reducido

Hosting compartido: bajo coste

Software utilizado: 100% libre

El coste real de la fase ha sido inferior al presupuestado, manteniendo la funcionalidad requerida
para el MVP.

8️⃣ Estado del Proyecto y Repositorio
📌 Estado actual
Fase 3 completada (MVP funcional)

Infraestructura base operativa

Servicios interconectados y verificados

📁 Repositorio GitHub
Incluye:

Este documento

docker-compose.yml

Historial de commits con trazabilidad

Documentación de incidencias reales

