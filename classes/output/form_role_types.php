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
 * Renderable and templatable for role form.
 *
 * @package   block_teaching_team
 * @copyright Brendon Pretorius <bpretorius@2u.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_teaching_team\output;

use renderer_base;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class form_role_types implements \renderable, \templatable {

    /**
     * Exports template data
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template($output) {
        $data = new \stdClass();
        // Reindex (so mustache can iterate correctly) and assign.
        $data->records = $this->get_data();
        return $data;
    }

    /**
     * Utility function to get the data needed for the template
     * @return array
     */
    public function get_data() {
        global $DB;
        $sql = '
            SELECT gscc.id , gscc.salesforceapi AS salesforceapi, IF(r.name <> \'\', r.name, r.shortname) AS name, r.id as roleid, gscc.senderviewids
            FROM {gs_contactus_config} AS gscc
            INNER JOIN {user} AS u ON u.id = gscc.userid
            INNER JOIN {role} AS r ON r.id = gscc.fromroleid;
        ';

        $results = $DB->get_records_sql($sql);
        $sql = "SELECT id, formreason FROM {gs_contactus_mappings} WHERE id ";

        $allsenderviews = $this->get_all_sender_views();
        $allroles = $this->get_all_roles();

        foreach ($results as &$result) {
            list($insql, $params) = $DB->get_in_or_equal(explode(',', $result->senderviewids));
            $result->senderviews = array_values($DB->get_records_sql($sql . $insql, $params));
            $result->allsenderviews = $allsenderviews;

            // Add flag to allow pre-selecting on FE
            foreach ($result->senderviews as $senderview) {
                foreach ($result->allsenderviews as &$allsenderview) {
                    $allsenderview->selected = $allsenderview->selected || $allsenderview->id == $senderview->id;
                }
            }

            $result->allroles = $allroles;
            // Add flag to allow pre-selecting on FE
            foreach ($result->allroles as &$role) {
                $role->selected = $role->id == $result->roleid;
            }
        }

        // echo "<pre>";
        // var_dump($results);
        // exit;

        // Cast object to array.
        return array_values(json_decode(json_encode($results), true));
    }

    /**
     * Function to get all the sender views
     * @return array
     */
    public function get_all_sender_views() {
        global $DB;

        $results = $DB->get_records('gs_contactus_mappings', null, '', 'id, formreason');
        return array_values($results);
    }

    /**
     * Function to get all roles
     * @return array
     */
    public function get_all_roles() {
        global $DB;

        $results = $DB->get_records('role', null, '', 'id, shortname');
        return array_values($results);
    }
}
