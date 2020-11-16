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

use block_teaching_team\output\contactusform;

require_once('../../config.php');
require_once('contactuslib.php');
require_login();

$context = context_system::instance();
$output = $PAGE->get_renderer('block_teaching_team');
$form = new contactusform();

require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('contact_us_form_page_title', 'block_teaching_team'));
$PAGE->set_heading(get_string('contact_us_form_page_heading', 'block_teaching_team'));

echo $OUTPUT->header();
echo $output->render($form);
echo $OUTPUT->footer();
