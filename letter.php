<?php
if (ob_get_level() === 0) {
    ob_start();
}

require('../../config.php');
require_login();
require_once($CFG->libdir . '/pdflib.php');

$id = required_param('id', PARAM_INT);
$context = context_system::instance();
$request = $DB->get_record('local_training_letter', ['id' => $id], '*', MUST_EXIST);

// Request owner or authorised coordinator only.
$ismanager = has_capability('local/training_letter:manage', $context);
if ((int)$request->userid !== (int)$USER->id && !$ismanager) {
    throw new moodle_exception('notyourrequest', 'local_training_letter');
}

// Keep the download gate: only approved requests may produce a letter.
if ($request->status !== 'approved') {
    throw new moodle_exception('nolettersuntilapproved', 'local_training_letter');
}

// Escape database values before using them in HTML strings.
$requiredfields = [
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
    'joiningdate',
];

foreach ($requiredfields as $field) {
    if (!isset($request->$field)
            || ($field === 'joiningdate' && (int)$request->$field <= 0)
            || ($field !== 'joiningdate' && trim((string)$request->$field) === '')) {
        throw new moodle_exception('incompleterequest', 'local_training_letter');
    }
}

$fullname = s($request->fullname);
$companyname = s($request->companyname);
$companyaddress = s($request->companyaddress);
$companyaddresshtml = nl2br($companyaddress);
$rollno = s($request->rollno);
$universityrollno = s($request->universityrollno);
$email = s($request->email);
$phone = s($request->phone);
$course = strtoupper(trim((string)$request->course));
$branch = s($request->branch);
$semester = trim((string)$request->semester);
$trainingtype = strtolower(trim((string)$request->trainingtype));
$duration = s($request->duration);
$joiningdate = userdate($request->joiningdate, '%d %B %Y');
$today = userdate(time(), '%d %B %Y');

// Keep the plugin's current numbering convention; confirm the exact official
// numbering sequence with the Training & Placement Cell before issuing letters.
$reference = 'PTP/2026/' . (int)$request->id;
// TCPDF requires a filesystem path for Image(); use Moodle's absolute root.
$logopath = $CFG->dirroot . '/local/training_letter/pix/gndec_logo.png';
if (!is_readable($logopath)) {
    throw new moodle_exception(
        'generalexceptionmessage',
        'error',
        '',
        'GNDEC logo image is missing or unreadable at ' . $logopath
    );
}

$durationvalue = strtolower(trim((string)$request->duration));
$templatekey = '';
$trainingdescription = '';
$signatory = '<b>Dr. K.S. Mann</b><br />Dean (Training &amp; Placement)';

