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
        return array('course' => true, 'blocks-teaching_team-course' => true);
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
        global $OUTPUT, $PAGE, $CFG, $USER, $DB;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_course::instance($PAGE->course->id);
        $canviewuserdetails = has_capability('moodle/user:viewdetails', $context);
        $configured = (
            isset($this->config->role_1) ||
            isset($this->config->role_2) ||
            isset($this->config->role_3) ||
            isset($this->config->role_4) ||
            isset($this->config->role_5) ||
            isset($this->config->role_6)
        );

        // Render block contents.
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->text .= html_writer::start_tag('div', array('class' => 'teaching_team'));

        if ($canviewuserdetails && $configured) {

            $users = $this->get_teaching_team_users();

            if (sizeof($users) == 0) {
                $this->content->text .= html_writer::tag('p', get_string('user_has_no_group', 'block_teaching_team'));
                return $this->content;
            }
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

        $courseid = $PAGE->course->id;
        $context = context_course::instance($courseid);
        $userroles = get_user_roles($context, $USER->id);
        $mappings = $DB->get_records_menu('gs_contactus_config', null, '', 'id, fromroleid');
        $configcontactformenabled = get_config('block_teaching_team');

        foreach ($userroles as $userrole) {
            if (in_array($userrole->roleid, $mappings)) {
                $url = new moodle_url('/blocks/teaching_team/contact_us.php', [
                    'courseid' => $courseid
                ]);
                $this->content->text .= html_writer::start_tag('div');
                if (!empty($configcontactformenabled->contact_us_form_enable)) {
                    $this->content->text .= html_writer::tag(
                        'a',
                        get_string('contact_us_form_support_page_link', 'block_teaching_team'),
                        [
                            'href' => $url,
                            'class' => 'btn btn-primary mx-auto',
                            'style' => 'border-bottom: 1px solid #bbb !important'
                        ]
                    );
                }

                if (!empty($configcontactformenabled->contact_us_form_support_help_link)) {
                    $this->content->text .= html_writer::tag(
                        'a',
                        get_string('contact_us_form_support_help_link', 'block_teaching_team'),
                        [
                            'class' => 'btn btn-primary',
                            'id' => 'contact_us_form_support_help_link',
                            'style' => 'margin-left: 4px; border-bottom: 1px solid #bbb !important'
                        ]
                    );
                }
                $this->content->text .= html_writer::end_tag('div');
            }
        }

        $this->content->text .= html_writer::end_tag('div');

        return $this->content;
    }

    /**
     * Get teaching team users
     */
    protected function get_teaching_team_users() {
        global $PAGE, $DB, $USER;

        $courseid = $PAGE->course->id;
        $context = context_course::instance($courseid);

        $groupids = '';

        if (isset($this->config->groupmode)) {
            if ($this->config->groupmode == 1) {
                if (isset($this->config->grouping)) {
                    foreach ($this->get_user_groups($this->config->grouping, $courseid, $USER->id) as $groupid) {
                        $groupids.= $groupid->id.',';
                    }
                }

                if ($groupids === '') {
                    return array();
                }
            }
        }

        if ((isset($this->config->groupmode) || isset($this->config->grouping)) && $groupids === '') {
            if ($this->config->groupmode == 1) {
                return array();
            }
        }


        $groupids = rtrim($groupids, ',');

        $roleid = array(
            'roleid1' => $this->config->role_1,
            'roleid2' => $this->config->role_2,
            'roleid3' => $this->config->role_3,
            'roleid4' => $this->config->role_4,
            'roleid5' => $this->config->role_5,
            'roleid6' => $this->config->role_6
        );

        list($roleassigninsql,
            $params) = $DB->get_in_or_equal($roleid, SQL_PARAMS_NAMED);

        $roleassignsql = 'SELECT ra.id, ra.roleid AS roleid, u.id, u.firstname, u.lastname, u.email, u.maildisplay FROM {role_assignments} ra LEFT JOIN {user} u on ra.userid = u.id LEFT JOIN {groups_members} gm ON u.id = gm.userid WHERE ra.contextid = '.$context->id.' AND ra.roleid ';

        $roleassignsql .= $roleassigninsql;

        if ($groupids != '') {
            $roleassignsql .= ' AND gm.groupid IN ('.$groupids.')';
        }

        $roleassignsql .= ' ORDER BY FIELD(roleid, '.$this->config->role_1.', '.$this->config->role_2.', '.$this->config->role_3.', '.$this->config->role_4.', '.$this->config->role_5.', '.$this->config->role_6.')';

        $users = $DB->get_records_sql($roleassignsql, $params);

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

            $html .= html_writer::start_tag('ul', array('class' => 'user_details'));
            $html .= html_writer::tag('li', $this->user_role($user), array('class' => 'detail role'));
            $html .= html_writer::tag('li', $this->user_name($user), array('class' => 'detail name'));
            $html .= html_writer::tag('li', $this->user_email($user), array('class' => 'detail email'));
            $html .= $this->user_custom_profile_fields($user->id);

            $html .= html_writer::end_tag('ul');

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


    protected function get_user_groups($groupingname, $courseid, $userid) {
        global $DB;

        $groupidsql = 'SELECT
                           g.id
                        FROM
                            {groups_members} gm
                                LEFT JOIN
                            {groups} g ON gm.groupid = g.id
                                LEFT JOIN
                            {groupings_groups} gg on g.id = gg.groupid
                                LEFT JOIN
                            {groupings} gs on gg.groupingid = gs.id
                        WHERE
                             gs.name = ? AND g.courseid = ? AND gm.userid = ?';

        $groupids = $DB->get_records_sql($groupidsql, array($groupingname, $courseid, $userid));

        return $groupids;

    }
}
