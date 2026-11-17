"""
Master Documentation Builder: CraftMarket (Academic Black & White Edition)
Complies with exact required structure:
- No cover page / No header / No footer
- Black & White styling throughout
- Comprehensive, in-depth academic Introduction
- System Requirements Specification (SRS):
  1. Introduction
  2. Objectives (General & Specific)
  3. Scope (In-Scope & Out-of-Scope)
  4. Functional Requirements (FR-01 to FR-13)
  5. Non-Functional Requirements (6 Pillars)
- System Design Documents (SDD):
  1. Use Case Diagram
  2. ER Diagram
  3. Context Diagram
  4. Architecture Diagram
  5. Database Schema (Data Dictionary)
  6. Wireframes
Output: c:\\CraftMarket\\CraftMarket_Documentation_SRS_and_SDD.docx
"""

import os
import sys
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import parse_xml
from docx.oxml.ns import nsdecls

OUTPUT_FILE = r"c:\CraftMarket\CraftMarket_Documentation_SRS_and_SDD.docx"
DIAGRAM_DIR = r"c:\CraftMarket\diagram_images"

def create_document():
    doc = docx.Document()

    # 1. Page Margins (1 inch all around)
    for section in doc.sections:
        section.top_margin = Inches(1.0)
        section.bottom_margin = Inches(1.0)
        section.left_margin = Inches(1.0)
        section.right_margin = Inches(1.0)
        section.different_first_page_header_footer = False
        
        # Explicitly ensure NO header and NO footer
        section.header.is_linked_to_previous = False
        section.footer.is_linked_to_previous = False
        for p in section.header.paragraphs:
            p.text = ""
        for p in section.footer.paragraphs:
            p.text = ""

    # Normal Style: Clean Black & White
    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Segoe UI'
    normal_style.font.size = Pt(10.0)
    normal_style.font.color.rgb = RGBColor(0, 0, 0)
    normal_style.paragraph_format.line_spacing = 1.15
    normal_style.paragraph_format.space_after = Pt(4)

    return doc

# Styling Helpers (Pure Black & White)
def set_cell_background(cell, fill_hex):
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    cell._tc.get_or_add_tcPr().append(shd)

def set_cell_padding(cell, top=100, bottom=100, left=140, right=140):
    tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
    cell._tc.get_or_add_tcPr().append(tcMar)

def add_styled_heading(doc, text, level):
    h = doc.add_heading(text, level=level)
    h.paragraph_format.keep_with_next = True
    run = h.runs[0]
    run.font.name = 'Segoe UI'
    run.font.color.rgb = RGBColor(0, 0, 0) # Pure Black
    run.font.bold = True
    if level == 1:
        run.font.size = Pt(16.0)
        h.paragraph_format.space_before = Pt(16)
        h.paragraph_format.space_after = Pt(6)
    elif level == 2:
        run.font.size = Pt(13.0)
        h.paragraph_format.space_before = Pt(12)
        h.paragraph_format.space_after = Pt(4)
    elif level == 3:
        run.font.size = Pt(11.0)
        h.paragraph_format.space_before = Pt(8)
        h.paragraph_format.space_after = Pt(2)
    return h

def add_p(doc, text, bold_prefix="", italic=False):
    p = doc.add_paragraph()
    p.paragraph_format.line_spacing = 1.15
    p.paragraph_format.space_after = Pt(4)
    if bold_prefix:
        r_pre = p.add_run(bold_prefix + " ")
        r_pre.bold = True
        r_pre.font.color.rgb = RGBColor(0, 0, 0)
    r = p.add_run(text)
    r.italic = italic
    r.font.color.rgb = RGBColor(0, 0, 0)
    return p

def add_bullet(doc, text, bold_prefix=""):
    p = doc.add_paragraph(style='List Bullet')
    p.paragraph_format.line_spacing = 1.15
    p.paragraph_format.space_after = Pt(3)
    if bold_prefix:
        r_pre = p.add_run(bold_prefix + ": ")
        r_pre.bold = True
        r_pre.font.color.rgb = RGBColor(0, 0, 0)
    r = p.add_run(text)
    r.font.color.rgb = RGBColor(0, 0, 0)
    return p

