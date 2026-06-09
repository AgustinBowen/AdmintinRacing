@extends('layouts.selector')
@section('title', 'Importar Pilotos')

@section('content')
<section class="screen active" id="pdfUpload" style="display:flex;">
    <div class="form-top">
        <a href="{{ route('admin.pilotos.index') }}" class="back-link">&larr; Volver a Pilotos</a>
        <div class="tag">Paso 1 de 2</div>
    </div>
    <div class="pdf-body">
        <div class="pdf-intro">
            <h1>Importar Resultados</h1>
            <p>Subí el archivo PDF con los resultados oficiales. Extraeremos automáticamente los nombres de los pilotos para cargarlos en el sistema.</p>
        </div>
        
        <div class="dropzone" id="dzArea" onclick="document.getElementById('pdf_file').click()">
            <svg class="dz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 12 15 15"/></svg>
            <div class="dz-title">Hacé clic para subir el PDF</div>
            <div class="dz-hint">PDF con columnas: Orden, Auto, Piloto</div>
            <input type="file" id="pdf_file" accept=".pdf" style="display:none;">
        </div>

        <div class="dz-file" id="dzFile">
            <div class="filebadge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span id="dzName">resultado.pdf</span>
            </div>
            <button class="btn sm ghost" onclick="resetFile(event)">Quitar</button>
        </div>
        
        <div id="loadingStatus" class="mt-3 text-center d-none" style="margin-top:20px; color:var(--gray);">
            <i class="fas fa-spinner fa-spin me-2"></i> Analizando el documento PDF, por favor espera...
        </div>
        
        <div id="errorStatus" class="mt-3 text-center d-none" style="margin-top:20px; color:var(--racing);">
        </div>

        <div class="pdf-actions">
            <button type="button" id="btnProcesarPdf" class="btn" style="opacity:0.5; pointer-events:none;">Siguiente Paso &#9656;</button>
        </div>
    </div>
</section>

<!-- Formulario Oculto para enviar el resultado a Preview -->
<form id="previewForm" action="{{ route('admin.pilotos.import.preview') }}" method="POST" class="d-none">
    @csrf
    <textarea name="pilotos_json" id="hidden_pilotos_json" style="display:none;"></textarea>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    document.getElementById('pdf_file').addEventListener('change', function(e) {
        if(this.files && this.files.length > 0) {
            document.getElementById('dzArea').style.display = 'none';
            document.getElementById('dzFile').classList.add('show');
            document.getElementById('dzName').textContent = this.files[0].name;
            const btn = document.getElementById('btnProcesarPdf');
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        }
    });

    function resetFile(e) {
        e.stopPropagation();
        document.getElementById('pdf_file').value = '';
        document.getElementById('dzFile').classList.remove('show');
        document.getElementById('dzArea').style.display = 'block';
        const btn = document.getElementById('btnProcesarPdf');
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';
    }

    document.getElementById('btnProcesarPdf').addEventListener('click', async function() {
        const fileInput = document.getElementById('pdf_file');
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
                    
                    for (let i = 1; i <= pdf.numPages; i++) {
                        const page = await pdf.getPage(i);
                        const textContent = await page.getTextContent();
                        
                        let textItems = textContent.items;
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
                        
                        const match = line.match(/^\s*(\d+)\s+(\d+)\s+([^0-9]+?)(?=\s+\d|$)/);
                        
                        if (match) {
                            const orden = match[1].trim();
                            const auto = match[2].trim();
                            
                            let pilotoRaw = match[3].replace(/\s+/g, ' ').trim().toLowerCase();
                            
                            let words = pilotoRaw.split(' ');
                            words = words.map(w => w.charAt(0).toUpperCase() + w.slice(1));
                            
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
                        throw new Error("No se detectaron pilotos con el formato esperado (Orden - Auto - Piloto).");
                    }

                    document.getElementById('hidden_pilotos_json').value = JSON.stringify(pilotosDetectados);
                    document.getElementById('previewForm').submit();

                } catch (err) {
                    console.error(err);
                    error.innerText = "No se pudo leer el contenido del PDF o " + err.message;
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
@endsection
