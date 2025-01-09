<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/myplugin:use' => [
        'captype' => 'write', // Or 'read' depending on the capability
        'contextlevel' => CONTEXT_SYSTEM, // Adjust as needed, e.g., CONTEXT_COURSE
        'archetypes' => [
            'manager' => CAP_ALLOW, // Assign the permission to specific roles
        ],
    ],
];
