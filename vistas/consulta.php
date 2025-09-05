<?php
session_start();
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Admin';

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Expedientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/publico/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .search-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .quick-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
        }
        
        .quick-search .form-control {
            border-radius: 50px;
            border: none;
            font-size: 1.1rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .quick-search .btn {
            border-radius: 50px;
            padding: 1rem 2rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .advanced-search {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(70, 89, 125, 0.08);
            padding: 2rem;
        }
        
        .section-title {
            color: #495057;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .toggle-advanced {
            cursor: pointer;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .toggle-advanced:hover {
            color: #495057;
            text-decoration: underline;
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
        }
        
        .input-group-search {
            position: relative;
        }
        
        @media (max-width: 767px) {
            .quick-search, .advanced-search {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php require 'header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
             
            <!-- Main Content -->
            <main class="col-12 col-md-10 ms-sm-auto px-4 py-4">
                <div class="search-container">
                    <h1 class="text-center mb-4" style="color: #495057; font-weight: 700;">
                        <i class="bi bi-search me-2"></i>Búsqueda de Expedientes
                    </h1>
                    
                    <!-- BÚSQUEDA RÁPIDA -->
                    <div class="quick-search">
                        <h3 class="text-center mb-3">
                            <i class="bi bi-lightning-fill me-2"></i>Búsqueda Rápida
                        </h3>
                        <p class="text-center mb-4 opacity-75">
                            Busca en todos los campos: número, letra, folio, libro, año, extracto o iniciador
                        </p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="input-group-search">
                                    <input type="text" 
                                           id="quickSearch" 
                                           class="form-control" 
                                           placeholder="Escribe cualquier término para buscar..."
                                           autocomplete="off">
                                    <i class="bi bi-search search-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mt-3 mt-md-0">
                                <button type="button" 
                                        class="btn btn-light w-100" 
                                        onclick="realizarBusquedaRapida()">
                                    <i class="bi bi-search me-2"></i>Buscar Ahora
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="opacity-75">
                                <i class="bi bi-info-circle me-1"></i>
                                Ejemplos: "123", "expediente A", "2024", "María González"
                            </small>
                        </div>
                    </div>
                    
                    <!-- ENLACE PARA BÚSQUEDA AVANZADA -->
                    <div class="text-center mb-3">
                        <a href="#" class="toggle-advanced" onclick="toggleAdvanced()">
                            <i class="bi bi-gear me-1"></i>
                            <span id="toggleText">Mostrar búsqueda avanzada</span>
                        </a>
                    </div>
                    
                    <!-- BÚSQUEDA AVANZADA -->
                    <div class="advanced-search" id="advancedSearch" style="display: none;">
                        <h4 class="section-title">
                            <i class="bi bi-sliders me-2"></i>Búsqueda Avanzada por Campos
                        </h4>
                        
                        <form action="resultados.php" method="post" autocomplete="off">
                            <div class="row g-3">
                                <!-- Número -->
                                <div class="col-md-4">
                                    <label for="numero" class="form-label">Número</label>
                                    <input type="text"
                                           id="numero"
                                           name="numero"
                                           class="form-control"
                                           placeholder="Ej: 132"
                                           pattern="[0-9]{1,6}"
                                           maxlength="6"
                                           title="Solo números, máximo 6 dígitos">
                                </div>

                                <!-- Letra -->
                                <div class="col-md-4">
                                    <label for="letra" class="form-label">Letra</label>
                                    <select id="letra" name="letra" class="form-select">
                                        <option value="">Seleccionar letra</option>
                                        <?php foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $l): ?>
                                            <option value="<?= e($l) ?>"><?= e($l) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Folio -->
                                <div class="col-md-4">
                                    <label for="folio" class="form-label">Folio</label>
                                    <input type="text"
                                           id="folio"
                                           name="folio"
                                           class="form-control"
                                           placeholder="Ej: 1234"
                                           pattern="[0-9]{1,6}"
                                           maxlength="6"
                                           title="Solo números, máximo 6 dígitos">
                                </div>
                                
                                <!-- Libro -->
                                <div class="col-md-4">
                                    <label for="libro" class="form-label">Libro</label>
                                    <input type="text"
                                           id="libro"
                                           name="libro"
                                           class="form-control"
                                           placeholder="Ej: 1234"
                                           pattern="[0-9]{1,6}"
                                           maxlength="6"
                                           title="Solo números, máximo 6 dígitos">
                                </div>
                                
                                <!-- Año -->
                                <div class="col-md-4">
                                    <label for="anio" class="form-label">Año</label>
                                    <select id="anio" name="anio" class="form-select">
                                        <option value="">Seleccionar año</option>
                                        <?php for ($y = 1973; $y <= 2030; $y++): ?>
                                            <option value="<?= e($y) ?>"><?= e($y) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="reset" class="btn btn-outline-secondary px-4">
                                    <i class="bi bi-eraser me-2"></i>Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-search me-2"></i>Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Toggle búsqueda avanzada
        function toggleAdvanced() {
            const advanced = document.getElementById('advancedSearch');
            const toggleText = document.getElementById('toggleText');
            
            if (advanced.style.display === 'none') {
                advanced.style.display = 'block';
                toggleText.textContent = 'Ocultar búsqueda avanzada';
            } else {
                advanced.style.display = 'none';
                toggleText.textContent = 'Mostrar búsqueda avanzada';
            }
        }

        // Búsqueda rápida con Enter
        document.getElementById('quickSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                realizarBusquedaRapida();
            }
        });

        // Función de búsqueda rápida
        function realizarBusquedaRapida() {
            const termino = document.getElementById('quickSearch').value.trim();
            
            if (!termino) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo vacío',
                    text: 'Por favor, ingresa un término de búsqueda',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Mostrar loading
            Swal.fire({
                title: 'Buscando...',
                text: 'Consultando la base de datos',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Realizar búsqueda AJAX
            fetch('busqueda_rapida.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'termino=' + encodeURIComponent(termino)
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    if (data.expedientes.length > 0) {
                        mostrarResultados(data.expedientes);
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin resultados',
                            text: 'No se encontraron expedientes que coincidan con tu búsqueda',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Ocurrió un error en la búsqueda',
                        confirmButtonColor: '#3085d6'
                    });
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#3085d6'
                });
            });
        }

        // Mostrar resultados en SweetAlert
        function mostrarResultados(expedientes) {
            let html = '<div class="text-start">';
            
            expedientes.forEach(exp => {
                html += `
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="mb-0">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Expediente: ${exp.numero}/${exp.letra}/${exp.folio}/${exp.libro}/${exp.anio}
                            </h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row text-sm">
                                <div class="col-6">
                                    <strong>Fecha:</strong> ${exp.fecha_ingreso}<br>
                                    <strong>Iniciador:</strong> ${exp.iniciador}
                                </div>
                                <div class="col-6">
                                    <strong>Ubicación:</strong> <span class="badge bg-warning">${exp.lugar}</span><br>
                                    <strong>Extracto:</strong> ${exp.extracto_corto}
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <a href="pases_expediente.php?id=${exp.id}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            
            Swal.fire({
                title: `<i class="bi bi-check-circle-fill text-success me-2"></i>Resultados Encontrados`,
                html: `
                    <div class="alert alert-success">
                        Se encontraron <strong>${expedientes.length}</strong> expediente(s)
                    </div>
                    ${html}
                `,
                width: '90%',
                showConfirmButton: true,
                confirmButtonText: '<i class="bi bi-plus-circle me-2"></i>Nueva Búsqueda',
                confirmButtonColor: '#28a745',
                showCancelButton: true,
                cancelButtonText: '<i class="bi bi-list-ul me-2"></i>Ver Todos los Resultados',
                cancelButtonColor: '#007bff'
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    // Ir a página de resultados completos
                    window.location.href = `resultados.php?busqueda_rapida=${encodeURIComponent(document.getElementById('quickSearch').value)}`;
                } else if (result.isConfirmed) {
                    // Limpiar y enfocar búsqueda
                    document.getElementById('quickSearch').value = '';
                    document.getElementById('quickSearch').focus();
                }
            });
        }
    </script>
</body>
</html>