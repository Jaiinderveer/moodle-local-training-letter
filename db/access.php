<?php
defined('MOODLE_INTERNAL') || die();
$capabilities = [
 'local/training_letter:request' => ['riskbitmask'=>RISK_PERSONAL,'captype'=>'write','contextlevel'=>CONTEXT_SYSTEM,'archetypes'=>['user'=>CAP_ALLOW]],
 'local/training_letter:manage' => ['riskbitmask'=>RISK_PERSONAL,'captype'=>'write','contextlevel'=>CONTEXT_SYSTEM,'archetypes'=>['manager'=>CAP_ALLOW]],
];
