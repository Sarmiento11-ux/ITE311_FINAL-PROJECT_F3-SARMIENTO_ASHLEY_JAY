"""
CraftMarket Documentation - Part 1: Cover, Executive Summary, Introduction & Objectives
"""

import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from build_comprehensive_docx import (
    add_styled_heading, add_p, add_bullet, add_callout, add_formatted_table
)

def build_cover_page(doc):
    # Spacing from top
    for _ in range(3):
        doc.add_paragraph()

    p_super = doc.add_paragraph()
    p_super.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_super = p_super.add_run("TECHNICAL SPECIFICATION & ARCHITECTURAL BLUEPRINT")
    r_super.font.name = "Segoe UI"
    r_super.font.size = Pt(11.0)
    r_super.font.bold = True
    r_super.font.color.rgb = RGBColor(0x2E, 0x53, 0x39)
    p_super.paragraph_format.space_after = Pt(12)

    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_title = p_title.add_run("CRAFTMARKET")
    r_title.font.name = "Segoe UI"
    r_title.font.size = Pt(36.0)
    r_title.font.bold = True
    r_title.font.color.rgb = RGBColor(0x1E, 0x3A, 0x2F)
    p_title.paragraph_format.space_after = Pt(8)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_sub = p_sub.add_run("System Requirements Specification (SRS) &\nSystem Design Documentation (SDD)")
    r_sub.font.name = "Segoe UI"
    r_sub.font.size = Pt(15.0)
    r_sub.font.color.rgb = RGBColor(0x4A, 0x55, 0x68)
    p_sub.paragraph_format.space_after = Pt(36)

    # Decorative line
    add_callout(
        doc,
        "An Integrated Multi-Vendor E-Commerce Platform, Escrow Settlement Vault, "
        "and Decoupled Headless Content Management Ecosystem for Verified Philippine Artisans.",
        "PROJECT CHARTER",
        "default"
    )

    for _ in range(3):
        doc.add_paragraph()

    # Metadata Block
    col_w = [Inches(2.5), Inches(3.5)]
    headers = ["Metadata Attribute", "Project Specification Details"]
    meta_rows = [
        ["Academic Course", "ITE311 — Web Systems & Tech 2 / System Integration & Architecture"],
        ["Lead Author / Developer", "Ashley Jay Sarmiento"],
        ["Degree & Section", "Bachelor of Science in Information Technology — BSIT-F3"],
        ["Academic Year / Term", "Academic Year 2026–2027 | First Semester"],
        ["Documentation Version", "Version 3.0 (Comprehensive Defense & Production Edition)"],
        ["System Repository", "Local Git / Monorepo Architecture (CraftMarket)"],
        ["Primary Tech Stack", "Laravel 12 (PHP 8.2) + Vue 3 (Vite, Pinia) + MySQL 8 + PayMongo + WordPress"],
        ["Automated Test Suite", "29 Feature Tests Passing (164 Assertions, 100% Pass Rate)"]
    ]
    add_formatted_table(doc, col_w, headers, meta_rows, header_bg="1E3A2F")

    doc.add_page_break()

