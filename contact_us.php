<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Teaching Team block
 *
 * @package    block_teaching_team
 * @copyright  2020 GetSmarter {@link http://www.getsmarter.co.za}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_teaching_team\output\contact_us;

require_once('../../config.php');
require_once('contactuslib.php');
require_login();

// Safety check to see if role configured.
$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);
$userrole = current(get_user_roles($context, $USER->id))->roleid;

$mappings = $DB->get_records_menu('gs_contactus_config', null, '', 'id, fromroleid');
$mappingexists = false;
$url = new moodle_url('/blocks/teaching_team/course.php', [
    'courseid' => $courseid
]);

if (in_array($userrole, $mappings)) {
    $mappingexists = true;
}

$configcontactformenabled = get_config('block_teaching_team');

// Redirect if not exist.
if (!$mappingexists || empty($configcontactformenabled->contact_us_form_enable)) {
    redirect($url);
}

$output = $PAGE->get_renderer('block_teaching_team');
$form = new contact_us($userrole, $url);

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('contact_us_form_page_title', 'block_teaching_team'));
$PAGE->set_heading(get_string('contact_us_form_page_heading', 'block_teaching_team'));

echo $OUTPUT->header();
echo $output->render($form);
echo $OUTPUT->footer();
