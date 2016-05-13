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
 * @copyright  2014 GetSmarter {@link http://www.getsmarter.co.za}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class overrides some block properties and generates the block content
 *
 * @package    block_teaching_team
 * @copyright  2014 GetSmarter {@link http://www.getsmarter.co.za}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_teaching_team extends block_base {

    private $courseroleids;

    /**
     * Initialize the block
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_teaching_team');
    }

    /**
     * Check if block has config
     */
    public function has_config() {
        return true;
    }

    /**
     * Check block formats
     */
    public function applicable_formats() {
        return array('course' => true);
    }

    /**
     * Block title
     */
    public function specialization() {
        if (isset($this->config->title)) {
            $this->title = format_string($this->config->title);
        } else {
            $this->title = format_string(get_string('pluginname', 'block_teaching_team'));
        }
    }

    /**
     * Allow multiple blocks
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Generate block content
     */
    public function get_content() {
        global $OUTPUT, $PAGE, $CFG;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_course::instance($PAGE->course->id);
        $canviewuserdetails = has_capability('moodle/user:viewdetails', $context);
        $configured = (
            isset($this->config->user_1) ||
            isset($this->config->user_2) ||
            isset($this->config->user_3) ||
            isset($this->config->user_4) ||
            isset($this->config->user_5) ||
            isset($this->config->user_6)
        );

        // Render block contents.
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->text .= html_writer::start_tag('div', array('class' => 'teaching_team'));

        if ($canviewuserdetails && $configured) {

            $users = $this->get_teaching_team_users();
            $this->courseroleids = array_keys(get_profile_roles($this->context));

            foreach ($users as $user) {
                $this->content->text .= $this->render_user_profile($user);
            }
        }

        if (!$canviewuserdetails) {
            $this->content->text .= html_writer::tag('p', get_string('cannot_view_user_details', 'block_teaching_team'));
        }

        if (!$configured) {
            $this->content->text .= html_writer::tag('p', get_string('not_configured', 'block_teaching_team'));
        }

        $this->content->text .= html_writer::end_tag('div');

        return $this->content;
    }

    /**
     * Get teaching team users
     */
    protected function get_teaching_team_users() {
        global $DB;

        $userids = array(
            'userid1' => $this->config->user_1,
            'userid2' => $this->config->user_2,
            'userid3' => $this->config->user_3,
            'userid4' => $this->config->user_4,
            'userid5' => $this->config->user_5,
            'userid6' => $this->config->user_6
        );

        list($useridinsql,
            $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $sql = "SELECT *
                    FROM {user}
                    WHERE id ";

        $sql .= $useridinsql;

        $sql .= " ORDER BY FIELD(id, :userid1, :userid2, :userid3, :userid4, :userid5, :userid6)";

        $params += $userids;

        $users = $DB->get_records_sql($sql, $params);

        return $users;
    }

    /**
     * Render user profile
     * @param object $userid the user id
     */
    protected function render_user_profile($user) {
        global $DB, $OUTPUT, $USER;

        if ($user) {
            $html = '';
            $html .= html_writer::start_tag('div', array('class' => 'user_profile'));

            $html .= html_writer::tag('div', $this->user_profile_picture($user), array('class' => 'user_picture'));

            $html .= html_writer::start_tag('div', array('class' => 'user_details'));
            $html .= html_writer::tag('div', $this->user_role($user), array('class' => 'detail role'));
            $html .= html_writer::tag('div', $this->user_name($user), array('class' => 'detail name'));
            $html .= html_writer::tag('div', $this->user_email($user), array('class' => 'detail email'));
            $html .= $this->user_custom_profile_fields($user->id);

            $html .= html_writer::end_tag('div');

            $html .= html_writer::end_tag('div');

            return $html;
        }
    }

    /**
     * Render user profile picture
     * @param object $user the user
     */
    protected function user_profile_picture(&$user) {
        global $OUTPUT;

        if ($this->config->display_profile_picture) {
            return $OUTPUT->user_picture($user, array('size' => 100, 'class' => 'user_image'));
        }
    }

    /**
     * Render user role
     * @param object $user the user
     */
    protected function user_role(&$user) {
        global $PAGE;

        if ($this->config->display_role) {
            $userrole = get_user_roles_in_course($user->id, $PAGE->course->id);
            return $userrole;
        }
    }

    /**
     * Render user name
     * @param object $user the user
     */
    protected function user_name(&$user) {

        if ($this->config->display_firstname) {
            $names[] = $user->firstname;
        }

        if ($this->config->display_lastname) {
            $names[] = $user->lastname;
        }

        return join(' ', $names);
    }

    /**
     * Render user email
     * @param object $user the user
     */
    protected function user_email(&$user) {
        if ($this->config->display_email && $user->maildisplay != 0) {
            return html_writer::tag('a', $user->email, array('href' => 'mailto:' . $user->email));
        }
    }

    /**
     * Render user profile fields
     * @param integer $userid the user's id
     */
    protected function user_custom_profile_fields($userid) {

        $fields = array(
            'profilefield1' => $this->config->display_custom_profile_field_1,
            'profilefield2' => $this->config->display_custom_profile_field_2,
            'profilefield3' => $this->config->display_custom_profile_field_3
        );

        $profiledata = $this->get_custom_profile_field_data($userid, $fields);

        $html = '';
        foreach ($profiledata as $userprofiledata) {
            $html .= html_writer::tag('div', $userprofiledata->data, array('class' => "detail $userprofiledata->shortname"));
        }

        return $html;
    }

    /**
     * Get custom profile field data
     * @param integer $userid the user's id
     * @param array $fields the user profile fields to display
     */
    protected function get_custom_profile_field_data($userid, $fields) {
        global $DB;

        list($fieldsinsql, $fieldsinparams) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED);

        $sql = "SELECT uif.shortname, uid.data
                FROM
                    {user_info_data}  uid
                INNER JOIN
                    {user} u
                ON
                    uid.userid = u.id
                INNER JOIN
                    {user_info_field}  uif
                ON
                    uif.id = uid.fieldid
                WHERE
                    u.id = :userid
                AND
                    uif.shortname ";

        $params = array('userid' => $userid);

        $sql .= $fieldsinsql;
        $params += $fieldsinparams;

        $sql .= " ORDER BY FIELD(uif.shortname, :profilefield1, :profilefield2, :profilefield3)";

        $params += $fields;

        $profiledata = $DB->get_records_sql($sql, $params);

        return $profiledata;
    }
}