def build_executive_summary_and_toc(doc):
    add_styled_heading(doc, "Executive Summary", level=1)
    
    add_p(
        doc,
        "CraftMarket is an enterprise-grade, multi-vendor electronic marketplace engineered specifically "
        "to solve the historical structural barriers faced by independent Filipino artisans, handicraft makers, "
        "and indigenous workshop collectives. In the traditional retail and generic social-commerce landscape, "
        "grassroots creators suffer from prohibitive intermediary commissions (often 15% to 30%), chronic "
        "mistrust from consumers regarding delivery authenticity, lack of bespoke negotiation channels, and "
        "vulnerability to payment default or dispute fraud.",
        bold_prefix="Market Opportunity & Context:"
    )

    add_p(
        doc,
        "To decisively overcome these friction points, CraftMarket delivers a decoupled multi-tier architectural "
        "ecosystem featuring four core technical innovations:",
        bold_prefix="Architectural Solution:"
    )

    add_bullet(
        doc,
        "Separates administrative editorial publishing from high-throughput transaction pipelines. "
        "Promotional bulletins, artisan highlights, and craft education blogs are published via WordPress "
        "and ingested asynchronously via REST API into Vue 3, completely insulating database commerce tables "
        "from content editor attacks or CMS downtime.",
        bold_prefix="1. Decoupled Headless WordPress CMS"
    )

    add_bullet(
        doc,
        "Buyer payments made via PayMongo Sandbox (GCash, Maya, Cards) are not transferred directly to sellers upon checkout. "
        "Instead, funds are sequestered in an automated database-level escrow ledger. Funds are unlocked for artisan cash-out "
        "only when the buyer confirms delivery inspection or after a 7-day auto-settlement window expires.",
        bold_prefix="2. Automated Escrow Settlement Vault"
    )

    add_bullet(
        doc,
        "Eliminates delivery disputes through a two-party cryptographic handover mechanism. When couriers dispatch orders, "
        "a 6-digit verification token and scannable QR payload are issued. Buyers verify receipt upon physical parcel inspection, "
        "updating the order state machine to 'delivered' and unlocking seller escrow balance.",
        bold_prefix="3. Cryptographic QR Logistics Dispatch Handover"
    )

    add_bullet(
        doc,
        "Offers a transparent dual-tier structure: Free Artisan tier (8% marketplace fee) and Artisan Pro subscription "
        "(3% discounted fee at ₱299/mo or ₱2,990/yr with featured storefront placement). Sellers can initiate structured payout "
        "requests funded strictly by released escrow, subject to rigorous administrative audit and mathematical verification.",
        bold_prefix="4. Sustainable Monetization & Payout Verification Engine"
    )

    add_callout(
        doc,
        "This document constitutes the authoritative technical baseline for the CraftMarket platform. "
        "It combines the formal functional and non-functional specifications (SRS) with the complete physical "
        "system architecture, database data dictionary, wireframes, user interfaces, algorithms, and automated test reports (SDD).",
        "DOCUMENT PURPOSE & SCOPE",
        "success"
    )

    doc.add_page_break()

