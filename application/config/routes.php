<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'pdf';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Rutas para la aplicación PDF
$route['pdf'] = 'pdf/index';
$route['pdf/subir'] = 'pdf/subir';
$route['pdf/procesar'] = 'pdf/procesar';
$route['pdf/ver/(:num)'] = 'pdf/ver/$1';
$route['pdf/descargar/(:num)'] = 'pdf/descargar/$1';