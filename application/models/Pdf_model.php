<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pdf_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function guardar_pdf($datos)
    {
        return $this->db->insert('pdfs', $datos);
    }

    public function obtener_pdfs()
    {
        $this->db->order_by('fecha_subida', 'DESC');
        $query = $this->db->get('pdfs');
        return $query->result_array();
    }

    public function obtener_pdf_por_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('pdfs');
        return $query->row_array();
    }

    public function obtener_por_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('pdfs');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return false;
    }

    public function eliminar($id)
    {
        try {
            $this->db->trans_start();
            
            // Eliminar el registro
            $this->db->where('id', $id);
            $this->db->delete('pdfs');
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error en modelo al eliminar PDF: ' . $e->getMessage());
            return false;
        }
    }

    public function actualizar_estado($id, $estado)
    {
        $this->db->where('id', $id);
        return $this->db->update('pdfs', array('estado' => $estado));
    }

    public function contar_pdfs()
    {
        return $this->db->count_all('pdfs');
    }

    public function contar_total()
    {
        return $this->db->count_all('pdfs');
    }

    public function obtener_estadisticas()
    {
        $this->db->select('estado, COUNT(*) as total');
        $this->db->group_by('estado');
        $query = $this->db->get('pdfs');
        return $query->result_array();
    }
}