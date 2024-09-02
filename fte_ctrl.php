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

$PAGE->set_url('/local/ibq/fte_ctrl.php');

require_login();


 


$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);

//admin_externalpage_setup('fte_bog_fr', '', $pageparams);

$session = $annee = 0;

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_ibq'));
$PAGE->set_heading($header);
$PAGE->set_url('/local/ibq/fte_ctrl.php', array("annee"=>$annee,"session" => $session));
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

$roles_ibq = array(7,8,10,15);
$roles_ul = array(5,11);
$roles_aud = array(12);
$roles_filtre = array(5,7,8,10,11,12,15);
$roles_etudiants = array(5,7,8,10,11,15);

$sql_filtre = "(5,7,8,10,11,12,15,16)";
$sql_teacher = "(3)";
$sql_corrector = "(4)";
$sql_tutor = "(9)";

$l_total_type = array(0);
$nouveau_deja = array();
$total_fte = 0;
$total_etudiants = 0;
for ($i = 0; $i <= 30; $i++) {
	$l_total_type[$i]=0;
}

$header = '<div class="row">
			<div class="col-4">'. $ibq->get_institute_info() .'</div>
			<div class="col-4">
				<center>
					<h3><strong> LISTE DE CONTROLE '. get_string("pour", "local_ibq").$annee .' ('. $titre_session[$session] .')</strong></h3>
				</center>
			</div>
			 
			<div class="col-4 t-txt-right">'. $print_date .'</div>
			
			<div class="t-dbl-line"></div>
		   </div>';
	
$body = '<tbody>';

// calcul complet

$query = "SELECT * FROM `mdl_course` WHERE fullname LIKE '". $la_session ."%'";

$sqlcourses = $DB->get_records_sql($query);
$les_credits = 3;

$body = '<tbody>';


$body .= '<table class="table table-hover">
	 
	<tbody>';

foreach($sqlcourses as $sqlcours)
{	
	//$body .= "<tr><strong>". $sqlcours->fullname ."</strong> ";
	$body .= '<tr class="t-row-head">
			<td colspan="2">'. $sqlcours->fullname .'</td>';
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
	$body .= "<td>(".$les_credits ." crédits)";
	

	// type du cours
	$types_cours = array("cours en classe à l'IBQ (P)","cours intensif en classe à l'IBQ (P)","cours en classe autre qu'à l'IBQ (P)","cours intensif en classe autre qu'à l'IBQ (P)","cours présentiel-hybride (H)","cours à distance-hybride (Y)","cours à distance asynchrone (D)","cours à distance synchrone (D)","cours comodal (C)","cours en travaux dirigés (TD)","stage (S)");
		
	$query22 = "SELECT c.id AS courseid, c.fullname AS coursename, d.fieldid, d.intvalue AS type
				FROM mdl_customfield_data as d 
    			JOIN mdl_course c ON c.id = d.instanceid
    			WHERE d.fieldid=2 AND c.id=".$courseid;
    
	$sqltypes = $DB->get_records_sql($query22);
	$le_type_du_cours=-1;
	foreach($sqltypes as $type)	
	{
		$le_type_du_cours = $type->type-1; 
	}
	if ($le_type_du_cours > -1)
	{
		$body .= " ".$types_cours[$le_type_du_cours] ." ";
		$l_total_type[$le_type_du_cours] += 1;
		
	}
	$body .= "</td></tr>";	
		
	
	
	
	
	
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
	
	$cpt = 1;
	$cpt_student = 0;
	$total_credits_cours = 0;
	foreach($sqlstudents as $student)
	{
		$le_nom = substr($student->nom,0,strpos($student->nom,'*'));
		$body .= "<tr><td>". $cpt++ . ". " .$le_nom ."</td>";
		$sql_role = "<td>". $student->role ."</td>";
		$sql_role_name = $student->name;
		$body .= "<td>". $sql_role_name ."</td>";
		$roleid = $student->roleid;
		$student_id = $student->studentid;
		if (in_array($roleid,$roles_etudiants)) {
			$total_fte += $les_credits;	
			$total_credits_cours += $les_credits;
			$cpt_student++;
			$total_etudiants++; 
		}
		
				
		$query4 = "SELECT d.data FROM mdl_user_info_data as d 
					Where d.userid=". $student_id ."
					AND d.fieldid = 20";
		
		$sqlleprogrammes = $DB->get_records_sql($query4);
		$le_programme = " ";
		foreach($sqlleprogrammes as $sqlleprogramme)
		{
			$le_programme = $sqlleprogramme->data;
		}
		$body .= "<td> ".$le_programme ."</td></tr>";
			 
	}
	$body .= "<tr><td>Crédits pour FTE : ".$total_credits_cours ." (". $cpt_student ." étudiants)</td></tr>";
}

