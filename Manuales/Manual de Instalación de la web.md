# Manual (solo web): creación, modificación e integración con n8n/MariaDB

## 1. Qué se ha creado

Se ha creado un **frontend** alojado en **Hostinger (hosting compartido)** para el dominio `girahub.es`.  
Este frontend servirá páginas HTML/CSS/JS y enviará solicitudes al backend (n8n). La web **no accede directamente** a la base de datos.

---

## 2. Cómo se ha creado en Hostinger (pasos realizados)

### 2.1 Acceso al panel correcto

Hostinger → **Sitios web** → `girahub.es`

### 2.2 Elección de panel de gestión

Se seleccionó **cPanel** (no WordPress) para poder trabajar con archivos reales del sitio.

### 2.3 Migración del sitio a hosting web clásico

Se realizó la migración a **cPanel y WHM** para habilitar:

- Administrador de archivos  
- Carpeta `public_html`  
- Edición de `index.html`  

---

## 3. Dónde están los archivos de la web

En cPanel:

- **File Manager / Administrador de archivos**  
- Ruta:

```text
public_html/
```

---

## 4. Cómo se modifica la web

### 4.1 Editar `index.html`

cPanel → File Manager → `public_html` → `index.html` → Editar → Guardar.

### 4.2 Organización recomendada de archivos

Dentro de `public_html`:

```text
public_html/
├── index.html
├── css/
│   └── styles.css
└── js/
    └── app.js
```

- `index.html`: estructura y formulario  
- `css/styles.css`: estilos  
- `js/app.js`: lógica (envío al backend)  

---

## 5. Cómo se conecta la web con el sistema (n8n + MariaDB)

### 5.1 Modelo de conexión (sin acceso directo a la BD)

La web se conecta a n8n mediante HTTP, y n8n es quien escribe/lee en MariaDB:

```text
Web (Hostinger) → Webhook (n8n en VPS) → MariaDB (VPS)
```

**Motivo:** seguridad y separación de capas (la BD no se expone a internet).

### 5.2 Qué necesita la web para "conectar"

1. Un formulario en `index.html` con los campos del MVP (los mismos que usa el workflow):
   - `aula`  
   - `dispositivo`  
   - `fecha`  
   - `hora_inicio`  
   - `hora_fin`  
   - `curso`  

2. Un script (`app.js`) que haga un `POST` en JSON al webhook de n8n, por ejemplo:

```text
https://SUBDOMINIO.DOMINIO/webhook/crear-reserva
```

### 5.3 Envío típico desde la web (descripción)

- El usuario rellena el formulario.  
- JavaScript captura el submit.  
- Se envía un JSON al webhook de n8n.  
- n8n valida e inserta en MariaDB.  
- n8n devuelve una respuesta (ok/error) y la web la muestra.  

---

## 6. Prueba mínima para verificar que "la web está conectada"

- La web carga desde `girahub.es`.  
- Al enviar el formulario, el workflow de n8n se ejecuta.  
- En MariaDB aparece una fila nueva en la tabla `reservas`.  

---

## 7. Qué se puede ampliar después (sin cambiar el hosting)

- Añadir páginas (disponibilidad, listado de reservas)  
- Mostrar respuestas en pantalla (mensajes de éxito/error)  
- Añadir autenticación y roles (admin/profesor)  
- Consumir webhooks de "listar reservas" (GET) para pintar tablas en la web 