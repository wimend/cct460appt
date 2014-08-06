<?php
/*
Plugin Name: CCT460 Appointments
Description: Users can book appointments to dentist office based on availabel times set by admin.
Plugin URI: 
Version: 1.0
Author: Group 4
Author URI: 
*/


// Create constants for table names using WP prefix. They'll be used many times in the code.
global $wpdb;
define ('SERVICE_TABLE_NAME', $wpdb->prefix . "cct460appt_services");
define ('BUSINESS_HOURS_TABLE_NAME', $wpdb->prefix . "cct460appt_business_hours");
define ('APPOINTMENTS_TABLE_NAME', $wpdb->prefix . "cct460appt_appointments");


// Create a menu item and its submenu items on the back-end. Hooks this function to 'admin_menu' hook.
function cct460appt_addmenu() {
    add_menu_page('CCT460 Appointments', 'CCT460 Appointments', 'administrator', 'cct460appt_settings', 'cct460appt_display_settings');
	add_submenu_page('cct460appt_settings', 'Services', 'Services', 'administrator', 'cct460appt_services',  'cct460appt_display_services');
	add_submenu_page('cct460appt_settings', 'Business Hours', 'Business Hours', 'administrator', 'cct460appt_business_hours',  'cct460appt_display_business_hours');
	add_submenu_page('cct460appt_settings', 'Appointments', 'Appointments', 'administrator', 'cct460appt_appointments',  'cct460appt_display_appointments');
}
add_action('admin_menu', 'cct460appt_addmenu');

 // Register the stylesheets, both for front-end and back-end.
wp_register_style( 'adminStyle', plugins_url('cct460appt_admin_style.css', __FILE__) );
wp_register_style( 'clientStyle', plugins_url('cct460appt_client_style.css', __FILE__) );

// Page showed when users click on menu 'CCT460 Appointments' on the back-end.
function cct460appt_display_settings() {
    $html = '<div class="wrap">
    <h1> Instructions </h1>
		<p>To use the plugin, create one simple page and simply add this shortcode into its body: [book_appointment_form].</p>
	     </div>';
	
    echo $html;
}


