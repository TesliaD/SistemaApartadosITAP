<?php
// ============================================================
// BASE_URL: ruta web de la carpeta del proyecto.
//   - Local (XAMPP):  /SistemaApartadosITAP
//   - Hosting (raíz): ""   (vacío)
//   - Hosting en subcarpeta: /subcarpeta
//
// Se calcula comparando la carpeta del proyecto con el
// DOCUMENT_ROOT del servidor, así funciona sin cambios en
// cualquier lugar donde se suba el sistema.
// Uso en vistas: href="{BASE_URL}/css/archivo.css" (con echo BASE_URL)
// ============================================================

if (!defined('BASE_URL')) {
    $raizProyecto = realpath(__DIR__ . '/..');
    $raizServidor = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');

    $baseUrl = null;

    if ($raizProyecto && $raizServidor) {
        $raizProyecto = str_replace('\\', '/', $raizProyecto);
        $raizServidor = rtrim(str_replace('\\', '/', $raizServidor), '/');

        // Comparación sin distinguir mayúsculas (Windows) en el prefijo
        if (stripos($raizProyecto, $raizServidor) === 0) {
            $baseUrl = rtrim(substr($raizProyecto, strlen($raizServidor)), '/');
        }
    }

    // Respaldo: si no se pudo calcular, se decide por el dominio
    if ($baseUrl === null) {
        $baseUrlHost = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
        $baseUrl = in_array($baseUrlHost, ['localhost', '127.0.0.1'], true) ? '/SistemaApartadosITAP' : '';
    }

    define('BASE_URL', $baseUrl);
}
