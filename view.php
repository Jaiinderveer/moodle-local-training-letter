
<?php
require('../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/training_letter:request', $context);

$PAGE->set_url('/local/training_letter/view.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('my_requests', 'local_training_letter'));
$PAGE->set_heading(get_string('my_requests', 'local_training_letter'));
$PAGE->set_pagelayout('standard');

$requests = $DB->get_records(
    'local_training_letter',
    ['userid' => $USER->id],
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
    .tl-applications {
        max-width: 1100px;
        margin: 0 auto 40px;
        color: #292524;
    }

    .tl-applications-hero {
        background: linear-gradient(135deg, #711c2c, #941f3b);
        color: #fff;
        padding: 28px 30px;
        border-radius: 14px;
        margin: 20px 0 25px;
    }

    .tl-applications-hero h2 {
        color: #fff;
        font-size: 26px;
        font-weight: 700;
        margin: 0 0 8px;
    }

    .tl-applications-hero p {
        color: #f8e9ec;
        margin: 0;
        line-height: 1.7;
    }

    .tl-applications-panel {
        background: #fff;
        border: 1px solid #e7e2e3;
        border-radius: 12px;
        padding: 22px;
        overflow-x: auto;
    }

    .tl-applications-panel .table {
        margin-bottom: 0;
        vertical-align: middle;
    }

    .tl-applications-panel .table thead th {
        background: #faf5f6;
        color: #711c2c;
        border-bottom: 1px solid #eadde0;
        white-space: nowrap;
        padding: 14px 12px;
    }

    .tl-applications-panel .table td {
        padding: 15px 12px;
        vertical-align: middle;
    }

    .tl-status {
        display: inline-block;
        padding: 5px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .tl-status-pending {
        color: #854d0e;
        background: #fef3c7;
    }

    .tl-status-approved {
        color: #166534;
        background: #dcfce7;
    }

    .tl-status-rejected {
        color: #991b1b;
        background: #fee2e2;
    }

    .tl-status-other {
        color: #44403c;
        background: #e7e5e4;
    }

    .tl-applications-empty {
        text-align: center;
        padding: 40px 20px;
    }

    .tl-applications-empty h3 {
        margin: 12px 0 8px;
        color: #292524;
    }

    .tl-applications-empty p {
        color: #78716c;
        margin-bottom: 20px;
    }

    .tl-applications-footer {
        margin-top: 20px;
    }

    .tl-maroon-button {
        background: #801d34;
        border-color: #801d34;
        color: #fff !important;
        font-weight: 600;
    }

    .tl-maroon-button:hover,
    .tl-maroon-button:focus {
        background: #651629;
        border-color: #651629;
        color: #fff !important;
    }

    @media (max-width: 600px) {
        .tl-applications-hero {
            padding: 22px 20px;
        }

        .tl-applications-hero h2 {
            font-size: 22px;
        }

        .tl-applications-panel {
            padding: 12px;
        }
    }
</style>

<div class="tl-applications">

    <section class="tl-applications-hero">
        <h2>My Training Applications</h2>
        <p>
            Track your submitted training requests and download your
            official letter once it has been approved.
        </p>
    </section>

    <?php if (!$requests): ?>

        <div class="tl-applications-panel tl-applications-empty">
            <h3>No applications yet</h3>
            <p>
                You haven't submitted a training application.
                Start a new application to get started.
            </p>

            <?php
            echo html_writer::link(
                new moodle_url('/local/training_letter/request.php'),
                get_string('new_request', 'local_training_letter'),
                ['class' => 'btn tl-maroon-button']
            );
            ?>
        </div>

    <?php else: ?>

        <div class="tl-applications-panel">
            <?php
            $table = new html_table();
            $table->attributes['class'] = 'table table-hover';

            $table->head = [
                get_string('requestid', 'local_training_letter'),
                get_string('companyname', 'local_training_letter'),
                get_string('date', 'local_training_letter'),
                get_string('status', 'local_training_letter'),
                get_string('decisionnote', 'local_training_letter'),
                get_string('actions', 'local_training_letter')
            ];

            foreach ($requests as $request) {
                $status = (string)$request->status;

                $statusclasses = [
                    'pending' => 'tl-status-pending',
                    'approved' => 'tl-status-approved',
                    'rejected' => 'tl-status-rejected',
                ];

                $statusclass = $statusclasses[$status] ?? 'tl-status-other';

                $statuslabel = get_string(
                    'status_' . $status,
                    'local_training_letter'
                );

                $statushtml = html_writer::tag(
                    'span',
                    $statuslabel,
                    ['class' => 'tl-status ' . $statusclass]
                );

                if ($status === 'approved') {
                    $action = html_writer::link(
                        new moodle_url('/local/training_letter/letter.php', [
                            'id' => $request->id
                        ]),
                        get_string('downloadpdf', 'local_training_letter'),
                        ['class' => 'btn tl-maroon-button btn-sm']
                    );
                } else {
                    $action = html_writer::tag(
                        'span',
                        'Available after approval',
                        ['class' => 'text-muted small']
                    );
                }

                $table->data[] = [
                    '#' . (int)$request->id,
                    format_string($request->companyname),
                    userdate($request->timecreated),
                    $statushtml,
                    ($status === 'rejected' && trim((string)$request->decisionnote) !== '')
                        ? nl2br(s($request->decisionnote))
                        : '-',
                    $action
                ];
            }

            echo html_writer::table($table);
            ?>
        </div>

        <div class="tl-applications-footer">
            <?php
            echo html_writer::link(
                new moodle_url('/local/training_letter/request.php'),
                get_string('new_request', 'local_training_letter'),
                ['class' => 'btn tl-maroon-button']
            );
            ?>
        </div>

    <?php endif; ?>

</div>

<?php
echo $OUTPUT->footer();