$body .= "<tr><td>Nombre total de crédits pour FTE : ". $total_fte ." (". $total_etudiants ." étudiants)</td></tr>";

$body .= "</tbody></table><div class='t-dbl-line'></div>";

$fte = $total_fte/12;

$header .= "<DIV align=center><STRONG>F.T.E. : " .number_format((float)$fte, 2, '.', ''). "</STRONG></DIV>";

// nombre d'étudiants et de nouveau étudiants et groupe par nombre de cours 

$query5 = "
SELECT COUNT(DISTINCT lra.userid) AS nb 
FROM mdl_course AS c  
LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
WHERE lra.roleid in ". $sql_teacher ."
AND c.fullname LIKE '". $la_session ."%'";

$sqlcountstudents = $DB->get_records_sql($query5);
foreach($sqlcountstudents as $sqlcountstudent)
	{
		if ($sqlcountstudent->nb>0) {
			$body .= "Nombre total d'enseignants distincts : ".$sqlcountstudent->nb ."<BR>";
		}
	}

$query5 = "
SELECT COUNT(DISTINCT lra.userid) AS nb 
FROM mdl_course AS c  
LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
WHERE lra.roleid in ". $sql_corrector ." 
AND c.fullname LIKE '". $la_session ."%'";

$sqlcountstudents = $DB->get_records_sql($query5);
foreach($sqlcountstudents as $sqlcountstudent)
	{
		if ($sqlcountstudent->nb>0) {
			$body .= "Nombre total de correcteurs distincts : ".$sqlcountstudent->nb ."<BR>";
		}
	}

$query5 = "
SELECT COUNT(DISTINCT lra.userid) AS nb 
FROM mdl_course AS c  
LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
WHERE lra.roleid in ". $sql_tutor ."
AND c.fullname LIKE '". $la_session ."%'";

$sqlcountstudents = $DB->get_records_sql($query5);
foreach($sqlcountstudents as $sqlcountstudent)
	{
		if ($sqlcountstudent->nb>0) {
			$body .= "Nombre total de tuteurs distincts : ".$sqlcountstudent->nb ."<BR>";
		}
	}
		
$query5 = "
SELECT COUNT(DISTINCT lra.userid) AS nb 
FROM mdl_course AS c  
LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
WHERE lra.roleid in ". $sql_filtre ."
AND c.fullname LIKE '". $la_session ."%'";

$sqlcountstudents = $DB->get_records_sql($query5);
foreach($sqlcountstudents as $sqlcountstudent)
	{
		if ($sqlcountstudent->nb>0) {
			$body .= "Nombre total d'étudiants distincts : ".$sqlcountstudent->nb ."<BR>";
		}
	}

$nombre_de_nouveaux = 0;
$nombre_par_nb_de_cours = array();
for ($j = 0; $j <= 10; $j++) {
	$nombre_par_nb_de_cours[$j]=0;
}

$query6 = "
SELECT DISTINCT  u.id as studentid, u.lastname, u.firstname  
FROM mdl_course AS c #, mdl_course_categories AS cats
LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
JOIN mdl_user AS u ON u.id = lra.userid

WHERE lra.roleid in ". $sql_filtre ."
AND c.fullname LIKE '". $la_session ."%'
ORDER BY u.lastname";

