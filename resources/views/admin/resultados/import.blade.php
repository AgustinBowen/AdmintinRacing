@extends('layouts.admin')

@section('title', 'Importar Resultados (OCR/PDF)')

@section('content')
@include('components.admin.page-header', [
    'title' => 'Importar Resultados de Sesión'
])

<div class="row">
    <div class="col-md-10">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5 class="mb-0 fw-semibold">Subir Foto o Planilla PDF Official</h5>
            </div>
            <div class="card-body-modern">
                <div class="alert alert-info border-0 rounded-4">
                    <i class="fas fa-info-circle me-2"></i> Esta herramienta puede leer la planilla directamente desde un archivo <strong>PDF oficial</strong> (recomendado) o intentarlo a través de Inteligencia Artificial (OCR) desde una foto. Los resultados pasarán primero por una Vista Previa Editable.
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <div class="form-field-container">
                            <label class="form-label fw-medium mb-2" style="color: hsl(var(--foreground)); font-size: 0.875rem;">
                                Seleccionar Sesión *
                            </label>
                            <select id="sesion_id" class="input-modern" required>
                                <option value="">Seleccionar...</option>
                                @foreach($sesiones as $s)
                                    <option value="{{ $s->id }}">{{ $s->tipo }} - {{ $s->fecha->nombre ?? 'Sin fecha' }}</option>
                                @endforeach
                            </select>
                            <small class="form-help">¿A qué sesión pertenecen estos resultados?</small>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-field-container">
                            <label class="form-label fw-medium mb-2" style="color: hsl(var(--foreground)); font-size: 0.875rem;">
                                Archivo (PDF, JPG, PNG) *
                            </label>
                            <input type="file" id="result_file" accept=".pdf, image/*" class="input-modern" required multiple>
                            <small class="form-help">Sube la planilla PDF oficial o varias fotos claras de los resultados.</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions mt-4 pt-3 border-top">
                    <button type="button" id="btnProcesarFile" class="btn-modern btn-primary-modern">
                        <i class="fas fa-magic me-2"></i> Analizar y Extraer Resultados
                    </button>
                    <a href="{{ route('admin.resultados.index') }}" class="btn-modern btn-secondary-modern">
                        Cancelar
                    </a>
                </div>
                
                <div id="loadingStatus" class="mt-3 text-primary d-none">
                    <i class="fas fa-spinner fa-spin me-2"></i> Analizando documento, este proceso puede tardar unos segundos...
                </div>
                
                <div id="errorStatus" class="mt-3 text-danger d-none">
                </div>
            </div>
        </div>
    </div>
</div>

<form id="previewForm" action="{{ route('admin.resultados.import.preview') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="sesion_id" id="hidden_sesion_id">
    <textarea name="resultados_json" id="hidden_resultados_json"></textarea>
