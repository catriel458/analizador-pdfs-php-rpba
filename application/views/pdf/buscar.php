<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda por Códigos - Registro de la Propiedad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2E86AB;
            --secondary-color: #A23B72;
            --accent-color: #F18F01;
            --light-blue: #E8F4F8;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background: linear-gradient(135deg, #E8F4F8 0%, #B8E0D2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .navbar {
            background: var(--primary-color) !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .search-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 134, 171, 0.25);
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 25px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 16px;
        }
        
        .btn-success {
            background: var(--success-color);
            border-color: var(--success-color);
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .code-display {
            background: #f8f9fa;
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        
        .code-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            font-family: 'Courier New', monospace;
        }
        
        .result-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .result-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left: 5px solid var(--success-color);
        }
        
        .result-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 5px solid var(--warning-color);
        }
        
        .checkbox-custom {
            transform: scale(1.2);
            margin-right: 10px;
        }
        
        .search-alternative {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-top: 20px;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .loading {
            display: none;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="fas fa-file-pdf me-2"></i>
                Análisis de PDFs - Registro de la Propiedad
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url() ?>">
                    <i class="fas fa-home me-1"></i>
                    Inicio
                </a>
                <a class="nav-link" href="<?= base_url('pdf/subir') ?>">
                    <i class="fas fa-upload me-1"></i>
                    Subir PDF
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="search-container">
            <div class="text-center mb-4">
                <i class="fas fa-search fa-3x text-primary mb-3"></i>
                <h2 class="fw-bold text-primary">Búsqueda por Códigos</h2>
                <p class="text-muted">Ingrese los códigos para buscar el documento PDF</p>
            </div>

            <!-- Formulario de búsqueda principal -->
            <form id="searchForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="codigo_entrada" class="form-label fw-bold">
                            <i class="fas fa-key me-2"></i>
                            Código Entrada (N2)
                        </label>
                        <input type="text" class="form-control" id="codigo_entrada" name="codigo_entrada" 
                               maxlength="2" pattern="[0-9]{2}" placeholder="Ej: 01" required>
                        <small class="text-muted">2 dígitos numéricos</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="numero_entrada" class="form-label fw-bold">
                            <i class="fas fa-hashtag me-2"></i>
                            Número Entrada (N7)
                        </label>
                        <input type="text" class="form-control" id="numero_entrada" name="numero_entrada" 
                               maxlength="7" pattern="[0-9]{7}" placeholder="Ej: 1234567" required>
                        <small class="text-muted">7 dígitos numéricos</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="digito_verificador" class="form-label fw-bold">
                            <i class="fas fa-shield-alt me-2"></i>
                            Dígito Verificador (N1)
                        </label>
                        <input type="text" class="form-control" id="digito_verificador" name="digito_verificador" 
                               maxlength="1" pattern="[0-9]{1}" placeholder="Ej: 9" required>
                        <small class="text-muted">1 dígito numérico</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ano" class="form-label fw-bold">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Año (N4)
                        </label>
                        <input type="text" class="form-control" id="ano" name="ano" 
                               maxlength="4" pattern="[0-9]{4}" placeholder="Ej: 2024" required>
                        <small class="text-muted">4 dígitos numéricos</small>
                    </div>
                </div>

                <!-- Visualización del código concatenado -->
                <div class="code-display" id="codeDisplay" style="display: none;">
                    <p class="mb-2"><strong>Código Generado:</strong></p>
                    <div class="code-number" id="generatedCode">-</div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg me-3" id="searchBtn">
                        <i class="fas fa-search me-2"></i>
                        <span class="btn-text">Buscar Documento</span>
                        <span class="loading">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Buscando...
                        </span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="limpiarFormulario()">
                        <i class="fas fa-eraser me-2"></i>
                        Limpiar
                    </button>
                </div>
            </form>

            <!-- Resultado de la búsqueda -->
            <div id="searchResult" style="display: none;"></div>

            <!-- Modal de búsqueda alternativa -->
            <div class="modal fade" id="modalBusquedaAlternativa" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-user-search me-2"></i>
                                Búsqueda Alternativa por Datos Personales
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Instrucciones:</strong> Marque los campos que desea usar para la búsqueda e ingrese los datos correspondientes.
                            </div>
                            
                            <form id="alternativeSearchForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input checkbox-custom" type="checkbox" 
                                                   id="usar_dni" name="usar_dni" onchange="toggleField('dni')">
                                            <label class="form-check-label fw-bold" for="usar_dni">
                                                <i class="fas fa-id-card me-2"></i>
                                                Buscar por DNI
                                            </label>
                                        </div>
                                        <input type="text" class="form-control" id="dni" name="dni" 
                                               placeholder="Ej: 12345678" maxlength="8" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input checkbox-custom" type="checkbox" 
                                                   id="usar_cuit" name="usar_cuit" onchange="toggleField('cuit')">
                                            <label class="form-check-label fw-bold" for="usar_cuit">
                                                <i class="fas fa-file-invoice me-2"></i>
                                                Buscar por CUIT
                                            </label>
                                        </div>
                                        <input type="text" class="form-control" id="cuit" name="cuit" 
                                               placeholder="Ej: 20-12345678-9" maxlength="13" disabled>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input checkbox-custom" type="checkbox" 
                                                   id="usar_nombre" name="usar_nombre" onchange="toggleField('nombre')">
                                            <label class="form-check-label fw-bold" for="usar_nombre">
                                                <i class="fas fa-user me-2"></i>
                                                Buscar por Nombre
                                            </label>
                                        </div>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               placeholder="Ej: Juan" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input checkbox-custom" type="checkbox" 
                                                   id="usar_apellido" name="usar_apellido" onchange="toggleField('apellido')">
                                            <label class="form-check-label fw-bold" for="usar_apellido">
                                                <i class="fas fa-user-tag me-2"></i>
                                                Buscar por Apellido
                                            </label>
                                        </div>
                                        <input type="text" class="form-control" id="apellido" name="apellido" 
                                               placeholder="Ej: Pérez" disabled>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" onclick="buscarAlternativo()" id="btnBuscarAlternativo">
                                <i class="fas fa-search me-2"></i>
                                <span class="btn-text">Buscar</span>
                                <span class="loading">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Buscando...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de validación de datos -->
<div class="modal fade" id="modalValidacionDatos" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-double me-2"></i>
                    Validación de Documento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Documento encontrado:</strong> <span id="archivoEncontrado"></span>
                    <br><strong>Código:</strong> <span id="codigoEncontrado"></span>
                </div>

                <!-- Campos dinámicos según tipo de traba -->
                <form id="validacionForm">
                    <input type="hidden" id="validacion_numero_completo" name="numero_completo">
                    <input type="hidden" id="validacion_contenido_texto" name="contenido_texto">
                    <input type="hidden" id="validacion_codigo_entrada" name="codigo_entrada">

                    <!-- Sección para Persona Física (código 01) -->
                    <div id="seccionPersonaFisica" style="display: none;">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-user me-2"></i>
                            Validación - Persona Física
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="validacion_nombre" class="form-label fw-bold">
                                    <i class="fas fa-user me-2"></i>
                                    Nombre
                                </label>
                                <input type="text" class="form-control" id="validacion_nombre" name="nombre" 
                                       placeholder="Ingrese el nombre a validar">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="validacion_apellido" class="form-label fw-bold">
                                    <i class="fas fa-user-tag me-2"></i>
                                    Apellido
                                </label>
                                <input type="text" class="form-control" id="validacion_apellido" name="apellido" 
                                       placeholder="Ingrese el apellido a validar">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="validacion_dni" class="form-label fw-bold">
                                    <i class="fas fa-id-card me-2"></i>
                                    DNI
                                </label>
                                <input type="text" class="form-control" id="validacion_dni" name="dni" 
                                       placeholder="Ej: 12345678" maxlength="8">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="validacion_cuit_pf" class="form-label fw-bold">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    CUIT (opcional)
                                </label>
                                <input type="text" class="form-control" id="validacion_cuit_pf" name="cuit" 
                                       placeholder="Ej: 20-12345678-9" maxlength="13">
                            </div>
                        </div>
                    </div>

                    <!-- Sección para Persona Jurídica (código 02) -->
                    <div id="seccionPersonaJuridica" style="display: none;">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-building me-2"></i>
                            Validación - Persona Jurídica
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="validacion_razon_social_parte1" class="form-label fw-bold">
                                    <i class="fas fa-building me-2"></i>
                                    Razón Social - Parte 1
                                </label>
                                <input type="text" class="form-control" id="validacion_razon_social_parte1" name="razon_social_parte1" 
                                       placeholder="Primera parte de la razón social">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="validacion_razon_social_parte2" class="form-label fw-bold">
                                    <i class="fas fa-building me-2"></i>
                                    Razón Social - Parte 2
                                </label>
                                <input type="text" class="form-control" id="validacion_razon_social_parte2" name="razon_social_parte2" 
                                       placeholder="Segunda parte de la razón social">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="validacion_cuit_pj" class="form-label fw-bold">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    CUIT
                                </label>
                                <input type="text" class="form-control" id="validacion_cuit_pj" name="cuit" 
                                       placeholder="Ej: 30-12345678-9" maxlength="13">
                            </div>
                        </div>
                    </div>

                    <!-- Vista previa del contenido (opcional) -->
                    <div class="mt-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="mostrarContenido">
                            <label class="form-check-label fw-bold" for="mostrarContenido">
                                <i class="fas fa-eye me-2"></i>
                                Mostrar contenido del PDF para referencia
                            </label>
                        </div>
                        <div id="contenidoPdf" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-file-alt me-2"></i>
                                        Contenido extraído del PDF
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <pre id="textoExtraido" style="max-height: 300px; overflow-y: auto; font-size: 12px;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="procesarValidacion()" id="btnValidar">
                    <i class="fas fa-check me-2"></i>
                    <span class="btn-text">Validar Documento</span>
                    <span class="loading">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Validando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de resultados de validación -->
<div class="modal fade" id="modalResultadosValidacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Resultados de Validación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoResultados">
                <!-- Los resultados se insertan aquí dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let modalBusquedaAlternativa;
    let modalValidacionDatos;
    let modalResultadosValidacion;
    let numeroCompleto = '';
    let datosDocumentoEncontrado = {};

    // Inicializar modales cuando cargue la página
    document.addEventListener('DOMContentLoaded', function() {
        modalBusquedaAlternativa = new bootstrap.Modal(document.getElementById('modalBusquedaAlternativa'));
        modalValidacionDatos = new bootstrap.Modal(document.getElementById('modalValidacionDatos'));
        modalResultadosValidacion = new bootstrap.Modal(document.getElementById('modalResultadosValidacion'));
        
        console.log('Modales inicializados correctamente');
        
        // Agregar botón de ejemplo
        const container = document.querySelector('.search-container');
        const exampleBtn = document.createElement('button');
        exampleBtn.type = 'button';
        exampleBtn.className = 'btn btn-outline-info btn-sm mb-3';
        exampleBtn.innerHTML = '<i class="fas fa-magic me-2"></i>Llenar Datos de Ejemplo';
        exampleBtn.onclick = llenarDatosEjemplo;
        
        const form = document.getElementById('searchForm');
        form.parentNode.insertBefore(exampleBtn, form);
    });

    // Generar código en tiempo real
    function generarCodigo() {
        const codigoEntrada = document.getElementById('codigo_entrada').value;
        const numeroEntrada = document.getElementById('numero_entrada').value;
        const digitoVerificador = document.getElementById('digito_verificador').value;
        const ano = document.getElementById('ano').value;

        if (codigoEntrada || numeroEntrada || digitoVerificador || ano) {
            const codigo = codigoEntrada + numeroEntrada + digitoVerificador + ano;
            document.getElementById('generatedCode').textContent = codigo;
            document.getElementById('codeDisplay').style.display = 'block';
        } else {
            document.getElementById('codeDisplay').style.display = 'none';
        }
    }

    // Event listeners para generar código en tiempo real
    document.getElementById('codigo_entrada').addEventListener('input', generarCodigo);
    document.getElementById('numero_entrada').addEventListener('input', generarCodigo);
    document.getElementById('digito_verificador').addEventListener('input', generarCodigo);
    document.getElementById('ano').addEventListener('input', generarCodigo);

    // Validar solo números
    function validarNumeros(event) {
        const char = String.fromCharCode(event.which);
        if (!/[0-9]/.test(char)) {
            event.preventDefault();
        }
    }

    // Agregar validación a todos los campos numéricos
    document.querySelectorAll('input[pattern*="[0-9]"]').forEach(input => {
        input.addEventListener('keypress', validarNumeros);
    });

    // Función principal de búsqueda
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('searchBtn');
        const btnText = btn.querySelector('.btn-text');
        const loading = btn.querySelector('.loading');
        
        // Mostrar loading
        btnText.style.display = 'none';
        loading.style.display = 'inline';
        btn.disabled = true;

        // Obtener datos del formulario
        const formData = new FormData(this);

        // Hacer petición AJAX
        fetch('<?= base_url() ?>index.php/pdf/procesar_busqueda', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta recibida:', data);
            
            if (data.success) {
                numeroCompleto = data.numero_completo;
                
                if (data.found) {
                    // Documento encontrado
                    mostrarResultado('success', data.message, data);
                } else {
                    // Documento no encontrado, mostrar búsqueda alternativa
                    mostrarResultado('warning', data.message, data);
                    setTimeout(() => {
                        modalBusquedaAlternativa.show();
                    }, 1000);
                }
            } else {
                mostrarResultado('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarResultado('error', 'Error de conexión. Inténtelo nuevamente.');
        })
        .finally(() => {
            // Restaurar botón
            btnText.style.display = 'inline';
            loading.style.display = 'none';
            btn.disabled = false;
        });
    });

    // Función para mostrar resultado de búsqueda
    function mostrarResultado(tipo, mensaje, data = null) {
        const resultDiv = document.getElementById('searchResult');
        let iconClass = 'fas fa-check-circle';
        let cardClass = 'result-success';
        let textClass = 'text-success';
        
        if (tipo === 'warning') {
            iconClass = 'fas fa-exclamation-triangle';
            cardClass = 'result-warning';
            textClass = 'text-warning';
        } else if (tipo === 'error') {
            iconClass = 'fas fa-times-circle';
            cardClass = 'result-warning';
            textClass = 'text-danger';
        }

        let content = `
            <div class="card result-card ${cardClass} fade-in">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="${iconClass} fa-2x ${textClass} me-3"></i>
                        <div class="flex-grow-1">
                            <h5 class="${textClass} mb-1">${mensaje}</h5>
                            ${data && data.numero_completo ? `<p class="mb-0"><strong>Código buscado:</strong> ${data.numero_completo}</p>` : ''}
                        </div>
                    </div>
        `;

        if (data && data.found && data.archivo) {
            // Si se encontró el documento, mostrar botón para validar
            content += `
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                <strong>Archivo:</strong> ${data.archivo}
                            </div>
                            <div>
                                <button class="btn btn-warning me-2" onclick="abrirModalValidacion()" data-documento='${JSON.stringify(data)}'>
                                    <i class="fas fa-check-double me-2"></i>
                                    Validar Datos
                                </button>
                                <a href="<?= base_url() ?>index.php/pdf/descargar_documento/${data.numero_completo}" 
                                   class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>
                                    Descargar
                                </a>
                            </div>
                        </div>
                    </div>
            `;
        }

        content += `
                </div>
            </div>
        `;

        resultDiv.innerHTML = content;
        resultDiv.style.display = 'block';
        
        // Guardar datos del documento para usar en el modal
        if (data && data.found) {
            datosDocumentoEncontrado = data;
        }
    }

    // Función para abrir modal de validación
    function abrirModalValidacion() {
        try {
            console.log('Abriendo modal de validación con datos:', datosDocumentoEncontrado);
            
            const data = datosDocumentoEncontrado;
            
            // Rellenar información del documento
            document.getElementById('archivoEncontrado').textContent = data.archivo || '';
            document.getElementById('codigoEncontrado').textContent = data.numero_completo || '';
            
            // Campos ocultos
            document.getElementById('validacion_numero_completo').value = data.numero_completo || '';
            document.getElementById('validacion_contenido_texto').value = data.contenido_texto || '';
            document.getElementById('validacion_codigo_entrada').value = data.codigo_entrada || '';
            
            // Mostrar contenido si está disponible
            if (data.contenido_texto) {
                document.getElementById('textoExtraido').textContent = data.contenido_texto;
            }
            
            // Mostrar sección apropiada según código de entrada
            if (data.codigo_entrada === '01') {
                document.getElementById('seccionPersonaFisica').style.display = 'block';
                document.getElementById('seccionPersonaJuridica').style.display = 'none';
            } else if (data.codigo_entrada === '02') {
                document.getElementById('seccionPersonaFisica').style.display = 'none';
                document.getElementById('seccionPersonaJuridica').style.display = 'block';
            } else {
                // Para otros códigos, mostrar ambas secciones
                document.getElementById('seccionPersonaFisica').style.display = 'block';
                document.getElementById('seccionPersonaJuridica').style.display = 'block';
            }
            
            modalValidacionDatos.show();
            
        } catch (error) {
            console.error('Error al abrir modal de validación:', error);
            alert('Error al abrir el modal de validación: ' + error.message);
        }
    }

    // Función para habilitar/deshabilitar campos
    function toggleField(fieldName) {
        const checkbox = document.getElementById('usar_' + fieldName);
        const field = document.getElementById(fieldName);
        
        if (checkbox && field) {
            field.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                field.value = '';
            }
            field.required = checkbox.checked;
        }
    }

    // Función para procesar la validación
    function procesarValidacion() {
        const btn = document.getElementById('btnValidar');
        const btnText = btn.querySelector('.btn-text');
        const loading = btn.querySelector('.loading');
        
        console.log('Iniciando validación...');
        
        // Mostrar loading
        btnText.style.display = 'none';
        loading.style.display = 'inline';
        btn.disabled = true;

        // Obtener datos del formulario
        const formData = new FormData(document.getElementById('validacionForm'));
        
        console.log('Datos del formulario:', [...formData.entries()]);

        // Hacer petición AJAX
        fetch('<?= base_url() ?>index.php/pdf/validar_pdf', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta de validación:', data);
            
            if (data.success) {
                modalValidacionDatos.hide();
                mostrarResultadosValidacion(data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error en validación:', error);
            alert('Error de conexión. Inténtelo nuevamente.');
        })
        .finally(() => {
            // Restaurar botón
            btnText.style.display = 'inline';
            loading.style.display = 'none';
            btn.disabled = false;
        });
    }

    // Función para mostrar resultados de validación
    function mostrarResultadosValidacion(data) {
        let contenido = '';
        
        if (data.estado === 'validado') {
            contenido = `
                <div class="alert alert-success d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading">¡Validación Exitosa!</h5>
                        <p class="mb-0">${data.message}</p>
                    </div>
                </div>
            `;
            
            if (data.validaciones_exitosas && data.validaciones_exitosas.length > 0) {
                contenido += `
                    <div class="mt-3">
                        <h6 class="fw-bold text-success">Validaciones exitosas:</h6>
                        <ul class="list-group list-group-flush">
                `;
                data.validaciones_exitosas.forEach(validacion => {
                    contenido += `<li class="list-group-item"><i class="fas fa-check text-success me-2"></i>${validacion}</li>`;
                });
                contenido += `</ul></div>`;
            }
        } else {
            contenido = `
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="fas fa-times-circle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading">Validación con Errores</h5>
                        <p class="mb-0">${data.message}</p>
                    </div>
                </div>
            `;
            
            if (data.errores && data.errores.length > 0) {
                contenido += `
                    <div class="mt-3">
                        <h6 class="fw-bold text-danger">Errores encontrados:</h6>
                        <ul class="list-group list-group-flush">
                `;
                data.errores.forEach(error => {
                    contenido += `<li class="list-group-item text-danger"><i class="fas fa-times text-danger me-2"></i>${error}</li>`;
                });
                contenido += `</ul></div>`;
            }
            
            if (data.validaciones_exitosas && data.validaciones_exitosas.length > 0) {
                contenido += `
                    <div class="mt-3">
                        <h6 class="fw-bold text-success">Validaciones exitosas:</h6>
                        <ul class="list-group list-group-flush">
                `;
                data.validaciones_exitosas.forEach(validacion => {
                    contenido += `<li class="list-group-item"><i class="fas fa-check text-success me-2"></i>${validacion}</li>`;
                });
                contenido += `</ul></div>`;
            }
        }
        
        contenido += `
            <div class="mt-3">
                <p class="mb-0"><strong>Observaciones:</strong> ${data.observaciones}</p>
            </div>
        `;
        
        document.getElementById('contenidoResultados').innerHTML = contenido;
        modalResultadosValidacion.show();
    }

    // Búsqueda alternativa
    function buscarAlternativo() {
        const btn = document.getElementById('btnBuscarAlternativo');
        const btnText = btn.querySelector('.btn-text');
        const loading = btn.querySelector('.loading');
        
        // Validar que al menos un campo esté seleccionado
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="usar_"]');
        const algunoSeleccionado = Array.from(checkboxes).some(cb => cb.checked);
        
        if (!algunoSeleccionado) {
            alert('Debe seleccionar al menos un criterio de búsqueda');
            return;
        }

        // Mostrar loading
        btnText.style.display = 'none';
        loading.style.display = 'inline';
        btn.disabled = true;

        // Obtener datos del formulario
        const formData = new FormData();
        formData.append('dni', document.getElementById('dni').value);
        formData.append('cuit', document.getElementById('cuit').value);
        formData.append('nombre', document.getElementById('nombre').value);
        formData.append('apellido', document.getElementById('apellido').value);
        formData.append('usar_dni', document.getElementById('usar_dni').checked);
        formData.append('usar_cuit', document.getElementById('usar_cuit').checked);
        formData.append('usar_nombre', document.getElementById('usar_nombre').checked);
        formData.append('usar_apellido', document.getElementById('usar_apellido').checked);

        // Hacer petición AJAX
        fetch('<?= base_url() ?>index.php/pdf/buscar_alternativa', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalBusquedaAlternativa.hide();
                
                if (data.found) {
                    // Parámetros encontrados
                    mostrarResultadoAlternativo('success', data.message, data.datos);
                } else {
                    // No se encontraron coincidencias
                    mostrarResultadoAlternativo('warning', data.message);
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión. Inténtelo nuevamente.');
        })
        .finally(() => {
            // Restaurar botón
            btnText.style.display = 'inline';
            loading.style.display = 'none';
            btn.disabled = false;
        });
    }

    // Mostrar resultado de búsqueda alternativa
    function mostrarResultadoAlternativo(tipo, mensaje, datos = null) {
        const resultDiv = document.getElementById('searchResult');
        let iconClass = 'fas fa-check-circle';
        let cardClass = 'result-success';
        let textClass = 'text-success';
        
        if (tipo === 'warning') {
            iconClass = 'fas fa-exclamation-triangle';
            cardClass = 'result-warning';
            textClass = 'text-warning';
        }

        let content = `
            <div class="card result-card ${cardClass} fade-in">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="${iconClass} fa-2x ${textClass} me-3"></i>
                        <div class="flex-grow-1">
                            <h5 class="${textClass} mb-1">Resultado de Búsqueda Alternativa</h5>
                            <p class="mb-0">${mensaje}</p>
                        </div>
                    </div>
        `;

        if (datos && tipo === 'success') {
            content += `
                    <div class="mt-3">
                        <h6 class="fw-bold">Datos encontrados:</h6>
                        <div class="row">
            `;
            
            if (datos.dni) {
                content += `<div class="col-md-6"><i class="fas fa-id-card me-2"></i><strong>DNI:</strong> ${datos.dni}</div>`;
            }
            if (datos.cuit) {
                content += `<div class="col-md-6"><i class="fas fa-file-invoice me-2"></i><strong>CUIT:</strong> ${datos.cuit}</div>`;
            }
            if (datos.nombre) {
                content += `<div class="col-md-6"><i class="fas fa-user me-2"></i><strong>Nombre:</strong> ${datos.nombre}</div>`;
            }
            if (datos.apellido) {
                content += `<div class="col-md-6"><i class="fas fa-user-tag me-2"></i><strong>Apellido:</strong> ${datos.apellido}</div>`;
            }
            
            content += `
                        </div>
                    </div>
            `;
        }

        content += `
                </div>
            </div>
        `;

        resultDiv.innerHTML = content;
        resultDiv.style.display = 'block';
    }

    // Limpiar formulario
    function limpiarFormulario() {
        document.getElementById('searchForm').reset();
        document.getElementById('alternativeSearchForm').reset();
        document.getElementById('searchResult').style.display = 'none';
        document.getElementById('codeDisplay').style.display = 'none';
        
        // Deshabilitar todos los campos alternativos
        document.querySelectorAll('input[type="checkbox"][name^="usar_"]').forEach(cb => {
            cb.checked = false;
            toggleField(cb.name.replace('usar_', ''));
        });
    }

    // Datos de ejemplo para testing
    function llenarDatosEjemplo() {
        document.getElementById('codigo_entrada').value = '98';
        document.getElementById('numero_entrada').value = '0716221';
        document.getElementById('digito_verificador').value = '8';
        document.getElementById('ano').value = '2025';
        generarCodigo();
    }

    // Función para mostrar/ocultar contenido del PDF
    document.getElementById('mostrarContenido').addEventListener('change', function() {
        const contenidoDiv = document.getElementById('contenidoPdf');
        contenidoDiv.style.display = this.checked ? 'block' : 'none';
    });

    // Prellenar campos desde parámetros URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('codigo')) {
        document.getElementById('codigo_entrada').value = urlParams.get('codigo');
    }
    if (urlParams.get('numero')) {
        document.getElementById('numero_entrada').value = urlParams.get('numero');
    }
    if (urlParams.get('digito')) {
        document.getElementById('digito_verificador').value = urlParams.get('digito');
    }
    if (urlParams.get('ano')) {
        document.getElementById('ano').value = urlParams.get('ano');
    }
    
    // Si hay parámetros, generar el código automáticamente
    if (urlParams.get('codigo') || urlParams.get('numero')) {
        generarCodigo();
    }
});

</script>
</body>
</html>