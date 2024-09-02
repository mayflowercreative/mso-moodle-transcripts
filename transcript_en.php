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
require_once("locallib.php");


$path = optional_param('path', '', PARAM_PATH); // $nameofarray = optional_param_array('nameofarray', null, PARAM_INT);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

global $CFG, $USER, $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/local/ibq/transcript_en.php');

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);

//admin_externalpage_setup('transcripts', '', $pageparams);

$session = $annee = 0;

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_ibq'));
$PAGE->set_heading($header);
$PAGE->set_url('/local/ibq/transcript_en.php', array("id"=>$userid));
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/ibq/css/custom.css'));
 
$userid = required_param('id', PARAM_INT);
$user = $ibq->get_user_detail($userid); 



echo $OUTPUT->header();

/* Add your custom code here..*/



$html = $ibq_no = $idul = $program = $admission = $base_admission = $graduation = $label_ibq = $label_program = $label_admission = $label_baseadmission = $label_graduation = '';

$name = 'None';
if(isset($user)){
	$name = $user->firstname . " " . $user->lastname;
}

$userdetail = $ibq->get_additional_detail($userid);
$print_date = $ibq->get_french_date();

if(isset($userdetail["Num_IBQ"])){
	if (strlen($userdetail["Num_IBQ"])>2) {
		$ibq_no = $userdetail["Num_IBQ"];
		$label_ibq = "Identification :";
	} else {
		$ibq_no = " ";
		$label_ibq = " ";
	}
}
if(isset($userdetail["IDUL"])){
	$idul = $userdetail["IDUL"];	
}
$Comment_transcript = " ";
if(isset($userdetail["Comment_transcript"])){
	$Comment_transcript = $userdetail["Comment_transcript"];	
}
if(isset($userdetail["Porgramme"])){
	$program = $userdetail["Porgramme"];
	$label_program = "Program : ";
}

$admission = " "; 
$label_admission = " ";
if(isset($userdetail["Date_Admission"])){
	if ($userdetail["Date_Admission"]>0) {
		
		$admission =  date("m Y", strtotime($userdetail["Date_Admission"]));
		$label_admission = "Admission : ";
	}
}
$base_admission = " ";
$label_baseadmission = " ";
if(isset($userdetail["Base_admission"])){
	if (strlen($userdetail["Base_admission"])>0) {
		$base_admission = $userdetail["Base_admission"];
		$label_baseadmission = "Admission Base : ";
	} 
}
$graduation2 = "9999";
if(isset($userdetail["Graduation"])){
	if ($userdetail["Graduation"] > 0) {
		
		$dt = date_create($userdetail["Graduation"]);
		
		$graduation = date("d m Y", strtotime($userdetail["Graduation"]));
		$graduation2 = date("Y", strtotime($userdetail["Graduation"]));
		
		
		 
		
		//setlocale(LC_TIME, 'fr_FR.utf8','fra'); 
		//$graduation = strftime("%B %Y", $userdetail["Graduation"]);
		//$graduation2 = strftime("%Y", $userdetail["Graduation"]);
		$label_graduation = "Graduation : ";	
	} else {
		$graduation = " ";
		$label_graduation = " ";
	}
}

$header = '<div class="row">
		<div class="col-4">'. $ibq->get_institute_info() .'</div>
		<div class="col-4">
			<center>
				<h3><strong>'. $name .'</strong></h3>
				<table>
					<tr>
						<td>'. $label_ibq .'</td>
						<td> '. $ibq_no .'</td>
					</tr>
					<tr>
						<td></td>
						<td> '. $idul .'</td>
					</tr>
				</table>
			</center>
		</div>
		<div class="col-4 t-txt-right">
			<strong>Transcript</strong><br>
			'. $print_date .'<br>
			<table class="pull-right">
				<tr>
					<td>'. $label_admission .'</td>
					<td> '. $admission .'</td>
				</tr>
				<tr>
					<td>'. $label_baseadmission .'</td>
					<td>' . $base_admission . '</td>
				</tr>
				<tr>
					<td>'. $label_graduation .'</td>
					<td>'. $graduation .'</td>
				</tr>
			</table>
		</div>
		<div class="col-12 t-program-name"><strong>'. $label_program . $program .'</strong></div>';

		if ($graduation2<2015) {
			$header = "<div class='col-12 t-program-name'><strong>Warning</STRONG>, this transcript is not official beacuse it contains only MOODLE courses</div>";
		}
		$header .= '<div class="t-dbl-line"></div>
	</div>';
	
$body = '<table class="table table-hover">
	<thead>
		<tr>
			<th colspan="3" width="400"> </th>
			<th>Credits</th>
			<th>Points</th>
			<th>Grades</th>
		</tr>
	</thead>
	<tbody>';
	
$roles = $ibq->get_courses_by_role($userid);
$totalcredits = $totalcredits2 = $totalcredits3 = $totalcourses = 0;

