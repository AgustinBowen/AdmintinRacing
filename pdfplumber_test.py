import pdfplumber
import pandas as pd
import codecs

def analyze_pdf():
    with pdfplumber.open('Pista-Planillaje-2026-01-TP1100.pdf') as pdf:
        with codecs.open('pdf_tables.md', 'w', 'utf-8') as f:
            for i, page in enumerate(pdf.pages):
                tables = page.extract_tables()
                if tables:
                    f.write(f"## Page {i+1}\n")
                    for t_idx, table in enumerate(tables):
                        # Clean out None and newlines
                        clean_table = [[str(cell).replace('\n', ' ') if cell is not None else '' for cell in row] for row in table]
                        if not clean_table: continue
                        
                        df = pd.DataFrame(clean_table[1:], columns=clean_table[0])
                        f.write(f"### Table {t_idx+1}\n")
                        f.write(df.head(10).to_markdown(index=False))
                        f.write("\n\n")

if __name__ == '__main__':
    analyze_pdf()
