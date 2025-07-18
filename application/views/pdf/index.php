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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="fas fa-file-pdf me-2"></i>
                Análisis de PDFs - Registro de la Propiedad
            </a>
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

        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-primary" id="contador-pdfs"><?= $total_pdfs ?></h2>
                        <p class="text-muted">Documentos Procesados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <a href="<?= base_url('pdf/subir') ?>" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-cloud-upload-alt me-2"></i>
                    Subir Nuevo PDF
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Lista de Documentos
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Tamaño</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-pdfs">
                            <?php if (empty($pdfs)): ?>
                                <tr id="fila-vacia">
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No hay documentos procesados aún.</p>
                                        <a href="<?= base_url('pdf/subir') ?>" class="btn btn-primary">
                                            Subir Primer PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pdfs as $pdf): ?>
                                    <tr id="pdf-row-<?= $pdf['id'] ?>">
                                        <td>
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <?= htmlspecialchars($pdf['nombre_archivo'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($pdf['fecha_subida'])) ?></td>
                                        <td>
                                            <span class="badge bg-info">
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

        function confirmarEliminacion(id, nombreArchivo) {
            pdfIdAEliminar = id;
            document.getElementById('nombreArchivo').textContent = nombreArchivo;
            modal.show();
        }

        document.getElementById('btnEliminar').addEventListener('click', function() {
            if (pdfIdAEliminar) {
                eliminarPdf(pdfIdAEliminar);
            }
        });

        function eliminarPdf(id) {
            const btn = document.getElementById('btnEliminar');
            const textoOriginal = btn.innerHTML;
            
            // Mostrar loading
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Eliminando...';
            btn.disabled = true;

            // Hacer petición AJAX
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
                    // Animar eliminación
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
                // Restaurar botón y cerrar modal
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
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay documentos procesados aún.</p>
                            <a href="<?= base_url('pdf/subir') ?>" class="btn btn-primary">
                                Subir Primer PDF
                            </a>
                        </td>
                    </tr>
                `;
            }
        }

        function mostrarAlerta(tipo, mensaje) {
            // Remover alertas existentes
            const alertaExistente = document.querySelector('.alert:not(.alert-dismissible)');
            if (alertaExistente) {
                alertaExistente.remove();
            }

            // Crear nueva alerta
            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
            alerta.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            // Insertar después del título
            const titulo = document.querySelector('.text-center.mb-4');
            titulo.insertAdjacentElement('afterend', alerta);

            // Auto-ocultar
            setTimeout(() => {
                if (alerta.parentNode) {
                    alerta.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>