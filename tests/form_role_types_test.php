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
 * Unit tests for the block_teaching_team implementation of form_role_types renderable and templatable.
 *
 * @package    block_teaching_team
 * @category   test
 * @copyright  2020 Brendon Pretorius <bpretorius@2u.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_teaching_team\output\form_role_types;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the block_teaching_team implementation of form_role_types renderable and templatable.
 *
 * @copyright  2020 Brendon Pretorius <bpretorius@2u.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_teaching_team_form_role_types_testcase extends advanced_testcase {
    /** @var array $gsformroletypesids */
    private $gsformroletypesids = null;
    /** @var string $sql */
    private $sql = '';

    public function setup() {
        $datagenerator = $this->getDataGenerator();
        $user = $datagenerator->create_user();
        $datagenerator = $datagenerator->get_plugin_generator('block_teaching_team');
        $this->gsformroletypesids[] = $datagenerator->create_contact_us_config($user->id, 1);
        $this->sql = '
            SELECT gscc.id , gscc.salesforceapi AS salesforceapi, r.name AS name
            FROM {gs_contactus_config} AS gscc
            INNER JOIN {user} AS u ON u.id = gscc.userid
            INNER JOIN {role} AS r ON r.id = gscc.fromroleid;
        ';
    }

    public function test_get_data() {
        global $DB;
        $this->resetAfterTest();

        $configvalues = (new form_role_types())->get_data();
        $expecteddropdowvalues = $DB->get_records_sql($this->sql);
        $expecteddropdowvalues = array_values(json_decode(json_encode($expecteddropdowvalues), true));
        $this->assertEquals($expecteddropdowvalues, $configvalues);
    }

    public function test_export_data_for_template() {
        global $DB;
        $this->resetAfterTest();

        $configvalues = (new form_role_types())->export_for_template(null);
        $expecteddropdowvalues = $DB->get_records_sql($this->sql);
        $expecteddropdowvalues = (object) ['records' => array_values(json_decode(json_encode($expecteddropdowvalues), true))];
        $this->assertEquals($expecteddropdowvalues, $configvalues);
    }
}
