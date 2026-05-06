<?php
session_start();
session_unset();
session_destroy();

// Redirigir siempre usando una ruta que funcione desde donde estemos
header("Location: login.php");
exit;
?>