$totalpoints = array();
$total_grade = 0;
foreach($roles as $key => $role){
	$rawrole = $ibq->get_role_name($key);
	$rolename = empty($rawrole->name) ? ucfirst($rawrole->shortname) : $rawrole->name;
	
	//collect all courses
	$totalcourses += count($role);
	
	if(count($role)>0){
		
		$body .= '
		<tr class="t-row-head">
			<td colspan="2">'. $rolename .'</td>
			<td colspan="4">Number of courses : '. count($role) .'</td>
		</tr>
		';
	
		foreach($role as $course){
			$credit = $point = $grade = '-';
			$rawcredit = $ibq->get_course_credits($course->courseid);
			 
			
			//default credit = 3
			if(isset($rawcredit->value)){
				$credit = $rawcredit->value;
				$credit--;
			}
			else{
				$credit = 3;
			}
			
			$percent = -1;
			$point = $ibq->grade_point($userid, $course->courseid);
			
			$grade = $ibq->grade_letter($userid, $course->courseid);
			
			$percent = $ibq->grade_percent($userid, $course->courseid);
					
			//if role is auditor, abandon, repris, blank credit, point and note
			if($key == 12 || $key == 13){
				$credit = $point = $grade = '-';
			} elseif ($key == 17) {
				$point = $grade = '-';
			}
			
			
			
			$body .= '
			<tr>
				<td colspan="3">'. $course->fullname .'</td>
				<td>'. $credit .'</td>
				<td>'. $point .'</td>
				<td>'. $grade .'</td>
			</tr>
			';
			
			//collect all credits
			if($credit == "-" OR $grade == "..."){
				if ($key == 17){
					$totalcredits2 += $credit;
					$percent = -1;
				}	
			}
			else{
				$totalcredits2 += $credit;
				if($key != 17){
					if ($grade != "E") {$totalcredits += $credit; }
					
					$totalcredits3 += $credit;
					
					if ($percent >-1) {
						$total_grade += $percent*$credit;
					}
				}
				
			}
			
			//collect all points
			if($key == 12 OR $key == 13 OR $key == 17 OR $grade == "..."){
				//if role is auditor or abandon, then don't calculate respective point in total
			}
			else{
				$totalpoints[] = $point;
			}
		}
	}
}

$body .='</tbody>
	</table>
	<div class="t-dbl-line"></div>';
	
//average points
//$point_avg = $ibq->get_avg($totalpoints);
$point_avg = round(($total_grade/$totalcredits3/100*4.33),5);
$avg_letter = $ibq->get_letter_by_point($point_avg);
$point_avg = round(($total_grade/$totalcredits3/100*4.33),2);
$avg_comment = $ibq->get_comment_by_letter_fr($avg_letter);

$footer = '
	<div class="row">
		<div class="col-12">
			<h3 class="cr-text-center">Summary</h3>
		</div>
		<div class="col-4">
			<table class="table">
			<thead></thead>
			<tbody>
			<tr class="t-row-head">
				<td></td>
				<td align="center">Number of courses</td>
			</tr>
			<tr>
				<td>Total</td>
				<td align="center">'. $totalcourses .'</td>
			</tr>
			</tbody>
			</table>
		</div>
		<div class="col-1"></div>
		<div class="col-3">
			<table class="table">
			<thead></thead>
			<tbody>
			<tr class="t-row-head">
				<td align="center">Attempted Credits</td>
			</tr>
			<tr>
				<td align="center">'. $totalcredits2 .'</td>
			</tr>
			
			<tr class="t-row-head">
				<td align="center">Completed Credits</td>
			</tr>
			<tr>
				<td align="center">'. $totalcredits .'</td>
			</tr>
			
			
			
			
			</tbody>
			</table>
		</div>
		<div class="col-1"></div>
		<div class="col-3">
			<table class="table">
			<thead></thead>
			<tbody>
			<tr class="t-row-head">
				<td align="center" colspan="2">Points</td>
			</tr>
			<tr>
				<td align="center" colspan="2">'. $point_avg .'</td>
			</tr>
			<tr class="t-row-head">
				<td align="center" colspan="2">Average</td>
			</tr>
			<tr>
				<td align="center">'. $avg_letter .'</td>
				<td align="center">'. $avg_comment .'</td>
			</tr>
			</tbody>
			</table>
		</div>
		
		
		
		
		
		<div class="col-12">
			<div class="t-signature">
				Registraire
			</div>
		</div>
		<div class="col-12">
			<div class="t-footer-note">
				'.$Comment_transcript.'
			</div>
		</div>
		<div class="col-12">
			<div class="t-footer-note">
				<small><strong>Note:</strong> This transcript shows the student’s progress for information purposes only. It can serve as an official statement when accompanied by the seal of the School and signed by the Registrar.</small>
			</div>
		</div>
	</div>';

$back = new moodle_url($CFG->wwwroot.'/local/ibq/transcripts.php');
$download = new moodle_url($CFG->wwwroot.'/local/ibq/download_en.php', array("id" => $userid));
$edituser = new moodle_url($CFG->wwwroot.'/user/editadvanced.php', array("id" => $userid, "course"=> 1));
$viewtranscript_fr = new moodle_url($CFG->wwwroot.'/local/ibq/transcript_fr.php', array("id" => $userid));

$html .= '<div class="row">
	<div class="col-12">
		<a class="btn btn-primary" href="'. $back .'">'. get_string("btnhome","local_ibq") .'</a>
		<a class="btn btn-primary" style="margin-left:10px" href="'. $edituser .'" target="_blank">'. get_string("btnedituser","local_ibq") .'</a>
		<a class="btn btn-primary" style="margin-left:10px" href="'. $download .'" target="_blank">'. get_string("btndownload_en","local_ibq") .'</a>
		<a class="btn btn-primary" style="margin-left:10px" href="'. $viewtranscript_fr .'" >'. get_string("btntranscript_fr","local_ibq") .'</a>
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
