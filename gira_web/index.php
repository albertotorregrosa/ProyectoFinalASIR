<?php
// En este index, dado que está en la raíz, reescribiremos las variables para las rutas CSS si hiciera falta.
// Modificaremos temporalmente el header.php para usar path absolutos y no relativos falsos.
// En producción, si el dominio es midominio.com, será necesario que la ruta de la DB esté limpia.
require_once 'includes/header.php';
?>

<div class="mb-10">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-brand-900 mb-2">Bienvenido de vuelta, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
    <p class="text-brand-900/60 font-medium">Panel de control institucional - GiraHub.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
    <?php if ($rol === 'ADMIN'): ?>
        <!-- Card 1 -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm ring-1 ring-brand-900/5 hover:shadow-xl hover:-translate-y-1 hover:ring-brand-200 transition-all duration-300 group">
            <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-500 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-brand-900 mb-2">Gestión Total</h3>
            <p class="text-brand-900/60 font-medium text-sm leading-relaxed">Administra usuarios, aulas y configuraciones maestras del sistema en tiempo real.</p>
        </div>
        <!-- Card 2 -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm ring-1 ring-brand-900/5 hover:shadow-xl hover:-translate-y-1 hover:ring-brand-200 transition-all duration-300 group">
            <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-500 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-brand-900 mb-2">Monitorización</h3>
            <p class="text-brand-900/60 font-medium text-sm leading-relaxed">Revisa el estado de todas las reservas y las incidencias reportadas en tu centro.</p>
        </div>
    <?php elseif ($rol === 'PROFESOR'): ?>
        <div class="bg-white rounded-[2rem] p-8 shadow-sm ring-1 ring-brand-900/5 hover:shadow-xl transition-all duration-300">
            <h3 class="text-xl font-bold text-brand-900 mb-2">Aulas</h3>
            <p class="text-brand-900/60 font-medium text-sm leading-relaxed">Reserva los espacios que necesitas para impartir tus clases diarias o eventos.</p>
        </div>
        <div class="bg-white rounded-[2rem] p-8 shadow-sm ring-1 ring-brand-900/5 hover:shadow-xl transition-all duration-300">
            <h3 class="text-xl font-bold text-brand-900 mb-2">Dispositivos y Soporte</h3>
            <p class="text-brand-900/60 font-medium text-sm leading-relaxed">Reserva carritos de portátiles y reporta incidencias técnicas al equipo IT.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-[2rem] p-8 shadow-sm ring-1 ring-brand-900/5 hover:shadow-xl transition-all duration-300">
            <h3 class="text-xl font-bold text-brand-900 mb-2">Préstamo de Dispositivos</h3>
            <p class="text-brand-900/60 font-medium text-sm leading-relaxed">Consulta y solicita portátiles o tablets disponibles en el centro de recursos.</p>
        </div>
    <?php endif; ?>
</div>

<div class="bg-white rounded-[2rem] p-8 overflow-hidden shadow-sm ring-1 ring-brand-900/5 mb-8">
    <div class="mb-6 border-b border-brand-50 pb-4">
        <h2 class="text-xl font-bold text-brand-900">Vista General de Reservas</h2>
        <p class="text-brand-900/60 font-medium text-sm mt-1">El calendario interactivo te mostrará tus compromisos y disponibilidad del centro.</p>
    </div>
    
    <div id="calendar" class="fc-theme-girahub text-sm relative z-0"></div>
