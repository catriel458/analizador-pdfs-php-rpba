<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir PDF - Registro de la Propiedad</title>
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
        
        .upload-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .upload-area {
            border: 3px dashed var(--primary-color);
            border-radius: 15px;
            padding: 60px 30px;
            text-align: center;
            background: var(--light-blue);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: var(--secondary-color);
            background: #f8f9fa;
        }
        
        .upload-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 25px;
            padding: 15px 40px;
            font-weight: 600;
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
        </div>
    </nav>

    <div class="container">
        <div class="upload-container">
            <div class="text-center mb-4">
                <i class="fas fa-building fa-3x text-primary mb-3"></i>
                <h2 class="fw-bold text-primary">Subir Documento PDF</h2>
                <p class="text-muted">Selecciona un archivo PDF para procesar y analizar</p>
            </div>

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $this->session->flashdata('error') ?>
                </div>
            <?php endif; ?>

            <?= form_open_multipart('pdf/procesar', array('id' => 'uploadForm')) ?>
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <h4 class="text-primary fw-bold">Arrastra tu archivo PDF aquí</h4>
                    <p class="text-muted mb-3">o haz clic para seleccionar</p>
                    <input type="file" name="archivo_pdf" id="archivo_pdf" style="display: none;" accept=".pdf" required>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('archivo_pdf').click()">
                        <i class="fas fa-folder-open me-2"></i>
                        Seleccionar Archivo
                    </button>
                </div>

                <div class="file-info" id="fileInfo" style="display: none; background: #f8f9fa; border-radius: 10px; padding: 15px; margin-top: 20px;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-1 fw-bold" id="fileName"></h6>
                            <small class="text-muted" id="fileSize"></small>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary me-3" id="submitBtn">
                        <i class="fas fa-upload me-2"></i>
                        Subir y Procesar
                    </button>
                    <a href="<?= base_url() ?>" class="btn btn-secondary" style="border-radius: 25px; padding: 15px 40px;">
                        <i class="fas fa-arrow-left me-2"></i>
                        Cancelar
                    </a>
                </div>
            <?= form_close() ?>

            <div style="background: #f8f9fa; border-radius: 10px; padding: 20px; margin-top: 30px;">
                <h6 class="fw-bold text-primary mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Especificaciones del Archivo
                </h6>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-check text-primary me-2"></i>
                    <span>Formato: Solo archivos PDF</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-check text-primary me-2"></i>
                    <span>Tamaño máximo: 10 MB</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-check text-primary me-2"></i>
                    <span>El sistema extraerá automáticamente el texto</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-check text-primary me-2"></i>
                    <span>Análisis de palabras y frecuencia</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('archivo_pdf').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = formatFileSize(file.size);
                document.getElementById('fileInfo').style.display = 'block';
            }
        });

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        document.getElementById('uploadForm').addEventListener('submit', function() {
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
            document.getElementById('submitBtn').disabled = true;
        });
    </script>
</body>
</html>