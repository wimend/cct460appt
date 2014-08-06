<?php
/*
Plugin Name: CCT460 Appointments
Description: Users can book appointments to dentist office based on availabel times set by admin.
Plugin URI: 
Version: 1.0
Author: Claudinei / Willian
Author URI: 
*/


// Creates table names
define ('SERVICE_TABLE_NAME', $wpdb->prefix . "cct460appt_services");
define ('BUSINESS_HOURS_TABLE_NAME', $wpdb->prefix . "cct460appt_business_hours");
define ('APPOINTMENTS_TABLE_NAME', $wpdb->prefix . "cct460appt_appointments");


// Creates a menu item and its submenu items on the back-end
function cct460appt_addmenu() {
    add_menu_page('CCT460 Appointments', 'CCT460 Appointments', 'administrator', 'cct460appt_settings', 'cct460appt_display_settings');
	add_submenu_page('cct460appt_settings', 'Services', 'Services', 'administrator', 'cct460appt_services',  'cct460appt_display_services');
	add_submenu_page('cct460appt_settings', 'Business Hours', 'Business Hours', 'administrator', 'cct460appt_business_hours',  'cct460appt_display_business_hours');
	add_submenu_page('cct460appt_settings', 'Appointments', 'Appointments', 'administrator', 'cct460appt_appointments',  'cct460appt_display_appointments');
}
add_action('admin_menu', 'cct460appt_addmenu');

 // Register the stylesheet.
wp_register_style( 'adminStyle', plugins_url('cct460appt_admin_style.css', __FILE__) );

// Page showed when users click on menu 'CCT460 Appointments'
function cct460appt_display_settings() {
    $html = '<div class="wrap">
				<p>To use the plugin, create one simple page and simply add this shortcode into its body: [book_appointment_form].</p>
			</div>';
	
    echo $html;
}


