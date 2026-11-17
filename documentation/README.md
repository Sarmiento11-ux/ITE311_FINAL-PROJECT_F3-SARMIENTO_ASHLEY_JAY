# CraftMarket — Documentation Index

This folder contains the complete system design and defense documentation for **CraftMarket**, the ITE311 final project of **Ashley Jay Sarmiento (BSIT F3)**.

---

## 1. Read in This Order

| # | Document | What it answers |
|---|---|---|
| 1 | [System Requirements Specification (SRS)](SRS/system-requirements.md) | What the system must do, with requirement IDs and acceptance criteria |
| 2 | [Use Case Diagram & Specifications](diagrams/use-case.md) | Who uses the system, what they can do, and the main/alternate flows |
| 3 | [Entity Relationship Diagram](diagrams/erd.md) | How data is structured, related, normalized, and protected |
| 4 | [Context Diagram (Level-0 DFD)](diagrams/context.md) | System boundary, external entities, data flows, trust boundaries |
| 5 | [System Architecture](diagrams/architecture.md) | Three-tier design, components, deployment, request lifecycle |
| 6 | [Database Schema](database/schema.sql) | Documentation-grade MySQL DDL aligned with migrations |
| 7 | [Wireframes & UI/UX Specification](diagrams/wireframes.md) | Screen layouts, design tokens, responsive behavior |
| 8 | [Business Model & Revenue Generation](business-model.md) | Pricing, unit economics, break-even, market, risks |
| 9 | [Security & Validation Controls](security/security-controls.md) | AuthN/AuthZ, validation, OWASP mapping, test evidence |
| 10 | [Final Defense Guide](presentation/defense-guide.md) | Demo script, rubric mapping, Q&A, slide outline |
| 11 | [Final Project Compliance Matrix](compliance-matrix.md) | Requirement A–G and rubric 1–7 mapping, CMS five functions, demo script |

---

## 2. Rubric Coverage

| Rubric criterion | Weight | Primary documents | Implementation evidence |
|---|---:|---|---|
| **System Functionality** | 30% | SRS §3, use case specs | Vue SPA + Laravel API, escrow lifecycle, analytics, payouts, reports |
| **UI/UX & Responsiveness** | 15% | Wireframes, SRS §4.4 | Responsive views, design tokens, loading/empty/error states |
| **Business Model & Revenue** | 15% | Business model | 8%/3% commission, ₱299 Pro, payout summary, MRR reporting |
| **Database Design** | 10% | ERD, schema.sql | Migrations, FKs, snapshot columns, transactions, indexes |
| **Security & Validation** | 10% | Security controls, SRS §4.1 | RBAC, rate limiting, signed webhooks, fail-closed CMS bridge with nonce-guarded write actions, 35 automated tests |
| **Documentation & System Design** | 10% | This entire folder | SRS, use case, ERD, context, architecture, schema, wireframes |
| **Presentation & Defense** | 10% | Defense guide | Demo script, rubric talking points, Q&A bank |

---

## 3. Documentation Map

```text
documentation/
├── README.md                          ← you are here
├── SRS/
│   └── system-requirements.md         ← functional + non-functional requirements
├── diagrams/
│   ├── use-case.md                    ← actors, use case diagram, 22 specifications
│   ├── erd.md                         ← Mermaid ERD + integrity/normalization notes
│   ├── context.md                     ← Level-0 DFD + external entities + trust boundaries
│   ├── architecture.md                ← layered architecture, components, deployment
│   └── wireframes.md                  ← screen layouts + responsive matrix
├── database/
│   └── schema.sql                     ← MySQL DDL snapshot
├── business-model.md                  ← revenue, unit economics, break-even, risks
├── compliance-matrix.md               ← requirement A–G + rubric 1–7 mapping
├── security/
│   └── security-controls.md           ← controls + OWASP Top 10 mapping
└── presentation/
    └── defense-guide.md               ← demo script + Q&A
```

---

## 4. Source of Truth

| Artifact | Canonical source |
|---|---|
| Database schema | `backend/database/migrations/` |
| API routes | `backend/routes/api.php` |
| Business rules | `backend/app/Http/Controllers/Api/` |
| Demo data | `backend/database/seeders/` |
| UI behavior | `frontend/src/views/` and `frontend/src/services/` |
| Automated tests | `backend/tests/Feature/` |

The ERD and `schema.sql` are documentation snapshots. When they disagree with the migrations, **trust the migrations**.

---

## 5. How to Verify the Documentation

```powershell
# 1. Rebuild the database from migrations
cd backend
php artisan migrate:fresh --seed

# 2. Run the feature tests
php artisan test

# 3. Build the SPA
cd ../frontend
npm run build
```

| Suite | Expected |
|---|---|
| `OrderEscrowTest` | 5 passed |
| `SellerPayoutAndVerificationTest` | 4 passed |
| `SecurityAndAuthorizationTest` | 9 passed |
| `PhaseCFeatureTest` | 4 passed |
| `CmsBridgeTest` | 13 passed |
| `SandboxPaymentSimulationTest` | 5 passed |
| **Total** | **47 passed / 261 assertions** |
| Frontend build | Success (chunk-size warning acceptable) |

---

## 6. Key Design Decisions (Quick Reference)

1. **Decoupled SPA + REST API** — independent deployment, mobile-ready contract.
2. **Sanctum bearer tokens** — stateless API auth with 7-day expiry and revocation on privilege change.
3. **Server-side money** — the API recomputes and snapshots prices, commission, shipping, and payouts.
4. **Escrow per line item** — multi-seller orders release independently on delivery.
5. **Verification-gated catalog** — unapproved storefronts are invisible in list, filters, and detail.
6. **Admin oversight endpoint** — moderators see everything without weakening the public rule.
7. **Safe role management** — no self-demotion, no self-deactivation, no last-admin demotion.
8. **Two revenue streams** — 8%/3% commission plus ₱299 Artisan Pro.
9. **Fail-closed CMS bridge** — shared secret, constant-time comparison, `503` when unconfigured.
10. **Least-privilege CMS writes** — WordPress can moderate product status, manage category taxonomy, approve/reject storefronts, and activate/deactivate accounts, with nonces and capability checks.
11. **Documentation as code** — Mermaid diagrams versioned beside the implementation.

---

## 7. Maintenance Notes

- Update the ERD and `schema.sql` in the same commit as any migration change.
- Keep requirement IDs stable; append new ones instead of renumbering.
- Update the defense guide whenever a demo route or seeded account changes.
- Re-run `php artisan test` and `npm run build` before every push.
- Rotate `CMS_BRIDGE_KEY` and payment secrets before any public deployment.
