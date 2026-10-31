
<?php
require('../../config.php');
require_once($CFG->dirroot . '/local/training_letter/classes/form/request_form.php');

require_login();

$context = context_system::instance();
require_capability('local/training_letter:request', $context);

$PAGE->set_url('/local/training_letter/request.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('request_training', 'local_training_letter'));
$PAGE->set_heading(get_string('request_training', 'local_training_letter'));
$PAGE->set_pagelayout('standard');

$form = new \local_training_letter\form\request_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/training_letter/index.php'));
}

if ($data = $form->get_data()) {
    $request = new stdClass();
    $request->userid = $USER->id;

    foreach ([
        'fullname',
        'rollno',
        'universityrollno',
        'phone',
        'email',
        'course',
        'branch',
        'semester',
        'trainingtype',
        'duration',
        'companyname',
        'companyaddress',
        'companycontact',
        'companyphone',
        'mentorname',
        'joiningdate'
    ] as $field) {
        $request->$field = $data->$field;
    }

    $request->status = 'pending';
    $request->decisionnote = null;
    $request->decidedby = 0;
    $request->timedecided = 0;
    $request->timecreated = time();
    $request->timemodified = time();

    $DB->insert_record('local_training_letter', $request);

    redirect(
        new moodle_url('/local/training_letter/view.php'),
        get_string('submitted', 'local_training_letter')
    );
}

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
    .tl-request-page {
        max-width: 1000px;
        margin: 0 auto 40px;
        color: #292524;
    }

    .tl-request-hero {
        background: linear-gradient(135deg, #711c2c, #941f3b);
        color: #fff;
        padding: 30px 32px;
        border-radius: 14px;
        margin: 20px 0 26px;
    }

    .tl-request-hero h2 {
        color: #fff;
        font-size: 26px;
        font-weight: 700;
        margin: 0 0 10px;
    }

    .tl-request-hero p {
        color: #f8e9ec;
        margin: 0;
        line-height: 1.7;
        max-width: 700px;
    }

    .tl-request-info {
        background: #fff8f0;
        border-left: 4px solid #941f3b;
        border-radius: 6px;
        padding: 14px 18px;
        margin-bottom: 24px;
        line-height: 1.6;
    }

    .tl-request-form {
        background: #fff;
        border: 1px solid #e7e2e3;
        border-radius: 12px;
        padding: 24px;
    }

    .tl-request-form .fdescription {
        margin-bottom: 18px;
    }

    @media (max-width: 600px) {
        .tl-request-hero {
            padding: 23px 20px;
        }

        .tl-request-hero h2 {
            font-size: 22px;
        }

        .tl-request-form {
            padding: 16px;
        }
        
        /* Safe form sizing: preserve Moodle's native layout */
        .tl-request-page {
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }

        .tl-request-form .mform {
            width: 100%;
        }

        .tl-request-form .mform .form-control,
        .tl-request-form .mform input[type="text"],
        .tl-request-form .mform input[type="email"],
        .tl-request-form .mform select,
        .tl-request-form .mform textarea {
            max-width: 100%;
            box-sizing: border-box;
        }

        .tl-request-form .mform textarea {
            width: 100%;
            min-height: 90px;
        }

        .tl-request-form .mform .fitem {
            margin-bottom: 12px;
        }

        @media (max-width: 767px) {
            .tl-request-form {
                padding: 16px;
            }

            .tl-request-form .mform .form-control,
            .tl-request-form .mform input[type="text"],
            .tl-request-form .mform input[type="email"],
            .tl-request-form .mform select,
            .tl-request-form .mform textarea {
                width: 100%;
            }
        }

</style>

<div class="tl-request-page">

    <section class="tl-request-hero">
        <h2>Training Application</h2>
        <p>
            Submit your training details for review by the Training &amp;
            Placement Cell at Guru Nanak Dev Engineering College, Ludhiana.
        </p>
    </section>

    <div class="tl-request-info">
        <strong>Before you submit:</strong>
        Please check that your personal details, company information, and
        proposed joining date are correct. Your application will remain
        pending until it is reviewed by the coordinator.
    </div>

    <div class="tl-request-form">
        <?php $form->display(); ?>
    </div>

</div>

<?php
echo $OUTPUT->footer();
