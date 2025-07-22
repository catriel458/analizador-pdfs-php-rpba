<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Validaciones - Registro de la Propiedad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2E86AB;
            --secondary-color: #A23B72;
            --accent-color: #F18F01;
            --light-blue: #E8F4F8;
            --success-color: #28a745;
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
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .badge-validado {
            background: var(--success-color);
        }
        
        .badge-erroneo {
            background: var(--danger-color);
        }
        
        .badge-pendiente {
            background: #6c757d;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
        }
        
        .stats-card {
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(46, 134, 171, 0.1);
        }
        
        .detalle-row {
            background-color: #f8f9fa;
            font-size: 0.9em;
        }
        
        .codigo-font {
            font-family: 'Courier New', monospace;
            font-weight: bold;
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
                <a class="nav-link" href="<?= base_url('pdf/buscar') ?>">
                    <i class="fas fa-search me-1"></i>
                    Buscar por Códigos
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-primary">Historial de Validaciones</h1>
            <p class="lead">Registro completo de documentos PDF procesados y validados</p>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= count($validaciones) ?></h3>
                        <p class="text-muted mb-0">Total Procesados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <?php 
                        $validados = array_filter($validaciones, function($v) { return $v['estado_validacion'] == 'validado'; });
                        ?>
                        <h3 class="text-success"><?= count($validados) ?></h3>
                        <p class="text-muted mb-0">Validados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <?php 
                        $erroneos = array_filter($validaciones, function($v) { return $v['estado_validacion'] == 'erroneo'; });
                        ?>
                        <h3 class="text-danger"><?= count($erroneos) ?></h3>
                        <p class="text-muted mb-0">Con Errores</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <?php 
                        $porcentaje = count($validaciones) > 0 ? round((count($validados) / count($validaciones)) * 100, 1) : 0;
                        ?>
                        <h3 class="text-info"><?= $porcentaje ?>%</h3>
                        <p class="text-muted mb-0">Éxito</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de historial -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Historial de Validaciones
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código Documento</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Fecha Validación</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($validaciones)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No hay validaciones registradas aún.</p>
                                        <a href="<?= base_url('pdf/buscar') ?>" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>
                                            Iniciar Primera Validación
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($validaciones as $validacion): ?>
                                    <tr>
                                        <td>
                                            <span class="codigo-font text-primary"><?= htmlspecialchars($validacion['numero_completo']) ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $tipo = 'Otro';
                                            if ($validacion['codigo_movimi'] == '01' || $validacion['codigo_movimi'] == '98') {
                                                $tipo = '<i class="fas fa-user me-1"></i> Persona Física';
                                            } elseif ($validacion['codigo_movimi'] == '02') {
                                                $tipo = '<i class="fas fa-building me-1"></i> Persona Jurídica';
                                            }
                                            echo $tipo;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = 'badge-pendiente';
                                            $icon = 'fas fa-clock';
                                            
                                            if ($validacion['estado_validacion'] == 'validado') {
                                                $badge_class = 'badge-validado';
                                                $icon = 'fas fa-check';
                                            } elseif ($validacion['estado_validacion'] == 'erroneo') {
                                                $badge_class = 'badge-erroneo';
                                                $icon = 'fas fa-times';
                                            }
                                            ?>
                                            <span class="badge <?= $badge_class ?>">
                                                <i class="<?= $icon ?> me-1"></i>
                                                <?= ucfirst($validacion['estado_validacion']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($validacion['fecha_validacion'])) ?>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= htmlspecialchars($validacion['observaciones']) ?>">
                                                <?= htmlspecialchars($validacion['observaciones']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="verDetalle(<?= $validacion['id'] ?>)" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="revalidar('<?= $validacion['numero_completo'] ?>')" title="Revalidar">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr id="detalle-<?= $validacion['id'] ?>" class="detalle-row" style="display: none;">
                                        <td colspan="6">
                                            <div class="p-3">
                                                <h6 class="fw-bold">Detalle de Validación</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Datos Buscados:</strong>
                                                        <?php
                                                        $datos = json_decode($validacion['datos_buscados'], true);
                                                        if ($datos) {
                                                            echo '<ul class="list-unstyled ms-3 mb-2">';
                                                            foreach ($datos as $campo => $valor) {
                                                                if (!empty($valor)) {
                                                                    $campo_nombre = str_replace('_', ' ', ucfirst($campo));
                                                                    echo "<li><strong>{$campo_nombre}:</strong> {$valor}</li>";
                                                                }
                                                            }
                                                            echo '</ul>';
                                                        } else {
                                                            echo '<p class="text-muted ms-3">No disponible</p>';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Validaciones Exitosas:</strong>
                                                        <?php
                                                        $exitosas = json_decode($validacion['validaciones_exitosas'], true);
                                                        if ($exitosas && count($exitosas) > 0) {
                                                            echo '<ul class="list-unstyled ms-3 mb-2">';
                                                            foreach ($exitosas as $exitosa) {
                                                                echo "<li class='text-success'><i class='fas fa-check me-1'></i> {$exitosa}</li>";
                                                            }
                                                            echo '</ul>';
                                                        } else {
                                                            echo '<p class="text-muted ms-3">Ninguna</p>';
                                                        }
                                                        ?>
                                                        
                                                        <strong>Errores Encontrados:</strong>
                                                        <?php
                                                        $errores = json_decode($validacion['errores_encontrados'], true);
                                                        if ($errores && count($errores) > 0) {
                                                            echo '<ul class="list-unstyled ms-3 mb-0">';
                                                            foreach ($errores as $error) {
                                                                echo "<li class='text-danger'><i class='fas fa-times me-1'></i> {$error}</li>";
                                                            }
                                                            echo '</ul>';
                                                        } else {
                                                            echo '<p class="text-muted ms-3 mb-0">Ninguno</p>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalle(id) {
            const detalle = document.getElementById('detalle-' + id);
            if (detalle.style.display === 'none') {
                detalle.style.display = 'table-row';
            } else {
                detalle.style.display = 'none';
            }
        }

        function revalidar(numeroCompleto) {
            if (confirm('¿Está seguro que desea revalidar este documento?')) {
                // Redirigir a la página de búsqueda con el código prellenado
                const codigo = numeroCompleto.substring(0, 2);
                const numero = numeroCompleto.substring(2, 9);
                const digito = numeroCompleto.substring(9, 10);
                const ano = numeroCompleto.substring(10, 14);
                
                const url = `<?= base_url('pdf/buscar') ?>?codigo=${codigo}&numero=${numero}&digito=${digito}&ano=${ano}`;
                window.location.href = url;
            }
        }
    </script>
</body>
</html>