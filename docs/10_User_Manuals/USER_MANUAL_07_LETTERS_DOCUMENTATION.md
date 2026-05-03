# CREAMS User Manual — Letters & Documentation

**Version**: 2.0 (re-baselined 2 May 2026)
**Audience**: Admin and Supervisor users
**Supersedes**: Version 1.0 (deprecated — claimed Digital Signature Integration, Compliance Tracking, and Automated Report Generation features that are not implemented)

---

## 1. What this module does

The Letters module lets authorised staff:

- Manage **letter templates** with reusable headers, footers, and variable placeholders
- **Generate official letters** (PDFs) from templates by filling in variable values
- **Download** generated letters as PDFs
- View an **archive / history** of previously generated letters

Two letter generators exist in the codebase:
- **Modern letter generator** (`Letters\ModernLetterGeneratorController` and `Letters\ModernLetterController`) — the current path, accessed at `/letters/modern/`
- **Legacy generator** (`/letters-old/`) — kept for backwards compatibility, do not use for new work

The module does **not** include digital signature integration, compliance tracking, or scheduled report automation. Those are valid future features but not implemented today.

---

## 2. Per-role permissions

| Action | Admin | Supervisor | Teacher | AJK |
|---|---|---|---|---|
| Manage letter templates | Yes | No | No | No |
| Generate letters | Yes | Yes (own centre) | Limited | No |
| View letter history | All centres | Own centre | Own letters | No |
| Download a generated letter | Yes | Yes (own centre) | Own letters | No |
| Delete a letter | Yes | No | No | No |

---

## 3. Schemas

### `letter_templates` table

| Field | Notes |
|---|---|
| `template_name` | Required, unique within centre |
| `template_description` | What the template is for |
| `template_type` | Category: `enrollment`, `progress`, `discharge`, `general`, etc. |
| `template_content` | The body text with `{{placeholder}}` variables |
| `template_variables` | JSON list of required variables, e.g. `["trainee_name", "date", "signed_by"]` |
| `header_image_path` | Optional letterhead image |
| `footer_image_path` | Optional footer image |
| `header_text` | Optional header HTML |
| `footer_text` | Optional footer HTML |
| `centre_id` | Owning centre |
| `is_active` | True = available for use |
| `usage_count` | Auto-incremented when used |

### `letters` table (one row per generated letter)

| Field | Notes |
|---|---|
| `letter_id` | Human-readable ID, e.g. `L-2026-0042` |
| `letter_title`, `letter_subject` | Free text |
| `letter_content` | Final rendered content |
| `recipient_*` | Name, email, address, type |
| `template_id` | Source template |
| `pdf_path`, `pdf_filename`, `pdf_file_size` | Generated PDF artefact |
| `is_sent`, `sent_at` | Tracking — sending integration is manual today (download then email externally) |
| `created_by`, `generated_by` | Who created the template draft and who generated the final letter |
| `centre_id` | Owning centre |

---

## 4. Common workflows

### Admin: create a template

1. Log in as Admin.
2. Navigate to `/admin/letter-templates`.
3. Click **Add new template**.
4. Fill in:
   - **Template name** (must be unique within centre)
   - **Type** (enrollment, progress, discharge, general)
   - **Header / footer** (optional images and HTML)
   - **Body** with `{{variable}}` placeholders, e.g. `Dear {{guardian_name}}, This letter is to confirm that {{trainee_name}} ...`
   - **Required variables** — declare what placeholders the template uses
5. Save. The template appears in `/admin/letter-templates` with `usage_count = 0`.

### Supervisor or Admin: generate a letter from a template

1. Navigate to `/letters/modern/create` (or top nav: Letters → New Letter).
2. Select a template.
3. The form shows the template's required variables — fill in:
   - Recipient name, email, address
   - Trainee/staff variables as defined by the template
   - Date (defaults to today)
4. Click **Preview** to render the letter inline.
5. Click **Generate PDF**. The PDF is saved to `storage/app/letters/` with a path recorded in the `letters` table.
6. The letter appears in the history with status `generated`.

### Download a generated letter

1. Open the letter from `/admin/letters` or `/letters` (centre-scoped).
2. Click **Download**. A PDF download starts.

### Send a letter externally

There is no built-in email send. Workflow today: download the PDF, attach to an email in your normal mail client, send. Mark the letter as sent manually if you want to track it.

---

## 5. Variable placeholders

Templates use `{{variable_name}}` syntax. Common variables registered in the schema's `template_variables` JSON:

- `trainee_name`, `trainee_id`
- `guardian_name`, `guardian_email`
- `date`, `today` (auto-fills if not provided)
- `centre_name`, `centre_address`
- `signed_by`, `signed_role`

You can declare custom variables per template. The form auto-renders an input field per declared variable.

---

## 6. Letter history and archive

Two views:

- `/letters` — letters relevant to the current user (centre-scoped)
- `/admin/letters/history` — full admin view across all centres
- `/letters-archive` — older letters past a certain age (configurable)

History supports filtering by template, recipient, date range, and status.

---

## 7. PDF generation

Letters are rendered to PDF via DomPDF (`barryvdh/laravel-dompdf`). HTML/CSS in template content should stay simple — DomPDF does not support modern CSS Grid, Flexbox, or JavaScript. Use tables for layout in custom HTML.

---

## 8. What is NOT implemented

- **Digital signatures** (e.g., DocuSign integration) — letters are unsigned PDFs. If a wet signature is required, print and sign physically.
- **Automated email distribution** — manual download + send is the only workflow.
- **Scheduled / triggered letter generation** (e.g., "send a progress letter every quarter") — not implemented.
- **Compliance tracking** module — basic letter logging exists, but no formal compliance workflow.
- **Bulk generation** — letters are generated one at a time in the current UI.

These are valid future-feature requests.

---

## 9. Troubleshooting

| Symptom | Likely cause | What to do |
|---|---|---|
| Template list is empty | No templates exist for your centre, or `is_active = false` | Admin: create one via `/admin/letter-templates`. |
| PDF generation fails with "Allowed memory size exhausted" | DomPDF chokes on large inline images | Reduce header/footer image sizes; or move them to absolute paths in template HTML. |
| Variables not substituted in the output | Variable name mismatch between template body and `template_variables` JSON declaration | Ensure all `{{names}}` in body are declared in `template_variables`. |
| Cannot download an old letter | The PDF file may have been moved or deleted from `storage/app/letters/` | Re-generate from the template and the saved variable values. |
| Letter shows wrong centre logo | Template's `centre_id` is set to a different centre | Edit the template and re-assign. Centre isolation prevents cross-centre template use. |

---

*Updated: 2 May 2026 — sprint Day 4*
*Version: 2.0*
*Source of truth: `letters` and `letter_templates` schemas, `app/Models/Letter.php`, `app/Models/LetterTemplate.php`, `app/Http/Controllers/Letters/*`, `app/Http/Controllers/Profile/LetterTemplateController.php`*