$body .= "<strong>Liste des étudiants et nombre de cours pour cette session : </strong><BR>";
$sqlstudents = $DB->get_records_sql($query6);
foreach($sqlstudents as $sqlstudent)
	{
		$student_id = $sqlstudent->studentid;
		$body .= $sqlstudent->firstname ." ";
		$body .= $sqlstudent->lastname ." ";
		
		//Homme ou femme ?
		$queryhf = "
		SELECT mdl_user_info_data.data AS sexe 
		FROM mdl_user_info_data  
		WHERE mdl_user_info_data.userid = ". $student_id ."
		AND mdl_user_info_data.fieldid=15";

		$sqlqueryhf = $DB->get_records_sql($queryhf);
		foreach($sqlqueryhf as $querysexe)
		{
			if (strlen($querysexe->sexe)>0)   {
				$body .= " (". $querysexe->sexe .") ";
			}
		}
	
	
		// nouvel étudiant ? 
		$query7 = "
		SELECT COUNT(DISTINCT c.fullname) AS nb_cours
		FROM mdl_course AS c  
		LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
		JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
		JOIN mdl_course_categories AS cats ON c.category = cats.id
		WHERE  lra.roleid IN ". $sql_filtre ."
		AND c.fullname < '". $la_session ."'
		AND lra.userid = '". $student_id ."'";
		$NB_TOTAL_COURS_DE_ETUDIANT = 0;
		$sqlstudentcours = $DB->get_records_sql($query7);
		foreach($sqlstudentcours as $sqlstudentnbcours)
		{
			$NB_TOTAL_COURS_DE_ETUDIANT = $sqlstudentnbcours->nb_cours;	
		}
		if ($NB_TOTAL_COURS_DE_ETUDIANT==0) {
			// est-ce que ce nouvel étudiant a déjà été compté ?
			if (in_array($student_id,$nouveau_deja)) {
				// déjà compté !
			} else {
				$nombre_de_nouveaux += 1;
				$body .= " (nouveau) ";	
				$nouveau_deja[] = $student_id;
			}
		}
		
		// nombre de cours dans la session 
		$query8 = "
		SELECT COUNT(DISTINCT c.fullname) AS nb_cours
		FROM mdl_course AS c  
		LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
		JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
		JOIN mdl_course_categories AS cats ON c.category = cats.id
		WHERE  lra.roleid IN ". $sql_filtre ."
		AND c.fullname LIKE '". $la_session ."%'
		AND lra.userid = '". $student_id ."'";
		
		$NB_TOTAL_COURS_DE_ETUDIANT_SESSION = 0;
		$sqlstudentcourssession = $DB->get_records_sql($query8);
		foreach($sqlstudentcourssession as $sqlstudentnbcourssession)
		{
			$NB_TOTAL_COURS_DE_ETUDIANT_SESSION = $sqlstudentnbcourssession->nb_cours;	
		}
		
		$body .= " : ". $NB_TOTAL_COURS_DE_ETUDIANT_SESSION ." cours<BR>";
		$nombre_par_nb_de_cours[$NB_TOTAL_COURS_DE_ETUDIANT_SESSION] +=1;
		
	
	
		
	
		
	}
	$body .= "<BR>". $nombre_de_nouveaux . " nouveaux étudiants";
	for ($j = 0; $j <= 10; $j++) {
		if ($nombre_par_nb_de_cours[$j]>0) {
			if ($nombre_par_nb_de_cours[$j]>1){
				$body .= "<BR>". $nombre_par_nb_de_cours[$j] . " étudiants avec ". $j. " cours";
			} else {
				$body .= "<BR>". $nombre_par_nb_de_cours[$j] . " étudiant avec ". $j. " cours";
			}
		}
	}
	 
	$body .= "<BR><BR> ";
	$query5 = "
	SELECT COUNT(DISTINCT lra.userid) AS nb 
	FROM mdl_course AS c  
	LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
	JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
	JOIN mdl_user_info_data ON mdl_user_info_data.userid = lra.userid
	WHERE lra.roleid in ". $sql_filtre ."
	AND c.fullname LIKE '". $la_session ."%'
	AND mdl_user_info_data.fieldid=15
	AND mdl_user_info_data.data LIKE 'F%'";

	$sqlcountstudents = $DB->get_records_sql($query5);
	foreach($sqlcountstudents as $sqlcountstudent)
	{
		if ($sqlcountstudent->nb>0) {
			$body .= "<BR>Nombre total d'étudiantes (F) distinctes : ".$sqlcountstudent->nb ." ";
		}
	}

	$query5 = "
	SELECT COUNT(DISTINCT lra.userid) AS nb 
	FROM mdl_course AS c  
	LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
	JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
	JOIN mdl_user_info_data ON mdl_user_info_data.userid = lra.userid
	WHERE lra.roleid in ". $sql_filtre ."
	AND c.fullname LIKE '". $la_session ."%'
	AND mdl_user_info_data.fieldid=15
	AND mdl_user_info_data.data LIKE 'M%'";

	$sqlcountstudents = $DB->get_records_sql($query5);
	foreach($sqlcountstudents as $sqlcountstudent)
	{
		if ($sqlcountstudent->nb>0) {
			$body .= "<BR>Nombre total d'étudiants (M) distincts : ".$sqlcountstudent->nb ." ";
		}
	}

	$body .= "<BR><BR> ";
	$total = 0;
	for ($i = 0; $i <= 30; $i++) {
		if ($l_total_type[$i]>0) {
			$body .=$types_cours[$i]. " : ". $l_total_type[$i] . "<BR>";
			$total += $l_total_type[$i];
		}
	}
	$body .= "<STRONG>Nombre total de cours : ". $total ."</STRONG><BR>";


$footer = '<br>   ';

$back = new moodle_url($CFG->wwwroot.'/local/ibq/fte.php');


$html .= '<div class="row">
	<div class="col-12">
		<a class="btn btn-primary" href="'. $back .'">'. get_string("btnhome","local_ibq") .'</a>
		 
		
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