// Page showed when users click on submenu 'Services' on the back-end.
function cct460appt_display_services() {
	
	// Load the stylesheet for the back-end
	wp_enqueue_style( 'adminStyle' );

	global $wpdb;
	
	// Create the HTML code for the form.
	$html = '<div class="wrap" id="apptAdmin">
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
	
	// Call the following function when users submit the form.			
	if('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['duration_post']))
		cct460appt_insert_services();
	
	// Create the HTML code for the table of results.
	$html .=	'<div id="existent_services">
					<table>
						<tr>
							<th>Service Name</th>
							<th>Duration (min)</th>
							<th></th>
						</tr>';
	
	// Get existing DB entries and show them in the table.					
	$results = $wpdb->get_results ("SELECT name, duration FROM " . SERVICE_TABLE_NAME);
	foreach ($results as $item) {
		$name = $item->name;
		// Times are blocks of 30 minutes. The database stores block indexes, i.e., 1 = 30min, 2 = 60min, etc.
		$duration = $item->duration * 30;
      		
      		// Put entries inside the HTML code, specifically one entry by table row.  
		$html .= 		"<tr>
							<td>$name</td>
							<td>$duration</td>
							<td><a>Delete</a></td>
						</tr>";
	}
						
	$html .= '		</table>
				</div>
			</div>';
			
	echo $html;
	
}

// Insert a new service into the database.
function cct460appt_insert_services(){
	global $wpdb;
	
	// Insert a new database entry into the table Services.
	// Times are blocks of 30 minutes. Duration: each hour corresponds to 2 blocks, and a new block is added if 'minutes' were set as '30'.
	$rows_affected = $wpdb->insert( SERVICE_TABLE_NAME, array( 'name' => $_POST['service_name'],
                                                                    'duration' => ($_POST['hour_duration'] * 2 + $_POST['min_duration']) 
                                                                    ) );
        
        // Show an error message to users when query is unsuccessfully (no rows inserted).
        if (!$rows_affected)
	{
            echo '<span class="error">Error saving data! Please try again.';
            echo '<br /><br />Error debug information: '.mysql_error() . "</span>";
	} else{
		echo "<span class='success'>Data recorded sucessfully!</span>";
	}
}


// Page showed when users click on submenu 'Business Hours'.
function cct460appt_display_business_hours() {
	global $wpdb;
	
	// load the stylesheet for the back-end.
	wp_enqueue_style( 'adminStyle' );
	
	// Create the HTML code for the form.
	$html = '<div class="wrap" id="apptAdmin">
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
			
	// Call the following function when users submit the form.
	if('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['business_hour_post']))
		cct460appt_insert_business_hour();
	
	// Get existing DB entries and show them in the table.		
	$html .=	'<div id="apptAdmin" class="table_result">
				<table>
					<tr>
						<th>Week Day</th>
						<th>Start</th>
						<th>End</th>
						<th></th>
					</tr>';
	
	// Get existing DB entries and show them in the table.			
	$results = $wpdb->get_results ("SELECT weekday, start_hour_index, end_hour_index FROM " . BUSINESS_HOURS_TABLE_NAME);
	foreach ($results as $item) {
		// These functions map an index to a real value. Ex: weekday '1' = 'Sunday'.
		$weekday = get_weekday_from_index($item->weekday);
		$start_hour = get_hour_from_index($item->start_hour_index);
		$end_hour = get_hour_from_index($item->end_hour_index);
      		
      		// Put entries inside the HTML code, specifically one entry by table row.   
		$html .= 		"<tr>
							<td>$weekday</td>
							<td>$start_hour</td>
							<td>$end_hour</td>
							<td><a>Delete</a></td>
						</tr>";
	}
						
	$html .= '		</table>
				</div>
			</div>';
			
	echo $html;
}


// Insert a new business hours row into the database.
function cct460appt_insert_business_hour(){
	global $wpdb;
	
	// Insert a new database entry into the table Business Hours.
	// Weekday and time are indexes. E.g., weekday '1' = 'Sunday', weekday '2' = 'Monday', hour '1' = '00:00', hour '2' = '00:30'.
	$rows_affected = $wpdb->insert( BUSINESS_HOURS_TABLE_NAME, array( 'weekday' => $_POST['week_day'],
                                                                    'start_hour_index' => ($_POST['hour_start'] * 2 + $_POST['min_start']),
                                                                    'end_hour_index' => ($_POST['hour_end'] * 2 + $_POST['min_end']) 
                                                                    ) );
        // Show an error message to users when query is unsuccessfully (no rows inserted).                                                           
        if (!$rows_affected)
	{
            echo '<span class="error">Error saving data! Please try again.';
            echo '<br /><br />Error debug information: '.mysql_error() . "</span>";
	}else{
		echo "<span class='success'>Data recorded sucessfully!</span>";
	}
}


// Create the tables into WP database when the plugin is activated.
function cct460appt_install() {
    global $wpdb;

    // This array stores all commands.	
    $sqls = array();

        // Create the table 'Services'.
        $sqls[] = "CREATE TABLE IF NOT EXISTS " . SERVICE_TABLE_NAME . " (
			  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  name varchar(100) NOT NULL,
			  duration int(11) NOT NULL,
			  PRIMARY KEY  (id),
			  UNIQUE KEY id (id),
			  UNIQUE KEY name (name)
			);";
			
	// Create the table 'Business Hours'.		
	$sqls[] = "CREATE TABLE IF NOT EXISTS " . BUSINESS_HOURS_TABLE_NAME . " (
			  id int(11) NOT NULL AUTO_INCREMENT,
			  weekday int(11) NOT NULL,
			  start_hour_index int(11) NOT NULL,
			  end_hour_index int(11) NOT NULL,
			  PRIMARY KEY  (id)
			);";
			
	// Create the table 'Appointments'.		
	$sqls[] = "CREATE TABLE IF NOT EXISTS " . APPOINTMENTS_TABLE_NAME . " (
			  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  client_id int(11) NOT NULL,
			  service_id int(11) NOT NULL,
			  hour_index int(11) NOT NULL,
			  day date NOT NULL,
			  PRIMARY KEY  (id),
			  UNIQUE KEY id (id)
			);";

	// Perform all commands.
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
							<th></th>
						</tr>';
						
	$results = $wpdb->get_results ("SELECT a.client_id, s.name, a.day, a.hour_index FROM " . APPOINTMENTS_TABLE_NAME . " a, " . 
									SERVICE_TABLE_NAME . " s WHERE a.service_id = s.id");
	foreach ($results as $item) {
		$client = $item->client_id;
		$service = $item->name;
		$day = $item->day;
		$time = get_hour_from_index($item->hour_index);
      		  
		$html .= 		"<tr>
							<td>$client</td>
							<td>$service</td>
							<td>$day</td>
							<td>$time</td>
							<td><a>Delete</a></td>
						<tr>";
	}
						
	$html .= '		</table>
				</div>
			</div>';
	
    echo $html;
}