</form>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    document.getElementById('btnProcesarFile').addEventListener('click', async function() {
        const fileInput = document.getElementById('result_file');
        const sesionId = document.getElementById('sesion_id').value;
        const loading = document.getElementById('loadingStatus');
        const error = document.getElementById('errorStatus');
        
        error.classList.add('d-none');

        if (!sesionId) {
            error.innerText = "Por favor selecciona una sesión.";
            error.classList.remove('d-none');
            return;
        }

        if (!fileInput.files || fileInput.files.length === 0) {
            error.innerText = "Por favor selecciona un archivo.";
            error.classList.remove('d-none');
            return;
        }

        const file = fileInput.files[0];

        loading.classList.remove('d-none');
        this.disabled = true;

        try {
            let extractedLines = [];

            for (let i = 0; i < fileInput.files.length; i++) {
                const file = fileInput.files[i];

                if (file.type === 'application/pdf') {
                    loading.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Analizando PDF ' + (i + 1) + ' de ' + fileInput.files.length + '...';
                    const arrayBuffer = await file.arrayBuffer();
                    const pdf = await pdfjsLib.getDocument({data: new Uint8Array(arrayBuffer)}).promise;

                    for (let p = 1; p <= pdf.numPages; p++) {
                        const page = await pdf.getPage(p);
                        const textContent = await page.getTextContent();
                        const textItems = textContent.items;

                        let linesByY = [];
                        const tolerance = 3; 
                        for (let item of textItems) {
                            let str = item.str.trim();
                            if(!str) continue;
                            let foundLine = linesByY.find(l => Math.abs(l.y - item.transform[5]) <= tolerance);
                            if(foundLine) {
                                foundLine.items.push(item);
                            } else {
                                linesByY.push({ y: item.transform[5], items: [item] });
                            }
                        }

                        // Sort lines from top to bottom (PDF Y is bottom-up usually)
                        let sortedY = linesByY.sort((a,b) => b.y - a.y);
                        for(let line of sortedY) {
                            // Sort text chunks from left to right
                            let items = line.items.sort((a,b) => a.transform[4] - b.transform[4]);
                            let lineText = items.map(it => it.str.trim()).filter(Boolean).join(' ');
                            extractedLines.push(lineText.replace(/,/g, '.'));
                        }
                    }
                } else {
                    // Es Imagen
                    const worker = await Tesseract.createWorker('spa', 1, {
                        logger: m => {
                            if (m.status === 'recognizing text') {
                                loading.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Analizando foto OCR ' + (i + 1) + ' de ' + fileInput.files.length + '... ' + Math.round(m.progress * 100) + '%';
                            }
                        }
                    });
                    const ret = await worker.recognize(file);
                    let lines = ret.data.text.split('\n').map(L => L.trim().replace(/,/g, '.'));
                    extractedLines = extractedLines.concat(lines);
                    await worker.terminate();
                }
            }

            // Identificar tipo de planilla a partir de cabeceras
            let hasSectors = false;
            let hasTiempoTotal = false;

            for(let line of extractedLines) {
                let lower = line.toLowerCase();
                if(lower.includes('s1 mejor')) hasSectors = true;
                if(lower.includes('tiempo total') || lower.includes('total t')) hasTiempoTotal = true;
            }

            const resultados = [];

            // Expresión regular robusta para la estructura general cronológica
            for (const line of extractedLines) {
                if (!line) continue;
                
                // Buscar líneas que empiecen con numero de posicion 1-99, "NT" o "EX"
                if (/^\s*(\d{1,2}|nt|ex)\s+/i.test(line)) {
                    let parts = line.split(/\s+/);
                    if (parts.length >= 4) {
                        const rec = {
                            posicion: ['nt', 'ex'].includes(parts[0].toLowerCase()) ? parts[0].toUpperCase() : parts[0],
                            auto: parts[1],
                            nombre: '',
                            mejor_tiempo: null,
                            diferencia: null,
                            tiempo_total: null,
                            vueltas: null,
                            sector_1: null,
                            sector_2: null,
                            sector_3: null
                        };

                        let cursor = parts.length - 1;

                        if (hasSectors) {
                            // Extraer sectores al final de la línea: S3, S2, S1
                            if (cursor >= 2 && /^\d{2}\.\d{3}$/.test(parts[cursor])) rec.sector_3 = parts[cursor--];
                            if (cursor >= 2 && /^\d{2}\.\d{3}$/.test(parts[cursor])) rec.sector_2 = parts[cursor--];
                            if (cursor >= 2 && /^\d{2}\.\d{3}$/.test(parts[cursor])) rec.sector_1 = parts[cursor--];
                        }

                        // Lo que queda en el medio son tiempos, vueltas y diferencias
                        let middleTokens = [];
                        while (cursor >= 2) {
                            let p = parts[cursor];
                            let tL = p.toLowerCase();
                            if (/[\d:]/.test(p) || tL === 'vueltas' || tL === 'laps' || tL === 'vuelta' || tL === 'nt' || tL === 'ex' || tL === 'vuel') {
                                if (tL !== 'vueltas' && tL !== 'laps' && tL !== 'vuelta' && tL !== 'vuel') {
                                    middleTokens.unshift(p);
                                } else {
                                    if (cursor >= 1 && /^\d+$/.test(parts[cursor - 1]) && parts[cursor - 1].length <= 2 && !['nt', 'ex', 'vueltas', 'laps', 'vuelta', 'vuel'].includes(parts[cursor - 1].toLowerCase())) {
                                        middleTokens.unshift(parts[cursor - 1] + " " + p);
                                        cursor--;
                                    }
                                }
                                cursor--;
                            } else {
                                break;
                            }
                        }

                        if (hasSectors && hasTiempoTotal) {
                            // Serie o Final con sectores. Formato: Vueltas, Total T°, Mejor Tm, Dif, Dif Ant
                            if (middleTokens.length > 0) rec.vueltas = middleTokens[0];
                            if (middleTokens.length > 1) rec.tiempo_total = middleTokens[1];
                            if (middleTokens.length > 2) rec.mejor_tiempo = middleTokens[2];
                            if (middleTokens.length > 3) rec.diferencia = middleTokens[3];
                        } else if (hasSectors && !hasTiempoTotal) {
                            // Clasificación con sectores. Formato: Mejor Tm, Dif, Dif Ant, Vueltas
                            // Recordar que vueltas está al final
                            if (middleTokens.length > 0 && /^\d+$/.test(middleTokens[middleTokens.length - 1])) {
                                rec.vueltas = middleTokens.pop();
                            }
                            if (middleTokens.length > 0) rec.mejor_tiempo = middleTokens[0];
                            if (middleTokens.length === 3) {
                                rec.diferencia = middleTokens[1];
                            } else if (middleTokens.length === 2 && rec.posicion !== '1' && rec.posicion != null) {
                                rec.diferencia = middleTokens[1];
                            }
                        } else if (hasTiempoTotal && !hasSectors) {
                            // Serie/Final sin Sectores
                            if (middleTokens.length >= 3) {
                                rec.vueltas = middleTokens[0];
                                rec.tiempo_total = middleTokens[1];
                                rec.mejor_tiempo = middleTokens[middleTokens.length - 1];
                                if (middleTokens.length >= 4) {
                                    rec.diferencia = middleTokens[2];
                                }
                            }
                        } else {
                            // Fallback genérico
                            if (middleTokens.length > 0 && /^\d+$/.test(middleTokens[middleTokens.length - 1])) {
                                rec.vueltas = middleTokens.pop();
                            }
                            if (middleTokens.length > 0) rec.mejor_tiempo = middleTokens[0];
                            if (middleTokens.length > 1 && rec.posicion !== '1') rec.diferencia = middleTokens[1];
                        }

                        // Todo lo que quede desde 2 hasta cursor es el nombre del piloto
                        let nameParts = [];
                        for (let i = 2; i <= cursor; i++) {
                            if (!/^[|\[\]]+$/.test(parts[i])) {
                                let w = parts[i].toLowerCase();
                                nameParts.push(w.charAt(0).toUpperCase() + w.slice(1));
                            }
                        }
                        
                        if (nameParts.length > 1) {
                            let apellido = nameParts.shift();
                            nameParts.push(apellido); // Enviar apellido al final "PEREZ JUAN" -> "Juan Perez"
                        }
                        rec.nombre = nameParts.join(' ');

                        if (rec.nombre && rec.nombre.length > 2 && rec.nombre !== "No Clasificado") {
                            resultados.push(rec);
                        }
                    }
                }
            }

            if (resultados.length === 0) {
                throw new Error("No se pudo detectar ninguna fila con formato de resultados. Revisa el documento.");
            }

            // Añadir flags meta de columnas para renderizar en el preview
            resultados.push({ _meta: { hasSectors, hasTiempoTotal } });

            document.getElementById('hidden_sesion_id').value = sesionId;
            document.getElementById('hidden_resultados_json').value = JSON.stringify(resultados);
            document.getElementById('previewForm').submit();

        } catch (err) {
            console.error(err);
            error.innerText = err.message || "Error al procesar el documento.";
            error.classList.remove('d-none');
            loading.classList.add('d-none');
            this.disabled = false;
            loading.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Analizando documento, este proceso puede tardar unos segundos...';
        }
    });
</script>
@endpush
