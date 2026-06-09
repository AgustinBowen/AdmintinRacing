@extends('layouts.admin')
@section('title', 'Importar Resultados (OCR/PDF)')

@section('content')
<div class="view-head">
    <h1>IMPORTAR RESULTADOS <span class="lap">SELECCIONAR ARCHIVO Y SESIÓN</span></h1>
</div>

<div style="background:var(--carbon-2); border-left:3px solid var(--racing); padding:16px; margin-bottom:24px; font-family:var(--font-sans); font-size:14px; color:var(--white);">
    <i class="fas fa-info-circle" style="color:var(--racing); margin-right:8px;"></i>
    Esta herramienta puede leer la planilla directamente desde un archivo <strong>PDF oficial</strong> (recomendado) o intentarlo a través de Inteligencia Artificial (OCR) desde una foto. Los resultados pasarán primero por una Vista Previa Editable.
</div>

<div class="form-card" style="max-width:800px; margin:0;">
    <h2 style="font-family:var(--font-display); font-size:20px; font-weight:600; margin-bottom:24px;">Subir Planilla PDF o Foto</h2>
    
    <div class="fgrid">
        <div class="field">
            <label>Seleccionar Fecha *</label>
            <select id="fecha_id" required>
                <option value="">Seleccionar...</option>
                @foreach($fechas as $f)
                    <option value="{{ $f->id }}">{{ $f->nombre }} ({{ $f->campeonato->nombre ?? 'Sin campeonato' }})</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label>Seleccionar Sesión *</label>
            <select id="sesion_id" required disabled>
                <option value="">Primero selecciona una fecha...</option>
                @foreach($sesiones as $s)
                    <option value="{{ $s->id }}" data-fecha-id="{{ $s->fecha_id }}">{{ $s->tipo }}</option>
                @endforeach
            </select>
        </div>

        <div class="field full">
            <label>Archivo (PDF, JPG, PNG) *</label>
            <input type="file" id="result_file" accept=".pdf, image/*" required multiple style="background:var(--carbon); border:1px solid var(--line); color:var(--white); padding:8px;">
            <small class="form-help">Sube la planilla PDF oficial o varias fotos claras de los resultados.</small>
        </div>
    </div>

    <div id="loadingStatus" style="display:none; color:var(--gray); margin-top:20px; font-size:14px;">
        <i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i> <span id="loadingMsg">Analizando documento...</span>
    </div>
    
    <div id="errorStatus" style="display:none; color:var(--racing); margin-top:20px; font-size:14px; padding:12px; background:rgba(225,6,0,0.1); border-left:2px solid var(--racing);">
    </div>

    <div style="margin-top:24px; padding-top:24px; border-top:1px solid var(--line); display:flex; gap:12px; justify-content:flex-end;">
        <a href="{{ route('admin.resultados.index') }}" class="btn ghost">Cancelar</a>
        <button type="button" id="btnProcesarFile" class="btn" style="background:var(--white); color:var(--black);">
            ANALIZAR Y EXTRAER RESULTADOS
        </button>
    </div>
</div>

