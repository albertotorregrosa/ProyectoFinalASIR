
# Creación y configuración del servicio LDAP

## 1. Introducción

LDAP (Lightweight Directory Access Protocol) es un protocolo utilizado para la gestión centralizada de usuarios y credenciales.  
En este proyecto, LDAP se utiliza para:

- Centralizar la autenticación de usuarios (profesores, futuros alumnos, administradores).  
- Evitar la creación de usuarios independientes en cada servicio.  
- Facilitar la integración con otras aplicaciones, como n8n.  

El servicio LDAP se ha desplegado en una VPS utilizando Docker, con acceso web mediante **phpLDAPadmin** y protegido con HTTPS gracias a **Traefik** y **Let’s Encrypt**.

---

## 2. Arquitectura del sistema

La arquitectura utilizada es la siguiente:

- VPS (Servidor virtual privado)  
- Docker y Docker Compose para la orquestación de servicios  
- OpenLDAP como servidor de directorio  
- phpLDAPadmin como interfaz web de administración  
- Traefik como proxy inverso y gestor de certificados SSL  
- Let’s Encrypt para certificados HTTPS  

Esquema lógico:

```text
Usuario → https://ldap.girahub.es → Traefik → phpLDAPadmin → OpenLDAP
```

---

## 3. Preparación del entorno

### 3.1 Requisitos previos

- VPS con sistema Linux  
- Acceso por SSH como usuario administrador  
- Docker y Docker Compose instalados  
- Dominio configurado (`girahub.es`)  

### 3.2 Puertos necesarios

- `80/tcp` → validación Let’s Encrypt  
- `443/tcp` → acceso HTTPS  
- `389/tcp` → LDAP interno  

---

## 4. Configuración del entorno Docker

### 4.1 Archivo `.env`

Se creó un archivo `.env` para centralizar la configuración:

```env
DOMAIN_NAME=girahub.es
SUBDOMAIN=n8n
GENERIC_TIMEZONE=Europe/Madrid
SSL_EMAIL=correo@girahub.es
```

Este archivo es utilizado por Docker Compose y Traefik.

---

## 5. Creación del archivo `docker-compose.yml`

Se definieron los siguientes servicios:

### 5.1 Servicio OpenLDAP

- Imagen: `osixia/openldap`  
- Función: servidor LDAP  
- Volúmenes persistentes para datos y configuración  
- Variables destacadas:
  - Dominio LDAP  
  - Usuario administrador  
  - Contraseña de administración  

### 5.2 Servicio phpLDAPadmin

- Imagen: `osixia/phpldapadmin`  
- Función: interfaz web de administración de LDAP  
- Acceso únicamente a través de Traefik (HTTPS)  

### 5.3 Servicio Traefik

- Proxy inverso  
- Gestión automática de certificados SSL con Let’s Encrypt  
- Enrutamiento por subdominios  

Ejemplo de regla:

```text
ldap.girahub.es → phpLDAPadmin
```

---

## 6. Configuración de DNS y HTTPS

### 6.1 Configuración DNS

Se añadió el siguiente registro DNS:

- Tipo: `A`  
- Nombre: `ldap`  
- Dominio: `girahub.es`  
- IP: IP de la VPS  

Resultado:

```text
https://ldap.girahub.es
```

### 6.2 Certificados SSL

Traefik gestiona automáticamente los certificados mediante Let’s Encrypt.  
La correcta emisión se comprobó con:

```bash
curl -I https://ldap.girahub.es
```

Resultado esperado:

```text
HTTP/2 200
```

---

## 7. Arranque de los servicios

Una vez configurado todo, se arrancaron los contenedores:

```bash
docker compose up -d
```

Comprobación de contenedores activos:

```bash
docker ps
```

Todos los servicios se encontraban en estado `Running`.

---

## 8. Acceso a la interfaz LDAP

La administración se realiza desde el navegador en:

```text
https://ldap.girahub.es
```

Credenciales de acceso:

- DN de administrador:  
  ```text
  cn=admin,dc=girahub,dc=local
  ```
- Contraseña: definida durante la instalación  

---

## 9. Verificación técnica del servicio LDAP

### 9.1 Verificación de autenticación

Se comprobó el correcto funcionamiento del LDAP mediante:

```bash
docker exec -it ldap ldapwhoami -x -H ldap://localhost \
  -D "cn=admin,dc=girahub,dc=local" -w "PASSWORD"
```

Resultado esperado:

```text
dn: cn=admin,dc=girahub,dc=local
```

### 9.2 Verificación de estructura LDAP

```bash
docker exec -it ldap ldapsearch -x -H ldap://localhost \
  -b "dc=girahub,dc=local"
```

Esto confirmó que el directorio estaba operativo y accesible.

---

## 10. Conclusión

El servicio LDAP ha sido desplegado correctamente cumpliendo los siguientes objetivos:

- Instalación segura mediante HTTPS  
- Gestión centralizada de usuarios  
- Persistencia de datos  
- Acceso web para administración  
- Preparado para integración con n8n  

Este servicio constituye la base del sistema de autenticación del proyecto.
