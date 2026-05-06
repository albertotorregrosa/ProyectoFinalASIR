<?php
session_start();

// Redirección con ruta absoluta
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /login.php"); 
    exit;
}

$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiraHub - Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#FDF6F0',   /* Crema suave fondo oscuro */
                            100: '#FCE7D8',  /* Peach claro para botones/arcos */
                            200: '#F8DAC6',
                            500: '#E89060',  /* Naranja / Accent */
                            800: '#8A4831',
                            900: '#4F2516',  /* Letra texto */
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <!-- Mantenemos el css base para compatibilidad -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-brand-50 text-brand-900 font-sans antialiased selection:bg-brand-500 selection:text-white" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-100 transition-transform duration-300 ease-in-out md:static md:translate-x-0 flex flex-col shadow-sm rounded-r-3xl">
        <div class="flex items-center justify-between h-20 px-8 border-b-2 border-brand-50">
            <a href="/index.php" class="text-2xl font-extrabold tracking-tight text-brand-900">GiraHub<span class="text-brand-500">.</span></a>
            <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-brand-500">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <?php 
                $current_page = basename($_SERVER['PHP_SELF']);
                $current_dir = basename(dirname($_SERVER['PHP_SELF']));
            ?>
            <a href="/index.php" class="<?php echo ($current_page == 'index.php' && $current_dir == 'public_html') ? 'bg-brand-100 text-brand-900' : 'text-gray-500 hover:bg-brand-50 hover:text-brand-900'; ?> group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all">
                <svg class="<?php echo ($current_page == 'index.php' && $current_dir == 'public_html') ? 'text-brand-500' : 'text-gray-400 group-hover:text-brand-500'; ?> flex-shrink-0 -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Inicio
            </a>
            
            <?php if ($rol === 'ADMIN'): ?>
                <a href="/aulas/index.php" class="<?php echo ($current_dir == 'aulas') ? 'bg-brand-100 text-brand-900' : 'text-gray-500 hover:bg-brand-50 hover:text-brand-900'; ?> group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all">
                    <svg class="<?php echo ($current_dir == 'aulas') ? 'text-brand-500' : 'text-gray-400 group-hover:text-brand-500'; ?> flex-shrink-0 -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Aulas
                </a>
                <a href="/dispositivos/index.php" class="<?php echo ($current_dir == 'dispositivos') ? 'bg-brand-100 text-brand-900' : 'text-gray-500 hover:bg-brand-50 hover:text-brand-900'; ?> group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all">
                    <svg class="<?php echo ($current_dir == 'dispositivos') ? 'text-brand-500' : 'text-gray-400 group-hover:text-brand-500'; ?> flex-shrink-0 -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Dispositivos
                </a>
                <a href="/usuarios/index.php" class="<?php echo ($current_dir == 'usuarios') ? 'bg-brand-100 text-brand-900' : 'text-gray-500 hover:bg-brand-50 hover:text-brand-900'; ?> group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all">
                    <svg class="<?php echo ($current_dir == 'usuarios') ? 'text-brand-500' : 'text-gray-400 group-hover:text-brand-500'; ?> flex-shrink-0 -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Usuarios
                </a>
                <a href="/incidencias/index.php" class="<?php echo ($current_dir == 'incidencias') ? 'bg-brand-100 text-brand-900' : 'text-gray-500 hover:bg-brand-50 hover:text-brand-900'; ?> group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all">
                    <svg class="<?php echo ($current_dir == 'incidencias') ? 'text-brand-500' : 'text-gray-400 group-hover:text-brand-500'; ?> flex-shrink-0 -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Incidencias
                </a>
            <?php elseif ($rol === 'PROFESOR'): ?>
                <a href="/aulas/reservar.php" class="text-gray-500 hover:bg-brand-50 hover:text-brand-900 group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all">Reservar Aula</a>
                <a href="/dispositivos/reservar.php" class="text-gray-500 hover:bg-brand-50 hover:text-brand-900 group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all">Reservar Dispositivo</a>
                <a href="/incidencias/reportar.php" class="text-gray-500 hover:bg-brand-50 hover:text-brand-900 group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all">Reportar Incidencia</a>
            <?php elseif ($rol === 'ALUMNO'): ?>
                <a href="/dispositivos/reservar.php" class="text-gray-500 hover:bg-brand-50 hover:text-brand-900 group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all">Dispositivos</a>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- Mobile overlay -->
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-brand-900 bg-opacity-30 backdrop-blur-sm md:hidden" @click="sidebarOpen = false" x-cloak></div>

    <!-- Main Content wrapper -->
    <div class="flex-1 flex flex-col w-0 overflow-hidden bg-brand-50">
        
        <header class="relative z-10 flex-shrink-0 h-20 bg-transparent flex">
            <button @click="sidebarOpen = true" class="px-6 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-500 md:hidden hover:text-brand-500">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
            </button>
            <div class="flex-1 px-8 flex justify-between">
                <div class="flex-1 flex items-center">
                    <span class="text-brand-900/40 text-sm font-bold uppercase tracking-widest hidden sm:block">Panel de Administración</span>
                </div>
                <div class="flex items-center gap-6">
                    <div class="hidden sm:flex flex-col items-end">
                        <span class="text-sm font-bold text-brand-900 leading-tight"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></span>
                        <span class="text-xs text-brand-500 font-medium leading-tight mt-0.5"><?php echo htmlspecialchars($rol); ?></span>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-brand-100 text-brand-800 flex items-center justify-center font-bold text-sm shadow-sm ring-4 ring-white">
                        <?php echo strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 1)); ?>
                    </div>
                    
                    <a href="/logout.php" class="text-brand-900/30 hover:text-brand-800 transition-colors p-2 bg-white rounded-full shadow-sm" title="Cerrar Sesión">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1 relative overflow-y-auto focus:outline-none p-4 sm:p-6 lg:p-8">