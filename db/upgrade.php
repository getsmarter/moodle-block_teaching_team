<?php

function xmldb_block_teaching_team_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();

    if($oldversion < 2020111000) {
        $gscontactusconfigtable = new xmldb_table('gs_contactus_config');
        $gscontactusmappingstable = new xmldb_table('gs_contactus_mappings');

        if (!$dbman->table_exists($gscontactusconfigtable)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot.'/blocks/teaching_team/db/gs_contactus_config.xml', 'gs_contactus_config');
        }

        if (!$dbman->table_exists($gscontactusmappingstable)) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot.'/blocks/teaching_team/db/gs_contactus_mappings.xml', 'gs_contactus_mappings');
        }

        unset($gscontactusconfigtable);
        unset($gscontactusmappingstable);

        upgrade_block_savepoint(true, 2020111000, 'teaching_team');
    }

    if ($oldversion < 2020112300) {
        $table = new xmldb_table('gs_contactus_config');
        $field = new xmldb_field('senderviewids', XMLDB_TYPE_TEXT);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_block_savepoint(true, 2020112300, 'teaching_team');
    }

    return true;
}
