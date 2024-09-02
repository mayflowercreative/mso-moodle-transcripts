<?php
/*  DOCUMENTATION
    .............

    require('../../../config.php');
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

require('/var/www/html/moodle1/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once("locallib.php");
require_once($CFG->libdir.'/pdflib.php');


$path = optional_param('path', '', PARAM_PATH); // $nameofarray = optional_param_array('nameofarray', null, PARAM_INT);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

global $CFG, $USER, $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/local/ibq/download_bog.php');

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);

//admin_externalpage_setup('transcripts', '', $pageparams);

$session = $annee = 0;

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_ibq'));
$PAGE->set_heading($header);
$PAGE->set_url('/local/ibq/download_bog.php', array("annee"=>$annee,"session" => $session));
$session = required_param('session', PARAM_INT);
$annee = required_param('annee', PARAM_INT);
if ($session>0) {
	$la_session = $annee. "-" .$session;	
} else {
	$la_session = $annee;
}


//$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/ibq/css/custom.css'));
 
 
 
//echo $OUTPUT->header();

/* Add your custom code here..*/

$html = $ibq_no = $idul = $program = $admission = $base_admission = $graduation = $label_ibq = $label_program = $label_admission = $label_baseadmission = $label_graduation = '';
$html = $ibq_no = '';

$titre_session = array( "Année complète - Full Year", "Hiver - Winter", "Été - Summer", "Automne - Autumn ","4","5","6","7","8","divers - others" );

$roles_ibq = array(7,8,10,15);
$roles_ul = array(5,11);
$roles_aud = array(12);
$roles_filtre = array(5,7,8,10,11,12,15,16);
$roles_etudiants = array(5,7,8,10,11,15);
$total_fte = 0;

$sql_filtre = "(5,7,8,10,11,12,15,16)";
$sql_teacher = "(3)";
$sql_corrector = "(4)";
$sql_tutor = "(9)";


$print_date = $ibq->get_french_date();
 

$header = $ibq->get_institute_info() .'<BR>
			<h3 class="cr-text-center">'. get_string("titre", "local_ibq") . get_string("pour", "local_ibq") . $titre_session[$session] . ' ' .$annee . '</h3></BR>';

// calcul complet
$l_pg = array( );
$l_pg2 = array( );
$l_ibq = array( );
$l_ul = array( );
$l_aud = array( );
$l_role = array();
$l_role_name = array();
$l_tableau = array();
$l_total = array();
$g_total = array();
$l_total_type = array();
$nouveau_deja = array();
for ($i = 0; $i <= 30; $i++) {
	$l_total[$i]=$l_total_type[$i]=$g_total[$i]=0;
	$l_pg[$i]="Z";
	for ($j = 0; $j <= 20; $j++) {
		$l_tableau[$i][$j]=0;
		$l_role_name[$j]="*";
	}
}


$query = "SELECT * FROM `mdl_course` WHERE fullname LIKE '". $la_session ."%'";

