<?php
require_once('../config.php');

$PAGE->set_url('/login/logout.php');
$PAGE->set_context(context_system::instance());

$sesskey = optional_param('sesskey', '__notpresent__', PARAM_RAW); // we want not null default to prevent required sesskey warning
$login   = optional_param('loginpage', 0, PARAM_BOOL);

// can be overridden by auth plugins
if ($login) {
    $redirect = get_login_url();
} else {
    $redirect = $CFG->wwwroot.'/';
}

if (!isloggedin()) {
    // no confirmation, user has already logged out
    require_logout();
    redirect($redirect);

} else if (!confirm_sesskey($sesskey)) {
    $PAGE->set_title(get_string('logout'));
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('logoutconfirm'), new moodle_url($PAGE->url, array('sesskey' => sesskey())), $CFG->wwwroot.'/');
    echo $OUTPUT->footer();
    die;
}

// Get the user's authentication method
global $USER;
if (!empty($USER->auth) && $USER->auth === 'auth0') {
    // User logged in via Auth0
    $auth0_logout_url = 'https://dev-yqj2guchohe24jhn.us.auth0.com/v2/logout';
    $client_id = '47oQNmpqPqvPsyugKyifg2HXv5d9TtKf';
    $return_to_url = $CFG->wwwroot . '/login/logout.php?sesskey=' . $sesskey;

    // Build Auth0 logout URL
    $logout_url = $auth0_logout_url . '?client_id=' . $client_id . '&returnTo=' . urlencode($return_to_url);

    // Perform the Auth0 logout
    redirect($logout_url);
}

// Default Moodle logout sequence
$authsequence = get_enabled_auth_plugins(); // auths, in sequence
foreach ($authsequence as $authname) {
    $authplugin = get_auth_plugin($authname);
    $authplugin->logoutpage_hook();
}

require_logout();
redirect($redirect);