if ($course === 'B.TECH' && $trainingtype === 'industrial' && $durationvalue === '6 weeks') {
    $templatekey = 'btech-six-week-industrial';
    $subject = '6 WEEKS INDUSTRIAL TRAINING FOR B.TECH. 2nd YEAR STUDENTS';
    $trainingintro = 'Guru Nanak Dev Engineering College has emerged as one of the most prestigious '
        . 'engineering institute of North India over 62 years of its inception and is conducting '
        . 'B.Tech. in seven disciplines as well as M.Tech., MBA and PhD. for meeting the research '
        . 'requirement of technical field.<br /><br />'
        . 'Since industrial training is equal in importance to theoretical foundation, '
        . 'the course curriculum is so designed that the students get exposure to practical aspects '
        . 'of their respective engineering branch. We are in a process of enrolling the second year '
        . 'students of our institute to various Industrial Organisations for 6 WEEKS INDUSTRIAL '
        . 'TRAINING which is an essential component of their four year B.Tech programme.';
    $trainingdescription = '6 weeks training';
    $programmeitems = [
        'To get familiar with the setup and working of the organisation.',
        'Working on the given project, 2 week onwards.',
        'Submission of Final Report.',
        'Submission of Daily Diary at the end of training.',
    ];
    $signatory = '<b>Prof. G.S. Sodhi</b><br />Training &amp; Placement Officer';
} else if ($course === 'B.TECH' && $trainingtype === 'industrial' && $durationvalue === '6 months') {
    $templatekey = 'btech-semestrial-industrial';
    $subject = 'REQUEST FOR INDUSTRIAL TRAINING OF B.TECH ' . s($semester) . 'TH SEMESTER STUDENTS';
    $trainingintro = 'Guru Nanak Dev Engineering College has emerged as one of the most prestigious '
        . 'engineering institute of North India over 62 years of its inception and is conducting '
        . 'B.Tech. in seven disciplines as well as M.Tech., MBA and PhD. for meeting the research '
        . 'requirement of technical field.<br /><br />'
        . 'Since practical training is equal in importance to theoretical foundation, '
        . 'the course curriculum is so designed that the students get exposure to practical aspects '
        . 'of their respective engineering branch. We are in a process of enrolling the final year '
        . 'students of our institute to various Industrial Organisations for INDUSTRIAL TRAINING '
        . '(6 MONTHS) which is an essential component of their four year B.Tech programme.';
    $trainingdescription = 'Industrial training';
    $programmeitems = [
        'To get familiar with the setup and working of the organisation.',
        'Preparation and submission of synopsis.',
        'Working on the given project provided by the company.',
        'Submission of Mid Term and Final Report.',
        'Submission of Daily Diary at the end of training maintained & checked by the company representative.',
    ];
} else if ($course === 'B.TECH' && $trainingtype === 'software' && $durationvalue === '6 weeks') {
    $templatekey = 'btech-semestrial-software';
    $softwaresemester = s($semester);
    $subject = 'REQUEST FOR SOFTWARE TRAINING OF B.TECH ' . $softwaresemester . 'TH SEMESTER STUDENTS';
    $trainingintro = 'Since software training has become a necessity in today\'s scenario, '
        . 'the course curriculum is so designed that students get exposure to different '
        . 'kinds of software for designing and other purposes. We are in a process of '
        . 'enrolling the final year students of our institute to various Organisations '
        . 'for <b>SOFTWARE TRAINING (ATLEAST 6 WEEKS)</b> which is an essential component '
        . 'of their four year B.Tech programme.';
    $trainingdescription = 'software training';
    $programmeitems = [
        'Getting familiar with various software technologies related to the respective disciplines.',
        'Preparation and submission of synopsis.',
        'Working and acquiring proficiency in some software technology/application(s).',
        'Submission of Mid Term and Final Report.',
        'Submission of Daily Diary at the end of training maintained &amp; checked by the company representative.',
    ];
    $signatory = '<b>(Faculty Coordinator)</b>';
} else if ($course === 'MBA' && $trainingtype === 'industrial' && $durationvalue === '6 weeks') {
    $templatekey = 'mba-six-week-summer';
    $subject = 'REQUEST FOR 6-8 WEEKS SUMMER TRAINING FOR M.B.A. STUDENTS';
    $trainingintro = 'Guru Nanak Dev Engineering College has emerged as one of the most prestigious '
        . 'engineering institute of North India over 57 years of its inception. The College imparts '
        . 'a regular two year M.B.A programme &amp; offers specialisation in diverse fields of '
        . 'Marketing, Finance &amp; HRM in order to promote Quality Education, Research, Training '
        . 'and Consultancy for sharpening their Managerial, Leadership &amp; Entrepreneurial skills '
        . 'for meeting the challenges of Global business environment.<br /><br />'
        . 'For a closer Academic &amp; Corporate interaction, a 6-8 WEEKS SUMMER TRAINING Programme '
        . 'is incorporated within the curriculum of M.B.A under which the students would be required '
        . 'to work on a project in their respective areas of specialisation.';
    $trainingdescription = 'summer training';
    $programmeitems = [];
} else if ($course === 'MCA' && $trainingtype === 'industrial' && $durationvalue === '6 months') {
    $templatekey = 'mca-semestrial-industrial';
    $subject = '6 MONTHS INDUSTRIAL TRAINING FOR FINAL YEAR MCA STUDENTS';
    $trainingintro = 'Guru Nanak Dev Engineering College has emerged as one of the most prestigious '
        . 'engineering institute of North India over 57 years of its inception. The College imparts '
        . 'a regular three year M.C.A as well as B.Tech, M.Tech, &amp; PhD programme for meeting '
        . 'the research requirements of technical fields in order to promote quality education.<br /><br />'
        . 'For a closer Academic &amp; Corporate interaction, a 6 MONTHS INDUSTRIAL TRAINING Programme '
        . 'is incorporated within the curriculum of MCA in order to give insight into the practical '
        . 'aspect of industrial working under which the students would be required to work on a project '
        . 'as assigned by the organisation.';
    $trainingdescription = 'semestrial training';
    $programmeitems = [];
} else {
    throw new moodle_exception(
        'generalexceptionmessage',
        'error',
        '',
        'No verified GNDEC letter template exists for course "' . $course
            . '", training type "' . $trainingtype . '", and duration "' . $durationvalue . '".'
    );
}

