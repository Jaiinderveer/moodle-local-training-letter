# GNDEC Training Request Letter Generator

**Moodle local plugin:** `local_training_letter`  
**Institutional context:** Guru Nanak Dev Engineering College (GNDEC), Ludhiana  
**Assignment:** Task 2 — Training Request Letter Generator System

## Project summary

This Moodle local plugin provides an end-to-end workflow for student training request letters. Authenticated students submit their academic, training, company and mentor details through a Moodle Form API form. Requests are stored through Moodle's Database API and initially marked **Pending**. Users with the coordinator-management capability can review each request and approve or reject it. A populated PDF letter can be generated only after approval.

The implementation uses Moodle's local-plugin structure, Form API, Database API, capability checks and Moodle's PDF library.

## Assessment evidence

The repository includes screenshots of the main application workflow and one generated PDF sample. The evidence files are linked below.

| Evidence | Description |
|---|---|
| Student request form | ![Student request form](screenshots/01-student-form.png) |
| Submitted request | ![Form submission](screenshots/02-form-submission.png) |
| Database persistence | ![Database entry](screenshots/03-database-entry.png) |
| Coordinator request list | ![Coordinator requests](screenshots/04-coordinator-requests.png) |
| Approval and resulting status | ![Approval and status](screenshots/05-approval-and-status.png) |
| Generated letter screen | ![Generated letter](screenshots/06-generated-letter.png) |

**Generated PDF sample:** [Open the exported training request letter](sample/Jaiinderveer%20Singh_2435412_1.pdf)

The PDF is included as a sample output from the plugin. Its inclusion demonstrates the generated document format; it should not be interpreted as evidence that the document has been institutionally signed or officially issued.

## Features and workflow

### Student request submission

The request form captures:

- Student name, college roll number and university roll number.
- Phone number and email address.
- Course, branch and semester.
- Training type, duration and joining date.
- Company name, postal address, contact person and phone number.
- College mentor name.

The form is implemented with Moodle's `moodleform` class in `classes/form/request_form.php`. It applies field types, required-field checks, email and phone validation, and server-side validation of supported course/training/duration combinations. Name and email are prefilled from the authenticated Moodle account where available.

After successful validation, `request.php` stores the submitted data in `local_training_letter` using Moodle's `$DB` API. New requests are created with the `pending` status.

### Coordinator review

The coordinator interface is implemented in `manage.php` and protected by the `local/training_letter:manage` capability. It lists submitted requests and provides access to the request details. A coordinator can approve or reject a pending request.

A rejection reason can be recorded and is displayed with the rejected request in the student's application list. Decision metadata includes the coordinator user ID and decision timestamp. Decision actions are submitted through POST and checked with Moodle's session key. A request that has already been decided cannot be processed again through the normal workflow.

### Student status and letter access

Students use `view.php` to view their own requests and their current status. The generated letter endpoint, `letter.php`, verifies that the requester owns the request or has coordinator-management permission, and that the request is approved. Pending and rejected requests cannot be used to generate the letter.

### PDF generation

PDF output is generated using Moodle's PDF library (`pdflib.php`). The implementation uses the supplied GNDEC logo asset at `pix/gndec_logo.png`, an A4 page layout and a letter format selected from the request's supported course, training type and duration.

The following combinations currently have matching templates in the implementation:

| Course | Training type | Duration |
|---|---|---|
| B.Tech | Industrial | 6 Weeks |
| B.Tech | Industrial | 6 Months |
| B.Tech | Software | 6 Weeks |
| MBA | Industrial | 6 Weeks |
| MCA | Industrial | 6 Months |

Other combinations are not represented as supported templates and are rejected by validation or PDF generation.

## Technical implementation

