<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pdf extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pdf_model');
        $this->load->library('SimplePdfExtractor');
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function index()
    {
        $data['pdfs'] = $this->Pdf_model->obtener_pdfs();
        $data['total_pdfs'] = $this->Pdf_model->contar_pdfs();
        
        $this->load->view('pdf/index', $data);
    }

    // NUEVA FUNCIÓN: Búsqueda por códigos
    public function buscar()
    {
        $this->load->view('pdf/buscar');
    }

    // NUEVA FUNCIÓN: Procesar búsqueda por códigos
    // NUEVA FUNCIÓN: Procesar búsqueda por códigos
   // FUNCIÓN MODIFICADA: Procesar búsqueda por códigos
    public function procesar_busqueda()
    {
        $this->output->set_content_type('application/json');

        try {
            // Obtener datos del formulario
            $codigo_entrada = $this->input->post('codigo_entrada');
            $numero_entrada = $this->input->post('numero_entrada');
            $digito_verificador = $this->input->post('digito_verificador');
            $ano = $this->input->post('ano');

            // Validar que todos los campos estén presentes
            if (empty($codigo_entrada) || empty($numero_entrada) || empty($digito_verificador) || empty($ano)) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Todos los campos son obligatorios'
                ]));
                return;
            }

            // Construir número concatenado
            $numero_completo = $codigo_entrada . $numero_entrada . $digito_verificador . $ano;

            // Definir path de búsqueda
            $path_busqueda = 'C:/Users/lp2165/Desktop/pdfbuscar/';
            $nombre_archivo = $numero_completo . '.pdf';
            $ruta_completa = $path_busqueda . $nombre_archivo;

            // Verificar si el archivo existe
            if (file_exists($ruta_completa)) {
                // Archivo encontrado - Extraer texto del PDF
                try {
                    $contenido_texto = $this->simplepdfextractor->extract_text($ruta_completa);
                    
                    $this->output->set_output(json_encode([
                        'success' => true,
                        'found' => true,
                        'message' => 'Documento encontrado. Ingrese datos para validar.',
                        'numero_completo' => $numero_completo,
                        'archivo' => $nombre_archivo,
                        'ruta' => $ruta_completa,
                        'codigo_entrada' => $codigo_entrada,
                        'contenido_texto' => $contenido_texto
                    ]));
                } catch (Exception $e) {
                    log_message('error', 'Error extrayendo texto del PDF: ' . $e->getMessage());
                    $this->output->set_output(json_encode([
                        'success' => true,
                        'found' => true,
                        'message' => 'Documento encontrado pero no se pudo extraer el texto.',
                        'numero_completo' => $numero_completo,
                        'archivo' => $nombre_archivo,
                        'ruta' => $ruta_completa,
                        'codigo_entrada' => $codigo_entrada,
                        'contenido_texto' => ''
                    ]));
                }
            } else {
                // Archivo no encontrado, solicitar búsqueda alternativa
                $this->output->set_output(json_encode([
                    'success' => true,
                    'found' => false,
                    'message' => 'Documento no encontrado. Intente búsqueda alternativa.',
                    'numero_completo' => $numero_completo
                ]));
            }

        } catch (Exception $e) {
            log_message('error', 'Error en búsqueda por códigos: ' . $e->getMessage());
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]));
        }
    }

    // NUEVA FUNCIÓN: Validar datos contra el contenido del PDF
    // FUNCIÓN CORREGIDA: Validar datos contra el contenido del PDF
    // NUEVA FUNCIÓN: Normalizar texto para mejorar coincidencias
    private function normalizar_texto($texto)
    {
        // Convertir a minúsculas
        $texto = strtolower($texto);
        
        // Remover acentos y caracteres especiales
        $caracteresEspeciales = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ā' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'ē' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'ī' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'ō' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'ū' => 'u',
            'ñ' => 'n',
            'ç' => 'c',
            'ý' => 'y', 'ÿ' => 'y'
        ];
        
        $texto = str_replace(array_keys($caracteresEspeciales), array_values($caracteresEspeciales), $texto);
        
        // Remover espacios múltiples
        $texto = preg_replace('/\s+/', ' ', $texto);
        
        // Remover caracteres especiales excepto letras, números y espacios
        $texto = preg_replace('/[^a-z0-9\s]/', '', $texto);
        
        return trim($texto);
    }

    // NUEVA FUNCIÓN: Buscar texto con diferentes estrategias
    private function buscar_texto_flexible($buscar, $contenido)
    {
        // Estrategia 1: Búsqueda exacta
        if (strpos($contenido, $buscar) !== false) {
            return ['encontrado' => true, 'metodo' => 'exacta'];
        }
        
        // Estrategia 2: Búsqueda normalizada
        $buscar_norm = $this->normalizar_texto($buscar);
        $contenido_norm = $this->normalizar_texto($contenido);
        
        if (strpos($contenido_norm, $buscar_norm) !== false) {
            return ['encontrado' => true, 'metodo' => 'normalizada'];
        }
        
        // Estrategia 3: Búsqueda por palabras individuales
        $palabras = explode(' ', $buscar_norm);
        $palabras_encontradas = 0;
        
        foreach ($palabras as $palabra) {
            if (strlen($palabra) > 2 && strpos($contenido_norm, $palabra) !== false) {
                $palabras_encontradas++;
            }
        }
        
        // Si encuentra al menos 70% de las palabras
        $porcentaje = count($palabras) > 0 ? ($palabras_encontradas / count($palabras)) : 0;
        if ($porcentaje >= 0.7) {
            return ['encontrado' => true, 'metodo' => 'parcial', 'porcentaje' => $porcentaje];
        }
        
        // Estrategia 4: Búsqueda con similitud (usando similar_text)
        $similitud = 0;
        similar_text($buscar_norm, $contenido_norm, $similitud);
        
        if ($similitud > 60) { // 60% de similitud
            return ['encontrado' => true, 'metodo' => 'similitud', 'porcentaje' => $similitud];
        }
        
        return ['encontrado' => false, 'metodo' => 'ninguna'];
    }

    // FUNCIÓN ACTUALIZADA: Validar datos contra el contenido del PDF
    public function validar_pdf()
    {
        $this->output->set_content_type('application/json');

        try {
            // Obtener datos del formulario
            $numero_completo = $this->input->post('numero_completo');
            $nombre = $this->input->post('nombre');
            $apellido = $this->input->post('apellido');
            $dni = $this->input->post('dni');
            $cuit = $this->input->post('cuit');
            $razon_social_parte1 = $this->input->post('razon_social_parte1');
            $razon_social_parte2 = $this->input->post('razon_social_parte2');
            $contenido_texto = $this->input->post('contenido_texto');
            $codigo_entrada = $this->input->post('codigo_entrada');

            log_message('debug', 'Validando PDF: ' . $numero_completo);

            if (empty($numero_completo)) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Número completo es requerido'
                ]));
                return;
            }

            // Si no hay contenido del texto, intentar extraerlo nuevamente
            if (empty($contenido_texto)) {
                $path_busqueda = 'C:/Users/lp2165/Desktop/pdfbuscar/';
                $nombre_archivo = $numero_completo . '.pdf';
                $ruta_completa = $path_busqueda . $nombre_archivo;
                
                if (file_exists($ruta_completa)) {
                    try {
                        $contenido_texto = $this->simplepdfextractor->extract_text($ruta_completa);
                        log_message('debug', 'Texto extraído nuevamente');
                    } catch (Exception $e) {
                        log_message('error', 'Error extrayendo texto: ' . $e->getMessage());
                        $contenido_texto = '';
                    }
                }
            }

            if (empty($contenido_texto)) {
                $this->output->set_output(json_encode([
                    'success' => true,
                    'estado' => 'erroneo',
                    'errores' => ['contenido_vacio'],
                    'validaciones_exitosas' => [],
                    'observaciones' => 'No se pudo extraer contenido del PDF',
                    'message' => 'No se pudo analizar el contenido del PDF'
                ]));
                return;
            }

            $errores = [];
            $validaciones_exitosas = [];

            // Determinar tipo de validación según CODIGO_MOVIMI
            if ($codigo_entrada == '01' || $codigo_entrada == '98') {
                
                // Validar apellido con búsqueda flexible
                if (!empty($apellido)) {
                    $resultado = $this->buscar_texto_flexible($apellido, $contenido_texto);
                    
                    if ($resultado['encontrado']) {
                        $metodo = $resultado['metodo'];
                        $porcentaje = isset($resultado['porcentaje']) ? ' (' . round($resultado['porcentaje'], 1) . '%)' : '';
                        $validaciones_exitosas[] = "Apellido '{$apellido}' encontrado (método: {$metodo}{$porcentaje})";
                        log_message('debug', "Apellido encontrado por método: {$metodo}");
                    } else {
                        $errores[] = "apellido no coincidente";
                        log_message('debug', 'Apellido NO encontrado con ningún método');
                        
                        // Log adicional para debug
                        log_message('debug', 'Apellido buscado: ' . $apellido);
                        log_message('debug', 'Apellido normalizado: ' . $this->normalizar_texto($apellido));
                        log_message('debug', 'Primeras 200 chars del contenido: ' . substr($contenido_texto, 0, 200));
                    }
                }

                // Validar nombre con búsqueda flexible
                if (!empty($nombre)) {
                    $resultado = $this->buscar_texto_flexible($nombre, $contenido_texto);
                    
                    if ($resultado['encontrado']) {
                        $metodo = $resultado['metodo'];
                        $porcentaje = isset($resultado['porcentaje']) ? ' (' . round($resultado['porcentaje'], 1) . '%)' : '';
                        $validaciones_exitosas[] = "Nombre '{$nombre}' encontrado (método: {$metodo}{$porcentaje})";
                        log_message('debug', "Nombre encontrado por método: {$metodo}");
                    } else {
                        $errores[] = "nombre no coincidente";
                        log_message('debug', 'Nombre NO encontrado con ningún método');
                    }
                }

                // Validar DNI (este se mantiene igual porque son solo números)
                if (!empty($dni)) {
                    $dni_clean = preg_replace('/[^0-9]/', '', $dni);
                    
                    if (strpos($contenido_texto, $dni_clean) !== false) {
                        $validaciones_exitosas[] = "DNI '{$dni}' encontrado en el documento";
                        log_message('debug', 'DNI encontrado');
                    } else {
                        $errores[] = "documento no coincidente";
                        log_message('debug', 'DNI NO encontrado');
                    }
                }

                // Validar CUIT
                if (!empty($cuit)) {
                    $cuit_clean = preg_replace('/[^0-9]/', '', $cuit);
                    
                    if (strpos($contenido_texto, $cuit_clean) !== false) {
                        $validaciones_exitosas[] = "CUIT '{$cuit}' encontrado en el documento";
                        log_message('debug', 'CUIT encontrado');
                    } else {
                        $errores[] = "cuit no coincidente";
                        log_message('debug', 'CUIT NO encontrado');
                    }
                }

            } elseif ($codigo_entrada == '02') {
                
                // Validar razón social con búsqueda flexible
                if (!empty($razon_social_parte1) || !empty($razon_social_parte2)) {
                    $razon_social_completa = trim($razon_social_parte1 . ' ' . $razon_social_parte2);
                    $resultado = $this->buscar_texto_flexible($razon_social_completa, $contenido_texto);
                    
                    if ($resultado['encontrado']) {
                        $metodo = $resultado['metodo'];
                        $porcentaje = isset($resultado['porcentaje']) ? ' (' . round($resultado['porcentaje'], 1) . '%)' : '';
                        $validaciones_exitosas[] = "Razón social '{$razon_social_completa}' encontrada (método: {$metodo}{$porcentaje})";
                        log_message('debug', "Razón social encontrada por método: {$metodo}");
                    } else {
                        $errores[] = "razón social no coincidente";
                        log_message('debug', 'Razón social NO encontrada');
                    }
                }

                // Validar CUIT
                if (!empty($cuit)) {
                    $cuit_clean = preg_replace('/[^0-9]/', '', $cuit);
                    
                    if (strpos($contenido_texto, $cuit_clean) !== false) {
                        $validaciones_exitosas[] = "CUIT '{$cuit}' encontrado en el documento";
                        log_message('debug', 'CUIT encontrado');
                    } else {
                        $errores[] = "cuit no coincidente";
                        log_message('debug', 'CUIT NO encontrado');
                    }
                }
            }

            // Verificar que se haya ingresado al menos un dato
            $datos_ingresados = 0;
            if (!empty($nombre)) $datos_ingresados++;
            if (!empty($apellido)) $datos_ingresados++;
            if (!empty($dni)) $datos_ingresados++;
            if (!empty($cuit)) $datos_ingresados++;
            if (!empty($razon_social_parte1)) $datos_ingresados++;
            if (!empty($razon_social_parte2)) $datos_ingresados++;

            if ($datos_ingresados == 0) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Debe ingresar al menos un dato para validar'
                ]));
                return;
            }

            // Determinar resultado final
            $estado = empty($errores) ? 'validado' : 'erroneo';
            $observaciones = empty($errores) ? 'Validación exitosa' : implode(', ', $errores);

            log_message('debug', 'Resultado final: ' . $estado);

            // Guardar resultado en base de datos
            $datos_validacion = array(
                'numero_completo' => $numero_completo,
                'estado_validacion' => $estado,
                'observaciones' => $observaciones,
                'fecha_validacion' => date('Y-m-d H:i:s'),
                'codigo_movimi' => $codigo_entrada,
                'datos_buscados' => json_encode([
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'dni' => $dni,
                    'cuit' => $cuit,
                    'razon_social_parte1' => $razon_social_parte1,
                    'razon_social_parte2' => $razon_social_parte2
                ]),
                'errores_encontrados' => json_encode($errores),
                'validaciones_exitosas' => json_encode($validaciones_exitosas),
                'contenido_pdf' => $contenido_texto
            );

            $this->Pdf_model->guardar_validacion($datos_validacion);

            $this->output->set_output(json_encode([
                'success' => true,
                'estado' => $estado,
                'errores' => $errores,
                'validaciones_exitosas' => $validaciones_exitosas,
                'observaciones' => $observaciones,
                'message' => $estado == 'validado' ? 'Validación completada exitosamente' : 'Validación completada con errores'
            ]));

        } catch (Exception $e) {
            log_message('error', 'Error en validación de PDF: ' . $e->getMessage());
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]));
        }
    }

    // NUEVA FUNCIÓN: Búsqueda alternativa por datos personales
    public function buscar_alternativa()
    {
        $this->output->set_content_type('application/json');

        try {
            $dni = $this->input->post('dni');
            $cuit = $this->input->post('cuit');
            $nombre = $this->input->post('nombre');
            $apellido = $this->input->post('apellido');
            $usar_dni = $this->input->post('usar_dni') == 'true';
            $usar_cuit = $this->input->post('usar_cuit') == 'true';
            $usar_nombre = $this->input->post('usar_nombre') == 'true';
            $usar_apellido = $this->input->post('usar_apellido') == 'true';

            // Simular búsqueda en base de datos de personas
            $resultados_encontrados = $this->simular_busqueda_personas($dni, $cuit, $nombre, $apellido, $usar_dni, $usar_cuit, $usar_nombre, $usar_apellido);

            if ($resultados_encontrados) {
                $this->output->set_output(json_encode([
                    'success' => true,
                    'found' => true,
                    'message' => 'Parámetros encontrados en la base de datos',
                    'datos' => [
                        'dni' => $usar_dni ? $dni : null,
                        'cuit' => $usar_cuit ? $cuit : null,
                        'nombre' => $usar_nombre ? $nombre : null,
                        'apellido' => $usar_apellido ? $apellido : null
                    ]
                ]));
            } else {
                $this->output->set_output(json_encode([
                    'success' => true,
                    'found' => false,
                    'message' => 'No se encontraron coincidencias con los parámetros especificados'
                ]));
            }

        } catch (Exception $e) {
            log_message('error', 'Error en búsqueda alternativa: ' . $e->getMessage());
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]));
        }
    }

    // NUEVA FUNCIÓN: Simular búsqueda en base de datos (reemplazar con lógica real)
    private function simular_busqueda_personas($dni, $cuit, $nombre, $apellido, $usar_dni, $usar_cuit, $usar_nombre, $usar_apellido)
    {
        // DEMO: Datos de ejemplo para la demostración
        $personas_demo = [
            [
                'dni' => '12345678',
                'cuit' => '20-12345678-9',
                'nombre' => 'Juan',
                'apellido' => 'Pérez'
            ],
            [
                'dni' => '87654321',
                'cuit' => '27-87654321-4',
                'nombre' => 'María',
                'apellido' => 'González'
            ],
            [
                'dni' => '11223344',
                'cuit' => '23-11223344-2',
                'nombre' => 'Carlos',
                'apellido' => 'Rodríguez'
            ]
        ];

        // Buscar coincidencias
        foreach ($personas_demo as $persona) {
            $coincidencias = 0;
            $criterios_activos = 0;

            if ($usar_dni) {
                $criterios_activos++;
                if ($persona['dni'] == $dni) {
                    $coincidencias++;
                }
            }

            if ($usar_cuit) {
                $criterios_activos++;
                if ($persona['cuit'] == $cuit) {
                    $coincidencias++;
                }
            }

            if ($usar_nombre) {
                $criterios_activos++;
                if (strtolower($persona['nombre']) == strtolower($nombre)) {
                    $coincidencias++;
                }
            }

            if ($usar_apellido) {
                $criterios_activos++;
                if (strtolower($persona['apellido']) == strtolower($apellido)) {
                    $coincidencias++;
                }
            }

            // Si todas las condiciones activas coinciden
            if ($criterios_activos > 0 && $coincidencias == $criterios_activos) {
                return true;
            }
        }

        return false;
    }

    // NUEVA FUNCIÓN: Descargar documento encontrado
    // NUEVA FUNCIÓN: Descargar documento encontrado
    public function descargar_documento($numero_completo)
    {
        // Verificar que el ID es válido
        if (!$numero_completo) {
            show_404();
            return;
        }
        
        // RUTA MODIFICADA: Usar la nueva ruta del escritorio
        $path_busqueda = 'C:/Users/lp2165/Desktop/pdfbuscar/';
        $nombre_archivo = $numero_completo . '.pdf';
        $ruta_completa = $path_busqueda . $nombre_archivo;

        if (!file_exists($ruta_completa)) {
            log_message('error', "Archivo físico no encontrado: $ruta_completa");
            $this->session->set_flashdata('error', 'El archivo PDF no se encuentra en el servidor.');
            redirect('pdf/buscar');
            return;
        }
        
        // Cargar helper de descarga
        $this->load->helper('download');
        
        try {
            // Obtener contenido del archivo
            $contenido = file_get_contents($ruta_completa);
            
            if ($contenido === false) {
                throw new Exception("No se pudo leer el archivo");
            }
            
            // Forzar descarga
            force_download($nombre_archivo, $contenido);
            
        } catch (Exception $e) {
            log_message('error', "Error en descarga documento $numero_completo: " . $e->getMessage());
            $this->session->set_flashdata('error', 'Error al descargar el archivo: ' . $e->getMessage());
            redirect('pdf/buscar');
        }
    }

    // Resto de funciones originales...
    public function subir()
    {
        $this->load->view('pdf/subir');
    }

    public function procesar()
    {
        $this->load->library('upload');
        
        $upload_path = './uploads/pdfs/';
        
        if (!is_dir($upload_path)) {
            if (!is_dir('./uploads/')) {
                mkdir('./uploads/', 0755, true);
            }
            mkdir($upload_path, 0755, true);
        }
        
        if (!is_dir($upload_path)) {
            $this->session->set_flashdata('error', 'No se pudo crear el directorio: ' . $upload_path);
            redirect('pdf/subir');
            return;
        }
        
        if (!is_writable($upload_path)) {
            chmod($upload_path, 0755);
            if (!is_writable($upload_path)) {
                $this->session->set_flashdata('error', 'El directorio no tiene permisos de escritura: ' . $upload_path);
                redirect('pdf/subir');
                return;
            }
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'pdf';
        $config['max_size'] = 10240;
        $config['encrypt_name'] = TRUE;
        $config['remove_spaces'] = TRUE;
        
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('archivo_pdf')) {
            $error = $this->upload->display_errors('', '');
            $this->session->set_flashdata('error', 'Error al subir archivo: ' . $error);
            redirect('pdf/subir');
        } else {
            $upload_data = $this->upload->data();
            
            try {
                $ruta_completa = $upload_data['full_path'];
                $contenido_texto = $this->simplepdfextractor->extract_text($ruta_completa);
                
                $datos_pdf = array(
                    'nombre_archivo' => $upload_data['orig_name'],
                    'ruta_archivo' => $ruta_completa,
                    'contenido_texto' => $contenido_texto,
                    'tamaño_archivo' => $upload_data['file_size'] * 1024,
                    'estado' => 'procesado',
                    'fecha_subida' => date('Y-m-d H:i:s')
                );
                
                if ($this->Pdf_model->guardar_pdf($datos_pdf)) {
                    $pdf_id = $this->db->insert_id();
                    $this->session->set_flashdata('success', 'PDF subido y procesado correctamente');
                    redirect('pdf/ver/' . $pdf_id);
                } else {
                    $this->session->set_flashdata('error', 'Error al guardar en la base de datos');
                    redirect('pdf/subir');
                }
                
            } catch (Exception $e) {
                log_message('error', 'Error procesando PDF: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'Error procesando el PDF: ' . $e->getMessage());
                redirect('pdf/subir');
            }
        }
    }

    public function eliminar($id = null)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_404();
            return;
        }

        $this->output->set_content_type('application/json');

        try {
            if (!$id || !is_numeric($id)) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'ID de documento inválido'
                ]));
                return;
            }

            $pdf = $this->Pdf_model->obtener_por_id($id);
            
            if (!$pdf) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ]));
                return;
            }

            if (isset($pdf['ruta_archivo']) && file_exists($pdf['ruta_archivo'])) {
                unlink($pdf['ruta_archivo']);
            }

            $eliminado = $this->Pdf_model->eliminar($id);

            if ($eliminado) {
                $this->output->set_output(json_encode([
                    'success' => true,
                    'message' => 'Documento eliminado correctamente'
                ]));
            } else {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar el documento de la base de datos'
                ]));
            }

        } catch (Exception $e) {
            log_message('error', 'Error al eliminar PDF: ' . $e->getMessage());
            
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]));
        }
    }

    public function ver($id)
    {
        $pdf = $this->Pdf_model->obtener_pdf_por_id($id);
        
        if (!$pdf) {
            show_404();
        }

        $palabras = $this->simplepdfextractor->extraer_palabras($pdf['contenido_texto']);
        
        $data = array(
            'pdf' => $pdf,
            'palabras' => $palabras,
            'total_palabras' => count($palabras)
        );

        $this->load->view('pdf/ver', $data);
    }

    public function descargar($id)
    {
        if (!$id || !is_numeric($id)) {
            show_404();
            return;
        }
        
        $pdf = $this->Pdf_model->obtener_pdf_por_id($id);
        
        if (!$pdf) {
            log_message('error', "PDF no encontrado en BD: ID $id");
            show_404();
            return;
        }
        
        $ruta_archivo = $pdf['ruta_archivo'];
        
        if (!file_exists($ruta_archivo)) {
            log_message('error', "Archivo físico no encontrado: $ruta_archivo");
            $this->session->set_flashdata('error', 'El archivo PDF no se encuentra en el servidor.');
            redirect('pdf');
            return;
        }
        
        $this->load->helper('download');
        
        try {
            $contenido = file_get_contents($ruta_archivo);
            
            if ($contenido === false) {
                throw new Exception("No se pudo leer el archivo");
            }
            
            force_download($pdf['nombre_archivo'], $contenido);
            
        } catch (Exception $e) {
            log_message('error', "Error en descarga PDF ID $id: " . $e->getMessage());
            $this->session->set_flashdata('error', 'Error al descargar el archivo: ' . $e->getMessage());
            redirect('pdf');
        }
    }

    public function descargar_directo($id)
    {
        $pdf = $this->Pdf_model->obtener_pdf_por_id($id);
        
        if (!$pdf || !file_exists($pdf['ruta_archivo'])) {
            show_404();
            return;
        }
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $pdf['nombre_archivo'] . '"');
        header('Content-Length: ' . filesize($pdf['ruta_archivo']));
        header('Cache-Control: no-cache, must-revalidate');
        
        readfile($pdf['ruta_archivo']);
        exit;
    }

    // NUEVA FUNCIÓN: Ver historial de validaciones
    // FUNCIÓN CORREGIDA: Ver historial de validaciones
    public function historial_validaciones()
    {
        try {
            $data['validaciones'] = $this->Pdf_model->obtener_validaciones(100);
            
            // Usar función simplificada para evitar problemas con PostgreSQL
            $data['estadisticas'] = $this->Pdf_model->obtener_estadisticas_simples();
            
            $this->load->view('pdf/historial_validaciones', $data);
            
        } catch (Exception $e) {
            log_message('error', 'Error en historial_validaciones: ' . $e->getMessage());
            
            // Si hay error, mostrar página con datos vacíos
            $data['validaciones'] = [];
            $data['estadisticas'] = [
                'total_validaciones' => 0,
                'validaciones_exitosas' => 0,
                'validaciones_erroneas' => 0,
                'porcentaje_exito' => 0
            ];
            
            $this->load->view('pdf/historial_validaciones', $data);
        }
    }

    // NUEVA FUNCIÓN: API para obtener estadísticas de validaciones
    // FUNCIÓN CORREGIDA: API para obtener estadísticas de validaciones
