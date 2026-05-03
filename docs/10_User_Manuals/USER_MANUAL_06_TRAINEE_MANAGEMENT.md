# CREAMS User Manual — Trainee Management

**Version**: 2.0 (re-baselined 2 May 2026)
**Audience**: PPDK staff and IIUM committee
**Supersedes**: Version 1.0 (deprecated — claimed features that do not exist: Family Communication Portal, Transition & Discharge Planning module, multi-step registration with "transfer from another centre" / "re-enrollment" types, IIUM Student ID field for trainees, Nationality field)

---

## 1. What this module does

Trainee Management lets authorised staff **create, view, update, and (admin only) soft-delete trainee records** for the rehabilitation centres they have access to. Each record holds the trainee's personal details, guardian information, medical condition, and consent status.

It is not a parent or trainee self-service portal. Parent and Trainee logins are planned but not yet implemented.

---

## 2. Who can do what

Per-role access (enforced by route middleware and `CentreScope` on the Trainee model):

| Action | Admin | Supervisor | Teacher | AJK |
|---|---|---|---|---|
| View trainee list (centre-scoped) | All centres | Own centre | Own centre | Own centre |
| View individual trainee record | Yes | Yes (own centre) | Yes (own centre) | Yes (own centre) |
| Register new trainee | Yes | Yes (own centre) | Limited | No |
| Edit trainee details | Yes | Yes (own centre) | Limited | No |
| Soft-delete a trainee | Yes | No | No | No |
| Update consent flags | Yes | Yes (own centre) | No | No |

Centre isolation is enforced at the database layer via `CentreScope` — a non-admin user cannot see trainees from other centres even if they craft a direct URL.

---

## 3. Trainee record fields

The full trainee schema (from `database/migrations/...create_trainees_table` and subsequent `add_missing_columns_to_trainees_table`):

### Identity
| Field | Type | Notes |
|---|---|---|
| `trainee_id` | string | Stable internal ID, e.g. `TR-001` (or `UAT-UA1-001` for UAT data) |
| `trainee_first_name` | string | required |
| `trainee_last_name` | string | required |
| `trainee_email` | string | optional, unique if set |
| `ic_number` | string | required, format `YYMMDD-PB-NNNN` (Malaysian IC) |
| `trainee_date_of_birth` | date | derived from IC where possible |
| `gender` | enum | Male / Female |

### Contact
| Field | Type | Notes |
|---|---|---|
| `trainee_phone_number` | string | optional |
| `trainee_address` | text | optional |

### Centre and clinical
| Field | Type | Notes |
|---|---|---|
| `centre_id` | string | required, links to a centre |
| `centre_name` | string | denormalised for display |
| `trainee_condition` | string | free text or selection: Autism, Down Syndrome, etc. |
| `medical_history` | text | optional |
| `additional_notes` | text | optional |

### Guardian
| Field | Type | Notes |
|---|---|---|
| `guardian_name` | string | required |
| `guardian_phone` | string | required |
| `guardian_email` | string | optional |
| `guardian_relationship` | string | Parent / Guardian / Other |
| `guardian_address` | text | optional |

### Emergency contact
| Field | Type | Notes |
|---|---|---|
| `emergency_contact_name` | string | required |
| `emergency_contact_phone` | string | required |
| `emergency_contact_relationship` | string | optional |

### Consent (PDPA)
| Field | Type | Notes |
|---|---|---|
| `photo_consent` | boolean | required, default false |
| `services_consent` | boolean | required, default false |
| `data_consent` | boolean | required, default false |

### Status
| Field | Type | Notes |
|---|---|---|
| `status` | enum | active / inactive / discharged |
| `registration_date` | date | auto-populated on create |
| `deleted_at` | timestamp | soft-delete column — admins only |

There is no Nationality field. There is no IIUM Student ID for trainees (IIUM IDs apply to staff only).

---

## 4. Viewing the trainee list

1. Log in as Admin, Supervisor, Teacher, or AJK.
2. Click **Trainees** in the main navigation, or go to `/trainees`.
3. The list shows trainees in your centre (admins see all centres). Filters typically include centre, status, and search-by-name.
4. Click a trainee row to view the full profile.

---

## 5. Registering a new trainee

1. Navigate to **Trainees → Add New Trainee** (or `/trainees/register`).
2. Fill in the single-page registration form:
   - **Identity** section: name, IC, DOB (auto-calculated from IC if left blank), gender
   - **Contact** section: phone, address
   - **Centre and condition**: pick the centre (auto-set to your centre if you are a supervisor/teacher), enter the condition
   - **Guardian** section: name, phone, relationship; optional email and address
   - **Emergency contact** section
   - **Consent** section: tick the three PDPA consent boxes — required to proceed
3. Click **Submit**.
4. The new trainee is assigned a `trainee_id` automatically and appears in the list immediately.

The current implementation is a **single-page form**, not a multi-step wizard. Earlier docs describing transfer/re-enrollment flows refer to features that do not exist.

---

## 6. Updating a trainee record

1. Open the trainee from the list.
2. Click **Edit**.
3. Modify the editable fields (admin/supervisor have the most editable fields; teachers can update progress notes only).
4. Save.

Every edit writes to the `trainee_audit_logs` table — see audit log section below.

---

## 7. Audit trail

CREAMS records every meaningful action on a trainee record in the `trainee_audit_logs` table. The audit log captures:

- The actor (`user_id`)
- The action verb (create / update / delete / restore)
- Old and new values (JSON)
- Optional notes
- IP address
- Timestamp

Audit log is not currently exposed in the UI for non-admin roles. Admins can query it via tinker:

```
php artisan tinker
>>> App\Models\TraineeAuditLog::where('trainee_id', $id)->latest()->take(10)->get();
```

A UI for the audit trail is on the planned-features list.

---

## 8. Soft delete

Trainees are **soft-deleted** — the `deleted_at` column is set to a timestamp, but the row stays in the database for audit and recovery. Only admins can soft-delete.

To restore a soft-deleted trainee, an admin can use:

```
php artisan tinker
>>> App\Models\Trainee::withTrashed()->find($id)->restore();
```

A UI restore button is on the planned-features list.

---

## 9. PDPA reminders

This module contains PDPA-protected personal data. Discipline:

- Do not screenshot real trainee records when sharing with anyone outside your authorised group.
- Do not export trainee lists to personal devices.
- Use the in-app workflows for all updates — do not edit the database directly.
- Treat the IC number, address, medical history, and guardian details as the most sensitive fields.

Staging, UAT, and demo environments use only `UATSeeder` (Faker-generated data with state-code 99 ICs that are not real). Real trainee data exists only on production and authorised local development.

---

## 10. Troubleshooting

| Symptom | Likely cause | What to do |
|---|---|---|
| "No trainees found" but you expected some | Centre isolation is filtering to your centre | Check your assigned centre with admin. As admin you would see all. |
| Can't see Edit button | Your role lacks update permission for this field | Confirm role and field-level permissions with admin. |
| Submit fails with "consent required" | One or more of the three PDPA consent flags is unchecked | All three consent flags are required by validation. |
| Submit fails with "IC already registered" | Another trainee record already has this IC number (uniqueness check) | Search the existing record; if duplicate, contact admin. |
| Trainee disappears from list | Soft-deleted by admin | Use `Trainee::withTrashed()` via tinker to confirm; restore if needed. |

---

*Updated: 2 May 2026 — sprint Day 4*
*Version: 2.0*
*Source of truth: trainees table schema, `app/Models/Trainee.php`, `app/Http/Controllers/Trainee/TraineeController.php`, `app/Services/TraineeService.php`*
