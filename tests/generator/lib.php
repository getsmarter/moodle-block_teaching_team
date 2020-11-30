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
 * Testing generator.
 *
 * @package   blocks_teaching_team
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Testing generator.
 *
 * @package   blocks_teaching_team
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_teaching_team_generator extends component_generator_base {

    /**
     * Create config for the contact us
     * @param int $userid
     * @param int $fromroleid
     * @param string|null (optional) $salesforceapi
     * @return int
     * @throws Exception
     */
    public function create_contact_us_config($userid, $fromroleid, $salesforceapi = null) {
        global $DB;
        if (is_null($salesforceapi)) {
            $salesforceapi = random_string(50);
        }

        $data = new stdClass();
        $data->userid = $userid;
        $data->fromroleid = $fromroleid;
        $data->salesforceapi = $salesforceapi;
        return $DB->insert_record('gs_contactus_config', $data);
    }

    /**
     * Create mappings for the contact us
     * @param int $userid
     * @param int $fromroleid
     * @param string|null (optional) $salesforceapi
     * @return int
     * @throws Exception
     */
    public function create_contact_us_mappings($userid, $formreason = null, $sfmapping = null) {
        global $DB;

        if (is_null($formreason)) {
            $formreason = random_string(50);
        }

        if (is_null($sfmapping)) {
            $sfmapping = random_string(50);
        }

        $data = new stdClass();
        $data->userid = $userid;
        $data->formreason = $formreason;
        $data->sfmapping = $sfmapping;
        return $DB->insert_record('gs_contactus_mappings', $data);
    }
}