function book_appointment_form_display($atts) {
	wp_enqueue_style( 'clientStyle' );
	global $wpdb;

	 $html = '<div id="apptClient"><form name="book_appointment_form" action="" method="post">
	 <input type="hidden" name="book_appointment_post" id="1"/>';
	 if(isset($_POST['book_appointment_post']))
		$html .= ' <label>Date: <input type="date" name="date" value="'.$_POST['date'].'"/> </label>';
	 else
		$html .= ' <label>Date: <input type="date" name="date"/></label>';
	 $html .= ' <label>Service: <select name="service">';

	 $results = $wpdb->get_results ("SELECT name, id FROM " . SERVICE_TABLE_NAME);
	 foreach ($results as $item) {
		$name 	= $item->name;
		$id 	= $item->id;
		if(isset($_POST['book_appointment_post']) && $_POST['service'] == $id)
			$html .= "<option value='$id' selected='selected'>$name</option>";
		else
			$html .= "<option value='$id'>$name</option>";
	 }

	 $html .= '</select> </label>
	 <input type="submit" value="Check available times">
	 </form></div>';

	 echo $html;

	 if('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['book_appointment_post']))
		cct460appt_request_form_available_times();
		
	if('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['request_form_available_times']))
		cct460appt_insert_appointment();
}
add_shortcode('book_appointment_form', 'book_appointment_form_display');


function cct460appt_request_form_available_times() {
	global $wpdb;

	 $time_index_array = array();
	 $time_index_array = get_available_time_indexes(); 

	if(empty($time_index_array)){

		$html = "<div>There is no time available for this day</div>";
	}else{
		 $html = '<form name="available_times_choice" action="" method="post">
			 <input type="hidden" name="request_form_available_times" id="1"/>
			 <input type="hidden" name="day" value="'.$_POST['date'].'"/>
			 <input type="hidden" name="service_number" value="'.$_POST['service'].'"/>
			 <label>Time: <select name="time">'; // Willian: inserir campo hidden, criar funcao insert e salvar no bd

			 foreach ($time_index_array as $time_index) {
				$hour = get_hour_from_index($time_index);
				$html .= '<option value="' . $time_index . '">' . $hour . '</option><br/>';
			 }

			 $html .= ' </select></label>
			 <label>Client number: <input type="text" name="client_number"/></label>
			 <input type="submit" value="Book appointment">
		 </form>';
	}
	 echo $html;
}


function cct460appt_insert_appointment(){
	global $wpdb;
		
		$rows_affected = $wpdb->insert( APPOINTMENTS_TABLE_NAME , array( 'service_id' => $_POST['service_number'],
                                                                    'client_id' => $_POST['client_number'],
                                                                    'hour_index' => $_POST['time'],
                                                                    'day' => $_POST['day']
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

// Calculates and returns the available time on the schedule
function get_available_time_indexes() { 
/*
 * SERVICE_TABLE_NAME
 * BUSINESS_HOURS_TABLE_NAME
 * APPOINTMENTS_TABLE_NAME
*/ 	
	global $wpdb;

	$sql = "SELECT hour_index as hi,
				duration as qnt,
				start_hour_index as shi, 
				end_hour_index as ehi
				
		FROM ". BUSINESS_HOURS_TABLE_NAME." AS w 

		LEFT JOIN ".APPOINTMENTS_TABLE_NAME."  AS c
			ON c.day ='".$_POST['date']."'
			AND w.weekday = (weekday(c.day) + 2) % 7 
			LEFT JOIN ". SERVICE_TABLE_NAME ." AS s
			ON c.service_id = s.id
		
		WHERE w.weekday =(weekday('".$_POST['date']."') + 2) % 7";
		
	$sql2 = "select duration from ". SERVICE_TABLE_NAME ." where id='".$_POST['service']."'";

	$results = $wpdb->get_results ($sql);
	
	$results2 = $wpdb->get_results ($sql2);
	
	$shi = -1;
	$ehi = -1;
	$qnt = -1;
	
	$qnt2 = -1;
	foreach ($results2 as $row2)
		$qnt2 = $row2->duration;
		
	foreach ($results as $row) {

		if($shi == -1){
				
				$shi = $row->shi;
				$ehi = $row->ehi;
				$qnt = $row->qnt;
				if(!empty($row->hi)){
					$exception[] = $row->hi;
					for($j=1; $j < $qnt; $j++)
						$exception[] = $row->hi + $j;
						
					for($k=1; $k < $qnt2; $k++)
						$exception[] = $row->hi - $k;
				}
				
	
				
		}else{
			if(!empty($row->hi)){
				$exception[] = $row->hi;
				$qnt = $row->qnt;
				for($j=1; $j < $qnt; $j++)
					$exception[] = $row->hi + $j;
					
				for($k=1; $k < $qnt2; $k++)
						$exception[] = $row->hi - $k;
			}
		}
	}
	for($i = $shi; $i < $ehi - $qnt2 + 1; $i++){

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
