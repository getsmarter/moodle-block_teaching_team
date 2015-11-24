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
 * Default settings page
 *
 * @package    block_teaching_team
 * @copyright  2014 GetSmarter {@link http://www.getsmarter.co.za}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Default settings heading.
    $name = 'block_teaching_team/default_settings_heading';
    $title = get_string('default_settings_heading', 'block_teaching_team');
    $description = get_string('default_settings_heading_desc', 'block_teaching_team');
    $setting = new admin_setting_heading($name, $title, $description, FORMAT_MARKDOWN);
    $settings->add($setting);

    // Display profile picture.
    $name = 'block_teaching_team/display_profile_picture';
    $title = get_string('display_profile_picture', 'block_teaching_team');
    $description = get_string('display_profile_picture_desc', 'block_teaching_team');
    $default = 1;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);

    // Display role.
    $name = 'block_teaching_team/display_role';
    $title = get_string('display_role', 'block_teaching_team');
    $description = get_string('display_role_desc', 'block_teaching_team');
    $default = 1;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);

    // Display first name.
    $name = 'block_teaching_team/display_firstname';
    $title = get_string('display_firstname', 'block_teaching_team');
    $description = get_string('display_firstname_desc', 'block_teaching_team');
    $default = 1;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);

    // Display lastname.
    $name = 'block_teaching_team/display_lastname';
    $title = get_string('display_lastname', 'block_teaching_team');
    $description = get_string('display_lastname_desc', 'block_teaching_team');
    $default = 1;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);

    // Display email.
    $name = 'block_teaching_team/display_email';
    $title = get_string('display_email', 'block_teaching_team');
    $description = get_string('display_email_desc', 'block_teaching_team');
    $default = 1;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);

    // Display custom profile field 1.
    $name = 'block_teaching_team/display_custom_profile_field_1';
    $title = get_string('display_custom_profile_field_1', 'block_teaching_team');
    $description = get_string('display_custom_profile_field_1_desc', 'block_teaching_team');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    // Display custom profile field 2.
    $name = 'block_teaching_team/display_custom_profile_field_2';
    $title = get_string('display_custom_profile_field_2', 'block_teaching_team');
    $description = get_string('display_custom_profile_field_2_desc', 'block_teaching_team');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    // Display custom profile field 3.
    $name = 'block_teaching_team/display_custom_profile_field_3';
    $title = get_string('display_custom_profile_field_3', 'block_teaching_team');
    $description = get_string('display_custom_profile_field_3_desc', 'block_teaching_team');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);
}
