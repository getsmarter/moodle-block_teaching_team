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
        '. registereditclick() .'
        '. registerdeleteclick($ajaxurl) .'
        '. registercancelclick() .'
        '. registersaveclick($ajaxurl) .'
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
            let localEvent = event || window.event;
            let addButton = localEvent.target;
            $(addButton).prop("disabled", true);

            // ===========================================
            // Adding form - NBNB assumption here is that the add button will be the next sibling of the table.
            // ===========================================
            let table = $(\'#formroletypes\')[0];
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
                        "salesforceapi": encodeURI($("#salesforceapi").val().trim()),
                        "senderviewids": $("#senderviewids").val().join()
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

            // ===========================================
            // Setting up event listener for cancel
            // ===========================================
            $("#role-type-cancel").click(function(e) {
                e.preventDefault();
                $(e.target).closest("div").remove();
                $(addButton).prop("disabled", false);
                return false;
            });
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
            let localEvent = event || window.event;
            let addButton = localEvent.target;
            $(addButton).prop("disabled", true);

            // ===========================================
            // Adding form - NBNB assumption here is that the add button will be the next sibling of the table
            // ===========================================
            let table = $(\'#dropdownvalues\')[0];
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

            // ===========================================
            // Setting up event listener for cancel
            // ===========================================
            $(".cancel").click(function(e) {
                e.preventDefault();
                $(e.target).closest("div").remove();
                $(addButton).prop("disabled", false);
                return false;
            });
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

/**
 * Helper function to register a JS function to the window
 * @return string
 */
function registereditclick() {
    $script = 'window.edit_click = function() {
        let localEvent = event || window.event;
        let target = localEvent.target;
        let targetrow = $(target).parents("tr");
        targetrow.find("span").toggleClass("hidden");
    }';

    return $script;
}

/**
 * Helper function to register a JS function to the window
 * @param string The url used for ajax posting
 * @return string
 */
function registerdeleteclick($ajaxurl) {
    $script = 'window.delete_click = function() {
        let confirmation = confirm("Delete?");
        if (confirmation === true) {
            let localEvent = event || window.event;
            let target = localEvent.target;
            let entityid = $(target).parents("tr").find("input[type=\'hidden\']").val();
            let params = {
                id: entityid
            };
            $.ajax({
                type: "POST",
                url: "'.$ajaxurl.'",
                data: {
                    "action": $(target).data("action"), params
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
        }
    }';

    return $script;
}

/**
 * Helper function to register a JS function to the window
 * @param string The url used for ajax posting
 * @return string
 */
function registersaveclick($ajaxurl) {
    global $USER;
    $script = 'window.save_click = function() {
        let localEvent = event || window.event;
        let target = localEvent.target;
        let params;
        let action = $(target).data("action");
        let row = $(target).parents("tr");
        switch (action) {
            case "edit_from_role_type":
                params = {
                    "userid": '.$USER->id.',
                    "id": row.find("input[name=\'id\']").val(),
                    "fromroleid": row.find("select[name=\'fromroleid\']").val(),
                    "salesforceapi": encodeURI(row.find("input[name=\'salesforceapi\']").val().trim()),
                    "senderviewids": row.find("select[name=\'senderviewids\']").val().join()
                }
                break;
            case "edit_form_dropdown":
                params = {
                    "userid": '.$USER->id.',
                    "id": row.find("input[name=\'id\']").val(),
                    "formreason": row.find("input[name=\'formreason\']").val(),
                    "sfmapping": row.find("input[name=\'sfmapping\']").val().trim()
                }
                break;
            default:
                throw "Unrecognised action"
                break;
        }

        $.ajax({
            type: "POST",
            url: "'.$ajaxurl.'",
            data: {
                "action": action, params
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
    }';

    return $script;
}

/**
 * Helper function to register a JS function to the window
 * @return string
 */
function registercancelclick() {
    $script = 'window.cancel_click = function() {
        let localEvent = event || window.event;
        let target = localEvent.target;
        let row = $(target).parents("tr");
        let rowspans = row.find("span")
        row.find("select:visible option").each(function() {
            $(this).prop("selected", $(this).data("selected") ?? false);
        });
        rowspans.find("input").each(function() {
            let originaltext = $(this).data("originaltext");
            $(this).val(originaltext);
        });
        rowspans.toggleClass("hidden");
    }';

    return $script;
}
