<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_myplugin', 'FastAPI Integration');
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
        'local_myplugin/api_url',
        'FastAPI URL',
        'Enter the URL of the FastAPI server.',
        'http://127.0.0.1:8000/docs#/job/generate_mcqs/',
        PARAM_URL
    ));
}
