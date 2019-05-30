<?php
/*
Plugin Name: Participants Registered
Description: This plugin allows admin to display a dynamic table of entries using the Registration Entries custom_post_type. Use this shortcode to display <strong>[registration_table]</strong>.
Requirements: Advanced Custom Fields with the Student Registration Modules field group; hwcoe-ufl-child theme with career fair modifications; Optional: Gravity Forms with 
Version: 1.0
Author: Allison Logan
Author URI: http://allisoncandreva.com/
*/

function reg_table_shortcode() {
	
	//Query
	$the_query = new WP_Query(array( 'post_type' => 'cf-registrations', 'posts_per_page' => 100 ));
	
	//Table
	$output = '<table id="reg-table">
				<thead>
					<tr>
						<th>Name</th>
						<th>Email</th>
						<th>Status</th>
						<th>Department</th>
						<th>Visa</th>
						<th>Resume</th>
					</tr>
				</thead>
				<tbody>';
	
	while ( $the_query->have_posts() ) : $the_query->the_post();
			$output .= '<tr>
							<td>' .get_field( 'student_name' ). '</td>
							<td>' .get_field( 'student_email' ). '</td>
							<td>' .get_field( 'student_status' ). '</td>
							<td>' .get_field( 'student_department' ). '</td>
							<td>' .get_field( 'student_visa_sponsorship' ). '</td>';
				if(get_field( 'student_resume' )):  //if the field is not empty
				$output .= '<td><a href="' .get_field( 'student_resume' ). '" download>Resume: ' .get_field( 'student_name' ). '</a></td>'; //display it
					else: 
			$output .= '<td>n/a</td>';
					endif; 
			$output .= '</tr>';
	endwhile;
	wp_reset_query();
	
	$output .= '</tbody>
				</table>';
	
	//Return code
	return $output;
}

add_shortcode('registration_table', 'reg_table_shortcode'); 