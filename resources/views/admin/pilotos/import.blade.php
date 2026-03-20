@extends('layouts.admin')

@section('title', 'Importar Pilotos')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Importar Pilotos desde PDF'
])

<div class="row">
    <div class="col-md-10">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5 class="mb-0 fw-semibold">Subir Archivo de Resultados</h5>
            </div>
            <div class="card-body-modern">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-field-container">
                            <label class="form-label fw-medium mb-2" style="color: hsl(var(--foreground)); font-size: 0.875rem;">
                                Asignar a Campeonato (Opcional)
                            </label>
                            <select id="campeonato_id" class="input-modern">
                                <option value="">Seleccionar...</option>
                                @foreach($campeonatos as $campeonato)
                                    <option value="{{ $campeonato->id }}">{{ $campeonato->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="form-help">Si seleccionas un campeonato, a los pilotos importados se les asignará el número de auto automáticamente correspondiente al "Orden" en el que terminaron.</small>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-field-container">
                            <label class="form-label fw-medium mb-2" style="color: hsl(var(--foreground)); font-size: 0.875rem;">
                                Archivo PDF *
                            </label>
                            <input type="file" id="pdf_file" accept=".pdf" class="input-modern" required>
                            <small class="form-help">Sube el archivo PDF que contenga las columnas Orden, Auto, Piloto.</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions mt-4 pt-3 border-top">
                    <button type="button" id="btnProcesarPdf" class="btn-modern btn-primary-modern">
                        <i class="fas fa-search me-2"></i> Detectar Pilotos
                    </button>
                    <a href="{{ route('admin.pilotos.index') }}" class="btn-modern btn-secondary-modern">
                        Cancelar
                    </a>
                </div>
                
                <div id="loadingStatus" class="mt-3 text-primary d-none">
                    <i class="fas fa-spinner fa-spin me-2"></i> Analizando el documento PDF, por favor espera...
                </div>
                
                <div id="errorStatus" class="mt-3 text-danger d-none">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulario Oculto para enviar el resultado a Preview -->
<form id="previewForm" action="{{ route('admin.pilotos.import.preview') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="campeonato_id" id="hidden_campeonato_id">
    <textarea name="pilotos_json" id="hidden_pilotos_json"></textarea>
</form>

@endsection

@push('scripts')
<!-- Cargar libreria Pdf.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    // Inicializar worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    document.getElementById('btnProcesarPdf').addEventListener('click', async function() {
        const fileInput = document.getElementById('pdf_file');
        const campeonatoId = document.getElementById('campeonato_id').value;
        const loading = document.getElementById('loadingStatus');
        const error = document.getElementById('errorStatus');
        
        error.classList.add('d-none');

        if (!fileInput.files || fileInput.files.length === 0) {
            error.innerText = "Por favor selecciona un archivo PDF.";
            error.classList.remove('d-none');
            return;
        }

        const file = fileInput.files[0];
        if (file.type !== 'application/pdf') {
            error.innerText = "El archivo debe ser un PDF válido.";
            error.classList.remove('d-none');
            return;
        }

        loading.classList.remove('d-none');
        this.disabled = true;

        try {
            const fileReader = new FileReader();
            
            fileReader.onload = async function(e) {
                const typedarray = new Uint8Array(e.target.result);
                
                try {
                    const pdf = await pdfjsLib.getDocument({data: typedarray}).promise;
                    let fullText = "";
                    
                    // Extraer texto de todas las paginas
                    for (let i = 1; i <= pdf.numPages; i++) {
                        const page = await pdf.getPage(i);
                        const textContent = await page.getTextContent();
                        
                        // Reconstruir lineas agrupando items que estan a la misma altura (transform Y)
                        let textItems = textContent.items;
                        
                        // Si es muy complejo el PDF a nivel visual, se puede simplemente agrupar por transform[5] (el eje Y)
                        // Para simplificar concatenamos todo asumiendo flujo estandar y procesando luego.
                        // pdf.js trae saltos de linea? normalmente no, asi que formamos lineas:
                        
                        let lastY, text = '';
                        for (let item of textItems) {
                            if (lastY == item.transform[5] || !lastY){
                                text += item.str + " ";
                            } else {
                                text += "\n" + item.str + " ";
                            }
                            lastY = item.transform[5];
                        }
                        
                        fullText += text + "\n";
                    }

                    const lines = fullText.split('\n');
                    const pilotosDetectados = [];
                    
                    for (const rawLine of lines) {
                        const line = rawLine.trim();
                        if (!line) continue;
                        
                        // Buscar patron: numero auto nombre
                        // Usamos [^0-9]+? para atrapar todo el texto hasta que encontremos un número (columna de puntos)
                        const match = line.match(/^\s*(\d+)\s+(\d+)\s+([^0-9]+?)(?=\s+\d|$)/);
                        
                        if (match) {
                            const orden = match[1].trim();
                            const auto = match[2].trim();
                            
                            // Limpiar multiples espacios
                            let pilotoRaw = match[3].replace(/\s+/g, ' ').trim().toLowerCase();
                            
                            // Formato Title Case (primera letra en mayuscula)
                            let words = pilotoRaw.split(' ');
                            words = words.map(w => w.charAt(0).toUpperCase() + w.slice(1));
                            
                            // Invertir el Apellido y Nombre (asumimos que la primera palabra o dos primeras es apellido)
                            // Si son 2 palabras: "Jones", "Martin" -> "Martin Jones"
                            // Si son más: movemos la primera al final: "Perez", "Juan", "Carlos" -> "Juan Carlos Perez"
                            // Para mayor precision solo movemos la primera palabra.
                            if (words.length > 1) {
                                let apellido = words.shift();
                                words.push(apellido);
                            }
                            
                            const piloto = words.join(' ');
                            
                            if (piloto.length > 2) { 
                                pilotosDetectados.push({
                                    orden: orden,
                                    auto: auto,
                                    nombre: piloto,
                                    pais: 'Argentina'
                                });
                            }
                        }
                    }

                    if (pilotosDetectados.length === 0) {
                        throw new Error("No se detectaron pilotos con el formato esperado (Orden - Auto - Piloto). Asegúrate de que las columnas están completas.");
                    }

                    // Enviar al Backend
                    document.getElementById('hidden_campeonato_id').value = campeonatoId;
                    document.getElementById('hidden_pilotos_json').value = JSON.stringify(pilotosDetectados);
                    document.getElementById('previewForm').submit();

                } catch (err) {
                    console.error(err);
                    error.innerText = "No se pudo leer el contenido del PDF. Es posible que esté dañado o encriptado excesivamente.";
                    error.classList.remove('d-none');
                    loading.classList.add('d-none');
                    document.getElementById('btnProcesarPdf').disabled = false;
                }
            };
            
            fileReader.readAsArrayBuffer(file);
            
        } catch (err) {
            console.error(err);
            error.innerText = "Error general al abrir el archivo.";
            error.classList.remove('d-none');
            loading.classList.add('d-none');
            this.disabled = false;
        }
    });
</script>
@endpush
