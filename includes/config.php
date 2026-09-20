<?php
$baseUrlHost = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
define('BASE_URL', in_array($baseUrlHost, ['localhost', '127.0.0.1'], true) ? '/SistemaApartadosITAP' : '');