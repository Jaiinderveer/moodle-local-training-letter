<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_training_letter_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_training_letter');

    if ($oldversion < 2026103100) {
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('fullname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
            $table->add_field('rollno', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, '');
            $table->add_field('universityrollno', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, '');
            $table->add_field('phone', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, '');
            $table->add_field('email', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
            $table->add_field('course', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, 'B.Tech');
            $table->add_field('branch', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '');
            $table->add_field('semester', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, '');
            $table->add_field('trainingtype', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'Software');
            $table->add_field('duration', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, '6 Weeks');
            $table->add_field('companyname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
            $table->add_field('companyaddress', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->add_field('companycontact', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
            $table->add_field('companyphone', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, '');
            $table->add_field('mentorname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
            $table->add_field('joiningdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending');
            $table->add_field('decisionnote', XMLDB_TYPE_TEXT, null, null, false);
            $table->add_field('decidedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timedecided', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $dbman->create_table($table);
        } else {
            $fields = [
                'userid' => [XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, '0'],
                'fullname' => [XMLDB_TYPE_CHAR, '255', XMLDB_NOTNULL, null, ''],
                'rollno' => [XMLDB_TYPE_CHAR, '50', XMLDB_NOTNULL, null, ''],
                'universityrollno' => [XMLDB_TYPE_CHAR, '50', XMLDB_NOTNULL, null, ''],
                'phone' => [XMLDB_TYPE_CHAR, '30', XMLDB_NOTNULL, null, ''],
                'email' => [XMLDB_TYPE_CHAR, '255', XMLDB_NOTNULL, null, ''],
                'course' => [XMLDB_TYPE_CHAR, '100', XMLDB_NOTNULL, null, 'B.Tech'],
                'branch' => [XMLDB_TYPE_CHAR, '100', XMLDB_NOTNULL, null, ''],
                'semester' => [XMLDB_TYPE_CHAR, '30', XMLDB_NOTNULL, null, ''],
                'trainingtype' => [XMLDB_TYPE_CHAR, '30', XMLDB_NOTNULL, null, 'Software'],
                'duration' => [XMLDB_TYPE_CHAR, '30', XMLDB_NOTNULL, null, '6 Weeks'],
                'companyname' => [XMLDB_TYPE_CHAR, '255', XMLDB_NOTNULL, null, ''],
                'companyaddress' => [XMLDB_TYPE_TEXT, null, XMLDB_NOTNULL, null, null],
                'companycontact' => [XMLDB_TYPE_CHAR, '255', XMLDB_NOTNULL, null, ''],
                'companyphone' => [XMLDB_TYPE_CHAR, '30', XMLDB_NOTNULL, null, ''],
                'mentorname' => [XMLDB_TYPE_CHAR, '255', XMLDB_NOTNULL, null, ''],
                'joiningdate' => [XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, '0'],
                'status' => [XMLDB_TYPE_CHAR, '20', XMLDB_NOTNULL, null, 'pending'],
                'decisionnote' => [XMLDB_TYPE_TEXT, null, null, null, null],
                'decidedby' => [XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, '0'],
                'timedecided' => [XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, '0'],
                'timecreated' => [XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, '0'],
                'timemodified' => [XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, '0'],
            ];

            foreach ($fields as $name => [$type, $length, $notnull, $sequence, $default]) {
                $field = new xmldb_field($name, $type, $length, null, $notnull, $sequence, $default);
                if (!$dbman->field_exists($table, $field)) {
                    $dbman->add_field($table, $field);
                }
            }
        }

        $useridkey = new xmldb_key('userid_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        if ($dbman->table_exists($table) && !$dbman->find_key_name($table, $useridkey)) {
            $dbman->add_key($table, $useridkey);
        }
        $decidedbykey = new xmldb_key('decidedby_fk', XMLDB_KEY_FOREIGN, ['decidedby'], 'user', ['id']);
        if ($dbman->table_exists($table) && !$dbman->find_key_name($table, $decidedbykey)) {
            $dbman->add_key($table, $decidedbykey);
        }

        $statusindex = new xmldb_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);
        if ($dbman->table_exists($table) && !$dbman->index_exists($table, $statusindex)) {
            $dbman->add_index($table, $statusindex);
        }
        $useridindex = new xmldb_index('userid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if ($dbman->table_exists($table) && !$dbman->index_exists($table, $useridindex)) {
            $dbman->add_index($table, $useridindex);
        }

        upgrade_plugin_savepoint(true, 2026103100, 'local', 'training_letter');
    }

    return true;
}
