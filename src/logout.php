<?php
require_once __DIR__ . '/Premios_a_la_Excelencia/config/auth.php';
 
cerrarSesion();
 
header('Location: index.php');
exit;