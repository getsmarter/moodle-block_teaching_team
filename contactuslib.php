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

use block_teaching_team\output\dropdown_values;
use block_teaching_team\output\form_role_types_single;
use block_teaching_team\output\form_role_types;

/**
 * Returns config list as HTML table
 * @return string
 */
function listformroleyypesconfig() {
    global $PAGE;

    $output = $PAGE->get_renderer('block_teaching_team');
    $form = new form_role_types();
    return $output->render($form);
}

/**
 * Returns config list as HTML table
 * @return string
 */
function listdropdownvalues() {
    global $PAGE;

    $output = $PAGE->get_renderer('block_teaching_team');
    $form = new dropdown_values();
    return $output->render($form);
}

/**
 * Function to house contact us JS logic in PHP
 * @return string
 */
function addcontactusjs() {

    $ajaxurl = new moodle_url('/blocks/teaching_team/contact_us_ajax.php');

    $script = '<script type="text/javascript">
        '. registeraddfromroletype($ajaxurl) .'
        '. registeraddformdropdown($ajaxurl) .'
    </script>';

    return $script;
}

/**
 * Helper function to register a JS function to the window
 * @param string The url used for ajax posting
 * @return string
 */
function registeraddfromroletype($ajaxurl) {
    global $USER, $PAGE;

    $output = $PAGE->get_renderer('block_teaching_team');
    $form = new form_role_types_single();
    $html = json_encode($output->render($form));

    $script = '
        window.add_from_role_type = function() {
            event = event || window.event;
            let addButton = event.target;
            $(addButton).prop("disabled", true);

            // ===========================================
            // Adding form - NBNB assumption here is that the add button will be the next sibling of the table.
            // ===========================================
            let table = $(addButton).parent().find("table")[0];
            if (table !== undefined) {
                let formContainer = document.createElement("div");
                formContainer.innerHTML = unescape('.$html.');
                table.parentNode.insertBefore(formContainer, table.nextSibling);
            }

            // ===========================================
            // Setting up event listener for form submit
            // ===========================================
            let roleTypeForm = $(table.nextSibling).find("form#add_contact_us_form");
            if (roleTypeForm) {
                $(roleTypeForm).submit(function(e) {
                    e.preventDefault();
                    var $form = $(this);

                    let params = {
                        "userid": '.$USER->id.',
                        "fromroleid": $("#fromroleid").val(),
                        "salesforceapi": encodeURI($("#salesforceapi").val().trim())
                    }

                    // ===========================================
                    // Submitting form via ajax
                    // ===========================================
                    $.ajax({
                        type: "POST",
                        url: "'.$ajaxurl.'",
                        data: { 
                            "action": $form.attr("action"), params
                        },
                        success: (response) => {
                            let responseParsed = response;
                            if (typeof response !== "object") {
                                responseParsed = JSON.parse(response);
                            };

                            if (responseParsed.error) {
                                throw responseParsed.error
                            }

                            if (responseParsed.success == 200) {
                                location.reload();
                            }
                        },
                        error: (response) => {
                            throw repsonse;
                        }
                    });
                });
            }
        };
    ';

    return $script;
}

/**
 * Helper function to register a JS function to the window
 * @param string The url used for ajax posting
 * @return string
 */
function registeraddformdropdown($ajaxurl) {
    global $USER, $OUTPUT;

    $html = json_encode($OUTPUT->render_from_template('block_teaching_team/form_from_dropdown_values_single', []));

    $script = '
        window.add_form_dropdown = function() {
            event = event || window.event;
            let addButton = event.target;
            $(addButton).prop("disabled", true);

            // ===========================================
            // Adding form - NBNB assumption here is that the add button will be the next sibling of the table
            // ===========================================
            let table = $(addButton).parent().find("table")[0];
            if (table !== undefined) {
                let formContainer = document.createElement("div");
                formContainer.innerHTML = unescape('.$html.');
                table.parentNode.insertBefore(formContainer, table.nextSibling);
            }

            // ===========================================
            // Setting up event listener for form submit
            // ===========================================
            let form = $(table.nextSibling).find("form#add_form_dropdown_form");
            if (form) {
                $(form).submit(function(e) {
                    e.preventDefault();
                    var $form = $(this);

                    let params = {
                        "userid": '.$USER->id.',
                        "formreason": $("#formreason").val(),
                        "sfmapping": $("#sfmapping").val()
                    }

                    // ===========================================
                    // Submitting form via ajax
                    // ===========================================
                    $.ajax({
                        type: "POST",
                        url: "'.$ajaxurl.'",
                        data: { 
                            "action": $form.attr("action"), params
                        },
                        success: (response) => {
                            let responseParsed = response;
                            if (typeof response !== "object") {
                                responseParsed = JSON.parse(response);
                            };

                            if (responseParsed.error) {
                                throw responseParsed.error
                            }

                            if (responseParsed.success == 200) {
                                location.reload();
                            }
                        },
                        error: (response) => {
                            throw repsonse;
                        }
                    });
                });
            }
        };
    ';

    return $script;
}

/**
 * Function to return the available roles for contactus_config
 * @return object $roles The available roles
 */
function getavailcontactusconfig() {
    global $DB;
    $roles = [];

    try {
        $sql = "SELECT id, shortname
                    FROM {role} r
                    WHERE r.id 
                    NOT IN (SELECT gcc.fromroleid FROM {gs_contactus_config} gcc)";

        $roles = $DB->get_records_sql($sql);
    } catch (\Exception $e) {
        error_log($e);
    }

    return $roles;
}