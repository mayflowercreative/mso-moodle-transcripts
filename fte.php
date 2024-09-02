<?php
/*  DOCUMENTATION
    .............

    require('../../config.php');
	It loads all the Moodle core library by initialising the database connection, session, current course, theme and language.
	
	require_once($CFG->libdir.'/adminlib.php');
	states the functions and classes used during installation, upgrades and for admin settings.
	
	$path = optional_param('path', '', PARAM_PATH);
    $pageparams = array();
    if ($path) {
        $pageparams['path'] = $path;
    }
	In Moodle you can call or pass the parameters. As moodle_url doesn't provide you a way of generating the array, so you'll
	have to construct the params yourself. By defining your custom page to the function admin external page.
	
	Core global variables in Moodle are identified using uppercase variables (ie $CFG, $SESSION, $USER, $COURSE, $SITE, $PAGE,
	$DB and $THEME).
	$CFG: $CFG stands for configuration. This global variable contains configuration values of the Moodle setup, such as the
	root directory, data directory, database details, and other config values.
	
	$SESSION: Moodle's wrapper round PHP's $_SESSION.
	
    $USER: Holds the user table record for the current user. This will be the 'guest' user record for people who are not
	logged in.
	
	$SITE: Frontpage course record. This is the course record with id=1.
	
	$COURSE: This global variable holds the current course details. An alias for $PAGE->course.
	
	$PAGE: This is a central store of information about the current page we are generating in response to the user's request.
	ex: $PAGE->set_url('/mod/mymodulename/view.php', array('id' => $cm->id));
        $PAGE->set_title('My modules page title');
        $PAGE->set_heading('My modules page heading');

    $OUTPUT: $OUTPUT is an instance of core_renderer or one of its subclasses. It is used to generate HTML for output.
	ex: echo $OUTPUT->header();
	    echo $OUTPUT->heading($pagetitle);
		
	$CONTEXT: A context is combined with role permissions to define a User's capabilities on any page in Moodle.

    $DB: This holds the database connection details. It is used for all access to the database.

    $PAGE->set_url('/local/slack/userdata.php');
	Every moodle page needs page url through a call to $PAGE->set_url. You are trying to define the page url for setting the 
	custom page.
	
	require_login();
	It verifies that user is logged in before accessing any moodle page.
	
	$PAGE->set_pagelayout('admin'); Set a default pagelayout. 
	(or) 
    $PAGE->set_pagelayout('standard');
	When setting the page layout you should use the layout that is the closest match to the page you are creating. 
    Layouts are used by themes to determine what is shown on the page. There are different layouts that can be, and are used
    throughout Moodle core that you can use within your code. The list of common layouts you are best to look at
	theme/base/config.php or refer to the list below.
	
	It's important to know that the theme determines what layouts are available and how each looks. If you select a layout
	that the theme doesn't support then it will revert to the default layout while using that theme. Themes are also able to 
	specify additional layouts, however its important to spot them and know that while they may work with one theme they are
	unlikely to work as you expect with other themes.
	
	$context = context_system::instance();
	$PAGE->set_context($context);
	Setting the context of the page should call set_context() once with the context that is most appropriate to the page you 
	are creating. If it is a plugin then the context to use would be the context you are using for your capability checks.

    admin_externalpage_setup();
    This function call ensures the user is logged in, and makes sure that they have the proper role permission to access the 
	page.It also configures all $PAGE properties needed for navigation.
	
	$header = $SITE->fullname;
	defines the title of your custom page.
	
	$PAGE->set_title(get_string('pluginname', 'local_slack'));
	defines the title of your plugin at the browser tab.
	
	$PAGE->set_heading($header);
	to display your plugin fullname.

    echo $OUTPUT->header();
	this line prints the header of the page and adds one heading to the page at the top of the content region. Page headings 
	are very important in Moodle and should be applied consistently.
	
	echo $OUTPUT->footer();
	this line prints the footer of the page.
*/

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$path = optional_param('path', '', PARAM_PATH); // $nameofarray = optional_param_array('nameofarray', null, PARAM_INT);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

global $CFG, $USER, $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/local/ibq/fte.php');

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);

