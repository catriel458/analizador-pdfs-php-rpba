<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de PDFs - Registro de la Propiedad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2E86AB;
            --secondary-color: #A23B72;
            --accent-color: #F18F01;
            --light-blue: #E8F4F8;
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
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
        }

        .btn-success {
            background: #28a745;
            border-color: #28a745;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
        }

        .btn-info {
            background: #17a2b8;
            border-color: #17a2b8;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
        }

        .btn-delete {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            border-color: #bd2130;
            color: white;
        }

        .row-fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        @keyframes fadeOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(-100%); }
        }

        .action-buttons {
            margin-bottom: 2rem;
        }

        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
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
                <a class="nav-link" href="<?= base_url('pdf/buscar') ?>">
                    <i class="fas fa-search me-1"></i>
                    Buscar por Códigos
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="text-center mb-4">
            <h1 class="display-4 fw-bold text-primary">Gestión de Documentos PDF</h1>
            <p class="lead">Sistema de análisis para el Registro de la Propiedad</p>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Botones de acción principales -->
        <div class="row action-buttons">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card feature-card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-search feature-icon text-primary"></i>
                        <h5 class="card-title">Buscar por Códigos</h5>
                        <p class="card-text text-muted">Busque documentos usando códigos registrales</p>
                        <a href="<?= base_url('pdf/buscar') ?>" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>
                            Buscar Documento
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card feature-card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-cloud-upload-alt feature-icon text-success"></i>
                        <h5 class="card-title">Subir Documento</h5>
                        <p class="card-text text-muted">Agregue nuevos documentos PDF al sistema</p>
                        <a href="<?= base_url('pdf/subir') ?>" class="btn btn-success w-100">
                            <i class="fas fa-cloud-upload-alt me-2"></i>
                            Subir PDF
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card feature-card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-chart-bar feature-icon text-info"></i>
                        <h5 class="card-title">Estadísticas</h5>
                        <p class="card-text text-muted">Vea estadísticas y reportes del sistema</p>
                        <button class="btn btn-info w-100" onclick="mostrarEstadisticas()">
                            <i class="fas fa-chart-bar me-2"></i>
                            Ver Estadísticas
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card feature-card text-center h-100">
                    <div class="card-body">
                        <h2 class="text-primary feature-icon" id="contador-pdfs"><?= $total_pdfs ?></h2>
                        <h5 class="card-title">Documentos</h5>
                        <p class="card-text text-muted">Total de documentos procesados</p>
                        <div class="btn btn-outline-primary w-100 disabled">
                            <i class="fas fa-file-pdf me-2"></i>
                            Procesados
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- Tabla de documentos -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Lista de Documentos Recientes
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Documento</th>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Tamaño</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-pdfs">
                            <?php if (empty($pdfs)): ?>
                                <tr id="fila-vacia">
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No hay documentos procesados aún.</p>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="<?= base_url('pdf/subir') ?>" class="btn btn-primary">
                                                <i class="fas fa-upload me-2"></i>
                                                Subir Primer PDF
                                            </a>
                                            <a href="<?= base_url('pdf/buscar') ?>" class="btn btn-success">
                                                <i class="fas fa-search me-2"></i>
                                                Buscar Documento
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pdfs as $pdf): ?>
                                    <tr id="pdf-row-<?= $pdf['id'] ?>">
                                        <td>
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <?= htmlspecialchars($pdf['nombre_archivo'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($pdf['numero_completo'])): ?>
                                                <span class="badge bg-info font-monospace">
                                                    <?= htmlspecialchars($pdf['numero_completo'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($pdf['fecha_subida'])) ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= number_format($pdf['tamaño_archivo'] / 1024, 2) ?> KB
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?= ucfirst($pdf['estado']) ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?= base_url('pdf/ver/' . $pdf['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Ver documento">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('pdf/descargar/' . $pdf['id']) ?>" 
                                                   class="btn btn-sm btn-outline-success"
                                                   title="Descargar">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-delete" 
                                                        onclick="confirmarEliminacion(<?= $pdf['id'] ?>, '<?= htmlspecialchars($pdf['nombre_archivo'], ENT_QUOTES, 'UTF-8') ?>')"
                                                        title="Eliminar documento">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal de estadísticas -->
        <div class="modal fade" id="modalEstadisticas" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-chart-bar me-2"></i>
                            Estadísticas del Sistema
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary" id="total-documentos"><?= $total_pdfs ?></h3>
                                        <p class="text-muted">Total Documentos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3 class="text-success" id="documentos-procesados">-</h3>
                                        <p class="text-muted">Procesados Hoy</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3 class="text-info" id="busquedas-realizadas">-</h3>
                                        <p class="text-muted">Búsquedas Realizadas</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3 class="text-warning" id="documentos-encontrados">-</h3>
                                        <p class="text-muted">Documentos Encontrados</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="modalConfirmacion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar el documento <strong id="nombreArchivo"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Esta acción no se puede deshacer. El archivo será eliminado permanentemente.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btnEliminar">
                        <i class="fas fa-trash me-2"></i>Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
        let pdfIdAEliminar = null;
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
        const modalEstadisticas = new bootstrap.Modal(document.getElementById('modalEstadisticas'));

        function confirmarEliminacion(id, nombreArchivo) {
            pdfIdAEliminar = id;
            document.getElementById('nombreArchivo').textContent = nombreArchivo;
            modal.show();
        }

        function mostrarEstadisticas() {
            // Aquí puedes hacer una llamada AJAX para obtener estadísticas reales
            // Por ahora mostramos datos de ejemplo
            document.getElementById('documentos-procesados').textContent = Math.floor(Math.random() * 10);
            document.getElementById('busquedas-realizadas').textContent = Math.floor(Math.random() * 50);
            document.getElementById('documentos-encontrados').textContent = Math.floor(Math.random() * 30);
            
            modalEstadisticas.show();
        }

        // Resto del código JavaScript (funciones de eliminación, etc.)
        document.getElementById('btnEliminar').addEventListener('click', function() {
            if (pdfIdAEliminar) {
                eliminarPdf(pdfIdAEliminar);
            }
        });

        function eliminarPdf(id) {
            const btn = document.getElementById('btnEliminar');
            const textoOriginal = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Eliminando...';
            btn.disabled = true;

            fetch('<?= base_url() ?>index.php/pdf/eliminar/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const fila = document.getElementById('pdf-row-' + id);
                    fila.classList.add('row-fade-out');
                    
                    setTimeout(() => {
                        fila.remove();
                        actualizarContador();
                        verificarTablaVacia();
                        mostrarAlerta('success', data.message);
                    }, 500);
                } else {
                    mostrarAlerta('danger', data.message || 'Error al eliminar el documento');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('danger', 'Error de conexión. Inténtelo nuevamente.');
            })
            .finally(() => {
                btn.innerHTML = textoOriginal;
                btn.disabled = false;
                modal.hide();
                pdfIdAEliminar = null;
            });
        }

        function actualizarContador() {
            const filas = document.querySelectorAll('#tabla-pdfs tr:not(#fila-vacia)');
            const contador = document.getElementById('contador-pdfs');
            contador.textContent = filas.length;
        }

        function verificarTablaVacia() {
            const filas = document.querySelectorAll('#tabla-pdfs tr:not(#fila-vacia)');
            const tbody = document.getElementById('tabla-pdfs');
            
            if (filas.length === 0) {
                tbody.innerHTML = `
                    <tr id="fila-vacia">
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay documentos procesados aún.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="<?= base_url('pdf/subir') ?>" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>
                                    Subir Primer PDF
                                </a>
                                <a href="<?= base_url('pdf/buscar') ?>" class="btn btn-success">
                                    <i class="fas fa-search me-2"></i>
                                    Buscar Documento
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }

        function mostrarAlerta(tipo, mensaje) {
            const alertaExistente = document.querySelector('.alert:not(.alert-dismissible)');
            if (alertaExistente) {
                alertaExistente.remove();
            }

            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
            alerta.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            const titulo = document.querySelector('.text-center.mb-4');
            titulo.insertAdjacentElement('afterend', alerta);

            setTimeout(() => {
                if (alerta.parentNode) {
                    alerta.remove();
                }
            }, 5000);
        }

        // Animaciones de carga para las tarjetas
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.feature-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });
    </script>
</body>
</html>