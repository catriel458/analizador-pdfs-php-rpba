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

            // RUTA MODIFICADA: Definir path de búsqueda en el escritorio
            $path_busqueda = 'C:/Users/lp2165/Desktop/pdfbuscar/';
            $nombre_archivo = $numero_completo . '.pdf';
            $ruta_completa = $path_busqueda . $nombre_archivo;

            // Verificar si el archivo existe
            if (file_exists($ruta_completa)) {
                // Archivo encontrado
                $this->output->set_output(json_encode([
                    'success' => true,
                    'found' => true,
                    'message' => 'Documento encontrado',
                    'numero_completo' => $numero_completo,
                    'archivo' => $nombre_archivo,
                    'ruta' => $ruta_completa
                ]));
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
}