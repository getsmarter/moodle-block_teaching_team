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
define('AJAX_SCRIPT', true);
require(__DIR__ . '/../../config.php');

require_login();

global $DB;

$action = required_param('action', PARAM_RAW);
$params = optional_param('params', '[]', PARAM_RAW);
$result = [];

switch ($action) {
    case 'add_from_role_type':
        try {
            $record = $DB->insert_record('gs_contactus_config', $params, true);
            $result['success'] = 200;
            $result['id'] = $record;
        } catch (\Exception $e) {
            $result['success'] = 500;
            $result['message'] = "Could not insert record";
        }
        break;
    case 'edit_from_role_type':
        try {
            $record = $DB->update_record('gs_contactus_config', $params, true);
            $result['success'] = 200;
            $result['id'] = $record;
        } catch (\Exception $e) {
            $result['success'] = 500;
            $result['message'] = "Could not update record";
        }
    break;
    case 'delete_from_role_type':
        try {
            $record = $DB->delete_records('gs_contactus_config', $params, true);
            $result['success'] = 200;
            $result['id'] = $record;
        } catch (\Exception $e) {
            $result['success'] = 500;
            $result['message'] = "Could not delete record";
        }
        break;
    case 'add_form_dropdown':
        try {
            $record = $DB->insert_record('gs_contactus_mappings', $params, true);
            $result['success'] = 200;
            $result['id'] = $record;
        } catch (\Exception $e) {
            $result['success'] = 500;
            $result['message'] = "Could not insert record";
        }
        break;
    case 'edit_form_dropdown':
        try {
            $record = $DB->update_record('gs_contactus_mappings', $params, true);
            $result['success'] = 200;
            $result['id'] = $record;
        } catch (\Exception $e) {
            $result['success'] = 500;
            $result['message'] = $e->getMessage();
        }
        break;
    case 'delete_form_dropdown':
        try {
            $record = $DB->delete_records('gs_contactus_mappings', $params, true);
            $result['success'] = 200;
            $result['id'] = $record;
        } catch (\Exception $e) {
            $result['success'] = 500;
            $result['message'] = "Could not delete record";
        }
        break;
    default:
        $result['success'] = 500;
        $result['message'] = "Invalid Action";
        break;
}

header('Content-type: application/json');

echo json_encode($result);
die();