| Component | Implementation |
|---|---|
| Plugin type | Moodle local plugin (`local_training_letter`) |
| Request form | Moodle Form API (`moodleform`) |
| Data persistence | Moodle Database API (`$DB`) |
| Database schema | `db/install.xml`; upgrade logic in `db/upgrade.php` |
| Authorization | Moodle capabilities declared in `db/access.php` |
| Language strings | `lang/en/local_training_letter.php` |
| PDF output | Moodle PDF library |
| Moodle minimum version | Moodle 4.5 (declared in `version.php`) |

### Database record

The plugin creates the `local_training_letter` table. Its fields include:

- **Record and ownership:** `id`, `userid`.
- **Student details:** name, roll numbers, phone and email.
- **Academic details:** course, branch and semester.
- **Training details:** training type, duration and joining date.
- **Organization details:** company name, address, contact person, company phone and mentor name.
- **Workflow:** `status` (`pending`, `approved` or `rejected`) and `decisionnote`.
- **Decision audit:** `decidedby`, `timedecided`, `timecreated`, `timemodified`.

The table defines a primary key, user-related foreign keys and indexes for `userid` and `status`.

## Installation

1. Use a Moodle 4.5-compatible site and sign in with an administrator account.
2. Copy this plugin directory into the Moodle installation at `<moodle-root>/local/training_letter/`.
3. Verify that `version.php` and `pix/gndec_logo.png` are present.
4. Open **Site administration → Notifications** and complete the plugin installation.
5. Open the plugin at `<moodle-url>/local/training_letter/index.php`.

On a fresh installation, Moodle reads `db/install.xml` to create the plugin table.

## Role and capability configuration

The plugin declares two system-context capabilities:

| Capability | Intended user | Access |
|---|---|---|
| `local/training_letter:request` | Student / authenticated user | Student request and application pages |
| `local/training_letter:manage` | Coordinator / manager | Coordinator review and decision actions |

The request capability is assigned to Moodle's default user archetype and the management capability to the manager archetype. To use a separate coordinator role, a Moodle administrator must grant `local/training_letter:manage` in the system context.

## Upgrade

For an existing installation:

1. Back up the Moodle database and plugin directory.
2. Replace the plugin code with the updated version. Do not uninstall the plugin when retaining existing request data is required.
3. Sign in as a Moodle administrator.
4. Open **Site administration → Notifications** and complete the available upgrade.
5. Confirm that Moodle reports no database or XMLDB upgrade errors. Purge caches if the updated interface or language strings are not visible.

The current plugin upgrade logic is in `db/upgrade.php`; the plugin version is declared in `version.php`.

## Repository structure

```text
training_letter/
├── classes/
│   └── form/
│       └── request_form.php
├── db/
│   ├── access.php
│   ├── install.xml
│   └── upgrade.php
├── lang/
│   └── en/
│       └── local_training_letter.php
├── pix/
│   └── gndec_logo.png
├── sample/
│   └── Jaiinderveer Singh_2435412_1.pdf
├── screenshots/
│   ├── 01-student-form.png
│   ├── 02-form-submission.png
│   ├── 03-database-entry.png
│   ├── 04-coordinator-requests.png
│   ├── 05-approval-and-status.png
│   └── 06-generated-letter.png
├── index.php
├── request.php
├── view.php
├── manage.php
├── letter.php
├── version.php
└── README.md
```

## Verification scope

The repository's evidence set documents the student form, submission, database record, coordinator request list, approval/status and generated-letter view. The generated PDF sample is provided under `sample/`.

The implementation also contains paths for rejection and rejection-reason display, access-control checks, server-side form validation, and approval-gated PDF generation. These should be exercised on the evaluator's Moodle instance as part of functional verification. The README does not claim automated test coverage or an independent certification of a Moodle/PHP deployment.

## Institutional-use note

This plugin is an assignment implementation and is not, by itself, an official GNDEC issuance or approval system. The generated PDF should be treated as a software-generated sample/draft until the Training & Placement Cell confirms the current official wording, reference-number convention, letterhead, signatory and authorization process. Do not use generated output as an officially issued institutional letter without that authorization.

Use dummy or authorized data for demonstrations. Do not publish students' personal information without permission.
