<?php
/*  DOCUMENTATION
    .............

    $hassiteconfig which can be used as a quick way to check for the moodle/site:config permission. This variable is set by
	the top-level admin tree population scripts. 
	
	$ADMIN->add('root', new admin_category();
	Add admin settings for the plugin with a root admin category as Slack variable.
	
	$ADMIN->add('slack', new admin_externalpage();
	Add new external pages for your Slack plugin.
*/

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $ADMIN->add('root', new admin_category('ibq', get_string('pluginname', 'local_ibq')));
	
	$ADMIN->add('ibq', new admin_externalpage('fte', get_string('fte', 'local_ibq'),
                 new moodle_url('/local/ibq/fte.php')));

    $ADMIN->add('ibq', new admin_externalpage('transcripts', get_string('transcripts', 'local_ibq'),
                 new moodle_url('/local/ibq/transcripts.php')));
}   