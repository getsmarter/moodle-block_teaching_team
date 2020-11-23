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
 * Renderable and templatable for a single role form.
 *
 * @package   block_teaching_team
 * @copyright Jan Swanevelder <jswanevelder@2u.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_teaching_team\output;

use renderer_base;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class form_role_types_single implements \renderable, \templatable {

    /**
     * Exports template data
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template($output) {
        $data = new \stdClass();
        // Reindex (so mustache can iterate correctly) and assign.
        list($data->availableroles, $data->senderviews) = $this->get_data();
        return $data;
    }

    /**
     * Utility function to get the available roles for template use
     * @return array
     */
    public function get_data() {
        global $DB;

        $sql = "SELECT id, shortname
                        FROM {role} r
                        WHERE r.id
                        NOT IN (SELECT gcc.fromroleid FROM {gs_contactus_config} gcc)";

    // Cast object to array.
        $availableroles = array_values(
            json_decode(
                json_encode(
                    $DB->get_records_sql($sql)
                ),
                true
            )
        );
        $senderviews = array_values(
            json_decode(
                json_encode(
                    $DB->get_records('gs_contactus_mappings', null, '', 'id, formreason')
                ),
                true
            )
        );
        return [$availableroles, $senderviews];
    }
}