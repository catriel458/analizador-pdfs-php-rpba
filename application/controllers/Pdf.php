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
        // Solo permitir POST
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_404();
            return;
        }

        // Configurar respuesta JSON
        $this->output->set_content_type('application/json');

        try {
            // Validar ID
            if (!$id || !is_numeric($id)) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'ID de documento inválido'
                ]));
                return;
            }

            // Obtener PDF
            $pdf = $this->Pdf_model->obtener_por_id($id);
            
            if (!$pdf) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ]));
                return;
            }

            // Eliminar archivo físico
            if (isset($pdf['ruta_archivo']) && file_exists($pdf['ruta_archivo'])) {
                unlink($pdf['ruta_archivo']);
            }

            // Eliminar de base de datos
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

    // Reemplaza el método descargar en tu controlador con este:
    public function descargar($id)
    {
        // Verificar que el ID es válido
        if (!$id || !is_numeric($id)) {
            show_404();
            return;
        }
        
        // Obtener información del PDF
        $pdf = $this->Pdf_model->obtener_pdf_por_id($id);
        
        if (!$pdf) {
            log_message('error', "PDF no encontrado en BD: ID $id");
            show_404();
            return;
        }
        
        // Verificar que el archivo existe
        $ruta_archivo = $pdf['ruta_archivo'];
        
        if (!file_exists($ruta_archivo)) {
            log_message('error', "Archivo físico no encontrado: $ruta_archivo");
            $this->session->set_flashdata('error', 'El archivo PDF no se encuentra en el servidor.');
            redirect('pdf');
            return;
        }
        
        // Cargar helper de descarga
        $this->load->helper('download');
        
        try {
            // Obtener contenido del archivo
            $contenido = file_get_contents($ruta_archivo);
            
            if ($contenido === false) {
                throw new Exception("No se pudo leer el archivo");
            }
            
            // Forzar descarga
            force_download($pdf['nombre_archivo'], $contenido);
            
        } catch (Exception $e) {
            log_message('error', "Error en descarga PDF ID $id: " . $e->getMessage());
            $this->session->set_flashdata('error', 'Error al descargar el archivo: ' . $e->getMessage());
            redirect('pdf');
        }
    }

    // Método adicional para descarga directa (debug)
    public function descargar_directo($id)
    {
        $pdf = $this->Pdf_model->obtener_pdf_por_id($id);
        
        if (!$pdf || !file_exists($pdf['ruta_archivo'])) {
            show_404();
            return;
        }
        
        // Headers para forzar descarga
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $pdf['nombre_archivo'] . '"');
        header('Content-Length: ' . filesize($pdf['ruta_archivo']));
        header('Cache-Control: no-cache, must-revalidate');
        
        // Enviar archivo
        readfile($pdf['ruta_archivo']);
        exit;
    }
}