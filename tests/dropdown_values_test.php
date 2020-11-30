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
 * Unit tests for the block_teaching_team implementation of dropdown_values renderable and templatable.
 *
 * @package    block_teaching_team
 * @category   test
 * @copyright  2020 Brendon Pretorius <bpretorius@2u.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_teaching_team\output\dropdown_values;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the block_teaching_team implementation of dropdown_values renderable and templatable.
 *
 * @copyright  2020 Brendon Pretorius <bpretorius@2u.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_teaching_team_dropdown_values_testcase extends advanced_testcase {
    /** @var array $gscontactusmappingsids */
    private $gscontactusmappingsids = [];

    public function setup() {
        $datagenerator = $this->getDataGenerator();
        $user = $datagenerator->create_user();
        $datagenerator = $datagenerator->get_plugin_generator('block_teaching_team');
        $this->gscontactusmappingsids[] = $datagenerator->create_contact_us_mappings($user->id);
    }

    public function test_get_data() {
        global $DB;
        $this->resetAfterTest();

        $dropdownvalues = (new dropdown_values())->get_data();
        $expecteddropdowvalues = $DB->get_records('gs_contactus_mappings', null, '', 'id, formreason, sfmapping');
        $expecteddropdowvalues = array_values(json_decode(json_encode($expecteddropdowvalues), true));
        $this->assertEquals($expecteddropdowvalues, $dropdownvalues);
    }

    public function test_export_data_for_template() {
        global $DB;
        $this->resetAfterTest();

        $dropdownvalues = (new dropdown_values())->export_for_template(null);
        $expecteddropdowvalues = $DB->get_records('gs_contactus_mappings', null, '', 'id, formreason, sfmapping');
        $expecteddropdowvalues = (object) ['records' => array_values(json_decode(json_encode($expecteddropdowvalues), true))];
        $this->assertEquals($expecteddropdowvalues, $dropdownvalues);
    }
}
