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

/**
 * @created    09/01/2023 
 * @package    local_ibq
 * @copyright  2024 Jonathan Bersot  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();
 
class local_ibq {
	
	public function get_all_session() {
		global $DB;	
	    $query = 'select LEFT(fullname,6) as session, count(fullname) as nombre  FROM mdl_course WHERE fullname < "9999" GROUP BY session ORDER BY fullname DESC ';
	    $sessions = $DB->get_records_sql($query);
		return $sesions;
	}
	
	
	public function get_institute_info(){
		return get_string("instituteinfo", "local_ibq");
	}
	
	
	public function get_french_date(){
		setlocale(LC_TIME, 'fr_FR.utf8','fra'); 
		
		// "date_default_timezone_set" may be required by your server
		date_default_timezone_set( 'America/Toronto' );

		// make a DateTime object 
		// the "now" parameter is for get the current date, 
		// but that work with a date recived from a database 
		// ex. replace "now" by '2022-04-04 05:05:05'
		$dateTimeObj = new DateTime('now', new DateTimeZone('America/Toronto'));

		// format the date according to your preferences
		// the 3 params are [ DateTime object, ICU date scheme, string locale ]
		$dateFormatted = 
			IntlDateFormatter::formatObject( $dateTimeObj, 'eee d MMMM y',  'fr' );

		// test :
		$date = ucwords($dateFormatted);
		
		return $date;
	}
	
	
	public function get_all_users() {
		global $DB;	
	    $query = 'SELECT * FROM {user} WHERE deleted = 0 ORDER BY firstname';
	    $users = $DB->get_records_sql($query);
		return $users;
	}
	
	public function get_user_detail($userid){
		global $DB;
		$sql = 'SELECT * FROM {user} WHERE id = ?';
		$params = array($userid);
		$user = $DB->get_record_sql($sql, $params);
		return $user;
	}
	
	public function check_role($userid){
		global $DB;
		$sql = 'SELECT * FROM {role_assignments} WHERE userid = ? AND (roleid = 5 OR roleid = 10 OR roleid = 11 OR roleid = 12 OR roleid = 13 OR roleid = 15)';
		$params = array($userid);
		$roles = $DB->get_records_sql($sql, $params);
		return $roles;
	}
	
	
	public function get_additional_detail($userid){
		global $DB;
		$sql = 'SELECT uid.id, uid.userid, uid.fieldid, uid.data, uif.name AS fieldname, uif.shortname FROM {user_info_data} AS uid 
		INNER JOIN {user_info_field} AS uif ON uif.id = uid.fieldid
		WHERE uid.userid = ?';
		
		$params = array($userid);
		$result = $DB->get_records_sql($sql, $params);
		
		$userdetail = array();

		foreach($result as $ad){
			$userdetail["$ad->shortname"] = $ad->data;
		}
		
		return $userdetail;
	}
	
	public function get_enrolled_courses($userid){
		global $DB;
		//get enrolled courses
		$sql = 'SELECT ue.id AS ueid, ue.enrolid, ue.userid, e.courseid, c.fullname
			FROM {user_enrolments} AS ue
			INNER JOIN {enrol} AS e ON e.id = ue.enrolid
			INNER JOIN {course} AS c ON c.id = e.courseid
			WHERE ue.userid = ? AND visible = ?';
		$params = array($userid,1);
		$courselist = $DB->get_records_sql($sql, $params);
		return $courselist;
	}
	
	public function get_all_roles(){
		global $DB;	
		$sql = 'SELECT * FROM {role}';
		$params = array();
		$roles = $DB->get_records_sql($sql, $params);
		return $roles;
	}
	
	public function remove_roles($roles){
		//remove below roles for transcript
		unset($roles[1]); // Manager
		unset($roles[2]); // Course creator
		unset($roles[3]); // Editing teacher
		unset($roles[4]); // Teacher
		unset($roles[6]); // Invite
		unset($roles[7]); // User
		unset($roles[8]); // Frontpage
		unset($roles[9]); // Tuteur
		unset($roles[14]); // Comptablite
		
		return $roles;
	}
	
	public function get_courses_by_role($userid){
		$rawroles = self::get_all_roles();
		$roles = self::remove_roles($rawroles);
		$courselist= self::get_enrolled_courses($userid);
		
		$tdata = $rdata = array();
		
		foreach($roles as $role){
			 foreach($courselist as $course){
				$sr = 0;
				if($sr == 0){
					$course_roles = self::get_roles_in_course($userid, $course->courseid);
					foreach($course_roles as $crole){
						if($crole->roleid == 13){
							//skip abandon role to add in rdata
							$crole->courseid = $course->courseid;
							$rdata["$course->courseid"] = $crole;
						}
					}
					$sr++;
				}
				$is_user_exist = self::is_user_exist_in_course($userid, $course->courseid, $role->id);
				if($is_user_exist === true){
					$course->roleid = $role->id;
					$course->rolename = empty($role->name) ? ucfirst($role->shortname) : $role->name;
					$tdata["$role->id"]["$course->courseid"] = $course;
				}
			 }
		}
		
		foreach($rdata as $crs){
			$course_roles = self::get_roles_in_course($userid, $crs->courseid);
			if(count($course_roles)>1){
				foreach($course_roles as $crole){
					if($crole->roleid != 13){
						unset($tdata["$crole->roleid"]["$crs->courseid"]);
					}
				}
			}
		}
		
		return $tdata;
	}
	
	public function get_roles_in_course($userid, $courseid){
		global $DB;	
		$context_course = context_course::instance($courseid);
		$sql = 'SELECT ra.id, ra.userid, ra.roleid, r.name, r.shortname
		FROM {role_assignments} AS ra
		INNER JOIN {role} AS r ON r.id = ra.roleid
		WHERE ra.contextid = ? AND ra.userid = ?';
		$params = array($context_course->id, $userid);
		$list = $DB->get_records_sql($sql, $params);
		
		return $list;
	}
	
	public function is_user_exist_in_course($userid, $courseid, $roleid){
		global $DB;	
		$context_course = context_course::instance($courseid);
		$sql = 'SELECT ra.id, ra.userid
		FROM {role_assignments} AS ra
		WHERE ra.contextid = ? AND ra.roleid = ? AND ra.userid = ?';
		$params = array($context_course->id, $roleid, $userid);
		$list = $DB->get_records_sql($sql, $params);
		
		$result = false;
		
		if(count($list)>0){
			$result = true;
		}
		
		return $result;
	}
	
	public function get_role_name($roleid){
		global $DB;
		$sql = 'SELECT * FROM {role} WHERE id = ?';
		$params = array($roleid);
		$role = $DB->get_record_sql($sql, $params);
		return $role;
	}
	
	public function get_course_credits($courseid){
		global $DB;
		$context_course = context_course::instance($courseid);
		$sql = 'SELECT cd.id AS cdid, cd.value, cf.shortname
			FROM {customfield_data} AS cd
			INNER JOIN {customfield_field} AS cf ON cf.id = cd.fieldid
			WHERE cd.instanceid = ? AND cd.contextid = ? AND cf.shortname = ?';
		$params = array($courseid, $context_course->id, "credits");
		$credit = $DB->get_record_sql($sql, $params);
		return $credit;
	}
	
	public function get_course_points($userid, $courseid){
		$percent = self::calculate_percentage($userid, $courseid);
		//$output = self::get_letter_by_percent($percent);
		$output = self::get_letter_by_default($percent);
		return $output;
	}
	
	public function calculate_percentage($userid, $courseid){
		global $DB;
		$sql = 'SELECT * FROM {grade_items} WHERE courseid = ? AND itemtype = ? AND hidden = ?';
		$params = array($courseid, "mod", 0);
		$mods = $DB->get_records_sql($sql, $params);
		
		$total = $obtained = $percent = $rawpoints = 0;
		//$total = $percent = 0;
		//$obtained = array();
		$skip_activity = array(4010,4011,4013);
		
		foreach($mods as $mod){
			if(!in_array($mod->id, $skip_activity)){
				$rawpoints = self::get_obtained_points($userid, $mod->id);
				$total += $mod->grademax;
				$obtained += $rawpoints;
				//$obtained[] = $rawpoints;
			}
		}
		
		if($total>0 && $obtained>0){
			$percent = ($obtained/$total)*100;
		}
		
		return $percent;
	}
	
	/* This method is a copy of calculate_percentage method, just used for testing purpose*/
	public function t_points($userid, $courseid){
		global $DB;
		$sql = 'SELECT * FROM {grade_items} WHERE courseid = ? AND itemtype = ? AND hidden = ? AND gradetype = ?';
		$params = array($courseid, "mod", 0, 1);
		$mods = $DB->get_records_sql($sql, $params);
		
		$total = $percent = $rawpoints = $obtained = 0;
		//$total = $percent = 0;
		//$obtained = array();
		$skip_activity = array(4010,4011,4013);
		
		foreach($mods as $mod){
			if(!in_array($mod->id, $skip_activity)){
				$rawpoints = self::get_obtained_points($userid, $mod->id);
				$total += $mod->grademax;
				$obtained += $rawpoints;
				//$obtained[] = $rawpoints;
			}
		}
		
		// if($total>0 && $obtained>0){
			// $percent = ($obtained/$total)*100;
		// }
		
		//$result = implode("-",$obtained);
		
		return $obtained;
		//return $percent;
	}
	
	/* function made by Jonathan Bersot from Moodle website  */
	public function grade_point($userid, $courseid){
		global $DB;
				
		
		$sql = 'SELECT u.id, c.fullname, c.id, gg.finalgrade
		FROM mdl_course AS c JOIN mdl_context AS ctx ON c.id = ctx.instanceid JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id JOIN mdl_user AS u ON u.id = ra.userid JOIN mdl_grade_grades AS gg ON gg.userid = u.id JOIN mdl_grade_items AS gi ON gi.id = gg.itemid JOIN mdl_course_categories as cc ON cc.id = c.category WHERE gi.courseid = c.id AND gi.itemtype = "course" AND u.ID = ? AND c.id = ?';
		
		//$params = array($courseid, "mod", 0, 1);
		$params = array($userid, $courseid);
		$mods = $DB->get_records_sql($sql, $params);
		
		$percent = $points = 0;
		foreach($mods as $mod){
			$percent = $mod->finalgrade;
				
		}
		
		
		
		if ($percent >100) {
		 
			$sql = 'SELECT  * FROM {grade_items} WHERE courseid = ? AND itemtype = ? AND hidden = ? AND gradetype = ?'; 
			$params = array($courseid, "mod", 0, 1);
			$mod2s = $DB->get_records_sql($sql, $params);
			$total = $obtained = $percent2 = $rawpoints = 0;
		
			foreach($mod2s as $mod2){
				$rawpoints = self::get_obtained_points($userid, $mod2->id);
				$total += $mod2->grademax;
				$obtained += $rawpoints;
			
			}
		
			if($total>0 && $obtained>0){
				$percent2 = ($obtained/$total)*100;
			}	
		
			$percent = $percent2;
		}
			
		$point = ROUND(($percent/100*4.33),2);
		$encours = self::cours_encours($courseid);
		if ($encours) { $point = "...";}
		return $point;
	}
	
	/* function made by Jonathan Bersot from Moodle website  */
	public function grade_percent($userid, $courseid){
		global $DB;
				
		
		$sql = 'SELECT u.id, c.fullname, c.id, gg.finalgrade
		FROM mdl_course AS c JOIN mdl_context AS ctx ON c.id = ctx.instanceid JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id JOIN mdl_user AS u ON u.id = ra.userid JOIN mdl_grade_grades AS gg ON gg.userid = u.id JOIN mdl_grade_items AS gi ON gi.id = gg.itemid JOIN mdl_course_categories as cc ON cc.id = c.category WHERE gi.courseid = c.id AND gi.itemtype = "course" AND u.ID = ? AND c.id = ?';
		
		//$params = array($courseid, "mod", 0, 1);
		$params = array($userid, $courseid);
		$mods = $DB->get_records_sql($sql, $params);
		
		$percent = $points = 0;
		foreach($mods as $mod){
			$percent = $mod->finalgrade;
				
		}
		
		
		
		if ($percent >100) {
		 
			$sql = 'SELECT  * FROM {grade_items} WHERE courseid = ? AND itemtype = ? AND hidden = ? AND gradetype = ?'; 
			$params = array($courseid, "mod", 0, 1);
			$mod2s = $DB->get_records_sql($sql, $params);
			$total = $obtained = $percent2 = $rawpoints = 0;
		
			foreach($mod2s as $mod2){
				$rawpoints = self::get_obtained_points($userid, $mod2->id);
				$total += $mod2->grademax;
				$obtained += $rawpoints;
			
			}
		
			if($total>0 && $obtained>0){
				$percent2 = ($obtained/$total)*100;
			}	
		
			$percent = $percent2;
		}
			
		
		$encours = self::cours_encours($courseid);
		if ($encours) { $percent = -1;}
		return $percent;
	}
	
	/* function made by Jonathan Bersot  */
	public function cours_encours($courseid){
		global $DB;
		$course_enddate = time();
		$sql = 'select enddate FROM mdl_course WHERE id = ?';
		$params = array($courseid);
		$mods = $DB->get_records_sql($sql, $params);
		foreach($mods as $mod){
			if(isset($mod)){
				$course_enddate = $mod->enddate;
			}
		}
		$now = time();
		if ($course_enddate > $now) { $result = TRUE;} else {$result =FALSE;}
		return $result;
		
	}
	
	/* function made by Jonathan Bersot from Moodle website  */
	public function grade_letter($userid, $courseid){
		global $DB;
		$course_name = " - ";
		 
		
		
		$sql = 'select c.fullname course, round(g.finalgrade,2) percent,
				(SELECT l.letter FROM mdl_grade_letters l
				join mdl_context x on l.contextid = x.id
				WHERE x.contextlevel = 50
				and x.instanceid in (c.id, 0) and l.lowerboundary <= g.finalgrade
				ORDER BY x.id desc, lowerboundary desc limit 1) overridden_grade_scale,

				(SELECT l.letter FROM mdl_grade_letters l 
				WHERE l.contextid = 1 
				and l.lowerboundary <= g.finalgrade
				ORDER BY l.lowerboundary desc limit 1) default_grade_scale

				from mdl_user AS u
				join mdl_grade_grades g on g.userid = u.id
				join mdl_grade_items gi on gi.id = g.itemid
				JOIN mdl_course c on c.id = gi.courseid

				#Join relationship for student role in the course
				JOIN mdl_enrol AS en ON en.courseid = c.id
				JOIN mdl_user_enrolments AS ue ON ue.enrolid = en.id and ue.userid = u.id
				JOIN mdl_context AS ctx ON c.id = ctx.instanceid
				JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id and ra.userid = u.id
				JOIN mdl_role r on ra.roleid = r.id

				where gi.itemtype = "course"

				AND u.id = ? 
				AND gi.courseid = ?';
		
		//$params = array($courseid, "mod", 0, 1);
		$params = array($userid, $courseid);
		$mods = $DB->get_records_sql($sql, $params);
		
		$total = $percent = $rawpoints = $obtained = $point = 0;
		$letter = "-";
		foreach($mods as $mod){
			if(isset($mod)){
				$letter = $mod->overridden_grade_scale;
				$course_name = $mod->course;
				$percent = $mod->percent;
				if (!$letter) {
					$letter = $mod->default_grade_scale;
				}
			}	
		}
		
		
		if ($percent >100) {	
			$sql = 'SELECT  * FROM {grade_items} WHERE courseid = ? AND itemtype = ? AND hidden = ? AND gradetype = ?'; 
			$params = array($courseid, "mod", 0, 1);
			$mod2s = $DB->get_records_sql($sql, $params);
			$total = $obtained = $percent2 = $rawpoints = 0;
		
			foreach($mod2s as $mod2){
				$rawpoints = self::get_obtained_points($userid, $mod2->id);
				$total += $mod2->grademax;
				$obtained += $rawpoints;
			}
		
			if($total>0 && $obtained>0){
				$percent2 = ($obtained/$total)*100;
			}
		
			$point = $percent2/100*4.3;
			if ($course_name > "2019-2"){
				$letter = self::get_letter_by_percent_new($percent2);
			} else {
				$letter = self::get_letter_by_percent_old($percent2);
			}
			
		}
		
		$encours = self::cours_encours($courseid);
		if ($encours) { $letter = "...";}
		return $letter;
	}
	
	public function get_obtained_points($userid, $itemid){
		global $DB;
		$sql = 'SELECT * FROM {grade_grades} WHERE itemid = ? AND userid = ? AND hidden = ?';
		//$sql = 'SELECT * FROM {grade_grades} WHERE itemid = ? AND userid = ?';
		$params = array($itemid, $userid, 0);
		$grade = $DB->get_record_sql($sql, $params);
		$result = 0;
		if(isset($grade->finalgrade)){				
			$result = $grade->finalgrade;
		}
		return $result;
	}
	
	public function get_letter_by_point($point){
		$grade = "-";
		if($point<= 2.40272){
			$grade = "E";
		}
		elseif($point>2.40272 && $point<=2.57592){
			$grade = "D";
		}
		elseif($point>2.57592 && $point<=2.79242){
			$grade = "D+";
		}
		elseif($point>2.79242 && $point<=2.92232){
			$grade = "C-";
		}
		elseif($point>2.92232 && $point<=3.05222){
			$grade = "C";
		}
		elseif($point>3.05222 && $point<=3.22542){
			$grade = "C+";
		}
		elseif($point>3.22542 && $point<=3.35532){
			$grade = "B-";
		}
		elseif($point>3.35532 && $point<=3.48522){
			$grade = "B";
		}
		elseif($point>3.48522 && $point<=3.65842 ){
			$grade = "B+";
		}
		elseif($point>3.65842 && $point<=3.87492){
			$grade = "A-";
		}
		elseif($point>3.87492 && $point<=4.09142){
			$grade = "A";
		}
		elseif($point>4.09142 && $point<=4.33333){
			$grade = "A+";
		}
		else{
			$grade = "-";
		}
		return $grade;
	}
	
	public function get_letter_by_percent_new($percent){
		$grade = $point = "-";
		if($percent>=0 && $percent<=55.49){
			$grade = "E";
			
		}
		elseif($percent>=55.50 && $percent<=59.49){
			$grade = "D";
			
		}
		elseif($percent>=59.50 && $percent<=64.49){
			$grade = "D+";
			
		}
		elseif($percent>=64.50 && $percent<=67.49){
			$grade = "C-";
			
		}
		elseif($percent>=67.50 && $percent<=70.49){
			$grade = "C";
			
		}
		elseif($percent>=70.50 && $percent<=74.49){
			$grade = "C+";
			
		}
		elseif($percent>=74.50 && $percent<=77.49){
			$grade = "B-";
			
		}
		elseif($percent>=77.50 && $percent<=80.49){
			$grade = "B";
			
		}
		elseif($percent>=80.50 && $percent<=84.49 ){
			$grade = "B+";
			
		}
		elseif($percent>=84.50 && $percent<=89.49){
			$grade = "A-";
			
		}
		elseif($percent>=89.50 && $percent<=94.49){
			$grade = "A";
			
		}
		elseif($percent>=94.50 && $percent<=100){
			$grade = "A+";
			
		}
		else{
			$grade = "-";
			
		}
		
		$result = $grade;
		
		return $result;
	}
	
	//default letter system as per IBQ Moodle
	public function get_letter_by_percent_old($percent){
		$grade = $point = "-";
		if($percent>=0 && $percent<=59.99){
			$grade = "E";
			
		}
		elseif($percent>=60 && $percent<=63.99){
			$grade = "D";
			
		}
		elseif($percent>=64 && $percent<=66.99){
			$grade = "D+";
			
		}
		elseif($percent>=67 && $percent<=69.99){
			$grade = "C-";
			
		}
		elseif($percent>=70 && $percent<=73.99){
			$grade = "C";
			
		}
		elseif($percent>=74 && $percent<=76.99){
			$grade = "C+";
			
		}
		elseif($percent>=77 && $percent<=79.99){
			$grade = "B-";
			
		}
		elseif($percent>=80 && $percent<=83.99){
			$grade = "B";
			
		}
		elseif($percent>=84 && $percent<=86.99 ){
			$grade = "B+";
			
		}
		elseif($percent>=87 && $percent<=89.99){
			$grade = "A-";
			
		}
		elseif($percent>=90 && $percent<=94.99){
			$grade = "A";
			
		}
		elseif($percent>=95 && $percent<=100){
			$grade = "A+";
			
		}
		else{
			$grade = "-";
			$point = "-";
		}
		
		$result = $grade;
		
		return $result;
	}
	
	public function get_custom_letters($userid, $courseid){	
		global $DB;
		$context = context_course::instance($courseid);
		
		$percent = self::calculate_percentage($userid, $courseid);
		
		$sql = 'SELECT * FROM {grade_letters} WHERE contextid = ? AND lowerboundary <= ? ORDER BY ID LIMIT 1';
		$params = array($context->id, $percent);
		$letter = $DB->get_record_sql($sql, $params);
		return $letter;
	}
	
	public function get_avg($values){
		if(count($values)>0){
			$avg = array_sum($values) / count($values);
			$avg = round($avg, 2);
		}
		else{
			$avg = 0;
		}
		
		return $avg;
	}
	
	public function get_comment_by_letter_en($letter){
		$comment = "-";
		if($letter == "A+"){
			$comment = "Outstanding";
		}
		elseif($letter == "A"){
			$comment = "Excellent";
		}
		elseif($letter == "A-"){
			$comment = "Excellent";
		}
		elseif($letter == "B+"){
			$comment = "Good";
		}
		elseif($letter == "B"){
			$comment = "Good";
		}
		elseif($letter == "B-"){
			$comment = "Good";
		}
		elseif($letter == "C+"){
			$comment = "Average";
		}
		elseif($letter == "C"){
			$comment = "Average";
		}
		elseif($letter == "C-"){
			$comment = "Average";
		}
		elseif($letter == "D+"){
			$comment = "Below Average";
		}
		elseif($letter == "D"){
			$comment = "Below Average";
		}
		elseif($letter == "E"){
			$comment = "Fail";
		}
		else{
			$comment = "-";
		}
		return $comment;
	}
	
	public function get_comment_by_letter_fr($letter){
		$comment = "-";
		if($letter == "A+"){
			$comment = "Exceptionnel";
		}
		elseif($letter == "A"){
			$comment = "Excellent";
		}
		elseif($letter == "A-"){
			$comment = "Excellent";
		}
		elseif($letter == "B+"){
			$comment = "Très bon";
		}
		elseif($letter == "B"){
			$comment = "Très bon";
		}
		elseif($letter == "B-"){
			$comment = "Très bon";
		}
		elseif($letter == "C+"){
			$comment = "Bon";
		}
		elseif($letter == "C"){
			$comment = "Bon";
		}
		elseif($letter == "C-"){
			$comment = "Bon";
		}
		elseif($letter == "D+"){
			$comment = "Passable";
		}
		elseif($letter == "D"){
			$comment = "Passable";
		}
		elseif($letter == "E"){
			$comment = "Echec";
		}
		else{
			$comment = "-";
		}
		return $comment;
	}
	
	
}

$ibq = new local_ibq(); 