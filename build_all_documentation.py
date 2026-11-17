"""
Master Documentation Builder: CraftMarket SRS & SDD
Orchestrates:
1. Document initialization & Page layout (1-inch margins, headers, footers)
2. Cover Page & Executive Summary
3. PART 1: SRS (Introduction, Objectives, Scope, 13 Functional Subsystems, 6 NFR Pillars)
4. PART 2: SDD (Context Diagram, Architecture Diagram, Use Case Diagram, ER Diagram,
   Data Dictionary of 11 Tables, 4 Live GUI Screenshots, 4 Wireframe Blueprints,
   Commission & Escrow Algorithms, 29/29 Test Results, Conclusion)
Output: c:\\CraftMarket\\CraftMarket_Documentation_SRS_and_SDD.docx
"""

import sys
import os
import time

from build_comprehensive_docx import create_document
from doc_cover_and_intro import build_cover_page, build_executive_summary_and_toc, build_srs_introduction_and_scope
from doc_srs_requirements import build_functional_requirements, build_non_functional_requirements
from doc_sdd_design import (
    build_sdd_diagrams_and_architecture,
    build_data_dictionary,
    build_gui_and_wireframes,
    build_algorithms_and_test_results
)

OUTPUT_FILE = r"c:\CraftMarket\CraftMarket_Documentation_SRS_and_SDD.docx"

def main():
    print("=" * 60)
    print("CraftMarket: Generating Comprehensive SRS & SDD Document (.docx)")
    print("Author: Ashley Jay Sarmiento | BSIT-F3 | ITE311")
    print("=" * 60)

    start_time = time.time()
    doc = create_document()

    print("[1/8] Generating Cover Page...")
    build_cover_page(doc)

    print("[2/8] Generating Executive Summary & Project Charter...")
    build_executive_summary_and_toc(doc)

    print("[3/8] Generating SRS Introduction, Objectives & Scope...")
    build_srs_introduction_and_scope(doc)

    print("[4/8] Generating 13 Functional Subsystems (FR-01 to FR-13)...")
    build_functional_requirements(doc)

    print("[5/8] Generating 6 Non-Functional Requirement Pillars...")
    build_non_functional_requirements(doc)

    print("[6/8] Generating SDD Context, Architecture, Use Case & ER Diagrams...")
    build_sdd_diagrams_and_architecture(doc)

    print("[7/8] Generating Complete Data Dictionary (All 11 Tables)...")
    build_data_dictionary(doc)

    print("[8/8] Generating Live GUIs, Wireframes, Algorithms & Test Suite Results...")
    build_gui_and_wireframes(doc)
    build_algorithms_and_test_results(doc)

    print(f"Saving finalized Word document to: {OUTPUT_FILE}...")
    doc.save(OUTPUT_FILE)

    elapsed = time.time() - start_time
    file_size_mb = os.path.getsize(OUTPUT_FILE) / (1024 * 1024)
    print(f"SUCCESS: Document generated in {elapsed:.2f} seconds.")
    print(f"File size: {file_size_mb:.2f} MB ({os.path.getsize(OUTPUT_FILE):,} bytes)")
    print("=" * 60)

if __name__ == "__main__":
    main()
