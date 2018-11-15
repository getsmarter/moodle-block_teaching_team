<?php
/**
 * Teaching Team block page. Dummy blank page to display teaching team block
 */

// Include required files.
require_once(dirname(__FILE__) . '/../../config.php');

// Gather form data.
$courseid = required_param('courseid', PARAM_INT);

// Determine course and context.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// Set up page parameters.
$PAGE->set_course($course);
$PAGE->requires->css('/blocks/teaching_team/styles.css');
$PAGE->set_url(
    '/blocks/teaching_team/course.php',
    array(
        'courseid' => $courseid,
    )
);

$context = context_course::instance($course->id);
$PAGE->set_course($course);
$PAGE->set_context($context);
$title = get_string('pluginname', 'block_teaching_team');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);
$PAGE->set_pagelayout('standard');

// Check user is logged in and capable of grading.
require_login($course, false);

// Start page output.
echo $OUTPUT->header();
echo $OUTPUT->footer();
