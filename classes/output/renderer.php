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
 * Renderer for teaching_team.
 *
 * @package   block_teaching_team
 * @copyright Brendon Pretorius <bpretorius@2u.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_teaching_team\output;

defined('MOODLE_INTERNAL') || die();
use plugin_renderer_base;
use block_teaching_team\output\form_role_types;
use block_teaching_team\output\form_role_types_single;
use block_teaching_team\output\dropdown_values;
use block_teaching_team\output\contactusform;

class renderer extends plugin_renderer_base {
    /**
     * Defer to template.
     *
     * @param block_teaching_team\output\form_role_types $formroletypes
     * @return string HTML
     */
    protected function render_form_role_types(form_role_types $formroletypes) {
        $data = $formroletypes->export_for_template($this);
        return parent::render_from_template('block_teaching_team/form_role_types', $data);
    }

    /**
     * Defer to template.
     *
     * @param block_teaching_team\output\dropdown_values $formroletypes
     * @return string HTML
     */
    protected function render_dropdown_values(dropdown_values $formroletypes) {
        $data = $formroletypes->export_for_template($this);
        return parent::render_from_template('block_teaching_team/dropdown_values', $data);
    }

    /**
     * Defer to template.
     *
     * @param block_teaching_team\output\form_role_types_single $formroletypes
     * @return string HTML
     */
    protected function render_form_role_types_single(form_role_types_single $formroletypessingle) {
        $data = $formroletypessingle->export_for_template($this);
        return parent::render_from_template('block_teaching_team/form_role_types_single', $data);
    }

    /**
     * Defer to template.
     *
     * @param block_teaching_team\output\contactusform $formroletypes
     * @return string HTML
     */
    protected function render_contactusform(contactusform $contactusform) {
        $data = $contactusform->export_for_template($this);
        return parent::render_from_template('block_teaching_team/contactusform', $data);
    }
}
