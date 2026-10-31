
<?php
require('../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/training_letter:request', $context);

$PAGE->set_url('/local/training_letter/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_training_letter'));
$PAGE->set_heading(get_string('pluginname', 'local_training_letter'));
$PAGE->set_pagelayout('standard');

$canmanage = has_capability('local/training_letter:manage', $context);

echo $OUTPUT->header();
?>

<style>
    .training-letter-home {
        max-width: 1100px;
        margin: 0 auto 40px;
        color: #292524;
    }

    .training-letter-hero {
        position: relative;
        overflow: hidden;
        padding: 36px;
        margin-bottom: 28px;
        color: #fff;
        background: linear-gradient(120deg, #702333, #963b4e);
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(86, 25, 39, 0.12);
    }

    .training-letter-hero h2 {
        margin: 0 0 12px;
        color: #fff;
        font-size: 30px;
        font-weight: 700;
    }

    .training-letter-hero p {
        max-width: 600px;
        margin: 0;
        color: #f8e9ec;
        font-size: 16px;
        line-height: 1.7;
    }

    .training-letter-eyebrow {
        margin-bottom: 10px;
        color: #f0c9d1;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    .training-letter-section-title {
        margin: 0 0 16px;
        font-size: 20px;
        font-weight: 700;
    }

    .training-letter-action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .training-letter-action-card {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 25px;
        background: #fff;
        border: 1px solid #e8e1e2;
        border-radius: 12px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .training-letter-action-card:hover {
        border-color: #b77b88;
        box-shadow: 0 8px 22px rgba(72, 30, 39, 0.08);
    }

    .training-letter-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin-bottom: 18px;
        color: #79283a;
        background: #f8edef;
        border-radius: 12px;
        font-size: 24px;
    }

    .training-letter-action-card h3 {
        margin: 0 0 9px;
        font-size: 18px;
        font-weight: 700;
    }

    .training-letter-action-card p {
        flex-grow: 1;
        margin: 0 0 22px;
        color: #6b6263;
        line-height: 1.65;
    }

    .training-letter-home .training-letter-btn {
        display: inline-block;
        padding: 10px 17px;
        border: 1px solid #79283a;
        border-radius: 7px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }

    .training-letter-home .training-letter-btn-primary {
        color: #fff;
        background: #79283a;
    }

    .training-letter-home .training-letter-btn-primary:hover {
        color: #fff;
        background: #5f1e2d;
    }

    .training-letter-home .training-letter-btn-secondary {
        color: #79283a;
        background: #fff;
    }

    .training-letter-home .training-letter-btn-secondary:hover {
        color: #5f1e2d;
        background: #f9eff1;
    }

    .training-letter-note {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 18px 20px;
        background: #fbf6f3;
        border: 1px solid #eee2dc;
        border-radius: 10px;
    }

    .training-letter-note strong {
        display: block;
        margin-bottom: 4px;
    }

    .training-letter-note p {
        margin: 0;
        color: #6b6263;
        line-height: 1.6;
    }

    @media (max-width: 600px) {
        .training-letter-hero {
            padding: 25px 21px;
        }

        .training-letter-hero h2 {
            font-size: 25px;
        }

        .training-letter-action-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
    }
</style>

<div class="training-letter-home">

    <section class="training-letter-hero">
        <div class="training-letter-eyebrow">
            Guru Nanak Dev Engineering College · Ludhiana
        </div>

        <h2>Training &amp; Placement Portal</h2>

        <p>
            Manage your training applications, track approval progress,
            and access your approved training request letters in one place.
        </p>
    </section>

    <h3 class="training-letter-section-title">What would you like to do?</h3>

    <div class="training-letter-action-grid">

        <article class="training-letter-action-card">
            <div class="training-letter-icon" aria-hidden="true">＋</div>

            <h3>Apply for Training</h3>

            <p>
                Submit a new training request with your academic details,
                training preferences, and company information.
            </p>

            <a
                href="<?php echo (new moodle_url('/local/training_letter/request.php'))->out(false); ?>"
                class="training-letter-btn training-letter-btn-primary">
                Start Application &rarr;
            </a>
        </article>

        <article class="training-letter-action-card">
            <div class="training-letter-icon" aria-hidden="true">&#9776;</div>

            <h3>My Applications</h3>

            <p>
                Review your submitted requests, check their current status,
                and download your letter when it has been approved.
            </p>

            <a
                href="<?php echo (new moodle_url('/local/training_letter/view.php'))->out(false); ?>"
                class="training-letter-btn training-letter-btn-secondary">
                View Applications &rarr;
            </a>
        </article>

        <?php if ($canmanage): ?>
            <article class="training-letter-action-card">
                <div class="training-letter-icon" aria-hidden="true">&#10003;</div>

                <h3>Coordinator Panel</h3>

                <p>
                    Review training requests and manage their approval
                    status using your coordinator permissions.
                </p>

                <a
                    href="<?php echo (new moodle_url('/local/training_letter/manage.php'))->out(false); ?>"
                    class="training-letter-btn training-letter-btn-secondary">
                    Manage Requests &rarr;
                </a>
            </article>
        <?php endif; ?>

    </div>

    <div class="training-letter-note">
        <span aria-hidden="true">&#9432;</span>

        <div>
            <strong>About your training letter</strong>

            <p>
                Submit your request and monitor its status under My Applications.
                The downloadable training letter becomes available after coordinator approval.
            </p>
        </div>
    </div>

</div>

<?php
echo $OUTPUT->footer();
