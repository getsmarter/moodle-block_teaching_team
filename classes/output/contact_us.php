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
 * Renderable and templatable for contact us page.
 *
 * @package   block_teaching_team
 * @copyright Brendon Pretorius <bpretorius@2u.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_teaching_team\output;

use dml_exception;
use moodle_url;
use renderer_base;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class contact_us implements \renderable, \templatable {
    /** @param string $cancelurl */
    private $cancelurl;
    /** @param int $userrole */
    private $userroleid;
    public function __construct($userroleid, $cancelurl = '') {
        $this->cancelurl = $cancelurl;
        $this->userroleid = $userroleid;
    }

    /**
     * Exports template data
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template($output) {
        $data = new \stdClass();
        $data->title = get_string('contact_us_page_heading', 'block_teaching_team');
        $data->reasonlabel = get_string('contact_us_reason_label', 'block_teaching_team');
        $data->contextlabel = get_string('contact_us_context_label', 'block_teaching_team');
        $data->reasons = $this->get_reasons();
        $data->cancelurl = $this->cancelurl;
        return $data;
    }

    /**
     * Function to get the mappings
     * @return array
     */
    public function get_reasons() {
        global $DB;
        $result = $DB->get_record('gs_contactus_config', ['fromroleid' => $this->userroleid]);
        $sql = "SELECT id, formreason FROM {gs_contactus_mappings} WHERE id ";
        list($insql, $params) = $DB->get_in_or_equal(explode(',', $result->senderviewids));
        return array_values($DB->get_records_sql($sql . $insql, $params));
    }
}