</div>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                day: 'Día'
            },
            slotMinTime: '08:00:00', // Límite de inicio
            slotMaxTime: '21:00:00', // Límite de fin
            hiddenDays: [0, 6], /* Oculta domingo (0) y sábado (6) */
            allDaySlot: false,
            nowIndicator: true,
            slotDuration: '00:30:00',
            expandRows: true,
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                omitZeroMinute: false,
                meridiem: false
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            dayHeaderFormat: { weekday: 'short', day: 'numeric' },
            height: 'auto',
            events: 'api/eventos.php',
            eventContent: function(arg) {
                let mainEl = document.createElement('div');
                mainEl.classList.add('flex', 'flex-col', 'h-full', 'justify-start', 'overflow-hidden');
                
                let titleEl = document.createElement('div');
                titleEl.classList.add('font-bold', 'text-xs', 'leading-tight', 'truncate');
                titleEl.innerHTML = arg.event.title;
                
                let timeEl = document.createElement('div');
                timeEl.classList.add('text-[10px]', 'opacity-80', 'font-medium', 'mt-0.5');
                timeEl.innerHTML = arg.timeText;

                mainEl.appendChild(titleEl);
                mainEl.appendChild(timeEl);
                
                return { domNodes: [mainEl] };
            }
        });
        calendar.render();
    }
});
</script>

<style>
/* FullCalendar Modern SaaS Overrides */
:root {
    --fc-border-color: #f3f4f6;
    --fc-button-text-color: #4b5563;
    --fc-button-bg-color: #ffffff;
    --fc-button-border-color: #e5e7eb;
    --fc-button-hover-bg-color: #f9fafb;
    --fc-button-hover-border-color: #d1d5db;
    --fc-button-active-bg-color: #f9ceb5;
    --fc-button-active-border-color: #f5a677;
    --fc-button-active-text-color: #7b3e19;
    --fc-event-bg-color: #fef0e7; /* brand-50 */
    --fc-event-border-color: #f9ceb5; /* brand-200 */
    --fc-event-text-color: #7b3e19;
    --fc-today-bg-color: #fff9f5;
    --fc-page-bg-color: #ffffff;
    --fc-neutral-bg-color: #f9fafb;
    --fc-now-indicator-color: #ef4444; /* red-500 */
}

/* Button aesthetics base */
.fc .fc-button {
    text-transform: capitalize;
    font-weight: 600 !important;
    font-family: inherit;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.fc .fc-button-primary:not(:disabled):active, 
.fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: var(--fc-button-active-bg-color) !important;
    border-color: var(--fc-button-active-border-color) !important;
    color: var(--fc-button-active-text-color) !important;
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

/* Title */
.fc .fc-toolbar-title {
    font-weight: 800;
    color: #4a2810; /* text-brand-900 */
    font-size: 1.5rem !important;
    letter-spacing: -0.025em;
    text-transform: capitalize;
}

/* Rounded Buttons */
.fc-direction-ltr .fc-button-group > .fc-button:first-child {
    border-top-left-radius: 9999px;
    border-bottom-left-radius: 9999px;
    padding-left: 1rem;
}
.fc-direction-ltr .fc-button-group > .fc-button:last-child {
    border-top-right-radius: 9999px;
    border-bottom-right-radius: 9999px;
    padding-right: 1rem;
}
.fc .fc-button.fc-today-button {
    border-radius: 9999px;
    padding: 0 1rem;
}

/* Modernize events */
.fc-timegrid-event .fc-event-main, .fc-daygrid-event .fc-event-main {
    padding: 4px 6px;
}
.fc-event {
    border-radius: 0.75rem !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: transform 0.15s ease, box-shadow 0.15s ease, z-index 0s;
    border-width: 1px !important;
    overflow: hidden;
}
.fc-event:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    z-index: 5 !important;
}

/* Grid & headers */
.fc-col-header-cell-cushion {
    padding: 12px 0 !important;
    color: #6b7280;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}
.fc-timegrid-slot-label-cushion {
    color: #9ca3af;
    font-weight: 600;
    font-size: 0.75rem;
}
.fc-scrollgrid {
    border-radius: 1rem;
    overflow: hidden;
    border-style: hidden !important; /* hide outer border */
    box-shadow: 0 0 0 1px var(--fc-border-color);
}
</style>

<?php
require_once 'includes/footer.php';
?>
