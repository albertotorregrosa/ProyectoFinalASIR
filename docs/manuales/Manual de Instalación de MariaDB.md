# Manual de Creación y Configuración del Servidor de Base de Datos (MariaDB)

## 1. Objetivo del servidor de base de datos

El objetivo de este servidor es proporcionar un sistema de almacenamiento persistente y centralizado para la gestión de reservas de aulas y dispositivos del instituto.

La base de datos se despliega en un VPS mediante contenedores Docker y es consumida por el sistema de automatización (n8n).

---

## 2. Preparación del entorno

### 2.1 Servidor utilizado

- Tipo: VPS  
- Proveedor: Hostinger  
- Sistema operativo: Ubuntu Server  
- Acceso: Usuario `root` mediante SSH o consola web  

### 2.2 Tecnologías utilizadas

- Docker  
- Docker Compose  
- MariaDB  

---

## 3. Instalación de Docker y Docker Compose

Actualización del sistema:

```bash
apt update && apt upgrade -y
```

Instalación de Docker:

```bash
curl -fsSL https://get.docker.com | sh
```

Instalación de Docker Compose (plugin):

```bash
apt install -y docker-compose-plugin
```

Comprobación de la instalación:

```bash
docker --version
docker compose version
```

---

## 4. Creación del servicio MariaDB con Docker Compose

### 4.1 Estructura del proyecto

Se crea un directorio para el despliegue:

```bash
mkdir /opt/girahub
cd /opt/girahub
```

### 4.2 Definición del servicio MariaDB

Dentro del archivo `docker-compose.yml` se define el servicio de base de datos:

```yaml
services:
  mariadb:
    image: mariadb:10.11
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: contraseña_root
      MYSQL_DATABASE: gira_db
      MYSQL_USER: gira_user
      MYSQL_PASSWORD: contraseña_user
    ports:
      - "3306:3306"
    volumes:
      - /docker/mariadb:/var/lib/mysql
```

Este servicio:

- Crea automáticamente la base de datos `gira_db`  
- Crea el usuario `gira_user`  
- Garantiza persistencia mediante volúmenes Docker  

---

## 5. Puesta en marcha del servidor de base de datos

Arranque del servicio:

```bash
docker compose up -d
```

Comprobación del estado:

```bash
docker ps
```

Se verifica que el contenedor de MariaDB se encuentra en estado `Running`.

---

## 6. Acceso a la base de datos

Acceso al contenedor:

```bash
docker exec -it root-mariaDB-1 mysql -u root -p
```

Una vez dentro, se comprueba la existencia de la base de datos:

```sql
SHOW DATABASES;
```

---

## 7. Creación y adaptación de la estructura de la base de datos

### 7.1 Tabla de reservas

Se utiliza una tabla `reservas` adaptada a la lógica del MVP y a los workflows de n8n.

Estructura final de la tabla:

```sql
CREATE TABLE reservas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  aula VARCHAR(50) NOT NULL,
  dispositivo VARCHAR(50) NOT NULL,
  fecha DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  curso VARCHAR(50) NOT NULL
);
```

Esta estructura permite:

- Registrar reservas de aulas y dispositivos  
- Gestionar fechas y franjas horarias  
- Asociar reservas a grupos o cursos  

---

## 8. Gestión de usuarios y permisos

Para permitir el acceso desde otros contenedores Docker (n8n), se configura el usuario con permisos desde cualquier host:

```sql
CREATE USER 'gira_user'@'%' IDENTIFIED BY 'contraseña_user';
GRANT ALL PRIVILEGES ON gira_db.* TO 'gira_user'@'%';
FLUSH PRIVILEGES;
```

Esto permite la comunicación correcta entre servicios dentro de la red Docker.

---

## 9. Verificación de funcionamiento

Inserción de prueba:

```sql
INSERT INTO reservas
(aula, dispositivo, fecha, hora_inicio, hora_fin, curso)
VALUES
('Aula 101', 'Ordenador 1', '2025-01-10', '10:00', '11:00', '2 ASIR');
```

Consulta de datos:

```sql
SELECT * FROM reservas;
```

La correcta inserción y consulta confirma que:

- MariaDB funciona correctamente  
- La estructura de datos es válida  
- El servidor de base de datos está listo para ser consumido por n8n  

---

## 10. Resultado final

Al finalizar estos pasos se dispone de:

- Un servidor MariaDB desplegado en un VPS  
- Base de datos persistente y operativa  
- Estructura adaptada al sistema de reservas  
- Usuario configurado para acceso desde otros servicios  
- Infraestructura lista para integrarse con n8n  
```
