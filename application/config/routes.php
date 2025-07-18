<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'pdf';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Rutas específicas para PDF (orden importante)
$route['pdf/eliminar/(:num)'] = 'pdf/eliminar/$1';
$route['pdf/ver/(:num)'] = 'pdf/ver/$1';
$route['pdf/debug_descarga/(:num)'] = 'pdf/debug_descarga/$1';
$route['pdf/descargar_directo/(:num)'] = 'pdf/descargar_directo/$1';
$route['pdf/procesar'] = 'pdf/procesar';
$route['pdf/subir'] = 'pdf/subir';
$route['pdf'] = 'pdf/index';