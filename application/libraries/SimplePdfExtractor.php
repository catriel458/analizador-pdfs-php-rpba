<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SimplePdfExtractor {
    
    public function extract_text($ruta_pdf)
    {
        if (!file_exists($ruta_pdf)) {
            throw new Exception("El archivo PDF no existe: " . $ruta_pdf);
        }
        
        $contenido = file_get_contents($ruta_pdf);
        if ($contenido === false) {
            throw new Exception("No se pudo leer el archivo PDF");
        }
        
        $texto_extraido = $this->extractRealText($contenido);
        $texto_limpio = $this->cleanForDatabase($texto_extraido);
        
        return $texto_limpio;
    }
    
    private function extractRealText($content)
    {
        $text = '';
        $all_found_text = array();
        
        // 1. Extraer de streams primero (más contenido)
        if (preg_match_all('/stream\s*\n(.*?)\nendstream/s', $content, $stream_matches)) {
            foreach ($stream_matches[1] as $stream) {
                $decoded = $this->tryDecodeStream($stream);
                $extracted = $this->extractFromDecodedStream($decoded);
                if (!empty($extracted)) {
                    $all_found_text[] = $extracted;
                }
            }
        }
        
        // 2. Extraer bloques de texto completos (BT...ET)
        if (preg_match_all('/BT\s+(.*?)\s+ET/s', $content, $bt_matches)) {
            foreach ($bt_matches[1] as $block) {
                $block_text = $this->extractFromTextBlock($block);
                if (!empty($block_text)) {
                    $all_found_text[] = $block_text;
                }
            }
        }
        
        // 3. Extraer texto con operador Tj
        if (preg_match_all('/\((.*?)\)\s*Tj/s', $content, $tj_matches)) {
            foreach ($tj_matches[1] as $match) {
                $clean = $this->cleanPdfString($match);
                if ($this->isRealContent($clean)) {
                    $all_found_text[] = $clean;
                }
            }
        }
        
        // 4. Extraer arrays de texto con TJ
        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $content, $array_matches)) {
            foreach ($array_matches[1] as $match) {
                $array_text = $this->extractFromTextArray($match);
                if (!empty($array_text)) {
                    $all_found_text[] = $array_text;
                }
            }
        }
        
        // 5. Buscar texto posicionado
        if (preg_match_all('/(?:Td|TD|Tm)\s+\((.*?)\)\s*Tj/s', $content, $positioned_matches)) {
            foreach ($positioned_matches[1] as $match) {
                $clean = $this->cleanPdfString($match);
                if ($this->isRealContent($clean)) {
                    $all_found_text[] = $clean;
                }
            }
        }
        
        // 6. Extraer strings simples en paréntesis (TODOS, sin filtros estrictos)
        if (preg_match_all('/\(([^)]*)\)/u', $content, $paren_matches)) {
            foreach ($paren_matches[1] as $match) {
                $clean = $this->cleanPdfString($match);
                if ($this->isAnyContent($clean)) { // Filtro más permisivo
                    $all_found_text[] = $clean;
                }
            }
        }
        
        // 7. Buscar números y códigos específicos (como 234234, 24503, DNIs)
        if (preg_match_all('/\b\d{5,8}\b/', $content, $number_matches)) {
            foreach ($number_matches[0] as $number) {
                if (strlen($number) >= 5) {
                    $all_found_text[] = $number;
                }
            }
        }
        
        // 8. Buscar patrones específicos del documento
        $patterns = array(
            '/Folio:\s*(\d+)/',
            '/DNI\s*(\d+)/',
            '/Carnet:\s*(\d+)/',
            '/Registro:\s*(\d+)/',
            '/Partida:\s*(\d+)/',
            '/N°?\s*(\d+)/',
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $pattern_matches)) {
                foreach ($pattern_matches[1] as $match) {
                    $all_found_text[] = trim($match);
                }
            }
        }
        
        // Eliminar duplicados y unir
        $all_found_text = array_unique($all_found_text);
        $text = implode(' ', $all_found_text);
        
        return trim($text);
    }
    
    private function extractFromTextBlock($block)
    {
        $text = '';
        
        // Extraer strings con Tj del bloque
        if (preg_match_all('/\((.*?)\)\s*Tj/s', $block, $matches)) {
            foreach ($matches[1] as $match) {
                $clean = $this->cleanPdfString($match);
                if ($this->isRealContent($clean)) {
                    $text .= $clean . ' ';
                }
            }
        }
        
        // Extraer arrays con TJ del bloque
        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $array_matches)) {
            foreach ($array_matches[1] as $match) {
                $array_text = $this->extractFromTextArray($match);
                if (!empty($array_text)) {
                    $text .= $array_text . ' ';
                }
            }
        }
        
        return trim($text);
    }
    
    private function extractFromTextArray($array_content)
    {
        $text = '';
        
        // Extraer strings del array
        if (preg_match_all('/\(([^)]*)\)/', $array_content, $string_matches)) {
            foreach ($string_matches[1] as $string) {
                $clean = $this->cleanPdfString($string);
                if ($this->isRealContent($clean)) {
                    $text .= $clean . ' ';
                }
            }
        }
        
        return trim($text);
    }
    
    private function tryDecodeStream($stream)
    {
        // Intentar gzuncompress
        $decoded = @gzuncompress($stream);
        if ($decoded !== false) {
            return $decoded;
        }
        
        // Intentar gzinflate
        $decoded = @gzinflate($stream);
        if ($decoded !== false) {
            return $decoded;
        }
        
        // Intentar sin header zlib (compatible con PHP 7.3)
        if (strlen($stream) > 2) {
            $decoded = @gzinflate(substr($stream, 2));
            if ($decoded !== false) {
                return $decoded;
            }
        }
        
        return $stream;
    }
    
    private function extractFromDecodedStream($decoded)
    {
        $text = '';
        
        // Buscar texto en el stream decodificado
        if (preg_match_all('/\((.*?)\)\s*Tj/s', $decoded, $matches)) {
            foreach ($matches[1] as $match) {
                $clean = $this->cleanPdfString($match);
                if ($this->isRealContent($clean)) {
                    $text .= $clean . ' ';
                }
            }
        }
        
        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $decoded, $matches)) {
            foreach ($matches[1] as $match) {
                $array_text = $this->extractFromTextArray($match);
                if (!empty($array_text)) {
                    $text .= $array_text . ' ';
                }
            }
        }
        
        // Buscar bloques BT...ET en streams
        if (preg_match_all('/BT\s+(.*?)\s+ET/s', $decoded, $bt_matches)) {
            foreach ($bt_matches[1] as $block) {
                $block_text = $this->extractFromTextBlock($block);
                if (!empty($block_text)) {
                    $text .= $block_text . ' ';
                }
            }
        }
        
        return trim($text);
    }
    
    private function cleanPdfString($string)
    {
        // Limpiar secuencias de escape básicas
        $string = str_replace('\\n', ' ', $string);
        $string = str_replace('\\r', ' ', $string);
        $string = str_replace('\\t', ' ', $string);
        $string = str_replace('\\\\', '\\', $string);
        $string = str_replace('\\(', '(', $string);
        $string = str_replace('\\)', ')', $string);
        
        // Limpiar secuencias octales
        $string = preg_replace('/\\\\[0-7]{1,3}/', '', $string);
        
        return trim($string);
    }
    
    private function isRealContent($text)
    {
        if (strlen($text) < 2) return false;
        
        // Debe contener al menos una letra o número
        if (!preg_match('/[a-zA-ZáéíóúñüÁÉÍÓÚÑÜ0-9]/', $text)) return false;
        
        // Filtrar comandos PDF conocidos
        $pdf_commands = array('obj', 'endobj', 'stream', 'endstream', 'xref', 'trailer', 'startxref', 'BT', 'ET', 'Tj', 'TJ');
        if (in_array(strtolower($text), $pdf_commands)) return false;
        
        // Filtrar coordenadas muy cortas (pero permitir números importantes)
        if (preg_match('/^[\d\s\.\-]+$/', $text) && strlen($text) < 6) return false;
        
        return true;
    }
    
    // Nuevo método más permisivo para capturar TODO
    private function isAnyContent($text)
    {
        if (strlen($text) < 1) return false;
        
        // Permitir cualquier cosa que tenga contenido visible
        if (!preg_match('/[a-zA-ZáéíóúñüÁÉÍÓÚÑÜ0-9]/', $text)) return false;
        
        // Solo filtrar comandos PDF obvios
        $pdf_commands = array('obj', 'endobj', 'stream', 'endstream', 'xref', 'trailer', 'startxref');
        if (in_array(strtolower($text), $pdf_commands)) return false;
        
        return true;
    }
    
    private function cleanForDatabase($text)
    {
        if (empty($text)) {
            return "El PDF fue procesado pero no contiene texto extraible en formato estandar.";
        }
        
        // LIMPIEZA MEJORADA PARA POSTGRESQL
        
        // 1. Normalizar espacios ANTES de limpiar caracteres
        $text = preg_replace('/\s+/', ' ', $text);
        
        // 2. MANTENER acentos y caracteres especiales del español
        // Solo eliminar caracteres de control realmente problemáticos
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // 3. NO eliminar caracteres extendidos (para mantener acentos)
        // $text = preg_replace('/[^\x20-\x7E]/', '', $text); // COMENTADO
        
        // 4. Limpiar solo caracteres realmente problemáticos para PostgreSQL
        $text = str_replace(array("\0", "\x08", "\x0C"), '', $text);
        
        // 5. Verificar que quede contenido útil
        if (strlen(trim($text)) < 10) {
            return "PDF procesado correctamente. Documento contiene principalmente elementos graficos.";
        }
        
        // 6. Límite de contenido
        if (strlen($text) > 100000) {
            $text = substr($text, 0, 100000) . '... [contenido truncado]';
        }
        
        // 7. Escapar comillas para PostgreSQL
        $text = str_replace("'", "''", $text);
        
        return trim($text);
    }
    
    public function extraer_palabras($texto)
    {
        if (empty($texto) || strlen($texto) < 20) {
            return array(
                'procesado' => 3,
                'contenido' => 2,
                'formato' => 1
            );
        }
        
        // Convertir a minúsculas (compatible PHP 7.3)
        $texto = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
        
        preg_match_all('/\b[a-záéíóúñü]{3,}\b/u', $texto, $matches);
        
        if (empty($matches[0])) {
            return array('sin' => 1, 'palabras' => 1);
        }
        
        $stopwords = array('que', 'para', 'por', 'con', 'una', 'del', 'las', 'los', 'esta', 'este', 'son', 'pero', 'más', 'muy', 'todo', 'bien', 'año', 'hasta', 'vez', 'tiempo', 'bajo', 'vida', 'donde', 'como', 'solo', 'sin', 'sobre', 'sus', 'dos', 'tres', 'así', 'nos', 'fue', 'era', 'ser', 'está');
        
        $palabras_filtradas = array();
        foreach ($matches[0] as $palabra) {
            if (!in_array($palabra, $stopwords) && strlen($palabra) > 2) {
                $palabras_filtradas[] = $palabra;
            }
        }
        
        $frecuencia = array_count_values($palabras_filtradas);
        arsort($frecuencia);
        
        return array_slice($frecuencia, 0, 50, true);
    }
    
    // Método para debug
    public function debug_pdf($ruta_pdf)
    {
        if (!file_exists($ruta_pdf)) {
            return "Archivo no encontrado: " . $ruta_pdf;
        }
        
        $contenido = file_get_contents($ruta_pdf);
        $info = array();
        
        $info['tamaño'] = strlen($contenido) . ' bytes';
        $info['objetos'] = preg_match_all('/\d+\s+\d+\s+obj/', $contenido);
        $info['streams'] = preg_match_all('/stream\s*\n.*?\nendstream/s', $contenido);
        $info['bloques_texto'] = preg_match_all('/BT\s+.*?\s+ET/s', $contenido);
        
        $texto = $this->extract_text($ruta_pdf);
        $info['caracteres_extraidos'] = strlen($texto);
        $info['muestra_texto'] = substr($texto, 0, 200) . '...';
        
        return $info;
    }
}