// Page showed when users click on submenu 'Services'
function cct460appt_display_services() {
	// load the stylesheet
	wp_enqueue_style( 'adminStyle' );

	global $wpdb;
	
	$html = '<div id="apptAdmin">
				<h1> Services </h1>
				<form name="services_form" method="post" action="">
					<input type="hidden" name="duration_post" id="1"/>
					<label> Name: <input type="text" name="service_name" maxlength="50" required> </label>
					<label> Duration: 
					<input name="hour_duration" type="number" min="0"  max="10"  required/>: 
							<select name="min_duration" >
								<option value=0 >00</option>
								<option value=1 >30</option>
							  </select></label>
					<input type="submit" value="Add">
				</form>';
				
	if('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['duration_post']))
		cct460appt_insert_services();
	
	$html .=	'<div id="existent_services">
					<table>
						<tr>
							<th>Service Name</th>
							<th>Duration (min)</th>
						</tr>';
						
	$results = $wpdb->get_results ("SELECT name, duration FROM " . SERVICE_TABLE_NAME);
	foreach ($results as $item) {
		$name = $item->name;
		$duration = $item->duration * 30;
      		  
		$html .= 		"<tr>
							<td>$name</td>
							<td>$duration</td>
						</tr>";
	}
						
	$html .= '		</table>
				</div>
			</div>';
			
	echo $html;
	
}

// insert the data on the database
function cct460appt_insert_services(){
	    global $wpdb;
		
		$rows_affected = $wpdb->insert( SERVICE_TABLE_NAME, array( 'name' => $_POST['service_name'],
                                                                    'duration' => ($_POST['hour_duration'] * 2 + $_POST['min_duration']) 
                                                                    ) );
         if (!$rows_affected)
		{
            echo 'Error saving data! Please try again.';
            echo '<br /><br />Error debug information: '.mysql_error();
            exit;
		}else{
			echo "<div>Data recorded sucessfully!</div>";
		}
}


// Page showed when users click on submenu 'Business Hours'
function cct460appt_display_business_hours() {
	// load the stylesheet
	wp_enqueue_style( 'adminStyle' );
	
	$html = '<div id="apptAdmin">
				<h1> Business Hours </h1>
				<form name="business_hours_form" method="post" action="">
					<input type="hidden" name="business_hour_post" id="1"/>
					<label>Week day: <select name="week_day">
								<option value="1">Sunday</option>
								<option value="2" selected>Monday</option>
								<option value="3">Tuesday</option>
								<option value="4">Wednesday</option>
								<option value="5">Thursday</option>
								<option value="6">Friday</option>
								<option value="7">Saturday</option>
							  </select></label>
					<label>Start: <input type="number" name="hour_start" min="0" max="23" step="1"  required> : 
						   <select name="min_start" >
								<option value=0 >00</option>
								<option value=1 >30</option>
							  </select></label>
					<label>End:   <input type="number" name="hour_end" min="0" max="23" step="1"  required> : 
							<select name="min_end" >
								<option value=0 >00</option>
								<option value=1 >30</option>
							  </select></label>
					<input type="submit" value="Add">
				</form>
			</div>';
			
	echo $html;
	
	if('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['business_hour_post']))
		cct460appt_insert_business_hour();	
}


// Insert business hours on the database
function cct460appt_insert_business_hour(){
	    global $wpdb;
		
		$rows_affected = $wpdb->insert( BUSINESS_HOURS_TABLE_NAME, array( 'weekday' => $_POST['week_day'],
                                                                    'start_hour_index' => ($_POST['hour_start'] * 2 + $_POST['min_start']),
                                                                    'end_hour_index' => ($_POST['hour_end'] * 2 + $_POST['min_end']) 
                                                                    ) );
         if (!$rows_affected)
		{
            echo 'Error saving data! Please try again.';
            echo '<br /><br />Error debug information: '.mysql_error();
            exit;
		}else{
			echo "<div>Data recorded sucessfully!</div>";
		}
}


// Creates the tables in WP database when the plugin is activated
function cct460appt_install() {
    global $wpdb;
	
	$sqls = array();

    $sqls[] = "CREATE TABLE IF NOT EXISTS " . SERVICE_TABLE_NAME . " (
			  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  name varchar(100) NOT NULL,
			  duration int(11) NOT NULL,
			  PRIMARY KEY  (id),
			  UNIQUE KEY id (id),
			  UNIQUE KEY name (name)
			);";
			
	$sqls[] = "CREATE TABLE IF NOT EXISTS " . BUSINESS_HOURS_TABLE_NAME . " (
			  id int(11) NOT NULL AUTO_INCREMENT,
			  weekday int(11) NOT NULL,
			  start_hour_index int(11) NOT NULL,
			  end_hour_index int(11) NOT NULL,
			  PRIMARY KEY  (id)
			);";
			
	$sqls[] = "CREATE TABLE IF NOT EXISTS " . APPOINTMENTS_TABLE_NAME . " (
			  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  client_id int(11) NOT NULL,
			  service_id int(11) NOT NULL,
			  hour_index int(11) NOT NULL,
			  day date NOT NULL,
			  PRIMARY KEY  (id),
			  UNIQUE KEY id (id)
			);";

	// Performs all queries
	foreach ($sqls as $sql)
		$wpdb->query($sql);
}
register_activation_hook( __FILE__, 'cct460appt_install');


// Deletes tables from WP database when the plugin is deactivated
function cct460appt_uninstall() {
	global $wpdb;
	
	$sqls = array();
	
	$sqls[] = "DROP TABLE IF EXISTS " . SERVICE_TABLE_NAME . ";";
	$sqls[] = "DROP TABLE IF EXISTS " . BUSINESS_HOURS_TABLE_NAME . ";";
	$sqls[] = "DROP TABLE IF EXISTS " . APPOINTMENTS_TABLE_NAME . ";";
	
	// Performs all queries
	foreach ($sqls as $sql)
		$wpdb->query($sql);
}
register_deactivation_hook( __FILE__, 'cct460appt_uninstall');


