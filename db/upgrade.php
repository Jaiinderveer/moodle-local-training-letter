
<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_training_letter_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026102702) {
        $table = new xmldb_table('local_training_letter');

        if (!$DB->get_manager()->table_exists($table)) {
            $table->add_field(
                'id',
                XMLDB_TYPE_INTEGER,
                '10',
                null,
                XMLDB_NOTNULL,
                XMLDB_SEQUENCE
            );

            $table->add_field(
                'userid',
                XMLDB_TYPE_INTEGER,
                '10',
                null,
                XMLDB_NOTNULL,
                null,
                '0'
            );

            foreach ([
                'fullname' => 255,
                'email' => 255,
                'companyname' => 255,
                'companycontact' => 255,
                'mentorname' => 255
            ] as $name => $length) {
                $table->add_field(
                    $name,
                    XMLDB_TYPE_CHAR,
                    (string)$length,
                    null,
                    XMLDB_NOTNULL,
                    null,
                    ''
                );
            }

            foreach ([
                'rollno' => 50,
                'universityrollno' => 50,
                'phone' => 30,
                'companyphone' => 30,
                'course' => 100,
                'branch' => 100,
                'semester' => 30,
                'trainingtype' => 30,
                'duration' => 30,
                'status' => 20
            ] as $name => $length) {
                $default = ($name === 'status') ? 'pending' : '';

                $table->add_field(
                    $name,
                    XMLDB_TYPE_CHAR,
                    (string)$length,
                    null,
                    XMLDB_NOTNULL,
                    null,
                    $default
                );
            }

            $table->add_field(
                'companyaddress',
                XMLDB_TYPE_TEXT,
                null,
                null,
                XMLDB_NOTNULL
            );

            $table->add_field(
                'decisionnote',
                XMLDB_TYPE_TEXT,
                null,
                null,
                false
            );

            foreach ([
                'joiningdate',
                'decidedby',
                'timedecided',
                'timecreated',
                'timemodified'
            ] as $name) {
                $table->add_field(
                    $name,
                    XMLDB_TYPE_INTEGER,
                    '10',
                    null,
                    XMLDB_NOTNULL,
                    null,
                    '0'
                );
            }

            $table->add_key(
                'primary',
                XMLDB_KEY_PRIMARY,
                ['id']
            );

            // Foreign key name changed to avoid the index-name collision.

            $table->add_index(
                'status_idx',
                XMLDB_INDEX_NOTUNIQUE,
                ['status']
            );

            $table->add_index(
                'userid_idx',
                XMLDB_INDEX_NOTUNIQUE,
                ['userid']
            );

            $DB->get_manager()->create_table($table);
        }

        upgrade_plugin_savepoint(
            true,
            2026102702,
            'local',
            'training_letter'
        );
    }

    return true;
}
