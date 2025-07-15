<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de <?= htmlspecialchars($pdf['nombre_archivo'], ENT_QUOTES, 'UTF-8') ?> - Registro de la Propiedad</title>
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
        
        .word-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            min-height: 200px;
        }
        
        .word-item {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            background: var(--light-blue);
            border-radius: 20px;
            border: 2px solid var(--primary-color);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .word-item:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.05);
        }
        
        .word-size-1 { font-size: 2.5rem; font-weight: bold; }
        .word-size-2 { font-size: 2rem; font-weight: bold; }
        .word-size-3 { font-size: 1.5rem; font-weight: 600; }
        .word-size-4 { font-size: 1.2rem; font-weight: 500; }
        .word-size-5 { font-size: 1rem; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-item {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .text-content {
            max-height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-color) !important;">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="fas fa-file-pdf me-2"></i>
                Análisis de PDFs - Registro de la Propiedad
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url() ?>">
                    <i class="fas fa-home me-1"></i>Inicio
                </a>
                <a class="nav-link" href="<?= base_url('pdf/subir') ?>">
                    <i class="fas fa-upload me-1"></i>Subir PDF
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card mb-4" style="border: none; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
            <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 15px 15px 0 0;">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-file-pdf me-2"></i>
                            <?= htmlspecialchars($pdf['nombre_archivo'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                        <p class="mb-0 mt-2 opacity-75">
                            <i class="fas fa-calendar me-2"></i>
                            Procesado el <?= date('d/m/Y H:i', strtotime($pdf['fecha_subida'])) ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <a href="<?= base_url('pdf/descargar/' . $pdf['id']) ?>" class="btn btn-light">
                                <i class="fas fa-download me-2"></i>Descargar
                            </a>
                            <a href="<?= base_url() ?>" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-item">
                <i class="fas fa-file-alt" style="font-size: 2rem; color: var(--accent-color); margin-bottom: 15px;"></i>
                <div class="stat-number"><?= number_format(strlen($pdf['contenido_texto'])) ?></div>
                <div style="color: #666; font-size: 0.9rem;">Caracteres</div>
            </div>
            <div class="stat-item">
                <i class="fas fa-font" style="font-size: 2rem; color: var(--accent-color); margin-bottom: 15px;"></i>
                <div class="stat-number"><?= $total_palabras ?></div>
                <div style="color: #666; font-size: 0.9rem;">Palabras Únicas</div>
            </div>
            <div class="stat-item">
                <i class="fas fa-weight" style="font-size: 2rem; color: var(--accent-color); margin-bottom: 15px;"></i>
                <div class="stat-number"><?= number_format($pdf['tamaño_archivo'] / 1024, 1) ?></div>
                <div style="color: #666; font-size: 0.9rem;">KB</div>
            </div>
            <div class="stat-item">
                <i class="fas fa-chart-line" style="font-size: 2rem; color: var(--accent-color); margin-bottom: 15px;"></i>
                <div class="stat-number"><?= count(array_filter($palabras, function($freq) { return $freq > 5; })) ?></div>
                <div style="color: #666; font-size: 0.9rem;">Palabras Frecuentes</div>
            </div>
        </div>

        <div class="card" style="border: none; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
            <div class="card-body" style="padding: 30px;">
                <ul class="nav nav-tabs mb-4" style="border-bottom: 2px solid var(--primary-color);">
                    <li class="nav-item">
                        <button class="nav-link active" id="cloud-tab" data-bs-toggle="tab" data-bs-target="#cloud" 
                                style="border: none; color: white; background: var(--primary-color); border-radius: 10px 10px 0 0;">
                            <i class="fas fa-cloud me-2"></i>Nube de Palabras
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="frequency-tab" data-bs-toggle="tab" data-bs-target="#frequency"
                                style="border: none; color: #666;">
                            <i class="fas fa-chart-bar me-2"></i>Frecuencia
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="text-tab" data-bs-toggle="tab" data-bs-target="#text"
                                style="border: none; color: #666;">
                            <i class="fas fa-file-alt me-2"></i>Texto Completo
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="cloud">
                        <div class="text-center mb-4">
                            <h5 class="text-primary">
                                <i class="fas fa-cloud me-2"></i>
                                Visualización de Palabras Más Frecuentes
                            </h5>
                            <p class="text-muted">El tamaño de cada palabra representa su frecuencia en el documento</p>
                        </div>
                        
                        <div class="word-cloud">
                            <?php 
                            if (!empty($palabras)) {
                                $max_freq = max($palabras);
                                $count = 0;
                                foreach ($palabras as $palabra => $frecuencia): 
                                    if ($count >= 50) break;
                                    $size = ceil(($frecuencia / $max_freq) * 5);
                                    $size = max(1, min(5, $size));
                            ?>
                                <span class="word-item word-size-<?= $size ?>" 
                                      title="<?= htmlspecialchars($palabra, ENT_QUOTES, 'UTF-8') ?>: <?= $frecuencia ?> veces">
                                    <?= htmlspecialchars($palabra, ENT_QUOTES, 'UTF-8') ?>
                                    <small class="badge bg-secondary ms-2"><?= $frecuencia ?></small>
                                </span>
                            <?php 
                                    $count++;
                                endforeach;
                            } else {
                                echo '<p class="text-muted">No se encontraron palabras para analizar.</p>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="frequency">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover">
                                <thead style="background: var(--primary-color); color: white; position: sticky; top: 0;">
                                    <tr>
                                        <th>#</th>
                                        <th>Palabra</th>
                                        <th>Frecuencia</th>
                                        <th>Porcentaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (!empty($palabras)) {
                                        $rank = 1;
                                        $total_words = array_sum($palabras);
                                        foreach ($palabras as $palabra => $frecuencia): 
                                    ?>
                                        <tr>
                                            <td><strong><?= $rank ?></strong></td>
                                            <td><span class="fw-bold text-primary"><?= htmlspecialchars($palabra, ENT_QUOTES, 'UTF-8') ?></span></td>
                                            <td><span class="badge bg-primary"><?= $frecuencia ?></span></td>
                                            <td><?= number_format(($frecuencia / $total_words) * 100, 2) ?>%</td>
                                        </tr>
                                    <?php 
                                            $rank++;
                                            if ($rank > 100) break; // Limitar a 100 palabras
                                        endforeach;
                                    } else {
                                        echo '<tr><td colspan="4" class="text-center">No hay palabras para mostrar</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="text">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-file-alt me-2"></i>
                                Contenido Completo del Documento
                            </label>
                        </div>
                        
                        <div class="text-content">
                            <?= nl2br(htmlspecialchars($pdf['contenido_texto'], ENT_QUOTES, 'UTF-8')) ?>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Total de caracteres: <?= number_format(strlen($pdf['contenido_texto'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cambiar estilos de tabs al hacer click
        document.querySelectorAll('.nav-link').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.nav-link').forEach(t => {
                    t.style.background = 'transparent';
                    t.style.color = '#666';
                });
                this.style.background = 'var(--primary-color)';
                this.style.color = 'white';
            });
        });
    </script>
</body>
</html>