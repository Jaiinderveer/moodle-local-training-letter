# GNDEC Training Letter — Moodle Local Plugin

A Moodle local plugin for submitting, reviewing, and managing student training-letter requests at **Guru Nanak Dev Engineering College (GNDEC), Ludhiana**.

The plugin provides separate student and coordinator workflows. Students submit training details and track request status. Coordinators review pending applications and approve or reject them. Students can download the generated letter after approval.

> **Important:** The generated letter is a downloadable PDF draft based on the supported GNDEC formats in the plugin. It is not an officially signed or issued college letter. Verify the wording, layout, and authorization process with GNDEC before using it for official purposes. Test with dummy data first.

## Features

### Student
- Submit a training request with personal, academic, company, and mentor details.
- Choose course, training type, and duration from the provided options.
- Track submitted applications and their status.
- Download the training-letter PDF only after coordinator approval.

### Coordinator
- Access a coordinator review panel.
- View submitted requests and their details.
- Approve or reject requests.
- Record who made the decision and when it was made.
- Process decisions through POST requests protected by Moodle session keys.
- Only pending requests can receive a decision.

### Letter generation
The plugin selects from the supplied GNDEC letter formats for these supported combinations:

- B.Tech — six-week industrial training
- B.Tech — six-month industrial training
- B.Tech — software training
- MBA — 6–8 week summer training
- MCA — six-month industrial training

Course, training type, duration, and semester are stored with each request. Combinations without a supplied supported format are rejected rather than receiving invented letter wording.

## Requirements

- Moodle **4.5.x** (the plugin declares Moodle 4.5 as its minimum required version).
- A working Moodle installation and a database supported by that Moodle installation.
- PHP version/extensions compatible with your Moodle 4.5 installation.

## Installation

1. Download or clone this repository.
2. Copy the plugin folder into your Moodle installation at:

   ```text
   moodle/local/training_letter/
   ```

3. Confirm that the plugin's `version.php` is located at:

   ```text
   moodle/local/training_letter/version.php
   ```

4. Log in to Moodle as an administrator.
5. Open **Site administration → Notifications** and complete the plugin installation/upgrade.
6. Open the plugin from the relevant Moodle navigation or visit:

   ```text
   /local/training_letter/index.php
   ```

## Permissions

The plugin defines two system-context capabilities in `db/access.php`:

| Capability | Intended role | Purpose |
|---|---|---|
| `local/training_letter:request` | Student / user | Access the student request and application pages |
| `local/training_letter:manage` | Coordinator / manager | Access the coordinator review panel and process decisions |

The plugin assigns the `request` capability to the default user archetype and the `manage` capability to the manager archetype. If your institution uses a separate coordinator role, an administrator should grant `local/training_letter:manage` to that role in the **system context**.

The pages also check login state and capabilities using Moodle's access-control APIs.

## Main pages

| File | Purpose |
|---|---|
| `index.php` | Plugin home page and role-appropriate navigation |
| `request.php` | Student training request submission |
| `view.php` | List of the logged-in student's own requests and available actions |
| `manage.php` | Coordinator review panel |
| `letter.php?id=ID` | Generates/downloads the letter for an approved request |
| `classes/form/request_form.php` | Moodle Form API form definition and validation |

## Database

The plugin creates the `local_training_letter` table, defined in `db/install.xml`.

The table stores:
- Moodle user ID and student identity/contact details.
- Course, branch, semester, training type, and duration.
- Company, contact person, company phone/address, and mentor details.
- Joining date.
- Request status (`pending`, `approved`, or `rejected`).
- Decision metadata (coordinator user ID and decision timestamp).
- Creation and modification timestamps.

The `id` field is the primary key and uses `SEQUENCE="true"` so Moodle's database layer assigns it automatically. The table also defines a foreign key to Moodle's user table and indexes for `userid` and `status`.

Database operations use Moodle's global `$DB` API rather than hard-coded database connections.

## Request workflow

1. A logged-in student opens the request form.
2. Moodle Form API validates the submitted fields.
3. The request is inserted into `local_training_letter` with status `pending`.
4. The student can see their own request in **My Applications**.
5. A user with `local/training_letter:manage` reviews the request.
6. The coordinator submits an approval or rejection decision.
7. The status and decision metadata are saved.
8. A student can download the letter only when the request status is `approved`.

## Repository structure

```text
training_letter/
├── classes/
│   └── form/
│       └── request_form.php
├── db/
│   ├── access.php
│   └── install.xml
├── lang/
│   └── en/
├── pix/
├── index.php
├── request.php
├── view.php
├── manage.php
├── letter.php
├── version.php
└── README.md
```

## Security and implementation notes

- Pages require an authenticated Moodle session.
- Student and coordinator capabilities are checked server-side.
- The coordinator decision handler accepts POST requests and checks Moodle's session key.
- Only a pending request can be approved or rejected.
- Students see only their own requests on the application list.
- Form fields use Moodle Form API types and validation.
- Database reads and writes use Moodle's `$DB` abstraction.

## Development and testing

Test the plugin on a non-production Moodle 4.5 site first. Use dummy student and company details while checking:

1. Installation and database table creation.
2. Student submission and pending status.
3. Student request list and ownership boundaries.
4. Coordinator access and approval/rejection.
5. Rejection of a second decision for an already-reviewed request.
6. PDF download availability for approved requests only.
7. The generated letter's format for every supported course/training combination.

## License and official use

No separate license or official GNDEC endorsement is declared in this repository. Confirm licensing and institutional approval before redistribution or real-world issuance.