$sqlcourses = $DB->get_records_sql($query);
$les_credits = 3;
foreach($sqlcourses as $sqlcours)
{	
	//$body .= "<BR><strong>". $sqlcours->fullname ."</strong> ";
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
	//$body .= "(".$les_credits ." crédits)";
	
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
	if ($le_type_du_cours > -1) {
		$l_total_type[$le_type_du_cours] += 1;	
	}
		
		
	// liste des étudiants 
	//$body .= "<BR>";
	$query3 = "SELECT
				CONCAT(u.firstname,' ',u.lastname,'  *',r.id) AS nom,
				u.id as studentid,
				r.shortname AS 'role', r.name, r.id AS roleid,
				ctx.instanceid AS 'Context instance id'
				FROM mdl_role_assignments ra
				JOIN mdl_user u ON u.id = ra.userid
				JOIN mdl_role r ON r.id = ra.roleid
				JOIN mdl_context ctx ON ctx.id = ra.contextid

				WHERE ctx.instanceid=".$courseid."
				ORDER BY u.lastname";
	
	$sqlstudents = $DB->get_records_sql($query3);
	
	foreach($sqlstudents as $student)
	{
		//$body .= $student->nom;
		$sql_role = $student->role;
		$sql_role_name = $student->name;
		//$body .= " (". $sql_role_name .")";
		$roleid = $student->roleid;
		$student_id = $student->studentid;
		
		if (in_array($roleid,$roles_etudiants)) {
			$total_fte += $les_credits;	
		}
		
		$key_role_name = -1;
		if (in_array($roleid, $roles_filtre)) {
				$last=-1;
				//if ($sql_role_name=="Cours IBQ") {$sql_role_name = "Cours IBQ/UL";}
				for ($i = 0; $i <= 20; $i++) {
					if ($sql_role_name==$l_role_name[$i]) {
						$key_role_name=$i;
					}
					if (($l_role_name[$i]=="*")and($last==-1)) {
						$last=$i;
					}
				}
					
				if ($key_role_name <0) {					 
					$l_role_name[$last]=$sql_role_name;	
					$key_role_name=$last;
				}
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
		//$body .= " ".$le_programme ."<BR>";
		
	
	
		$key_prog = -1;
		
		$last=-1;
		if ($le_programme=="Diplôme en théologie pratique (cours en partenariat avec l'Université Laval)") {$le_programme = "Diplôme en théologie pratique";}
		
		if ($le_programme=="Diplôme en théologie - option Bible et théologie (cours en partenariat avec l’Université Laval)") {$le_programme = "Diplôme en théologie - option Bible et théologie";}
		
		if ($le_programme=="Diplôme en théologie option Bible et théologie (cours en partenariat avec l'Université Laval)") {$le_programme = "Diplôme en théologie - option Bible et théologie";}

		
		if ($le_programme=="Diplôme en Théologie (cours en partenariat avec l'Université Laval)") {$le_programme = "Diplôme en Théologie";}

		
		

		if ($le_programme=="Certificat en théologie avec concentration en accompagnement spirituel (cours avec l’université Laval)") {$le_programme = "Certificat en théologie avec concentration en accompagnement spirituel";}

		if ($le_programme=="Certificat en études bibliques (cours avec l’Université Laval)") {$le_programme = "Certificat en études bibliques";}
		
		if ($le_programme=="Certificat en études bibliques (cours en partenariat avec l'Université Laval)") {$le_programme = "Certificat en études bibliques";}
		
		if ($le_programme=="Certificat en études bibliques") {$le_programme = "Certificat en études bibliques";}
		
		if ($le_programme=="Certificat en études bibliques ") {$le_programme = "Certificat en études bibliques";}
		
		
		if ($le_programme=="Certificat en théologie pratique (cours en partenariat avec l'Université Laval)") {$le_programme = "Certificat en théologie pratique";}
		
		if ($le_programme=="Certificat en théologie (cours en partenariat avec l'Université Laval)") {$le_programme = "Certificat en théologie";}
		
		if ($le_programme=="Certificat en études pastorales (cours en partenariat avec l'Université Laval)") {$le_programme = "Certificat en études pastorales";}
		
		
		
		
		if ($le_programme=="Certificat en études bibliques (cours en partenariat avec l'Université Laval)") {$le_programme = "Certificat en études bibliques";}

		if ($le_programme=="Certificat in Practical Theology (courses in partnership with Laval University)") {$le_programme = "Certificate in Practical Theology";}
		
		if ($le_programme=="Certificate in Practical Theology (courses in partnership with Laval University)") {$le_programme = "Certificate in Practical Theology";}
		
		if ($le_programme=="Certificate in Practical Theology (courses in partnership with Laval University)") {$le_programme = "Certificate in Practical Theology";}
		
		

		if ($le_programme=="Programme court en accompagnement spirituel (cours avec l’Université Laval)") {$le_programme = "Programme court en accompagnement spirituel";}
		
		if ($le_programme=="Programme court en accompagnement spirituel (cours en partenariat avec l'Université Laval)") {$le_programme = "Programme court en accompagnement spirituel";}
		
		if ($le_programme=="Programme court en ministère jeunesse (cours avec l’Université Laval)") {$le_programme = "Programme court en ministère jeunesse";}
		
		if ($le_programme=="Microprogramme en théologie (cours en partenariat avec l'Université Laval)") {$le_programme = "Microprogramme en théologie";}
		
		
		if ($le_programme=="Certificat en théologie avec concentration en accompagnement spirituel (cours en partenariat avec l'Université Laval)") {$le_programme = "Certificat en théologie avec concentration en accompagnement spirituel";}
		
		
		
		 
		
		for ($i = 0; $i <= 30; $i++) {
			if ($le_programme==$l_pg[$i]) {
				$key_prog=$i;
			}
			if (($l_pg[$i]=="Z")and($last==-1)) {
				$last=$i;
			}
		}
								
		if ($key_prog <0) {
				$l_pg[$last]=$le_programme;	
				$key_prog=$last;
		} 
		
		if (($key_prog>-1) and ($key_role_name>-1)) {
			$l_tableau[$key_prog][$key_role_name]++; 		
		}
		
	
	
	}
}

$fte = $total_fte/12;

$header .= "<BR><h3 class='cr-text-center'>F.T.E. : " .number_format((float)$fte, 2, '.', ''). "</h3></BR>";


$l_pg2 = $l_pg;
sort($l_pg2);

// afficher le tableau

$body .= '<BR><table width="90%" >
			<tr class="t-row-head"><td width="70%">Programme - Program</td>';
			
for ($j = 0; $j <= 20; $j++) 
{
	if ($l_role_name[$j]!="*") {
		if ($l_role_name[$j]=="Cours IBQ") {$l_role_name[$j]="IBQ";}
		if ($l_role_name[$j]=="Cours IBQ/UL") {$l_role_name[$j]="UL";}
		if ($l_role_name[$j]=="Auditrice / Auditeur") {$l_role_name[$j]="AUD";}
		$body .= '<td  width="6%" align="right">'. $l_role_name[$j] .'</td>';
	}
}
$body .= '<td  width="10%" align="right">TOTAL</td><td></td>&nbsp;</tr>';

 			


for ($i = 0; $i <= 30; $i++)
{
	if ($l_pg2[$i]!="Z") {	
		$ligne = '<tr><td >'. $l_pg2[$i] .'</td>';
		$i2=-1;
		for ($i3 = 0; $i3 <= 30; $i3++) {
			if ($l_pg2[$i]==$l_pg[$i3]) {$i2=$i3;}
		}
		$j=0;
		$total=0;
		for ($j = 0; $j <= 20; $j++) 
		{
			if ($l_role_name[$j]!="*") {
				$ligne .= '<td align="right">'. $l_tableau [$i2][$j] .'</td>';
				$total+=$l_tableau [$i2][$j];
				$g_total[$j]+=$l_tableau [$i2][$j];	
			}	
		}
		$ligne .= '<td align="right">'. $total .'</td></tr>';
		//$ligne .= ' ';
		if ($total>0) {
			$body .= $ligne;
		}
		$body .= " ";
	}
}
$body .= '<tr><td ><strong>Total</strong></td>';
		$j=0;
		$total=0;
		for ($j = 0; $j <= 20; $j++) 
		{
			if ($l_role_name[$j]!="*") {
				$body .= '<td align="right">'. $g_total[$j] .'</td>';
				$total+=$g_total[$j];
			}
		}
		$body .= '<td align="right"><strong>'. $total .'</strong></td>';
		$body .= '</tr></table>';


$body .= "<div class='t-dbl-line'></div>";


// nombre d'étudiant et de nouveau étudiants et groupe par nombre de cours 

$body .= "<BR>";	

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
			$body .= "<BR>". $sqlcountstudent->nb ." étudiantes (F) distinctes - Distinct Students (F)";
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
			$body .= "<BR>". $sqlcountstudent->nb ." étudiants (M) distincts - Distinct Students (M)";
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
			$body .= "<BR><Strong>". $sqlcountstudent->nb ." étudiants distincts total- Distinct Students</strong>";
		}
	}

