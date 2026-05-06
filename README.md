# 🚀 GiraHub

¡Bienvenido a **GiraHub**! Una plataforma integral gestionada profesionalmente para el Proyecto Final de ASIR. Este repositorio contiene toda la infraestructura de servicios y el frontend de nuestra aplicación centralizada.

## 🛠️ Descripción Técnica

GiraHub está diseñado como una infraestructura moderna contenerizada basada en microservicios, lo que facilita su despliegue, escalabilidad y mantenimiento. Utilizamos **Traefik** como proxy inverso dinámico para gestionar los certificados SSL (Let's Encrypt) y enrutar el tráfico de forma segura hacia nuestros servicios.

## 📦 Servicios Incluidos

El stack tecnológico está compuesto por los siguientes servicios principales desplegados vía Docker:

- **🐳 Docker / Traefik:** Orquestación de contenedores y Proxy Inverso.
- **🗄️ MariaDB:** Base de datos relacional para la persistencia segura de la información.
- **🔐 OpenLDAP:** Gestión centralizada de identidades y autenticación de usuarios.
- **⚙️ n8n:** Automatización avanzada de flujos de trabajo e integración de procesos.
- **📊 Grafana & Prometheus:** Stack completo de monitorización, métricas y dashboards en tiempo real.
- **🌐 Uptime Kuma:** Monitorización del estado y tiempo de actividad de los servicios.

## 🚀 Guía Rápida de Instalación

Sigue estos pasos para desplegar toda la infraestructura en cuestión de minutos:

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/albertotorregrosa/ProyectoFinalASIR.git
   cd ProyectoFinalASIR
   ```

2. **Configurar las variables de entorno:**
   Copia el archivo de ejemplo y configura tus contraseñas y dominios:
   ```bash
   cp .env.example .env
   # Edita el archivo .env con tus credenciales seguras
   ```

3. **Desplegar los servicios:**
   Asegúrate de tener Docker y Docker Compose instalados en tu sistema.
   ```bash
   docker-compose up -d
   ```

4. **Verificar el estado:**
   ```bash
   docker-compose ps
   ```

---
*Mantenido con 💻 por el equipo de GiraHub.*