// Page showed when users click on submenu 'Appointments'
function cct460appt_display_appointments() {
     global $wpdb;

	 $html = '<div class="wrap">
	 <div class="table_result">
	 <table>
	 <tr>
	 <th>Client</th>
	 <th>Service</th>
	 <th>Day</th>
	 <th>Time</th>
	 </tr>';

	 $results = $wpdb->get_results ("SELECT a.client_id, s.name, a.day, a.hour_index FROM " . APPOINTMENTS_TABLE_NAME . " a, " .
	 SERVICE_TABLE_NAME . " s WHERE a.service_id = s.id");
	 foreach ($results as $item) {
		$client = $item->client_id;
		$service = $item->name;
		$day = $item->day;
		$time = get_hour_from_index($item->hour_index);

		 $html .= "<tr>
		 <td>$client</td>
		 <td>$service</td>
		 <td>$day</td>
		 <td>$time</td>
		 <tr>";
	 }

	 $html .= ' </table>
	 </div>
	 </div>';
     echo $html;
}

function book_appointment_form_display($atts) {
	 global $wpdb;

	 $html = '<form name="book_appointment_form" action="" method="post">
	 <input type="hidden" name="book_appointment_post" id="1"/>
	 Date: <input type="date" name="date">
	 Service: <select name="service">';

	 $results = $wpdb->get_results ("SELECT name FROM " . SERVICE_TABLE_NAME);
	 foreach ($results as $item) {
		$name = $item->name;
		$html .= "<option>$name</option>";
	 }

	 $html .= '</select>
	 <input type="submit" value="Check available times">
	 </form>';

	 echo $html;

	 if('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['book_appointment_post']))
		cct460appt_request_form_available_times();
}
add_shortcode('book_appointment_form', 'book_appointment_form_display');


function cct460appt_request_form_available_times() {
	 global $wpdb;

	 $time_index_array = array();
	 $time_index_array = get_available_time_indexes(); // Willian: inserir parametros

	 $html = '<form name="available_times_choice" action="" method="post">
	 Time: <select name="time">'; // Willian: inserir campo hidden, criar funcao insert e salvar no bd

	 foreach ($time_index_array as $time_index) {
		$hour = get_hour_from_index($time_index);
		$html .= '<option value="' . $time_index . '">' . $hour . '</option><br/>';
	 }

	 $html .= ' </select>
	 Your name: <input type="text" name="name">
	 <input type="submit" value="Book appointment">
	 </form>';

	 echo $html;
}


function get_weekday_from_index($index) {
	 $weekdays = array ("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

	 return $weekdays[$index-1];
}


function get_hour_from_index($index) {
	 $hour = "";
	 $hour .= ($index/2) < 10 ? "0".floor($index/2) : floor($index/2);
	 $hour .= ($index%2) == 0 ? ":00" : ":30";

	 return $hour;
}

function get_available_time_indexes() { // Willian: procurar dados no banco atraves desta funcao

/*
 * SERVICE_TABLE_NAME
 * BUSINESS_HOURS_TABLE_NAME
 * APPOINTMENTS_TABLE_NAME
*/ 	

	$sql = "SELECT hour_index as hi,
				duration as qnt,
				start_hour_index as shi, 
				end_hour_index as ehi
				
		FROM ". BUSINESS_HOURS_TABLE_NAME." AS w 

		LEFT JOIN ".APPOINTMENTS_TABLE_NAME."  AS c
			AND w.weekday = weekday(c.day)
			LEFT JOIN". SERVICE_TABLE_NAME ." AS s
			ON c.service_id = s.id
		
		WHERE w.weekday = weekday(now())";
			

	$results = $wpdb->get_results ($sql);

	$shi = -1;
	$ehi = -1;
	$qnt = -1;
	foreach ($results as $row) {

		if($shi == -1){
				
				$shi = $row->shi;
				$ehi = $row->ehi;
				$qnt = $row->qnt;
				if(!empty($row->hi)){
					$exception[] = $row->hi;
					for($j=1; $j < $qnt; $j++)
						$exception[] = $row->hi + $j;
				}
				
	
				
		}else{
			if(!empty($row->hi)){
				$exception[] = $row->hi;
				$qnt = $row->qnt;
				for($j=1; $j < $qnt; $j++)
					$exception[] = $row->hi + $j;
			}
		}
	}
	for($i = $shi; $i < $ehi; $i++){

		if(!isset($exception) || !in_array($i,$exception)){
			$ret[] = $i;
		}
	}
	
	
	if(isset($ret) && !empty($ret))
		return $ret;
	else
		return array();
}

?>
