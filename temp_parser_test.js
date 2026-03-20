const extractedLines = [
    "GRAN PREMIO ' MICHELIN' Clasificado por vueltas",
    "CAT. TP.1100 MAR Y VALLE TW CORTO 2.747 km",
    "1ER SERIE 15/11/2025 14:25",
    "Carrera iniciado a 15:09:13",
    "Pos. N° Nombre Vueltas Total T° Mejor Tm Dif. resp. 1° if. resp. anterior S1 Mejor S2 Mejor S3 Mejor",
    "1 7 VALLEJOS FRANCO 6 7:51.865 1:17.940 29.430 23.199 25.211",
    "2 23 MARZOLI ROBERTO 6 7:52.753 1:17.686 0.888 0.888 28.894 23.153 25.315",
    "13 86 LARREBURO ARIEL 5 7:36.130 1:23.425 1 Vuelta 1 Vuelta 30.792 23.667 25.691",
    "14 20 CARUGO VALENTINO 4 8:54.433 1:21.007 2 Vueltas 1 Vuelta 30.044 24.535 25.900",
    "No clasificado",
    "NT 19 VILLAR SANTIAGO 3 4:10.097 1:18.040 NT 29.360 23.368 25.210",
    "NT 23 RIVA CRISTIAN" // Another edge case where no laps were completed? Or we just assume the image format
];

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
    
    // Buscar líneas que empiecen con numero de posicion 1-99 o "NT"
    if (/^\s*(\d{1,2}|nt)\s+/i.test(line)) {
        let parts = line.split(/\s+/);
        if (parts.length >= 4) {
            const rec = {
                posicion: parts[0].toLowerCase() === 'nt' ? 'NT' : parts[0],
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

                // Lo que queda en el medio son tiempos, vueltas y diferencias
                let middleTokens = [];
                // Special case for 'NT' or 'X Vuelta(s)':
                // If we see "NT", "1 Vuelta", "2 Vueltas", these belong to differences.
                while (cursor >= 2) {
                    let p = parts[cursor];
                    if (/[\d:]/.test(p) || p.toLowerCase() === 'vueltas' || p.toLowerCase() === 'laps' || p.toLowerCase() === 'vuelta' || p.toLowerCase() === 'nt' || p.toLowerCase() === 'vuel') {
                        if (p.toLowerCase() !== 'vueltas' && p.toLowerCase() !== 'laps' && p.toLowerCase() !== 'vuelta' && p.toLowerCase() !== 'vuel') {
                            middleTokens.unshift(p);
                        } else {
                            // If p is "Vueltas" and previous is a number (like "2"), we might want to capture "2 Vueltas" as difference, or just "2"
                            if (cursor >= 1 && /^\d+$/.test(parts[cursor - 1]) && parts[cursor - 1].length <= 2) {
                                // Probably difference "X Vueltas"
                                // We'll just push the number to middleTokens, and we can infer "Vueltas" later, or push "X Vueltas"
                                middleTokens.unshift(parts[cursor - 1] + " Vueltas");
                                cursor--; // Skip the number
                            }
                        }
                        cursor--;
                    } else {
                        break;
                    }
                }

                console.log("Middle Tokens for", parts.slice(0, 3).join(' '), ":", middleTokens);

                // middleTokens: [Vueltas, Tiempo Total, Mejor Tm, Dif (opt)] wait what is the order for Serie?
                // Vueltas | Total Tm | Mejor Tm | Dif | Dif Anterior
                // Data row 1: 1 7 VALLEJOS FRANCO 6 7:51.865 1:17.940 (only 3 tokens: 6, 7:51.865, 1:17.940)
                // Data row 2: 2 23 MARZOLI ROBERTO 6 7:52.753 1:17.686 0.888 0.888 (5 tokens: 6, 7:52.753, 1:17.686, 0.888, 0.888)
                // Data row 13: 13 86 LARREBURO ARIEL 5 7:36.130 1:23.425 1 Vuelta 1 Vuelta (5 tokens since we grouped "1 Vueltas")
                // Data row 16: NT 19 VILLAR SANTIAGO 3 4:10.097 1:18.040 NT (4 tokens: 3, 4:10.097, 1:18.040, NT)

                if (hasTiempoTotal) {
                    // It's a Serie/Final WITH sectors
                    // Order is supposed to be: Vueltas, Tiempo Total, Mejor Tm, Dif Resp 1, Dif Resp Ant
                    if (middleTokens.length > 0) rec.vueltas = middleTokens[0];
                    if (middleTokens.length > 1) rec.tiempo_total = middleTokens[1];
                    if (middleTokens.length > 2) rec.mejor_tiempo = middleTokens[2];
                    if (middleTokens.length > 3) rec.diferencia = middleTokens[3]; // We just capture the first difference (to first place)
                } else {
                    // Clasificacion
                    if (middleTokens.length > 0 && /^\d+$/.test(middleTokens[middleTokens.length - 1])) {
                        rec.vueltas = middleTokens.pop(); 
                    }
                    if (middleTokens.length > 0) {
                        rec.mejor_tiempo = middleTokens[0];
                    }
                    // logic for dif...
                }
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
                nameParts.push(apellido); 
            }
            rec.nombre = nameParts.join(' ');

            if (rec.nombre && rec.nombre.length > 2 && rec.nombre !== "No Clasificado") {
                resultados.push(rec);
            }
        }
    }
}

console.log(resultados);
