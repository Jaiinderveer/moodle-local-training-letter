<?php
namespace local_training_letter\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

class request_form extends \moodleform {

    public function definition() {
        global $USER;

        $m = $this->_form;

        // Student details.
        $m->addElement(
            'header',
            'studentdetails',
            get_string('studentdetails', 'local_training_letter')
        );

        $m->addElement('text', 'fullname', get_string('fullname', 'local_training_letter'));
        $m->setType('fullname', PARAM_TEXT);
        $m->setDefault('fullname', fullname($USER));
        $m->addRule('fullname', null, 'required', null, 'client');

        $m->addElement('text', 'rollno', get_string('rollno', 'local_training_letter'));
        $m->setType('rollno', PARAM_TEXT);
        $m->addRule('rollno', null, 'required', null, 'client');

        $m->addElement('text', 'universityrollno', get_string('universityrollno', 'local_training_letter'));
        $m->setType('universityrollno', PARAM_TEXT);
        $m->addRule('universityrollno', null, 'required', null, 'client');

        $m->addElement('text', 'phone', get_string('phone', 'local_training_letter'));
        $m->setType('phone', PARAM_TEXT);
        $m->addRule('phone', null, 'required', null, 'client');

        $m->addElement('text', 'email', get_string('email', 'local_training_letter'));
        $m->setType('email', PARAM_EMAIL);
        $m->setDefault('email', $USER->email);
        $m->addRule('email', null, 'required', null, 'client');

        $m->addElement('select', 'course', get_string('course', 'local_training_letter'), [
            'B.Tech' => 'B.Tech',
            'MBA' => 'MBA',
            'MCA' => 'MCA',
        ]);
        $m->setDefault('course', 'B.Tech');
        $m->addRule('course', null, 'required', null, 'client');

        $m->addElement('text', 'branch', get_string('branch', 'local_training_letter'));
        $m->setType('branch', PARAM_TEXT);
        $m->setDefault('branch', 'Computer Science and Engineering');
        $m->addRule('branch', null, 'required', null, 'client');

        $m->addElement('text', 'semester', get_string('semester', 'local_training_letter'));
        $m->setType('semester', PARAM_TEXT);
        $m->addRule('semester', null, 'required', null, 'client');

        // Training details.
        $m->addElement(
            'header',
            'trainingdetails',
            get_string('trainingdetails', 'local_training_letter')
        );

        $m->addElement('select', 'trainingtype', get_string('trainingtype', 'local_training_letter'), [
            'Software' => get_string('software', 'local_training_letter'),
            'Industrial' => get_string('industrial', 'local_training_letter'),
        ]);
        $m->addRule('trainingtype', null, 'required', null, 'client');

        $m->addElement('select', 'duration', get_string('duration', 'local_training_letter'), [
            '6 Weeks' => get_string('sixweeks', 'local_training_letter'),
            '6 Months' => get_string('sixmonths', 'local_training_letter'),
        ]);
        $m->addRule('duration', null, 'required', null, 'client');

        // Company and mentor details.
        $m->addElement(
            'header',
            'companydetails',
            get_string('companydetails', 'local_training_letter')
        );

        $m->addElement('text', 'companyname', get_string('companyname', 'local_training_letter'));
        $m->setType('companyname', PARAM_TEXT);
        $m->addRule('companyname', null, 'required', null, 'client');

        $m->addElement('textarea', 'companyaddress', get_string('companyaddress', 'local_training_letter'), [
            'rows' => 3,
            'cols' => 50,
        ]);
        $m->setType('companyaddress', PARAM_TEXT);
        $m->addRule('companyaddress', null, 'required', null, 'client');

        $m->addElement('text', 'companycontact', get_string('companycontact', 'local_training_letter'));
        $m->setType('companycontact', PARAM_TEXT);
        $m->addRule('companycontact', null, 'required', null, 'client');

        $m->addElement('text', 'companyphone', get_string('companyphone', 'local_training_letter'));
        $m->setType('companyphone', PARAM_TEXT);
        $m->addRule('companyphone', null, 'required', null, 'client');

        $m->addElement('text', 'mentorname', get_string('mentorname', 'local_training_letter'));
        $m->setType('mentorname', PARAM_TEXT);
        $m->addRule('mentorname', null, 'required', null, 'client');

        $m->addElement('date_selector', 'joiningdate', get_string('joiningdate', 'local_training_letter'));
        $m->addRule('joiningdate', null, 'required', null, 'client');

        $this->add_action_buttons(true, get_string('submitrequest', 'local_training_letter'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

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
        ];

        foreach ($requiredfields as $field) {
            if (trim((string)($data[$field] ?? '')) === '') {
                $errors[$field] = get_string('required', 'local_training_letter');
            }
        }

        if (!empty($data['email']) && !validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');
        }

        foreach (['phone', 'companyphone'] as $field) {
            if (!empty($data[$field])
                    && !preg_match('/^[0-9+\-\s()]{7,20}$/', (string)$data[$field])) {
                $errors[$field] = get_string('invaliddata', 'error');
            }
        }

        if (empty($data['joiningdate'])) {
            $errors['joiningdate'] = get_string('required', 'local_training_letter');
        }

        return $errors;
    }
}