<form id="previewForm" action="{{ route('admin.resultados.import.preview') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="sesion_id" id="hidden_sesion_id">
    <textarea name="resultados_json" id="hidden_resultados_json"></textarea>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    document.addEventListener('DOMContentLoaded', function() {
        const fechaSelect = document.getElementById('fecha_id');
        const sesionSelect = document.getElementById('sesion_id');
        
        const originalOptions = Array.from(sesionSelect.querySelectorAll('option')).filter(opt => opt.value !== "");

        fechaSelect.addEventListener('change', function() {
            const fechaId = this.value;
            
            sesionSelect.innerHTML = '';
            
            if (fechaId) {
                sesionSelect.disabled = false;
                const defaultOpt = document.createElement('option');
                defaultOpt.value = "";
                defaultOpt.textContent = "Seleccionar...";
                sesionSelect.appendChild(defaultOpt);
                
                originalOptions.forEach(opt => {
                    if (opt.getAttribute('data-fecha-id') === fechaId) {
                        sesionSelect.appendChild(opt.cloneNode(true));
                    }
                });
            } else {
                const defaultOpt = document.createElement('option');
                defaultOpt.value = "";
                defaultOpt.textContent = "Primero selecciona una fecha...";
                sesionSelect.appendChild(defaultOpt);
                sesionSelect.disabled = true;
            }
        });
    });

    document.getElementById('btnProcesarFile').addEventListener('click', async function() {
        const fileInput = document.getElementById('result_file');
        const sesionId = document.getElementById('sesion_id').value;
        const loading = document.getElementById('loadingStatus');
        const error = document.getElementById('errorStatus');
        const loadingMsg = document.getElementById('loadingMsg');
        
        error.style.display = 'none';

        if (!sesionId) {
            error.innerText = "Por favor selecciona una sesión.";
            error.style.display = 'block';
            return;
        }

        if (!fileInput.files || fileInput.files.length === 0) {
            error.innerText = "Por favor selecciona un archivo.";
            error.style.display = 'block';
            return;
        }

        const file = fileInput.files[0];

        loading.style.display = 'block';
        this.disabled = true;

        try {
            let extractedLines = [];

            for (let i = 0; i < fileInput.files.length; i++) {
                const file = fileInput.files[i];

                if (file.type === 'application/pdf') {
                    loadingMsg.innerText = 'Analizando PDF ' + (i + 1) + ' de ' + fileInput.files.length + '...';
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

                        let sortedY = linesByY.sort((a,b) => b.y - a.y);
                        for(let line of sortedY) {
                            let items = line.items.sort((a,b) => a.transform[4] - b.transform[4]);
                            let lineText = items.map(it => it.str.trim()).filter(Boolean).join(' ');
                            extractedLines.push(lineText.replace(/,/g, '.'));
                        }
                    }
                } else {
                    const worker = await Tesseract.createWorker('spa', 1, {
                        logger: m => {
                            if (m.status === 'recognizing text') {
                                loadingMsg.innerText = 'Analizando foto OCR ' + (i + 1) + ' de ' + fileInput.files.length + '... ' + Math.round(m.progress * 100) + '%';
                            }
                        }
                    });
                    const ret = await worker.recognize(file);
                    let lines = ret.data.text.split('\n').map(L => L.trim().replace(/,/g, '.'));
                    extractedLines = extractedLines.concat(lines);
                    await worker.terminate();
                }
            }

            let hasSectors = false;
            let hasTiempoTotal = false;

            for(let line of extractedLines) {
                let lower = line.toLowerCase();
                if(lower.includes('s1 mejor')) hasSectors = true;
                if(lower.includes('tiempo total') || lower.includes('total t')) hasTiempoTotal = true;
            }

            const resultados = [];

            for (const line of extractedLines) {
                if (!line) continue;
                
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
                            if (cursor >= 2 && /^\d{2}\.\d{3}$/.test(parts[cursor])) rec.sector_3 = parts[cursor--];
                            if (cursor >= 2 && /^\d{2}\.\d{3}$/.test(parts[cursor])) rec.sector_2 = parts[cursor--];
                            if (cursor >= 2 && /^\d{2}\.\d{3}$/.test(parts[cursor])) rec.sector_1 = parts[cursor--];
                        }

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
                            if (middleTokens.length > 0) rec.vueltas = middleTokens[0];
                            if (middleTokens.length > 1) rec.tiempo_total = middleTokens[1];
                            if (middleTokens.length > 2) rec.mejor_tiempo = middleTokens[2];
                            if (middleTokens.length > 3) rec.diferencia = middleTokens[3];
                        } else if (hasSectors && !hasTiempoTotal) {
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
                            if (middleTokens.length >= 3) {
                                rec.vueltas = middleTokens[0];
                                rec.tiempo_total = middleTokens[1];
                                rec.mejor_tiempo = middleTokens[middleTokens.length - 1];
                                if (middleTokens.length >= 4) {
                                    rec.diferencia = middleTokens[2];
                                }
                            }
                        } else {
                            if (middleTokens.length > 0 && /^\d+$/.test(middleTokens[middleTokens.length - 1])) {
                                rec.vueltas = middleTokens.pop();
                            }
                            if (middleTokens.length > 0) rec.mejor_tiempo = middleTokens[0];
                            if (middleTokens.length > 1 && rec.posicion !== '1') rec.diferencia = middleTokens[1];
                        }

                        let nameParts = [];
                        for (let i = 2; i <= cursor; i++) {
                            if (!/^[|\[\]]+$/.test(parts[i])) {
                                let w = parts[i].toLowerCase();
                                nameParts.push(w.charAt(0).toUpperCase() + w.slice(1));
                            }
                        }
                        
                        if (nameParts.length > 1) {
                            let apellido = nameParts.shift();
                            nameParts.push(apellido);
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

            resultados.push({ _meta: { hasSectors, hasTiempoTotal } });

            document.getElementById('hidden_sesion_id').value = sesionId;
            document.getElementById('hidden_resultados_json').value = JSON.stringify(resultados);
            document.getElementById('previewForm').submit();

        } catch (err) {
            console.error(err);
            error.innerText = err.message || "Error al procesar el documento.";
            error.style.display = 'block';
            loading.style.display = 'none';
            this.disabled = false;
        }
    });
</script>
@endpush
@endsection