$nombre_de_nouveaux = 0;
$nombre_par_nb_de_cours = array();
for ($j = 0; $j <= 20; $j++) {
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

 
$sqlstudents = $DB->get_records_sql($query6);
foreach($sqlstudents as $sqlstudent)
	{
		$student_id = $sqlstudent->studentid;
		
		
		// nouvel étudiant ? 
		$query7 = "
		SELECT COUNT(DISTINCT c.fullname) AS nb_cours
		FROM mdl_course AS c  
		LEFT JOIN mdl_context AS ctx ON c.id = ctx.instanceid
		JOIN mdl_role_assignments AS lra ON lra.contextid = ctx.id
		JOIN mdl_course_categories AS cats ON c.category = cats.id
		WHERE  lra.roleid IN ". $sql_filtre . "
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
		
		$nombre_par_nb_de_cours[$NB_TOTAL_COURS_DE_ETUDIANT_SESSION] +=1;
		

		
	}
	if ($nombre_de_nouveaux>1){
		$body .= "<BR>". $nombre_de_nouveaux . " nouveaux étudiants - new Students";
	} elseif ($nombre_de_nouveaux==1) {
		$body .= "<BR>Un(e) nouvell(e) étudiant(e) - One new Student";
	}

	$body .= "<div class='t-dbl-line'></div>";

	for ($j = 0; $j <= 10; $j++) {
		if ($nombre_par_nb_de_cours[$j]>0) {
			if ($nombre_par_nb_de_cours[$j]>1){
				$body .= "<BR>". $nombre_par_nb_de_cours[$j] . " étudiants ont suivi ". $j. " cours ";
				$body .= " - ". $nombre_par_nb_de_cours[$j] . " students followed ". $j. " courses ";
			} else {
				$body .= "<BR>". $nombre_par_nb_de_cours[$j] . " étudiant a suivi ". $j. " cours ";
				$body .= " - ". $nombre_par_nb_de_cours[$j] . " Student followed - ". $j. " Courses";
			}
		}
	}
	$body .= "<BR> ";
	$total = 0;
	for ($i = 0; $i <= 30; $i++) 
	{
		if ($l_total_type[$i]>0) {
			$body .= "<BR>". $l_total_type[$i] . " ". $types_cours[$i]. " ";
			$total += $l_total_type[$i];
		}
	}
	$body .= "<BR><STRONG> ". $total . " cours - " . $total . " Courses</STRONG>";
	$body .= "<BR>";







$footer = '<br><small>'. $print_date .'</small>';


	
$style = '<style>
table.table{
	width:100%;
}

.cr-text-center {
	text-align: center;
	margin-top:25px;
}

div.t-dbl-line {
	border-top: 1px double #666;
	height:1px;
	width: 100%;
	margin-top: 20px;
}

.t-program-name{
	margin:20px 0px;
}

.t-txt-right{
	text-align:right;
}

tr.t-row-head {
	background-color: lightcyan;
	border:1px solid #ccc;
}

div.t-signature {
	width: 300px;
	max-width: 300px;
	padding-top: 5px;
	border-top: 1px solid #666;
	margin-top: 40px;
}
div.t-footer-note{
	margin-top:50px;
}
div.emptyspace1{
	height:1px;
	width:100%;
}
div.emptyspace25{
	height:25px;
	width:100%;
}
div.emptyspace5{
	height:5px;
	width:100%;
}
div.emptyspace10{
	height:10px;
	width:100%;
}
</style>';


$html .= '
'. $style .'
'. $header .' 
'. $body .'
'. $footer .'
';

//start pdf building
$pdf_name = "BOG-". $annee ."-". $titre_session[$session] .".pdf";

$pdf = new pdf('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);  
$pdf->SetTitle("BOG");  
$pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);  
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
$pdf->SetDefaultMonospacedFont('helvetica');  
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);  
$pdf->SetMargins(PDF_MARGIN_LEFT, '5', PDF_MARGIN_RIGHT);  
$pdf->setPrintHeader(false);  
$pdf->setPrintFooter(false);  
$pdf->SetAutoPageBreak(TRUE, 10);  
$pdf->SetFont('helvetica', '', 9); 
$pdf->AddPage();
ob_clean();
$pdf->writeHTML($html, true, false, true, false, '');
ob_end_clean();
$pdf->Output($pdf_name, 'I');




echo $OUTPUT->footer();
