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

    // NUEVAS FUNCIONES PARA LA BÚSQUEDA POR CÓDIGOS

    /**
     * Buscar PDF por número completo concatenado
     */
    public function buscar_por_numero_completo($numero_completo)
    {
        $this->db->where('numero_completo', $numero_completo);
        $query = $this->db->get('pdfs');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return false;
    }

    /**
     * Guardar PDF con número completo
     */
    public function guardar_pdf_con_codigo($datos)
    {
        return $this->db->insert('pdfs', $datos);
    }

    /**
     * Buscar personas por DNI
     */
    public function buscar_por_dni($dni)
    {
        // Simulación de tabla de personas registrales
        // En un sistema real, esta sería una consulta a la base de datos
        $this->db->where('dni', $dni);
        $query = $this->db->get('personas_registrales');
        
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        
        return false;
    }

    /**
     * Buscar personas por CUIT
     */
    public function buscar_por_cuit($cuit)
    {
        $this->db->where('cuit', $cuit);
        $query = $this->db->get('personas_registrales');
        
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        
        return false;
    }

    /**
     * Buscar personas por nombre
     */
    public function buscar_por_nombre($nombre)
    {
        $this->db->like('nombre', $nombre);
        $query = $this->db->get('personas_registrales');
        
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        
        return false;
    }

    /**
     * Buscar personas por apellido
     */
    public function buscar_por_apellido($apellido)
    {
        $this->db->like('apellido', $apellido);
        $query = $this->db->get('personas_registrales');
        
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        
        return false;
    }

    /**
     * Búsqueda combinada por múltiples criterios
     */
    public function buscar_por_criterios_combinados($criterios)
    {
        $this->db->select('*');
        $this->db->from('personas_registrales');
        
        $condiciones_aplicadas = false;
        
        if (isset($criterios['dni']) && !empty($criterios['dni'])) {
            $this->db->where('dni', $criterios['dni']);
            $condiciones_aplicadas = true;
        }
        
        if (isset($criterios['cuit']) && !empty($criterios['cuit'])) {
            if ($condiciones_aplicadas) {
                $this->db->where('cuit', $criterios['cuit']);
            } else {
                $this->db->where('cuit', $criterios['cuit']);
            }
            $condiciones_aplicadas = true;
        }
        
        if (isset($criterios['nombre']) && !empty($criterios['nombre'])) {
            if ($condiciones_aplicadas) {
                $this->db->where('nombre', $criterios['nombre']);
            } else {
                $this->db->like('nombre', $criterios['nombre']);
            }
            $condiciones_aplicadas = true;
        }
        
        if (isset($criterios['apellido']) && !empty($criterios['apellido'])) {
            if ($condiciones_aplicadas) {
                $this->db->where('apellido', $criterios['apellido']);
            } else {
                $this->db->like('apellido', $criterios['apellido']);
            }
            $condiciones_aplicadas = true;
        }
        
        if (!$condiciones_aplicadas) {
            return false;
        }
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        
        return false;
    }

    /**
     * Obtener información completa de documento por código
     */
    public function obtener_documento_por_codigo($codigo_entrada, $numero_entrada, $digito_verificador, $ano)
    {
        $numero_completo = $codigo_entrada . $numero_entrada . $digito_verificador . $ano;
        
        $this->db->select('*');
        $this->db->from('pdfs');
        $this->db->where('numero_completo', $numero_completo);
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return false;
    }

    /**
     * Registrar intento de búsqueda
     */
    public function registrar_intento_busqueda($datos_busqueda)
    {
        $datos_log = array(
            'numero_completo' => $datos_busqueda['numero_completo'],
            'encontrado' => $datos_busqueda['encontrado'] ? 1 : 0,
            'fecha_busqueda' => date('Y-m-d H:i:s'),
            'ip_origen' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        );
        
        return $this->db->insert('log_busquedas', $datos_log);
    }

    /**
     * Obtener estadísticas de búsquedas
     */
    public function obtener_estadisticas_busquedas()
    {
        $this->db->select('
            COUNT(*) as total_busquedas,
            SUM(encontrado) as documentos_encontrados,
            COUNT(*) - SUM(encontrado) as documentos_no_encontrados,
            DATE(fecha_busqueda) as fecha
        ');
        $this->db->from('log_busquedas');
        $this->db->group_by('DATE(fecha_busqueda)');
        $this->db->order_by('fecha', 'DESC');
        $this->db->limit(30); // Últimos 30 días
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Verificar si un número completo ya existe en la base de datos
     */
    public function verificar_numero_completo_existe($numero_completo)
    {
        $this->db->where('numero_completo', $numero_completo);
        $query = $this->db->get('pdfs');
        
        return $query->num_rows() > 0;
    }

    /**
     * Actualizar información de un documento existente
     */
    public function actualizar_documento($id, $datos)
    {
        $this->db->where('id', $id);
        return $this->db->update('pdfs', $datos);
    }

    /**
     * Buscar documentos por rango de fechas
     */
    public function buscar_por_rango_fechas($fecha_inicio, $fecha_fin)
    {
        $this->db->select('*');
        $this->db->from('pdfs');
        $this->db->where('fecha_subida >=', $fecha_inicio);
        $this->db->where('fecha_subida <=', $fecha_fin);
        $this->db->order_by('fecha_subida', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Buscar documentos por año
     */
     public function buscar_por_ano($ano)
    {
        $this->db->select('*');
        $this->db->from('validaciones_pdfs');
        $this->db->where("RIGHT(numero_completo, 4)", $ano);
        $this->db->order_by('fecha_validacion', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

     public function obtener_estadisticas_simples()
    {
        // Consulta simple sin GROUP BY para evitar problemas
        $this->db->select("
            COUNT(*) as total_validaciones,
            SUM(CASE WHEN estado_validacion = 'validado' THEN 1 ELSE 0 END) as validaciones_exitosas,
            SUM(CASE WHEN estado_validacion = 'erroneo' THEN 1 ELSE 0 END) as validaciones_erroneas,
            SUM(CASE WHEN estado_validacion = 'pendiente' THEN 1 ELSE 0 END) as validaciones_pendientes
        ");
        $this->db->from('validaciones_pdfs');
        
        $query = $this->db->get();
        $resultado = $query->row_array();
        
        // Calcular porcentaje de éxito
        if ($resultado['total_validaciones'] > 0) {
            $resultado['porcentaje_exito'] = round(($resultado['validaciones_exitosas'] / $resultado['total_validaciones']) * 100, 2);
        } else {
            $resultado['porcentaje_exito'] = 0;
        }
        
        return $resultado;
    }

    /**
     * Obtener últimos documentos procesados
     */
    public function obtener_ultimos_documentos($limit = 10)
    {
        $this->db->select('*');
        $this->db->from('pdfs');
        $this->db->order_by('fecha_subida', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        return $query->result_array();
    }

   
    // NUEVA FUNCIÓN: Guardar resultado de validación
    public function guardar_validacion($datos_validacion)
    {
        try {
            $this->db->trans_start();
            
            // Insertar en tabla de validaciones
            $this->db->insert('validaciones_pdfs', $datos_validacion);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error al guardar validación: ' . $e->getMessage());
            return false;
        }
    }

    // NUEVA FUNCIÓN: Obtener historial de validaciones
    public function obtener_validaciones($limite = 50)
    {
        $this->db->select('*');
        $this->db->from('validaciones_pdfs');
        $this->db->order_by('fecha_validacion', 'DESC');
        $this->db->limit($limite);
        
        $query = $this->db->get();
        return $query->result_array();
    }

    // NUEVA FUNCIÓN: Buscar validación por número completo
    public function buscar_validacion_por_numero($numero_completo)
    {
        $this->db->where('numero_completo', $numero_completo);
        $query = $this->db->get('validaciones_pdfs');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return false;
    }

    // NUEVA FUNCIÓN: Obtener estadísticas de validaciones
    public function obtener_estadisticas_validaciones()
    {
        $this->db->select("
            COUNT(*) as total_validaciones,
            SUM(CASE WHEN estado_validacion = 'validado' THEN 1 ELSE 0 END) as validaciones_exitosas,
            SUM(CASE WHEN estado_validacion = 'erroneo' THEN 1 ELSE 0 END) as validaciones_erroneas,
            DATE(fecha_validacion) as fecha
        ");
        $this->db->from('validaciones_pdfs');
        $this->db->group_by('DATE(fecha_validacion)');
        $this->db->order_by('fecha', 'DESC');
        $this->db->limit(30); // Últimos 30 días
        
        $query = $this->db->get();
        return $query->result_array();
    }

    // NUEVA FUNCIÓN: Actualizar estado de validación
    public function actualizar_validacion($id, $nuevos_datos)
    {
        $this->db->where('id', $id);
        return $this->db->update('validaciones_pdfs', $nuevos_datos);
    }

    // NUEVA FUNCIÓN: Obtener validaciones por estado

    public function obtener_validaciones_por_estado($estado, $limite = 20)
    {
        $this->db->select('*');
        $this->db->from('validaciones_pdfs');
        $this->db->where('estado_validacion', $estado);
        $this->db->order_by('fecha_validacion', 'DESC');
        $this->db->limit($limite);
        
        $query = $this->db->get();
        return $query->result_array();
    }

    // NUEVA FUNCIÓN: Buscar validaciones por rango de fechas
        public function obtener_validaciones_por_fecha($fecha_inicio, $fecha_fin)
    {
        $this->db->select('*');
        $this->db->from('validaciones_pdfs');
        $this->db->where('fecha_validacion >=', $fecha_inicio);
        $this->db->where('fecha_validacion <=', $fecha_fin);
        $this->db->order_by('fecha_validacion', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
    }
}