def add_callout(doc, text, title="NOTE"):
    table = doc.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = table.cell(0, 0)
    set_cell_padding(cell, top=120, bottom=120, left=160, right=160)
    set_cell_background(cell, "F5F5F5") # Light grayscale
    
    # Left thick black border
    borders = parse_xml(f'<w:tcBorders {nsdecls("w")}><w:left w:val="single" w:sz="24" w:space="0" w:color="000000"/><w:top w:val="none"/><w:right w:val="none"/><w:bottom w:val="none"/></w:tcBorders>')
    cell._tc.get_or_add_tcPr().append(borders)

    p = cell.paragraphs[0]
    p.paragraph_format.space_after = Pt(0)
    r_title = p.add_run(f"[{title}] ")
    r_title.bold = True
    r_title.font.size = Pt(9.5)
    r_title.font.color.rgb = RGBColor(0, 0, 0)
    
    r_text = p.add_run(text)
    r_text.font.size = Pt(9.5)
    r_text.font.color.rgb = RGBColor(0, 0, 0)
    doc.add_paragraph().paragraph_format.space_after = Pt(3)

def add_formatted_table(doc, col_widths, headers, rows_data, zebra=True):
    table = doc.add_table(rows=len(rows_data) + 1, cols=len(headers))
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.style = 'Table Grid'

    # Repeat header row & cantSplit
    header_tr = table.rows[0]._tr.get_or_add_trPr()
    header_tr.append(parse_xml(f'<w:tblHeader {nsdecls("w")}/>'))

    # Format Header Row (Black header, white text)
    for col_idx, header_text in enumerate(headers):
        cell = table.cell(0, col_idx)
        cell.width = col_widths[col_idx]
        set_cell_background(cell, "222222") # Dark black/charcoal
        set_cell_padding(cell, top=100, bottom=100, left=120, right=120)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.space_after = Pt(0)
        run = p.add_run(header_text)
        run.bold = True
        run.font.size = Pt(9.0)
        run.font.color.rgb = RGBColor(255, 255, 255) # White text for contrast

    # Format Data Rows
    for row_idx, row_data in enumerate(rows_data):
        tr = table.rows[row_idx + 1]._tr.get_or_add_trPr()
        tr.append(parse_xml(f'<w:cantSplit {nsdecls("w")}/>'))
        
        row_bg = "F9F9F9" if (zebra and row_idx % 2 == 1) else "FFFFFF"

        for col_idx, cell_value in enumerate(row_data):
            cell = table.cell(row_idx + 1, col_idx)
            cell.width = col_widths[col_idx]
            set_cell_background(cell, row_bg)
            set_cell_padding(cell, top=80, bottom=80, left=120, right=120)
            p = cell.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.LEFT
            p.paragraph_format.space_after = Pt(0)
            run = p.add_run(str(cell_value))
            run.font.size = Pt(8.5)
            run.font.color.rgb = RGBColor(0, 0, 0)

    doc.add_paragraph().paragraph_format.space_after = Pt(4)
    return table

def add_diagram(doc, image_path, caption, width=Inches(6.0)):
    if os.path.exists(image_path):
        p_img = doc.add_paragraph()
        p_img.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_img.paragraph_format.space_before = Pt(8)
        p_img.paragraph_format.space_after = Pt(4)
        run_img = p_img.add_run()
        run_img.add_picture(image_path, width=width)

        p_cap = doc.add_paragraph()
        p_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_cap.paragraph_format.space_after = Pt(10)
        r_cap = p_cap.add_run(caption)
        r_cap.font.size = Pt(9.0)
        r_cap.font.italic = True
        r_cap.font.color.rgb = RGBColor(50, 50, 50)
    else:
        add_callout(doc, f"Diagram image file missing at: {image_path}", "IMAGE NOTICE")

print("Black and white builder helper initialized.")
