<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/myplugin/lib.php');

require_login();
$context = context_system::instance();
require_capability('local/myplugin:use', $context);

$PAGE->set_url(new moodle_url('/local/myplugin/index.php'));
$PAGE->set_context($context);
$PAGE->set_title('FastAPI Integration');
$PAGE->set_heading('FastAPI Integration');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = required_param('data', PARAM_RAW);
    $processed_data = local_fastapi_integration_process_data(json_decode($data, true));
}

echo $OUTPUT->header();
?>

<form method="post">
    <label for="data">Data to Process:</label>
    <input type="text" id="data" name="data">
    <button type="submit">Submit</button>

</form>
<?php
    echo "You submitted: " . htmlspecialchars($data); // Display the data back to the user.
?>
<?php
if (!empty($processed_data)) {
    echo '<pre>' . print_r($processed_data, true) . '</pre>';
}
echo $OUTPUT->footer();
?>
