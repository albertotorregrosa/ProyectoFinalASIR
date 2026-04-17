# 🎓 GIRAHUB – Sistema Institucional de Automatización y Reservas (MVP)

> **GIRAHUB** es un proyecto académico diseñado para centralizar, automatizar y gestionar eficientemente la reserva de aulas y dispositivos en un entorno educativo, apoyado en una infraestructura moderna y escalable.

---

## 🎯 Objetivos del MVP (Fase 3)
El alcance de esta fase se centra en consolidar la base del proyecto:
- **Despliegue de Infraestructura:** Levantar una arquitectura base operativa y segura.
- **Validación de Servicios:** Comprobar la conectividad cruzada entre contenedores y el entorno web.
- **Documentación Técnica:** Registrar el proceso de despliegue, la resolución de incidencias y los manuales de procedimiento.

---

## 🏗️ Arquitectura del Sistema
La infraestructura está dividida de forma modular para garantizar rendimiento y seguridad:

* **🌐 Frontend / Portal Web:** Alojado en un entorno de hosting compartido (punto de acceso e interfaz de usuario).
* **⚙️ Backend / Infraestructura:** Servidor VPS en Hostinger gestionado íntegramente mediante contenedores Docker.
* **🤖 Automatización:** **n8n** como motor de flujos de trabajo (workflows), expuesto de forma segura a través del proxy inverso **Traefik**.
* **🗄️ Base de Datos:** **MariaDB** como motor relacional central (alojando usuarios, reservas, incidencias y recursos).

---

## 🛠️ Stack Tecnológico
- **OS & Hosting:** Ubuntu Server | VPS Hostinger
- **Contenedores:** Docker | Docker Compose
- **Proxy & Redes:** Traefik
- **Base de Datos:** MariaDB
- **Automatización:** n8n
- **Autenticación (Backend):** Servidor LDAP *(En desarrollo)*

---

## 📊 Estado Actual del Proyecto

### ✅ Completado y en funcionamiento:
- [x] Despliegue de VPS (Ubuntu Server) en Hostinger.
- [x] Instalación y configuración del entorno Docker y Docker Compose.
- [x] Creación de red Docker funcional para la comunicación entre contenedores.
- [x] Base de datos MariaDB desplegada y operativa.
- [x] Motor de automatización n8n accesible vía web.

### ⏳ Pendiente (Próximas Fases):
- [ ] Integración completa del portal Web con la Base de Datos y autenticación LDAP.
- [ ] Modelado final de las tablas relacionales.
- [ ] Creación e implementación de los Workflows completos de reservas y alertas en n8n.

---

## 🚀 Despliegue Rápido (Entorno VPS)

Para levantar o administrar los servicios en el servidor, utiliza los siguientes comandos desde el directorio raíz donde se encuentra el archivo `docker-compose.yml`:

```bash
# Iniciar todos los servicios en segundo plano
docker compose up -d

# Verificar el estado de los contenedores activos
docker ps

# Revisar los logs de un servicio específico (ej. mariadb o n8n)
docker logs <nombre_del_servicio>

# Detener y eliminar los contenedores de forma segura
docker compose down