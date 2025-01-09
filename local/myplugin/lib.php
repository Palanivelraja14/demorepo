<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php'); // Include Moodle's cURL library.

function local_fastapi_integration_process_data($data) {
    global $CFG;

    $api_url = get_config('local_myplugin', 'api_url');
    // $api_url = 'http://127.0.0.1:8000/job/generate_mcqs';

    $curl = new curl();
    $options = [
        'CURLOPT_RETURNTRANSFER' => true,  
        'CURLOPT_HTTPHEADER' => ['Content-Type: application/json'],
    ];

    $response = $curl->post("$api_url/process-data/", json_encode($data), $options);
    return json_decode($response, true);
}