public function api_estadisticas_validaciones()
{
    $this->output->set_content_type('application/json');
    
    try {
        $fecha_inicio = $this->input->get('fecha_inicio') ?: date('Y-m-d', strtotime('-30 days'));
        $fecha_fin = $this->input->get('fecha_fin') ?: date('Y-m-d');
        
        $estadisticas = $this->Pdf_model->obtener_validaciones_por_fecha($fecha_inicio . ' 00:00:00', $fecha_fin . ' 23:59:59');
        
        // Procesar estadísticas manualmente
        $resumen = [
            'total_validaciones' => count($estadisticas),
            'validaciones_exitosas' => 0,
            'validaciones_erroneas' => 0,
            'personas_fisicas' => 0,
            'personas_juridicas' => 0
        ];
        
        foreach ($estadisticas as $validacion) {
            if ($validacion['estado_validacion'] == 'validado') {
                $resumen['validaciones_exitosas']++;
            } elseif ($validacion['estado_validacion'] == 'erroneo') {
                $resumen['validaciones_erroneas']++;
            }
            
            if ($validacion['codigo_movimi'] == '01' || $validacion['codigo_movimi'] == '98') {
                $resumen['personas_fisicas']++;
            } elseif ($validacion['codigo_movimi'] == '02') {
                $resumen['personas_juridicas']++;
            }
        }
        
        $resumen['porcentaje_exito'] = $resumen['total_validaciones'] > 0 
            ? round(($resumen['validaciones_exitosas'] / $resumen['total_validaciones']) * 100, 2) 
            : 0;
        
        $this->output->set_output(json_encode([
            'success' => true,
            'resumen' => $resumen,
            'validaciones' => $estadisticas
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error obteniendo estadísticas: ' . $e->getMessage());
        $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Error interno del servidor'
        ]));
    }
}

    // NUEVA FUNCIÓN: Revalidar un documento
    public function revalidar_documento($numero_completo)
    {
        $this->output->set_content_type('application/json');
        
        try {
            // Buscar validación anterior
            $validacion_anterior = $this->Pdf_model->buscar_validacion_por_numero($numero_completo);
            
            if (!$validacion_anterior) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'No se encontró validación anterior para este documento'
                ]));
                return;
            }
            
            // Obtener ruta del archivo
            $path_busqueda = 'C:/Users/lp2165/Desktop/pdfbuscar/';
            $nombre_archivo = $numero_completo . '.pdf';
            $ruta_completa = $path_busqueda . $nombre_archivo;
            
            if (!file_exists($ruta_completa)) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'El archivo PDF ya no existe'
                ]));
                return;
            }
            
            // Extraer texto nuevamente
            $contenido_texto = $this->simplepdfextractor->extract_text($ruta_completa);
            
            // Obtener datos anteriores para revalidar
            $datos_anteriores = json_decode($validacion_anterior['datos_buscados'], true);
            
            $this->output->set_output(json_encode([
                'success' => true,
                'validacion_anterior' => $validacion_anterior,
                'datos_anteriores' => $datos_anteriores,
                'contenido_texto' => $contenido_texto,
                'message' => 'Listo para revalidar documento'
            ]));
            
        } catch (Exception $e) {
            log_message('error', 'Error en revalidación: ' . $e->getMessage());
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]));
        }
    }
}