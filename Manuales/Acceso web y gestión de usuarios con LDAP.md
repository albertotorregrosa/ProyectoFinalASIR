# Acceso web y gestión de usuarios y grupos en LDAP

## 1. Objetivo del documento

Este documento describe cómo acceder a la **interfaz web de LDAP** y cómo gestionar usuarios y grupos utilizando **phpLDAPadmin**, sin necesidad de usar la terminal.

Está orientado a usuarios administradores que necesiten:

- Crear usuarios  
- Crear grupos  
- Modificar contraseñas  
- Verificar la estructura del directorio  

---

## 2. Acceso a la interfaz web de LDAP

### 2.1 URL de acceso

El acceso a la administración de LDAP se realiza mediante un navegador web en la siguiente dirección:

```text
https://ldap.girahub.es
```

La conexión está protegida mediante HTTPS gracias a certificados emitidos por Let’s Encrypt.

### 2.2 Credenciales de acceso

Para acceder a phpLDAPadmin se utiliza el **usuario administrador** del directorio LDAP.

Usuario (DN):

```text
cn=admin,dc=girahub,dc=local
```

Contraseña:  
La definida durante la creación del contenedor LDAP.

> ⚠️ No se accede con un usuario normal, sino con el **DN completo del administrador**.

---

## 3. Estructura del directorio LDAP

Una vez autenticado, se muestra el árbol LDAP.  
La estructura utilizada en el proyecto es la siguiente:

```text
dc=girahub,dc=local
 ├── ou=usuarios
 │    └── cn=David Granados
 └── ou=grupos
      └── cn=Profesores
```

**Explicación:**

- `dc=girahub,dc=local` → Dominio raíz del directorio  
- `ou=usuarios` → Unidad organizativa para usuarios  
- `ou=grupos` → Unidad organizativa para grupos  
- `cn=David Granados` → Usuario de ejemplo (profesor)  

Esta estructura es estándar, clara y fácilmente ampliable.

---

## 4. Creación de unidades organizativas (OU)

Las unidades organizativas permiten ordenar el directorio LDAP.

### 4.1 Crear `ou=usuarios`

1. Seleccionar `dc=girahub,dc=local`  
2. Pulsar **⭐ Create a child entry**  
3. Seleccionar **Generic: Organizational Unit**  
4. Introducir:
   - `ou: usuarios`  
5. Confirmar con **Create Object → Commit**  

### 4.2 Crear `ou=grupos`

Repetir los mismos pasos anteriores, cambiando el nombre a:

- `ou: grupos`  

---

## 5. Creación de grupos

### 5.1 Crear el grupo "Profesores"

1. Seleccionar `ou=grupos`  
2. Pulsar **⭐ Create new entry here**  
3. Seleccionar **Generic: Posix Group**  
4. Rellenar los campos:
   - `cn: Profesores`  
   - `gidNumber: 1000`  
5. Confirmar la creación  

Este grupo servirá para asignar permisos a los usuarios profesores.

---

## 6. Creación de usuarios

### 6.1 Usuario de ejemplo: David Granados

1. Seleccionar `ou=usuarios`  
2. Pulsar **⭐ Create new entry here**  
3. Seleccionar **Generic: User Account**  

### 6.2 Campos configurados

- **First name:** `David`  
- **Last name:** `Granados`  
- **Common Name (cn):** `David Granados`  
- **User ID (uid):** `dgranados`  
- **Password:** definida por el administrador  
- **GID Number:** `1000` (grupo *Profesores*)  
- **Login shell:** `/bin/bash`  

Una vez completado, se confirma con **Create Object → Commit**.

---

## 7. Gestión de contraseñas

Las contraseñas:

- Se almacenan cifradas (SSHA)  
- No son visibles en texto plano  
- Pueden modificarse desde la ficha del usuario  

**Cambiar contraseña:**

1. Seleccionar el usuario  
2. Editar el campo `userPassword`  
3. Introducir la nueva contraseña  
4. Guardar cambios  

---

## 8. Verificación de autenticación

Para comprobar que el usuario puede autenticarse correctamente, se utilizó el siguiente comando desde la VPS:

```bash
docker exec -it ldap ldapwhoami -x -H ldap://localhost \
  -D "cn=David Granados,ou=usuarios,dc=girahub,dc=local" \
  -w "dgranados"
```

Resultado esperado:

```text
dn:cn=David Granados,ou=usuarios,dc=girahub,dc=local
```

Esto confirma que:

- El usuario existe  
- La contraseña es correcta  
- El LDAP funciona correctamente  

---

## 9. Buenas prácticas aplicadas

- Separación clara entre usuarios y grupos  
- Uso de `ou` para organización  
- DN completos para autenticación  
- Acceso web seguro por HTTPS  
- Usuario administrador separado de usuarios normales  

---

## 10. Conclusión

La interfaz web **phpLDAPadmin** permite una gestión completa y sencilla del directorio LDAP, facilitando:

- La creación y administración de usuarios  
- La asignación de grupos  
- La verificación de accesos  
- La ampliación futura del sistema  

Este sistema está preparado para integrarse con otras aplicaciones del proyecto, como n8n, garantizando una **autenticación centralizada**.