def build_srs_introduction_and_scope(doc):
    add_styled_heading(doc, "PART 1: SYSTEM REQUIREMENTS SPECIFICATION (SRS)", level=1)
    
    add_styled_heading(doc, "1. Introduction & Overview", level=2)
    
    add_styled_heading(doc, "1.1 Document Purpose", level=3)
    add_p(
        doc,
        "The purpose of this System Requirements Specification (SRS) is to establish a rigorous, unambiguous, "
        "and complete definition of the software requirements for the CraftMarket platform. It serves as the primary "
        "contract between stakeholders, project evaluators, system architects, and test engineers for the ITE311 "
        "capstone defense."
    )

    add_styled_heading(doc, "1.2 Document Conventions & RFC Standards", level=3)
    add_p(
        doc,
        "The key words 'MUST', 'MUST NOT', 'REQUIRED', 'SHALL', 'SHALL NOT', 'SHOULD', 'SHOULD NOT', "
        "'RECOMMENDED', 'MAY', and 'OPTIONAL' in this document are to be interpreted as described in IETF RFC 2119. "
        "In this technical specification:"
    )
    add_bullet(doc, "Identifies mandatory criteria essential for baseline academic compliance, security integrity, and transaction correctness.", "MUST / SHALL")
    add_bullet(doc, "Identifies architectural best practices, performance targets, and scalability recommendations.", "SHOULD")
    add_bullet(doc, "Identifies optional capabilities or planned iterations reserved for future project milestones.", "MAY")

    add_styled_heading(doc, "1.3 Target Audience & Stakeholder Matrix", level=3)
    col_w = [Inches(1.8), Inches(2.2), Inches(2.0)]
    headers = ["Stakeholder Role", "Platform Responsibilities", "Primary Technical Touchpoints"]
    stakeholders_data = [
        [
            "System Administrator",
            "Marketplace oversight, artisan verification approval/rejection, payout request auditing, category management, and CMS integration monitoring.",
            "Admin Dashboard SPA (`/admin`), REST API endpoints (`/api/admin/*`), Sanctum Token Auth, CSV audit export."
        ],
        [
            "Artisan / Seller",
            "Workshop profile management, craft inventory publishing, custom order negotiation, order fulfillment, QR dispatch, and payout requests.",
            "Seller Dashboard SPA (`/seller/dashboard`), Product CRUD modals, Logistics dispatch stepper, Financial cash-out pipeline."
        ],
        [
            "Customer / Shopper",
            "Product discovery, faceted catalog filtering, shopping cart, sandbox payment execution, order tracking, printable tax receipts, and reviews.",
            "Storefront Views (`/products`, `/cart`, `/checkout`), Customer Dashboard (`/dashboard`), Review submission modal."
        ],
        [
            "Academic Panel Evaluators",
            "Verification of system compliance, architecture decoupling, test pass rates, database normal forms, and security safeguards.",
            "SRS/SDD Documentation, Automated PHPUnit feature test suite (29 tests), Live code inspection, ER schema."
        ]
    ]
    add_formatted_table(doc, col_w, headers, stakeholders_data, header_bg="2E5339")

    add_styled_heading(doc, "2. System Objectives", level=2)
    add_styled_heading(doc, "2.1 General Objective", level=3)
    add_p(
        doc,
        "To architect, develop, test, and deploy a secure, decoupled, and highly reliable multi-vendor e-commerce "
        "marketplace tailored to Philippine artisan enterprises, incorporating headless CMS synchronization, "
        "automated escrow financial settlements, and end-to-end QR logistics tracking."
    )

    add_styled_heading(doc, "2.2 Specific Objectives", level=3)
    add_bullet(doc, "Implement a verified onboarding pipeline where sellers upload business and craft credentials before publishing listings.", "Obj 1: Verified Artisan Workshop Storefronts")
    add_bullet(doc, "Integrate an external WordPress headless instance via REST API to consume announcements and educational craft stories with zero coupling to transactional data.", "Obj 2: Decoupled Headless CMS Integration")
    add_bullet(doc, "Enforce an automated database escrow ledger that locks buyer payments until confirmed physical parcel receipt or 7-day auto-release.", "Obj 3: Automated Escrow & Commission Engine")
    add_bullet(doc, "Equip sellers and couriers with a 6-digit cryptographic verification code and QR dispatch workflow to eliminate false delivery claims.", "Obj 4: Cryptographic QR Logistics Handover")
    add_bullet(doc, "Provide real-time sales reporting and 13-column financial CSV data export for sellers and platform administrators.", "Obj 5: Data Auditability & CSV Export")
    add_bullet(doc, "Enable direct customer-to-artisan custom craft inquiry dialogs with material preferences, lead times, and budget constraints.", "Obj 6: Bespoke Craft Negotiation Channel")
    add_bullet(doc, "Provide a dual-tier monetization engine (Free 8% vs Artisan Pro 3% subscription) with full accounting auditability.", "Obj 7: Sustainable Monetization & Payout Verification")

    add_styled_heading(doc, "3. System Scope & Boundaries", level=2)
    add_styled_heading(doc, "3.1 In-Scope Capabilities", level=3)
    add_p(doc, "The CraftMarket production release strictly incorporates the following functional boundaries:")
    add_bullet(doc, "Role-Based Access Control (RBAC) across three distinct roles: Admin, Seller, and Customer, enforced via Laravel Sanctum middleware and Pinia state guards.", "Access Control")
    add_bullet(doc, "Full catalog discovery featuring debounced live search, category taxonomy, origin/region filtering, price range sliders, and stock status filters.", "Catalog Engine")
    add_bullet(doc, "Real-time client cart management with synchronized backend checkout validation, order total calculations, and shipping fee logic.", "Cart & Checkout")
    add_bullet(doc, "PayMongo Sandbox payment simulation supporting GCash, Maya, and Credit/Debit cards with webhook event simulation.", "Payment Gateway")
    add_bullet(doc, "5-stage visual delivery tracker: Pending -> Processing -> Shipped -> In Transit -> Delivered, complemented by printable PDF-grade HTML invoices.", "Logistics Tracker")
    add_bullet(doc, "Artisan Pro subscription management with annual and monthly billing simulation and immediate commission discount application.", "Subscriptions")
    add_bullet(doc, "Strictly verified customer review engine allowing 1-5 star ratings and reviews only on delivered orders associated with the authenticated buyer.", "Verified Reviews")

    add_styled_heading(doc, "3.2 Out-of-Scope Capabilities (Future Enhancements)", level=3)
    add_p(doc, "The following features are formally documented as architectural extensions for subsequent releases:")
    add_bullet(doc, "Direct real-time courier API polling (e.g., Lalamove, J&T Express live GPS tracking); fulfillment currently relies on internal QR dispatch state transitions.", "External Couriers")
    add_bullet(doc, "Multi-currency cross-border exchange rate conversions; all monetary transactions are natively denominated in Philippine Peso (PHP / ₱).", "Foreign Currencies")
    add_bullet(doc, "Native compiled mobile applications (iOS Swift / Android Kotlin); system is architected as an ultra-responsive progressive web application (PWA).", "Native Mobile Apps")
    add_bullet(doc, "Integrated live voice or video consultation; custom inquiries are managed through structured in-app inquiry modals.", "Live Video Consultation")

    doc.add_page_break()

print("Cover, Executive Summary, Introduction & Scope module loaded.")