// -------------------------------------------------------------------------
// PDF setup. Manual positioning is used to resemble the sample's A4 layout.
// -------------------------------------------------------------------------
$pdf = new pdf();
$pdf->SetCreator('GNDEC Training Letter System');
$pdf->SetAuthor('Guru Nanak Dev Engineering College');
$pdf->SetTitle('GNDEC Training Request Letter');
$pdf->SetSubject('Training Request Letter');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetPageOrientation('P');
$pdf->SetMargins(18, 9, 18);
$pdf->SetAutoPageBreak(false, 0);
$pdf->SetCellPadding(0);
$pdf->setCellHeightRatio(1.12);
$pdf->AddPage('P', 'A4');

// Letterhead emblem. Draw it immediately after AddPage() so it remains visible.
$pdf->Image($logopath, 29.0, 13.8, 25.0, 27.5, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

// Outer frame, as shown in the official sample.
$pdf->SetDrawColor(105, 105, 105);
$pdf->SetLineWidth(0.25);
$pdf->Rect(18, 9, 174, 278, 'D');

// The template's faint central emblem is not provided separately. Reuse the
// extracted GNDEC logo as a low-opacity watermark approximation.
$pdf->SetAlpha(0.075);
$pdf->Image($logopath, 63, 98, 84, 76, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);
$pdf->SetAlpha(1);

// Letterhead text.
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('times', 'B', 14.0);
$pdf->SetXY(58, 17.0);
$pdf->Cell(126, 5.0, 'GURU NANAK DEV ENGINEERING COLLEGE, LUDHIANA', 0, 1, 'C');
$pdf->SetFont('times', '', 9.0);
$pdf->SetXY(54, 21.8);
$pdf->Cell(130, 3.0, '(TRAINING AND PLACEMENT CELL)', 0, 1, 'C');
$pdf->SetFont('times', '', 8.0);
$pdf->SetXY(54, 26.8);
$pdf->Cell(130, 3.0, 'PUNJAB GOVERNMENT AIDED STATUS', 0, 1, 'C');
$pdf->SetFont('times', '', 8.0);
$pdf->SetXY(54, 29.5);
$pdf->Cell(130, 3.0, 'An Autonomous college under UGC Act-1956 [2(f) and 12(B)] - NBA', 0, 1, 'C');
$pdf->SetXY(54, 32.2);
$pdf->writeHTMLCell(
    130,
    3.0,
    54,
    32.2,
    'Accredited (<a href="http://www.gndec.ac.in" style="color:#0000FF;text-decoration:underline;">http://www.gndec.ac.in</a>)',
    0,
    1,
    false,
    true,
    'C',
    true
);

// Maroon header rule.
$pdf->SetDrawColor(133, 59, 59);
$pdf->SetLineWidth(0.9);
$pdf->Line(31, 44.8, 183, 44.8);

// Reference number and date row.
$pdf->SetFont('times', '', 10.0);
$pdf->SetXY(27, 50.0);
$pdf->Cell(88, 4.3, 'Ref.No. ' . $reference, 0, 0, 'L');
$pdf->SetXY(137, 50.0);
$pdf->Cell(46, 4.3, 'Dated: ' . $today, 0, 0, 'R');

$contentx = 27.0;
$contentw = 156.0;

/** Write a block at a fixed position and return TCPDF's reported ending Y. */
$writeHtmlAt = static function(
    string $html,
    float $x,
    float $y,
    float $width,
    float $fontSize = 12.0,
    string $align = 'L',
    float $lineRatio = 1.12
) use ($pdf): float {
    $pdf->SetFont('times', '', $fontSize);
    $pdf->setCellHeightRatio($lineRatio);
    $wrapped = '<div style="font-family:times;font-size:' . $fontSize
        . 'pt;line-height:' . $lineRatio . ';margin:0;padding:0;">' . $html . '</div>';
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell($width, 0, $x, $y, $wrapped, 0, 1, false, true, $align, true);
    return $pdf->GetY();
};

// Recipient block. Long addresses can push the letter body down slightly.
$recipientHtml = '<b>To</b><br />' . $companyname;
if ($companyaddresshtml !== '') {
    $recipientHtml .= '<br />' . $companyaddresshtml;
}
$recipientBottom = $writeHtmlAt($recipientHtml, $contentx, 55.0, 100.0, 12.0, 'L', 1.15);
// Flow content from the measured height of each block so long template text
// cannot overlap the following section.
$subjectY = max(69.5, $recipientBottom + 3.0);
$subjectBottom = $writeHtmlAt(
    '<b><u>SUBJECT:</u> ' . s($subject) . '</b>',
    $contentx,
    $subjectY,
    $contentw,
    12.0,
    'L',
    1.15
);

$sirBottom = $writeHtmlAt('Sir,', $contentx, $subjectBottom + 2.5, $contentw, 12.0, 'L', 1.15);
$greetingBottom = $writeHtmlAt(
    'Greetings from GNDEC, Ludhiana.',
    $contentx,
    $sirBottom + 4.0,
    $contentw,
    12.0,
    'L',
    1.15
);

$paragraph1 = 'Guru Nanak Dev Engineering College has emerged as one of the most prestigious '
    . 'engineering institute of North India over the 55 years of its inception and is '
    . 'conducting B.Tech. in seven disciplines as well as M.Tech., MBA and PhD. for '
    . 'meeting the research requirement of technical field.';
$hasSeparateCollegeParagraph = $templatekey === 'btech-semestrial-software';
$contentY = $greetingBottom + 4.0;
if ($hasSeparateCollegeParagraph) {
    $contentY = $writeHtmlAt($paragraph1, $contentx, $contentY, $contentw, 12.0, 'J', 1.15) + 4.0;
}

$trainingintroBottom = $writeHtmlAt(
    $trainingintro,
    $contentx,
    $contentY,
    $contentw,
    12.0,
    'J',
    1.15
);

$hasProgramme = !empty($programmeitems);
if ($hasProgramme) {
    $programmeBottom = $writeHtmlAt(
        '<u>The programme will be as under:</u>',
        $contentx,
        $trainingintroBottom + 4.0,
        $contentw,
        12.0,
        'L',
        1.15
    );
}

// Each bullet starts after the measured height of the previous bullet.
$bulletY = $hasProgramme ? $programmeBottom + 2.0 : $trainingintroBottom + 4.0;
foreach ($programmeitems as $item) {
    $pdf->SetFont('times', '', 12.0);
    $pdf->SetCellHeightRatio(1.15);

    // Draw round bullets as vector circles; this is more reliable than a
    // font-specific bullet character in different TCPDF installations.
    $pdf->SetFillColor(0, 0, 0);
    $pdf->Circle(26.4, $bulletY + 1.45, 0.48, 0, 360, 'F');

    $pdf->SetXY(31.0, $bulletY);
    $pdf->writeHTMLCell(151.5, 0, 31.0, $bulletY, $item, 0, 1, false, true, 'L', true);
    $bulletY = $pdf->GetY() + 1.0;
}

// Dynamic student recommendation details, including both roll numbers stored
// by this plugin. Slightly smaller bold type helps retain the sample's layout.
if ($course === 'MBA') {
    $recommendationHtml = '<b>We recommend our post-graduating student Mr./Ms. ' . $fullname
        . ', Roll No. ' . $rollno . ', University Roll no. ' . $universityrollno
        . ', M.B.A (Specialisation) ' . $branch . ', e-mail id ' . $email
        . ', Phone No. ' . $phone . ' to undergo summer training in your esteemed '
        . 'organization starting from ' . s($joiningdate) . '.</b>';
} else if ($course === 'MCA') {
    $recommendationHtml = '<b>We recommend our post-graduating student Mr./Ms. ' . $fullname
        . ', Roll No. ' . $rollno . ', University Roll no. ' . $universityrollno
        . ', MCA(' . s($semester) . 'th semester' . ') ' . $branch . ', e-mail id ' . $email
        . ', Phone No. ' . $phone . ' to undergo semestrial training in your esteemed '
        . 'organization starting from ' . s($joiningdate) . '.</b>';
} else {
    $recommendationHtml = '<b>We recommend our graduating student Mr./Ms. ' . $fullname
        . ', Roll no. ' . $rollno
        . ', University Roll no. ' . $universityrollno
        . ', of B.Tech (Branch) ' . $branch
        . ', Email Id ' . $email . ', Phone no. ' . $phone
        . ', to undergo ' . s($trainingdescription)
        . ' in your esteemed organization for ' . $duration
        . ', starting from ' . s($joiningdate) . '.</b>';
}
$recommendationBottom = $writeHtmlAt(
    $recommendationHtml,
    $contentx,
    $bulletY + 4.0,
    $contentw,
    11.5,
    'L',
    1.20
);

$noteBottom = $writeHtmlAt(
    '<i>(* Exact date of joining may be intimated at a later stage. An early and favourable response will be highly appreciated.)</i>',
    $contentx,
    $recommendationBottom + 2.0,
    $contentw,
    9.0,
    'L',
    1.18
);

$closingParagraph = 'We would very much appreciate if the student can be accommodated for the training '
    . 'programme. Our students are sincere and hard working and we are sure that they will put in '
    . 'their best efforts during the training program. Looking for the confirmation from your side.';
$closingBottom = $writeHtmlAt(
    $closingParagraph,
    $contentx,
    $noteBottom + 7.0,
    $contentw,
    12.0,
    'J',
    1.15
);

$sincerelyBottom = $writeHtmlAt(
    'Yours Sincerely,',
    $contentx,
    $closingBottom + 5.0,
    $contentw,
    11.5,
    'L',
    1.15
);
$signatureBottom = $writeHtmlAt(
    $signatory,
    $contentx,
    $sincerelyBottom + 3.0,
    $contentw,
    11.5,
    'L',
    1.18
);

// Keep the complete footer in the lower reserved area of the letter frame.
// The body and signature must remain above this fixed footer region.
$footerY = 273.0;

// Footer and maroon divider line, matching the reference sample.
$pdf->SetDrawColor(133, 59, 59);
$pdf->SetLineWidth(0.9);
$pdf->Line(32, $footerY, 182, $footerY);

$pdf->SetFont('times', 'B', 7.5);
$pdf->SetXY(31, $footerY + 1.5);
$pdf->Cell(151, 3.0, 'Contact Us At:', 0, 1, 'C');
$pdf->SetFont('times', '', 7.0);
$pdf->SetXY(27, $footerY + 4.5);
$pdf->Cell(156, 3.0, 'Address: Training and Placement Cell, GNDEC, Gill Road, Ludhiana - 141006', 0, 1, 'C');
$pdf->SetXY(27, $footerY + 7.5);
$pdf->writeHTMLCell(
    156,
    3.0,
    27,
    $footerY + 7.5,
    'E-mail: <a href="mailto:tpo@gndec.ac.in" style="color:#0000FF;text-decoration:underline;">tpo@gndec.ac.in</a> and <a href="mailto:mannkulvinder@yahoo.com" style="color:#0000FF;text-decoration:underline;">mannkulvinder@yahoo.com</a>',
    0,
    1,
    false,
    true,
    'C',
    true
);
$pdf->SetXY(27, $footerY + 10.5);
$pdf->Cell(156, 3.0, 'Telefax: +91-161-2501106   Cell: +91-9915507920   Phone No. 0161-5064510', 0, 1, 'C');

$filename = $fullname . '_' . $universityrollno . '_' . (int)$request->id . '.pdf';

// Clear buffered output before sending the PDF response.
while (ob_get_level() > 0) {
    ob_end_clean();
}

$pdf->Output($filename, 'D');
exit;
