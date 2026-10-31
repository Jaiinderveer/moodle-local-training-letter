
<?php
require('../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/training_letter:manage', $context);

$PAGE->set_url('/local/training_letter/manage.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage_requests', 'local_training_letter'));
$PAGE->set_heading(get_string('manage_requests', 'local_training_letter'));
$PAGE->set_pagelayout('standard');

// Process coordinator decisions through POST only.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();

    $id = required_param('id', PARAM_INT);
    $decision = required_param('decision', PARAM_ALPHA);

    if (!in_array($decision, ['approve', 'reject'], true)) {
        throw new moodle_exception('invaliddata', 'error');
    }

    $request = $DB->get_record(
        'local_training_letter',
        ['id' => $id],
        '*',
        MUST_EXIST
    );

    // Only pending requests can receive a new decision.
    if ($request->status !== 'pending') {
        redirect(
            new moodle_url('/local/training_letter/manage.php'),
            'This application has already been reviewed.',
            null,
            \core\output\notification::NOTIFY_WARNING
        );
    }

    $request->status = ($decision === 'approve') ? 'approved' : 'rejected';
    $request->decidedby = $USER->id;
    $request->timedecided = time();
    $request->timemodified = time();

    $DB->update_record('local_training_letter', $request);

    redirect(
        new moodle_url('/local/training_letter/manage.php'),
        get_string('savechanges', 'local_training_letter'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$requests = $DB->get_records(
    'local_training_letter',
    null,
    'timecreated DESC'
);

echo $OUTPUT->header();

echo html_writer::div(
    html_writer::link(
        new moodle_url('/local/training_letter/index.php'),
        '← Back to Home',
        [
            'class' => 'btn tl-home-button',
        ]
    ),
    'tl-home-navigation'
);

?>

<style>
    
    .tl-home-navigation {
        margin: 0 0 20px;
    }

    .tl-home-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        color: #801d34 !important;
        border: 1px solid #801d34;
        border-radius: 7px;
        padding: 9px 16px;
        font-weight: 600;
        text-decoration: none !important;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .tl-home-button:hover,
    .tl-home-button:focus {
        background: #801d34;
        color: #fff !important;
        text-decoration: none !important;
    }

    .tl-manage {
        max-width: 1200px;
        margin: 0 auto 40px;
        color: #292524;
    }

    .tl-manage-hero {
        background: linear-gradient(135deg, #711c2c, #941f3b);
        color: #fff;
        padding: 28px 30px;
        border-radius: 14px;
        margin: 20px 0 25px;
    }

    .tl-manage-hero h2 {
        color: #fff;
        font-size: 26px;
        font-weight: 700;
        margin: 0 0 8px;
    }

    .tl-manage-hero p {
        color: #f8e9ec;
        margin: 0;
        line-height: 1.7;
    }

    .tl-manage-panel {
        background: #fff;
        border: 1px solid #e7e2e3;
        border-radius: 12px;
        padding: 20px;
        overflow: visible;
    }

    .tl-manage-panel .table {
        margin-bottom: 0;
        vertical-align: middle;
        width: 100%;
        table-layout: fixed;
    }

    .tl-manage-panel .table thead th {
        background: #faf5f6;
        color: #711c2c;
        border-bottom: 1px solid #eadde0;
        white-space: nowrap;
        padding: 14px 12px;
    }

    .tl-manage-panel .table td {
        padding: 14px 12px;
        vertical-align: middle;
        overflow-wrap: anywhere;
    }

    .tl-manage-status {
        display: inline-block;
        padding: 5px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .tl-manage-pending {
        background: #fef3c7;
        color: #854d0e;
    }

    .tl-manage-approved {
        background: #dcfce7;
        color: #166534;
    }

    .tl-manage-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .tl-manage-other {
        background: #e7e5e4;
        color: #44403c;
    }

    .tl-manage-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .tl-manage-actions form {
        display: inline;
        margin: 0;
    }

    .tl-manage-details {
        margin-top: 10px;
        width: 100%;
    }

    .tl-manage-details summary {
        display: inline-block;
        color: #801d34;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
    }

    .tl-manage-details summary:hover,
    .tl-manage-details summary:focus {
        color: #651629;
        text-decoration: underline;
    }

    .tl-manage-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 24px;
        margin-top: 12px;
        padding: 16px;
        background: #fffaf8;
        border: 1px solid #eadde0;
        border-radius: 8px;
        white-space: normal;
    }

    .tl-manage-detail-item {
        line-height: 1.45;
    }

    .tl-manage-detail-item-wide {
        grid-column: 1 / -1;
    }

    .tl-manage-detail-label {
        display: block;
        color: #711c2c;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .tl-manage-detail-value {
        overflow-wrap: anywhere;
    }

    .tl-manage-panel .table th:first-child,
    .tl-manage-panel .table td:first-child {
        width: 7%;
    }

    .tl-manage-panel .table th:nth-child(2),
    .tl-manage-panel .table td:nth-child(2) {
        width: 29%;
    }

    .tl-manage-panel .table th:nth-child(3),
    .tl-manage-panel .table td:nth-child(3) {
        width: 24%;
    }

    .tl-manage-panel .table th:nth-child(4),
    .tl-manage-panel .table td:nth-child(4) {
        width: 16%;
    }

    .tl-manage-panel .table th:last-child,
    .tl-manage-panel .table td:last-child {
        width: 24%;
    }

    .tl-manage-approve {
        background: #166534;
        border-color: #166534;
        color: #fff !important;
    }

    .tl-manage-reject {
        background: #b42332;
        border-color: #b42332;
        color: #fff !important;
    }

    @media (max-width: 600px) {
        .tl-manage-hero {
            padding: 22px 20px;
        }

        .tl-manage-hero h2 {
            font-size: 22px;
        }

        .tl-manage-panel {
            padding: 12px;
        }

        .tl-manage-detail-grid {
            grid-template-columns: 1fr;
        }

        .tl-manage-detail-item-wide {
            grid-column: auto;
        }
    }
</style>

<div class="tl-manage">

    <section class="tl-manage-hero">
        <h2>Coordinator Review Panel</h2>
        <p>
            Review student training applications, record decisions,
            and access approved training letters.
        </p>
    </section>

    <?php if (!$requests): ?>

        <div class="tl-manage-panel">
            <?php
            echo $OUTPUT->notification(
                get_string('norequests', 'local_training_letter'),
                'info'
            );
            ?>
        </div>

    <?php else: ?>

        <div class="tl-manage-panel">
            <?php
            $table = new html_table();
            $table->attributes['class'] = 'table table-hover';

            $table->head = [
                'ID',
                get_string('fullname', 'local_training_letter'),
                get_string('companyname', 'local_training_letter'),
                get_string('status', 'local_training_letter'),
                get_string('actions', 'local_training_letter'),
            ];

            foreach ($requests as $request) {
                $status = (string)$request->status;

                $statusclasses = [
                    'pending' => 'tl-manage-pending',
                    'approved' => 'tl-manage-approved',
                    'rejected' => 'tl-manage-rejected',
                ];

                $statusclass = $statusclasses[$status] ?? 'tl-manage-other';

                $statushtml = html_writer::tag(
                    'span',
                    get_string('status_' . $status, 'local_training_letter'),
                    ['class' => 'tl-manage-status ' . $statusclass]
                );

                $detailitems = [
                    ['Student full name', format_string($request->fullname), false],
                    ['College roll number', format_string($request->rollno), false],
                    ['University roll number', format_string($request->universityrollno), false],
                    ['Phone number', format_string($request->phone), false],
                    ['Email', s($request->email), false],
                    ['Course', format_string($request->course), false],
                    ['Branch / department', format_string($request->branch), false],
                    ['Semester / batch', format_string($request->semester), false],
                    ['Training type', format_string($request->trainingtype), false],
                    ['Duration', format_string($request->duration), false],
                    ['Company name', format_string($request->companyname), false],
                    ['Contact person', format_string($request->companycontact), false],
                    ['Company contact phone', format_string($request->companyphone), false],
                    ['College mentor', format_string($request->mentorname), false],
                    [
                        'Proposed joining date',
                        !empty($request->joiningdate) ? userdate($request->joiningdate) : '-',
                        false,
                    ],
                    [
                        'Application status',
                        get_string('status_' . $status, 'local_training_letter'),
                        false,
                    ],
                    [
                        'Complete company address',
                        nl2br(s($request->companyaddress)),
                        true,
                    ],
                ];

                if (trim((string)$request->decisionnote) !== '') {
                    $detailitems[] = [
                        'Decision note',
                        nl2br(s($request->decisionnote)),
                        true,
                    ];
                }

                $detailshtml = html_writer::start_tag('details', [
                    'class' => 'tl-manage-details',
                ]);
                $detailshtml .= html_writer::tag('summary', 'View complete details');
                $detailshtml .= html_writer::start_tag('div', [
                    'class' => 'tl-manage-detail-grid',
                ]);

                foreach ($detailitems as [$label, $value, $wide]) {
                    $classes = 'tl-manage-detail-item';
                    if ($wide) {
                        $classes .= ' tl-manage-detail-item-wide';
                    }

                    $detailshtml .= html_writer::div(
                        html_writer::span($label, 'tl-manage-detail-label')
                        . html_writer::div($value, 'tl-manage-detail-value'),
                        $classes
                    );
                }

                $detailshtml .= html_writer::end_tag('div');
                $detailshtml .= html_writer::end_tag('details');

                if ($status === 'pending') {
                    $approveform = html_writer::start_tag('form', [
                        'method' => 'post',
                        'action' => (new moodle_url('/local/training_letter/manage.php'))->out(false),
                    ]);

                    $approveform .= html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'sesskey',
                        'value' => sesskey(),
                    ]);

                    $approveform .= html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $request->id,
                    ]);

                    $approveform .= html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'decision',
                        'value' => 'approve',
                    ]);

                    $approveform .= html_writer::tag(
                        'button',
                        get_string('approve', 'local_training_letter'),
                        [
                            'type' => 'submit',
                            'class' => 'btn btn-sm tl-manage-approve',
                            'onclick' => 'return confirm("Approve this training application?");',
                        ]
                    );

                    $approveform .= html_writer::end_tag('form');

                    $rejectform = html_writer::start_tag('form', [
                        'method' => 'post',
                        'action' => (new moodle_url('/local/training_letter/manage.php'))->out(false),
                    ]);

                    $rejectform .= html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'sesskey',
                        'value' => sesskey(),
                    ]);

                    $rejectform .= html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $request->id,
                    ]);

                    $rejectform .= html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'decision',
                        'value' => 'reject',
                    ]);

                    $rejectform .= html_writer::tag(
                        'button',
                        get_string('reject', 'local_training_letter'),
                        [
                            'type' => 'submit',
                            'class' => 'btn btn-sm tl-manage-reject',
                            'onclick' => 'return confirm("Reject this training application?");',
                        ]
                    );

                    $rejectform .= html_writer::end_tag('form');

                    $actions = html_writer::div(
                        $approveform . $rejectform,
                        'tl-manage-actions'
                    );

                } else if ($status === 'approved') {
                    $actions = html_writer::link(
                        new moodle_url('/local/training_letter/letter.php', [
                            'id' => $request->id,
                        ]),
                        get_string('letter', 'local_training_letter'),
                        ['class' => 'btn btn-sm tl-manage-approve']
                    );

                } else {
                    $actions = html_writer::tag(
                        'span',
                        'No actions available',
                        ['class' => 'text-muted small']
                    );
                }

                $table->data[] = [
                    '#' . (int)$request->id,
                    format_string($request->fullname) . $detailshtml,
                    format_string($request->companyname),
                    $statushtml,
                    $actions,
                ];
            }

            echo html_writer::table($table);
            ?>
        </div>

    <?php endif; ?>

</div>

<?php
echo $OUTPUT->footer();