admin_externalpage_setup('fte', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_ibq'));
$PAGE->set_heading($header);

echo $OUTPUT->header();

/* Add your custom code here..*/



$html = '';

$html .= '<p class="cr-text-center">' . get_string("tab1dsc", "local_ibq") . '</p>';



$html .= '<table class="table table-hover">
	<thead>
		<tr>
			<th>'. get_string("th1b", "local_ibq") .'</th>
			<th>'. get_string("th2b", "local_ibq") .'</th>
			<th style="min-width:270px">'. get_string("th3b", "local_ibq") .'</th>
			<th style="min-width:270px">'. get_string("th4b", "local_ibq") .'</th>
		</tr>
	</thead>
	<tbody id="cr-resultuser">';


global $DB;	
$query = 'select LEFT(fullname,6) as session, count(fullname) as nombre  FROM mdl_course WHERE fullname < "9999" AND fullname > "2000" GROUP BY session ORDER BY fullname DESC ';
$sessions = $DB->get_records_sql($query);
$une_annee = "first";
foreach($sessions as $session){
	 
		$la_session = $session->session;
		$s1=substr($la_session,0,4);
		$s2=substr($la_session,5,1);
	
		if ($une_annee=="first") {$une_annee=$s1;}
		
		if ($une_annee!=$s1){
			$url_en = new moodle_url('/local/ibq/fte_en.php', array('annee' => $une_annee, 'session' => 0));
			$fte_link_en = html_writer::link($url_en, get_string("ftebtnviewen", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));
	
			$url_fr = new moodle_url('/local/ibq/fte_fr.php', array('annee' => $une_annee, 'session' => 0));
			$fte_link_fr = html_writer::link($url_fr, get_string("ftebtnviewfr", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));

			
			$url_bog_en = new moodle_url('/local/ibq/fte_bog_fr.php', array('annee' => $une_annee, 'session' => 0));
			$fte_bog_link_en = html_writer::link($url_bog_en, get_string("ftebogbtnviewen", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));
	
			 
			
			
			$url_ctrl = new moodle_url('/local/ibq/fte_ctrl.php', array('annee' => $une_annee, 'session' => 0));
			$fte_link_ctrl = html_writer::link($url_ctrl, get_string("ftebtnviewctrl", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));

			$url_ul = new moodle_url('/local/ibq/fte_ul.php', array('annee' => $une_annee, 'session' => 0));
			$fte_link_ul = html_writer::link($url_ul, get_string("ftebtnviewul", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));
			
			$html .= '<tr>';
			$html .= '<td><strong>'. $une_annee .'</strong></td>';
			$html .= '<td align=right> </td>';
			$html .= '<td>'. $fte_link_fr . ' ' . $fte_bog_link_en . ' ' . $fte_link_ctrl.'</td>';
			$html .= '<td>'. $fte_link_ul .'</td>';
			$html .= '</tr>';
	
			$une_annee=$s1;
			
		}
		
	
		$url_en = new moodle_url('/local/ibq/fte_en.php', array('annee' => $s1, 'session' => $s2));
		$fte_link_en = html_writer::link($url_en, get_string("ftebtnviewen", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));
	
		$url_fr = new moodle_url('/local/ibq/fte_fr.php', array('annee' => $s1, 'session' => $s2));
		$fte_link_fr = html_writer::link($url_fr, get_string("ftebtnviewfr", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));
	
		$url_bog_en = new moodle_url('/local/ibq/fte_bog_fr.php', array('annee' => $s1, 'session' => $s2));
		$fte_bog_link_en = html_writer::link($url_bog_en, get_string("ftebogbtnviewen", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));
	
		 
	
		$url_ctrl = new moodle_url('/local/ibq/fte_ctrl.php', array('annee' => $s1, 'session' => $s2));
		$fte_link_ctrl = html_writer::link($url_ctrl, get_string("ftebtnviewctrl", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));

		$url_ul = new moodle_url('/local/ibq/fte_ul.php', array('annee' => $s1, 'session' => $s2));
		$fte_link_ul = html_writer::link($url_ul, get_string("ftebtnviewul", "local_ibq"), array('class' => 'btn btn-sm btn-primary'));

		
	
		$html .= '<tr>';
		$html .= '<td>'. $session->session .'</td>';
		$html .= '<td align=right>'. $session->nombre .'</td>';
		$html .= '<td>';
		$html .= $fte_link_fr .  ' ';
		$html .= $fte_bog_link_en . ' ';
		$html .= $fte_link_ctrl.'</td>';
		$html .= '<td>'. $fte_link_ul .'</td>';
		$html .= '</tr>';
	
}

$html .= '</tbody></table>';

echo $html;







echo $OUTPUT->footer();
