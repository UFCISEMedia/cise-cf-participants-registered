<?php
/*
Plugin Name: Participants Registered
Description: This plugin allows admin to display a dynamic table of entries using the Registration Entries custom_post_type. Use this shortcode to display the table:<strong>[registration_table]</strong>.
Requirements: Advanced Custom Fields with the Student Registration Modules field group; hwcoe-ufl-career-fair theme; Gravity Forms with the Student Registration form and Gravity Forms + Custom Post Types plugin. 
Version: 1.0
Author: Allison Logan
Author URI: http://allisoncandreva.com/
*/

/*Adds custom post type*/
function create_post_type() {
  register_post_type( 'cf-registrations',
    array(
      'labels' => array(
        'name' => __( 'Entries' ), //Top of page when in post type
        'singular_name' => __( 'Entry' ), //per post
		'menu_name' => __('Registration Entries'), //Shows up on side menu
		'all_items' => __('All Entries'), //On side menu as name of all items
      ),
      'public' => true,
	  'menu_position' => 5,
      'has_archive' => true,
    )
  );
}
add_action( 'init', 'create_post_type' );

/*Add in custom columns in the admin panel*/
add_filter( 'manage_edit-hwcoe-participants_columns', 'hwcoe_participants_columns' ) ;

function hwcoe_participants_columns( $columns ) {

	$columns = array(
		'cb' => '&lt;input type="checkbox" />',
		'name' => __( 'Name' ),
		'email' => __( 'Email' ),
		'status' => __( 'Status' ),
		'department' => __( 'Department' ),
		'visa' => __( 'Visa Sponsorship' ),
		'resume' => __( 'Resume' ),
		'date' => __( 'Date' )		
	);

	return $columns;
}

add_action( 'manage_hwcoe-participants_posts_custom_column', 'manage_participants_columns', 10, 2 );

/*Pull in data for the custom columns*/
function manage_participant_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		/* If displaying the 'name' column. */
		case 'name' :

			/* Get the post meta. */
			$name = get_post_meta( $post_id, 'student_name', true );

			/* Display the post meta. */
			printf( $name );

			break;

		/* If displaying the 'email' column. */
		case 'email' :

			/* Get the post meta. */
			$email = get_post_meta( $post_id, 'stuent_email', true );

			/* Display the post meta. */
			printf( $email );

			break;			

		/* If displaying the 'status' column. */
		case 'status' :

			/* Get the post meta. */
			$status = get_post_meta( $post_id, 'student_status', true );

			/* Display the post meta. */
			printf( $status );

			break;	
			
		/* If displaying the 'department' column. */
		case 'department' :

			/* Get the post meta. */
			$department = get_post_meta( $post_id, 'student_department', true );

			/* Display the post meta. */
			printf( $department );

			break;
			
		/* If displaying the 'visa' column. */
		case 'visa' :

			/* Get the post meta. */
			$visa = get_post_meta( $post_id, 'student_visa_sponsorship', true );

			/* Display the post meta. */
			printf( $visa );

			break;
			
		/* If displaying the 'resume' column. */
		case 'resume' :

			/* Get the post meta. */
			$resume = get_post_meta( $post_id, 'student_resume', true );

			/* Display the post meta. */
			printf( '<a href="' . $resume . '">Resume</a>');

			break;			

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

/* Enqueue assets */
add_action( 'wp_enqueue_scripts', 'hwcoe_participants_registered_assets' );
function hwcoe_participants_registered_assets() {
    wp_register_style( 'hwcoe-participants-datatables', plugins_url( '/css/datatables.min.css' , __FILE__ ) );
    wp_register_style( 'participants-registered', plugins_url( '/css/participantsregistered.css' , __FILE__ ) );

    wp_register_script( 'hwcoe-participants-datatables', plugins_url( '/js/datatables.min.js' , __FILE__ ), array( 'jquery' ), null, true );
    wp_register_script( 'participants-registered', plugins_url( '/js/participantsregistered.js' , __FILE__ ), array( 'jquery' ), null, true );
}

/*Plugin shortcode*/
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