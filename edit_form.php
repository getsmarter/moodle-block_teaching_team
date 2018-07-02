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
 * Edit block page
 *
 * @package    block_teaching_team
 * @copyright  2014 GetSmarter {@link http://www.getsmarter.co.za}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class adds custom form fields
 *
 * @package    block_teaching_team
 * @copyright  2014 GetSmarter {@link http://www.getsmarter.co.za}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_teaching_team_edit_form extends block_edit_form {

    /**
     * Add form fields specific to this block
     * @param object $mform the form being built
     */
    protected function specific_definition($mform) {
        global $DB, $COURSE;

        $config = get_config('block_teaching_team');

        // Heading.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Title.
        $mform->addElement('text', 'config_title', get_string('name'));
        $mform->setType('config_title', PARAM_TEXT);
        $mform->setDefault('config_title', get_string('pluginname', 'block_teaching_team'));

        // Profile picture.
        $mform->addElement('advcheckbox', 'config_display_profile_picture',
            get_string('display_profile_picture', 'block_teaching_team'));
        $mform->setDefault('config_display_profile_picture', $config->display_profile_picture);

        // Role.
        $mform->addElement('advcheckbox', 'config_display_role',
            get_string('display_role', 'block_teaching_team'));
        $mform->setDefault('config_display_role', $config->display_profile_picture);

        // First name.
        $mform->addElement('advcheckbox', 'config_display_firstname',
            get_string('display_firstname', 'block_teaching_team'));
        $mform->setDefault('config_display_firstname', $config->display_profile_picture);

        // Surname.
        $mform->addElement('advcheckbox', 'config_display_lastname',
            get_string('display_lastname', 'block_teaching_team'));
        $mform->setDefault('config_display_lastname', $config->display_profile_picture);

        // Email.
        $mform->addElement('advcheckbox', 'config_display_email',
            get_string('display_email', 'block_teaching_team'));
        $mform->setDefault('config_display_email', $config->display_profile_picture);

        // Custom profile field 1.
        $mform->addElement('text', 'config_display_custom_profile_field_1',
            get_string('display_custom_profile_field_1', 'block_teaching_team'));
        $mform->setType('config_display_custom_profile_field_1', PARAM_TEXT);
        $mform->setDefault('config_display_custom_profile_field_1', $config->display_custom_profile_field_1);

        // Custom profile field 2.
        $mform->addElement('text', 'config_display_custom_profile_field_2',
            get_string('display_custom_profile_field_2', 'block_teaching_team'));
        $mform->setType('config_display_custom_profile_field_2', PARAM_TEXT);
        $mform->setDefault('config_display_custom_profile_field_2', $config->display_custom_profile_field_1);

        // Custom profile field 3.
        $mform->addElement('text', 'config_display_custom_profile_field_3',
            get_string('display_custom_profile_field_3', 'block_teaching_team'));
        $mform->setType('config_display_custom_profile_field_3', PARAM_TEXT);
        $mform->setDefault('config_display_custom_profile_field_3', $config->display_custom_profile_field_1);

        $roles = $this->get_course_user_roles();

        $mform->addElement('select', 'config_role_1', get_string('role_1', 'block_teaching_team'), $roles);
        $mform->addElement('select', 'config_role_2', get_string('role_2', 'block_teaching_team'), $roles);
        $mform->addElement('select', 'config_role_3', get_string('role_3', 'block_teaching_team'), $roles);
        $mform->addElement('select', 'config_role_4', get_string('role_4', 'block_teaching_team'), $roles);
        $mform->addElement('select', 'config_role_5', get_string('role_5', 'block_teaching_team'), $roles);
        $mform->addElement('select', 'config_role_6', get_string('role_6', 'block_teaching_team'), $roles);

        $mform->addElement('header', 'block_teaching_team_groupings', get_string('grouping_header', 'block_teaching_team'));

        $options = array(NOGROUPS       => get_string('groupsnone'),
                             SEPARATEGROUPS => get_string('groupsseparate'),
                             VISIBLEGROUPS  => get_string('groupsvisible'));
        $mform->addElement('select', 'config_groupmode', get_string('groupmode', 'group'), $options, NOGROUPS);
        $mform->addHelpButton('config_groupmode', 'groupmode', 'group');


        $options = array();
        if ($groupings = $DB->get_records('groupings', array('courseid'=>$COURSE->id))) {
            foreach ($groupings as $grouping) {
                $options[$grouping->name] = format_string($grouping->name);
            }
        }
        core_collator::asort($options);
        $options = array(0 => get_string('none')) + $options;
        $mform->addElement('select', 'config_grouping', get_string('grouping', 'group'), $options);
        $mform->addHelpButton('config_grouping', 'grouping', 'group');
    }

    /**
     * Returns an array of users in the course formatted for a select box.
     */
    private function get_course_users() {
        global $PAGE;

        $courseid = $PAGE->course->id;
        $context = context_course::instance($courseid);
        $users = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname', null, 0, 0, true);

        foreach ($users as $key => &$value) {
            $value = $value->firstname . ' ' . $value->lastname;
        }

        $users = array('0' => get_string('no_user', 'block_teaching_team')) + $users;

        return $users;
    }

    /**
     * Returns an array of users in the course formatted for a select box.
     */
    private function get_course_user_roles() {
        global $PAGE, $DB;

        $courseid = $PAGE->course->id;
        $context = context_course::instance($courseid);
        $rolessql = 'SELECT r.id, r.name  from {role_assignments} ra LEFT JOIN {role} r on ra.roleid WHERE r.shortname <> "student" and r.name <> "" and ra.contextid = ?';

        $roles = $DB->get_records_sql_menu($rolessql, array($context->id));

        return array('0' => get_string('no_user', 'block_teaching_team')) + $roles;
    }    
}
