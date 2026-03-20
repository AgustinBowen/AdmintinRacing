import PyPDF2
import codecs
import json

def parse_pdf():
    reader = PyPDF2.PdfReader('Pista-Planillaje-2026-01-TP1100.pdf')
    results = []
    for i, page in enumerate(reader.pages):
        text = page.extract_text()
        lines = [L.strip() for L in text.splitlines() if L.strip()]
        results.append(lines[:30]) # get first 30 lines to see structure

    with codecs.open('pdf_structure.json', 'w', 'utf-8') as f:
        json.dump(results, f, indent=2, ensure_ascii=False)

if __name__ == '__main__':
    parse_pdf()
