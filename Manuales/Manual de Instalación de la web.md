
# Manual de Creación y Configuración de la Web del Proyecto

## 1. Objetivo de la web

El objetivo de la web es actuar como **interfaz de usuario** del sistema de reservas del instituto.  
Desde la web los usuarios podrán:

- Consultar disponibilidad de aulas y dispositivos  
- Realizar solicitudes de reserva  
- Visualizar horarios y reservas existentes  

La web no contiene la lógica de negocio, sino que actúa como **frontend**, comunicándose con el backend (n8n) mediante peticiones HTTP a webhooks.

---

## 2. Arquitectura de la solución web

La arquitectura sigue un modelo distribuido:

- Frontend: Web alojada en hosting compartido (Hostinger)  
- Backend: n8n desplegado en un VPS  
- Base de datos: MariaDB en el mismo VPS  

Flujo de funcionamiento:

1. El usuario interactúa con la web  
2. La web envía peticiones HTTP a n8n  
3. n8n procesa la lógica y accede a MariaDB  
4. n8n devuelve la respuesta a la web  

---

## 3. Plataforma de alojamiento

### 3.1 Hosting utilizado

- Proveedor: Hostinger  
- Tipo: Hosting web compartido  
- Acceso mediante panel de control (hPanel)  

Este tipo de hosting es suficiente para:

- Servir contenido web  
- Gestionar formularios  
- Realizar peticiones HTTP al backend  

---

## 4. Creación de la estructura web

### 4.1 Estructura básica de archivos

La web se organiza con una estructura simple:

```text
/public_html
├── index.html
├── css/
│   └── styles.css
└── js/
    └── app.js
```

Esta estructura permite separar:

- Contenido (HTML)  
- Estilos (CSS)  
- Lógica de cliente (JavaScript)  

---

## 5. Página principal (`index.html`)

La página principal incluye:

- Presentación del proyecto  
- Formulario de creación de reservas  
- Zona de visualización de resultados  

Ejemplo de formulario:

```html
<form id="reservaForm">
  <input type="text" name="aula" placeholder="Aula" required>
  <input type="text" name="dispositivo" placeholder="Dispositivo" required>
  <input type="date" name="fecha" required>
  <input type="time" name="hora_inicio" required>
  <input type="time" name="hora_fin" required>
  <input type="text" name="curso" placeholder="Curso" required>
  <button type="submit">Reservar</button>
</form>
```

---

## 6. Comunicación con el backend (n8n)

### 6.1 Uso de webhooks

La web se comunica con n8n mediante peticiones HTTP a webhooks expuestos por el backend.

Ejemplo de endpoint:

```text
https://subdominio.dominio/webhook/crear-reserva
```

### 6.2 Lógica JavaScript

El archivo `app.js` gestiona el envío de datos del formulario:

```javascript
document.getElementById('reservaForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const data = {
    aula: e.target.aula.value,
    dispositivo: e.target.dispositivo.value,
    fecha: e.target.fecha.value,
    hora_inicio: e.target.hora_inicio.value,
    hora_fin: e.target.hora_fin.value,
    curso: e.target.curso.value,
  };

  fetch('https://subdominio.dominio/webhook/crear-reserva', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      console.log('Reserva creada', result);
      // Aquí se podría actualizar la interfaz o mostrar un mensaje al usuario
    })
    .catch((error) => {
      console.error('Error al crear la reserva', error);
    });
});
```

Esta lógica:

- Recoge los datos del formulario  
- Los envía al webhook de n8n  
- Recibe la respuesta del backend  

---

## 7. Seguridad básica de la web

Las medidas aplicadas incluyen:

- Uso de HTTPS para todas las comunicaciones  
- No exposición directa de la base de datos  
- Separación entre frontend y backend  
- Validación de datos en el backend (n8n)  

---

## 8. Pruebas de funcionamiento

Se realizan las siguientes comprobaciones:

- Acceso correcto a la web desde navegador  
- Envío de formularios sin errores  
- Recepción de datos en n8n  
- Inserción de datos en MariaDB  
- Respuesta correcta del webhook  

Estas pruebas validan la correcta integración entre web, backend y base de datos.

---

## 9. Resultado final

Al finalizar estos pasos se dispone de:

- Web funcional alojada en Hostinger  
- Interfaz de usuario para el sistema de reservas  
- Comunicación correcta con n8n mediante webhooks  
- Integración completa con la base de datos MariaDB  
- Arquitectura distribuida operativa y escalable  

La web queda preparada para futuras mejoras como autenticación de usuarios, gestión de roles y visualización avanzada de horarios.
