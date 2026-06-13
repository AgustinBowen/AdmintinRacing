@extends('layouts.admin')
@section('content')
<div class="view-head">
    <h1>IMPORTAR RESULTADOS</h1>
</div>

<div style="background:var(--carbon-2); border-left:3px solid var(--racing); padding:16px; margin-bottom:24px; font-family:var(--font-sans); font-size:14px; color:var(--white);">
    <x-heroicon-o-information-circle style="width:1em; height:1em; vertical-align:-0.125em; color:var(--racing); margin-right:8px;" />
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

        const error = document.getElementById('errorStatus');

        
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


        this.disabled = true;

        try {
            let extractedLines = [];
            const isPdf = fileInput.files[0].type === 'application/pdf';
            const resultados = [];
            let hasSectors = false;
            let hasTiempoTotal = false;

            if (isPdf) {

                
                let textItems = [];
                for (let i = 0; i < fileInput.files.length; i++) {
                    const file = fileInput.files[i];
                    if (file.type !== 'application/pdf') continue;
                    
                    const arrayBuffer = await file.arrayBuffer();
                    const pdf = await pdfjsLib.getDocument({data: new Uint8Array(arrayBuffer)}).promise;

                    for (let p = 1; p <= pdf.numPages; p++) {
                        const page = await pdf.getPage(p);
                        const textContent = await page.getTextContent();
                        textItems = textItems.concat(textContent.items);
                    }
                }

                // Extraer strings secuenciales manteniendo el orden de columnas del PDF
                let extractedStrings = [];
                let lastY = null;
                let currentStr = "";
                for (let item of textItems) {
                    if (lastY !== null && Math.abs(lastY - item.transform[5]) > 3) {
                        if (currentStr.trim()) extractedStrings.push(currentStr.trim().replace(/,/g, '.'));
                        currentStr = "";
                    }
                    currentStr += item.str;
                    lastY = item.transform[5];
                }
                if (currentStr.trim()) extractedStrings.push(currentStr.trim().replace(/,/g, '.'));

                // Parsear por columnas
                const headerKeys = {
                    'pos.': 'posicion',
                    'n°': 'auto',
                    'nombre': 'nombre',
                    'vueltas': 'vueltas',
                    'laps': 'vueltas',
                    'total t°': 'tiempo_total',
                    'tiempo total': 'tiempo_total',
                    'mejor tm': 'mejor_tiempo',
                    'mejor tiempo': 'mejor_tiempo',
                    'dif. resp. 1°': 'diferencia',
                    'dif. resp.': 'diferencia',
                    'dif.': 'diferencia',
                    'dif. resp. anterior': 'dif_anterior',
                    'dif. ant.': 'dif_anterior',
                    's1 mejor': 'sector_1',
                    's2 mejor': 'sector_2',
                    's3 mejor': 'sector_3'
                };

                let colsData = {};
                let currentColumn = null;
                let possibleHeaders = Object.keys(headerKeys);

                for (let line of extractedStrings) {
                    let lowerLine = line.toLowerCase();
                    
                    if (lowerLine.includes('s1 mejor') && lowerLine.includes('s2 mejor')) {
                        currentColumn = null; // Fila de headers vacía
                        continue;
                    }

                    if (lowerLine.includes('clasificado por') || lowerLine.includes('márgen de victoria') || lowerLine.includes('margen de victoria') || lowerLine.includes('notificaciones') || lowerLine.includes('mejor t° de vuelta') || lowerLine.includes('mejor vel') || lowerLine.includes('impresos:')) {
                        currentColumn = null; // Ignorar el pie de página
                        continue;
                    }

                    let foundHeader = possibleHeaders.find(h => lowerLine === h || lowerLine.startsWith(h));
                    
                    if (foundHeader) {
                        currentColumn = headerKeys[foundHeader];
                        if (!colsData[currentColumn]) colsData[currentColumn] = [];
                        continue;
                    }

                    if (currentColumn) {
                        colsData[currentColumn].push(line);
                    }
                }

                if (colsData['posicion']) {
                    colsData['posicion'] = colsData['posicion'].filter(p => /^(\d+|nt|ex)$/i.test(p.trim()));
                }
                if (colsData['auto']) {
                    colsData['auto'] = colsData['auto'].filter(a => /^\d+$/.test(a.trim()));
                }

                let numPilotos = Math.max(
                    (colsData['auto'] || []).length, 
                    (colsData['nombre'] || []).length
                );

                if (colsData['posicion'] && colsData['posicion'][0] === '1') {
                    if (colsData['diferencia']) colsData['diferencia'].unshift('');
                    if (colsData['dif_anterior']) colsData['dif_anterior'].unshift('');
                }

                hasSectors = !!colsData['sector_1'];
                hasTiempoTotal = !!colsData['tiempo_total'];

                for (let i = 0; i < numPilotos; i++) {
                    let nombre = (colsData['nombre'] && colsData['nombre'][i]) ? colsData['nombre'][i] : '';
                    if (!nombre || nombre.toLowerCase().includes('no clasificado')) continue;
                    
                    let rec = {
                        posicion: colsData['posicion'] && colsData['posicion'][i] ? colsData['posicion'][i] : '',
                        auto: colsData['auto'] && colsData['auto'][i] ? colsData['auto'][i] : '',
                        nombre: nombre,
                        mejor_tiempo: colsData['mejor_tiempo'] && colsData['mejor_tiempo'][i] ? colsData['mejor_tiempo'][i] : null,
                        diferencia: colsData['diferencia'] && colsData['diferencia'][i] ? colsData['diferencia'][i] : null,
                        tiempo_total: colsData['tiempo_total'] && colsData['tiempo_total'][i] ? colsData['tiempo_total'][i] : null,
                        vueltas: colsData['vueltas'] && colsData['vueltas'][i] ? colsData['vueltas'][i] : null,
                        sector_1: colsData['sector_1'] && colsData['sector_1'][i] ? colsData['sector_1'][i] : null,
                        sector_2: colsData['sector_2'] && colsData['sector_2'][i] ? colsData['sector_2'][i] : null,
                        sector_3: colsData['sector_3'] && colsData['sector_3'][i] ? colsData['sector_3'][i] : null,
                    };

                    if (rec.vueltas && !/^\d+$/.test(rec.vueltas.trim())) rec.vueltas = null;
                    if (rec.mejor_tiempo && !/[\d:]/.test(rec.mejor_tiempo.trim())) rec.mejor_tiempo = null;
                    if (rec.diferencia && !/[\d.]/.test(rec.diferencia.trim())) rec.diferencia = null;
                    if (rec.tiempo_total && !/[\d:]/.test(rec.tiempo_total.trim())) rec.tiempo_total = null;
                    
                    resultados.push(rec);
                }

            } else {
                for (let i = 0; i < fileInput.files.length; i++) {
                    const file = fileInput.files[i];
                    const worker = await Tesseract.createWorker('spa', 1, {
                        logger: m => {
                            if (m.status === 'recognizing text') {
                                console.log('Analizando foto OCR ' + (i + 1) + ' de ' + fileInput.files.length + '... ' + Math.round(m.progress * 100) + '%');
                            }
                        }
                    });
                    const ret = await worker.recognize(file);
                    let lines = ret.data.text.split('\n').map(L => L.trim().replace(/,/g, '.'));
                    extractedLines = extractedLines.concat(lines);
                    await worker.terminate();
                }

                let joinedText = extractedLines.slice(0, 30).join(' ').toLowerCase();
                let startIdx = joinedText.indexOf('nombre');
                if (startIdx === -1) startIdx = joinedText.indexOf('pos.');
                if (startIdx === -1) startIdx = 0;

                let headerText = joinedText.substring(startIdx);

                let colsConfig = [
                    { key: 'mejor_tiempo', pos: Math.max(headerText.indexOf('mejor tm'), headerText.indexOf('mejor tiempo')) },
                    { key: 'diferencia', pos: Math.max(headerText.indexOf('dif. resp. 1'), headerText.indexOf('dif.')) },
                    { key: 'dif_anterior', pos: Math.max(headerText.indexOf('resp. anterior'), headerText.indexOf('dif. ant')) },
                    { key: 'vueltas', pos: Math.max(headerText.indexOf('vueltas'), headerText.indexOf('laps')) },
                    { key: 'tiempo_total', pos: Math.max(headerText.indexOf('total t'), headerText.indexOf('tiempo total')) },
                    { key: 'sector_1', pos: headerText.indexOf('s1 mejor') },
                    { key: 'sector_2', pos: headerText.indexOf('s2 mejor') },
                    { key: 'sector_3', pos: headerText.indexOf('s3 mejor') }
                ];

                let expectedCols = colsConfig.filter(c => c.pos > -1).sort((a, b) => a.pos - b.pos).map(c => c.key);

                hasSectors = joinedText.includes('s1 mejor');
                hasTiempoTotal = joinedText.includes('total t') || joinedText.includes('tiempo total');

                if (expectedCols.length === 0 || !expectedCols.includes('vueltas')) {
                    let isCarrera = joinedText.includes('clasificado por vueltas') || joinedText.includes('carrera iniciado');

                    if (isCarrera) {
                        expectedCols = ['vueltas', 'tiempo_total', 'mejor_tiempo', 'diferencia', 'dif_anterior'];
                    } else if (hasSectors && hasTiempoTotal) {
                        expectedCols = ['mejor_tiempo', 'diferencia', 'vueltas', 'tiempo_total', 'sector_1', 'sector_2', 'sector_3'];
                    } else if (hasSectors && !hasTiempoTotal) {
                        expectedCols = ['mejor_tiempo', 'diferencia', 'vueltas', 'sector_1', 'sector_2', 'sector_3'];
                    } else if (hasTiempoTotal && !hasSectors) {
                        expectedCols = ['mejor_tiempo', 'diferencia', 'vueltas', 'tiempo_total'];
                    } else {
                        expectedCols = ['mejor_tiempo', 'diferencia', 'vueltas'];
                    }
                }

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

                            let rowCols = [...expectedCols];
                            
                            if (rec.posicion === '1') {
                                rowCols = rowCols.filter(c => c !== 'diferencia' && c !== 'dif_anterior');
                            }

                            while (middleTokens.length < rowCols.length) {
                                if (rowCols.includes('dif_anterior')) {
                                    rowCols = rowCols.filter(c => c !== 'dif_anterior');
                                } else if (rowCols.includes('diferencia')) {
                                    rowCols = rowCols.filter(c => c !== 'diferencia');
                                } else if (rowCols.includes('sector_3')) {
                                    rowCols = rowCols.filter(c => c !== 'sector_3');
                                } else if (rowCols.includes('sector_2')) {
                                    rowCols = rowCols.filter(c => c !== 'sector_2');
                                } else if (rowCols.includes('sector_1')) {
                                    rowCols = rowCols.filter(c => c !== 'sector_1');
                                } else {
                                    break;
                                }
                            }

                            for (let i = 0; i < Math.min(middleTokens.length, rowCols.length); i++) {
                                rec[rowCols[i]] = middleTokens[i];
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

            this.disabled = false;
        }
    });
</script>
@endpush
@endsection
