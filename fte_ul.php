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
require_once("locallib.php");

require_once($CFG->libdir.'/adminlib.php');

$path = optional_param('path', '', PARAM_PATH); // $nameofarray = optional_param_array('nameofarray', null, PARAM_INT);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

global $CFG, $USER, $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/local/ibq/fte_ul.php');

require_login();


 


$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);

//admin_externalpage_setup('fte_bog_fr', '', $pageparams);

$session = $annee = 0;

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_ibq'));
$PAGE->set_heading($header);
$PAGE->set_url('/local/ibq/fte_ul.php', array("annee"=>$annee,"session" => $session));
$session = required_param('session', PARAM_INT);
$annee = required_param('annee', PARAM_INT);
if ($session>0) {
	$la_session = $annee. "-" .$session;	
} else {
	$la_session = $annee;
}


echo $OUTPUT->header();

/* Add your custom code here..*/

$html = $ibq_no = '';

$print_date = $ibq->get_french_date();

$titre_session = array( "année complète", "hiver", "été", "automne","session 4","session 5","session 6","session 7","session 8","divers" );


$roles_ul = array(5,11);
$roles_filtre = array(5,7,8,10,11,12,15);

$header = '<div class="row">
			<div class="col-4">'. $ibq->get_institute_info() .'</div>
			<div class="col-4">
				<center>
					<h3><strong> Liste UL '. get_string("pour", "local_ibq").$annee .' ('. $titre_session[$session] .')</strong></h3>
				</center>
			</div>
			 
			<div class="col-4 t-txt-right">'. $print_date .'</div>
			
			<div class="t-dbl-line"></div>
		   </div>';
	
 

// calcul complet

$query = "SELECT * FROM `mdl_course` WHERE fullname LIKE '". $la_session ."%'";

$sqlcourses = $DB->get_records_sql($query);
$les_credits = 3;
$total_credits = 0;

$body = '<tbody>';


$body .= '<table class="table table-hover">
	 
	<tbody>';

foreach($sqlcourses as $sqlcours)
{	
	//$body .= "<tr><strong>". $sqlcours->fullname ."</strong> ";
	$total_cours_credits = 0;
	$body .= '<tr class="t-row-head">
			<td colspan="4">'. $sqlcours->fullname .'</td>';
	$courseid = $sqlcours->id;
	$query2 = "SELECT c.id AS courseid, c.fullname AS coursename, d.fieldid, d.intvalue AS credit
				FROM mdl_customfield_data as d 
    			JOIN mdl_course c ON c.id = d.instanceid
    			WHERE d.fieldid=1 AND c.id=".$courseid;
    
	$sqlcredits = $DB->get_records_sql($query2);
	foreach($sqlcredits as $credits)	
	{
		$les_credits = $credits->credit - 1;
		if ($les_credits < 1) $les_credits = 3;
	}
	if ($les_credits < 1) $les_credits = 3;
	$body .= "<td>(".$les_credits ." crédits)</td></tr>";
	
	// liste des étudiants 
	 
	$query3 = "SELECT
				CONCAT(u.firstname,' ',u.lastname,'  *',r.id) AS nom,				 
				u.username,u.id as studentid,
				r.shortname AS 'role', r.name, r.id AS roleid,
				ctx.instanceid AS 'Context instance id'
				FROM mdl_role_assignments ra
				JOIN mdl_user u ON u.id = ra.userid
				JOIN mdl_role r ON r.id = ra.roleid
				JOIN mdl_context ctx ON ctx.id = ra.contextid

				WHERE ctx.instanceid=".$courseid."
				ORDER BY u.lastname";
	
	$sqlstudents = $DB->get_records_sql($query3);
	$x=0;
	foreach($sqlstudents as $student)
	{
		$roleid = $student->roleid;
		if (in_array($roleid, $roles_ul)) {
			$x++;
			$total_credits += $les_credits;
			$total_cours_credits += $les_credits;
			$le_nom = substr($student->nom,0,strpos($student->nom,'*'));
			$body .= "<tr><td>". $x ."</td><td>". $le_nom ."</td>";
			$sql_role = $student->role ;
			$sql_role_name = $student->name;
			$body .= "<td>". $sql_role_name ."</td>";
		
			$student_id = $student->studentid;
		
		
			$query4 = "SELECT d.data FROM mdl_user_info_data as d 
						Where d.userid=". $student_id ."
						AND d.fieldid = 4";
		
			$sqlstudentsidul = $DB->get_records_sql($query4);
			$idul = " ";
			foreach($sqlstudentsidul as $sqlstudentidul)
			{
				$idul = $sqlstudentidul->data;
			}
			$body .= "<td> ".$idul ."</td>";
			
			
		
			$query4 = "SELECT d.data FROM mdl_user_info_data as d 
						Where d.userid=". $student_id ."
						AND d.fieldid = 20";
		
			$sqlleprogrammes = $DB->get_records_sql($query4);
			$le_programme = "vide";
			foreach($sqlleprogrammes as $sqlleprogramme)
			{
				$le_programme = $sqlleprogramme->data;
			}
			$body .= "<td> ".$le_programme ."</td></tr>";
		
		}
		
		
	}
	$body .= "<tr><td colspan=3><strong>Nombre total de crédits : ".$total_cours_credits." crédits</strong><br></td></tr>";
	
}


$body .= "</table><div class='t-dbl-line'></div>";

$footer = '<br><strong>Nombre total de crédits pour la session : '. $total_credits .' crédits</strong>';

$back = new moodle_url($CFG->wwwroot.'/local/ibq/fte.php');
$download = new moodle_url($CFG->wwwroot.'/local/ibq/download_ul.php', array("annee" => $annee,"session" => $session));

$html .= '<div class="row">
	<div class="col-12">
		<a class="btn btn-primary" href="'. $back .'">'. get_string("btnhome","local_ibq") .'</a>
		<a class="btn btn-primary" style="margin-left:10px" href="'. $download .'" target="_blank">'. get_string("btndownload_fr","local_ibq") .'</a>
		 
		
	</div>
</div>
<hr>
<div class="container">
'. $header .' 
'. $body .'
'. $footer .'
</div>';

echo $html;




echo $OUTPUT->footer();
