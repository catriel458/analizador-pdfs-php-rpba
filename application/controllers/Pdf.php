<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pdf extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pdf_model');
        $this->load->library('SimplePdfExtractor');
    }

    public function index()
    {
        $data['pdfs'] = $this->Pdf_model->obtener_pdfs();
        $data['total_pdfs'] = $this->Pdf_model->contar_pdfs();
        
        $this->load->view('pdf/index', $data);
    }

    public function subir()
    {
        $this->load->view('pdf/subir');
    }

    // AQUÍ ES DONDE VA EL CÓDIGO QUE TE DI
    public function procesar()
    {
        // MÉTODO 1: Usar ruta relativa simple
        $upload_path = './uploads/pdfs/';
        
        // Crear directorios si no existen
        if (!is_dir($upload_path)) {
            // Crear directorio uploads primero
            if (!is_dir('./uploads/')) {
                mkdir('./uploads/', 0755, true);
            }
            // Luego crear subdirectorio pdfs
            mkdir($upload_path, 0755, true);
        }
        
        // Verificar que el directorio existe y es escribible
        if (!is_dir($upload_path)) {
            $this->session->set_flashdata('error', 'No se pudo crear el directorio: ' . $upload_path);
            redirect('pdf/subir');
            return;
        }
        
        if (!is_writable($upload_path)) {
            // Intentar dar permisos
            chmod($upload_path, 0755);
            if (!is_writable($upload_path)) {
                $this->session->set_flashdata('error', 'El directorio no tiene permisos de escritura: ' . $upload_path);
                redirect('pdf/subir');
                return;
            }
        }

        // Configuración de upload
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'pdf';
        $config['max_size'] = 10240; // 10MB
        $config['encrypt_name'] = TRUE;
        $config['remove_spaces'] = TRUE;
        
        // Limpiar cualquier configuración previa
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('archivo_pdf')) {
            $error = $this->upload->display_errors('', '');
            
            // Debug adicional
            $debug_info = array(
                'upload_path' => $upload_path,
                'path_exists' => is_dir($upload_path) ? 'YES' : 'NO',
                'path_writable' => is_writable($upload_path) ? 'YES' : 'NO',
                'real_path' => realpath($upload_path),
                'error' => $error
            );
            
            log_message('error', 'Upload Debug: ' . print_r($debug_info, true));
            
            $this->session->set_flashdata('error', 'Error al subir archivo: ' . $error);
            redirect('pdf/subir');
        } else {
            $upload_data = $this->upload->data();
            
            try {
                // Extraer texto del PDF
                $ruta_completa = $upload_data['full_path'];
                $contenido_texto = $this->simplepdfextractor->extract_text($ruta_completa);
                
                // Guardar en base de datos
                $datos_pdf = array(
                    'nombre_archivo' => $upload_data['orig_name'],
                    'ruta_archivo' => $ruta_completa,
                    'contenido_texto' => $contenido_texto,
                    'tamaño_archivo' => $upload_data['file_size'] * 1024,
                    'estado' => 'procesado'
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

    public function ver($id)
    {
        $pdf = $this->Pdf_model->obtener_pdf_por_id($id);
        
        if (!$pdf) {
            show_404();
        }

        // Extraer palabras únicas
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
        $pdf = $this->Pdf_model->obtener_pdf_por_id($id);
        
        if (!$pdf || !file_exists($pdf['ruta_archivo'])) {
            show_404();
        }

        $this->load->helper('download');
        force_download($pdf['nombre_archivo'], file_get_contents($pdf['ruta_archivo']));
    }
}