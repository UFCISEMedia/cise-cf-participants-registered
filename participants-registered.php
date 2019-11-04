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
function create_participant_post_type() {
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
add_action( 'init', 'create_participant_post_type' );

/*Add in custom columns in the admin panel*/
add_filter( 'manage_edit-cf-registrations_columns', 'cf_registrations_columns' ) ;

function cf_registrations_columns( $columns ) {

	$columns = array(
		'cb' => '&lt;input type="checkbox" />',
		'title' => __( 'Title' ),
		'email' => __( 'Email' ),
		'status' => __( 'Status' ),
		'department' => __( 'Department' ),
		'visa' => __( 'Visa Sponsorship' ),
		'resume' => __( 'Resume' ),
		'date' => __( 'Date' )		
	);

	return $columns;
}

add_action( 'manage_cf-registrations_posts_custom_column', 'manage_cf_registrations_columns', 10, 2 );

/*Pull in data for the custom columns*/
function manage_cf_registrations_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {
			
		/* If displaying the 'email' column. */
		case 'email' :

			/* Get the post meta. */
			$email = get_post_meta( $post_id, 'student_email', true );

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

			/* If no resume is found, output a default message. */
			if ( empty( $resume ) )
				echo __( 'n/a' );
			
			/* If resume is uploaded, display the post meta. */
			else
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

/*Convert Name field to Title Case*/
$theformID = RGFormsModel::get_form_id('Student Registration');
//$thefieldID = RGFormsModel::get_field($theformID, 'name_first');

add_action('gform_pre_submission', 'titlecase_fields');
function titlecase_fields($form){
	// add all the field IDs you want to capitalize, to this array
	$form  = GFAPI::get_form( $theformID );
	$fields_to_titlecase = array(
						'input_2_3',
						'input_2_6');
	foreach ($fields_to_titlecase as $each) {
			// for each field, convert the submitted value to uppercase and assign back to the POST variable
			// the rgpost function strips slashes
			$lowercase = strtolower(rgpost($each));
			$_POST[$each] = ucwords($lowercase);
		} 
	// return the form, even though we did not modify it
	return $form;
}//end field titlecaseing

/*Convert Email field to Lowercase*/
add_action('gform_pre_submission', 'lowercase_fields');
function lowercase_fields($form){
	// add all the field IDs you want to capitalize, to this array
	$form  = GFAPI::get_form( $theformID );
	$fields_to_lower = array(
				'input_4');
	foreach ($fields_to_lower as $each) {
			// for each field, convert the submitted value to uppercase and assign back to the POST variable
			// the rgpost function strips slashes
			$_POST[$each]= strtolower(rgpost($each));
	}
	// return the form, even though we did not modify it
	return $form;
}//end field lowercasing

/**
 * Gravity Wiz // Gravity Forms // Rename Uploaded Files
 *
 * Rename uploaded files for Gravity Forms. 
 *
 * @version   2.3
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/rename-uploaded-files-for-gravity-form/
 */
class GW_Rename_Uploaded_Files {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
			'template' => ''
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if( ! is_callable( array( 'GFFormsModel', 'get_physical_file_path' ) ) ) {
			return;
		}

		add_filter( 'gform_entry_post_save', array( $this, 'rename_uploaded_files' ), 9, 2 );
		add_filter( 'gform_entry_post_save', array( $this, 'stash_uploaded_files' ), 99, 2 );

		add_action( 'gform_after_update_entry', array( $this, 'rename_uploaded_files_after_update' ), 9, 2 );
		add_action( 'gform_after_update_entry', array( $this, 'stash_uploaded_files_after_update' ), 99, 2 );

	}

	function rename_uploaded_files( $entry, $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return $entry;
		}

		foreach( $form['fields'] as &$field ) {

			if( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$uploaded_files = rgar( $entry, $field->id );

			if( empty( $uploaded_files ) ) {
				continue;
			}

			$uploaded_files = $this->parse_files( $uploaded_files, $field );
			$stashed_files  = $this->parse_files( gform_get_meta( $entry['id'], 'gprf_stashed_files' ), $field );
			$renamed_files  = array();

			foreach( $uploaded_files as $_file ) {

				// Don't rename the same files twice.
				if( in_array( $_file, $stashed_files ) ) {
					$renamed_files[] = $_file;
					continue;
				}

				$dir  = wp_upload_dir();
				$dir  = $this->get_upload_dir( $form['id'] );
				$file = str_replace( $dir['url'], $dir['path'], $_file );

				if( ! file_exists( $file ) ) {
					continue;
				}

				$renamed_file = $this->rename_file( $file, $entry );

				if ( ! is_dir( dirname( $renamed_file ) ) ) {
					wp_mkdir_p( dirname( $renamed_file ) );
				}

				$result = rename( $file, $renamed_file );

				$renamed_files[] = $this->get_url_by_path( $renamed_file, $form['id'] );

			}

			// In cases where 3rd party add-ons offload the image to a remote location, no images can be renamed.
			if( empty( $renamed_files ) ) {
				continue;
			}

			if( $field->get_input_type() == 'post_image' ) {
				$value = str_replace( $uploaded_files[0], $renamed_files[0], rgar( $entry, $field->id ) );
			} else if( $field->multipleFiles ) {
				$value = json_encode( $renamed_files );
			} else {
				$value = $renamed_files[0];
			}

			GFAPI::update_entry_field( $entry['id'], $field->id, $value );

			$entry[ $field->id ] = $value;

		}

		return $entry;
	}

	function get_upload_dir( $form_id ) {
		$dir = GFFormsModel::get_file_upload_path( $form_id, 'PLACEHOLDER' );
		$dir['path'] = dirname( $dir['path'] );
		$dir['url']  = dirname( $dir['url'] );
		return $dir;
	}

	function rename_uploaded_files_after_update( $form, $entry_id ) {
		$entry = GFAPI::get_entry( $entry_id );
		$this->rename_uploaded_files( $entry, $form );
	}

	/**
	 * Stash the "final" version of the files after other add-ons have had a chance to interact with them.
	 *
	 * @param $entry
	 * @param $form
	 */
	function stash_uploaded_files( $entry, $form ) {

		foreach ( $form['fields'] as &$field ) {

			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$uploaded_files = rgar( $entry, $field->id );
			gform_update_meta( $entry['id'], 'gprf_stashed_files', $uploaded_files );

		}

		return $entry;
	}

	function stash_uploaded_files_after_update( $form, $entry_id ) {
		$entry = GFAPI::get_entry( $entry_id );
		$this->stash_uploaded_files( $entry, $form );
	}

	function rename_file( $file, $entry ) {

		$new_file = $this->get_template_value( $this->_args['template'], $file, $entry );
		$new_file = $this->increment_file( $new_file );

		return $new_file;
	}

	function increment_file( $file ) {

		$file_path = GFFormsModel::get_physical_file_path( $file );
		$pathinfo  = pathinfo( $file_path );
		$counter   = 1;

		// increment the filename if it already exists (i.e. balloons.jpg, balloons1.jpg, balloons2.jpg)
		while ( file_exists( $file_path ) ) {
			$file_path = str_replace( ".{$pathinfo['extension']}", "{$counter}.{$pathinfo['extension']}", GFFormsModel::get_physical_file_path( $file ) );
			$counter++;
		}

		$file = str_replace( basename( $file ), basename( $file_path ), $file );

		return $file;
	}

	function is_path( $filename ) {
		return strpos( $filename, '/' ) !== false;
	}

	function get_template_value( $template, $file, $entry ) {

		$info = pathinfo( $file );

		if( strpos( $template, '/' ) === 0 ) {
			$dir      = wp_upload_dir();
			$template = $dir['basedir'] . $template;
		} else {
			$template = $info['dirname'] . '/' . $template;
		}
		
		// removes the original file name - Added by Allison Logan
		$newname = str_replace($info['filename'], "", $info['filename']);
		
		// replace our custom "{filename}" psuedo-merge-tag
		$value = str_replace( '{filename}', $newname, $template );

		// replace merge tags
		$form  = GFAPI::get_form( $entry['form_id'] );
		$value = GFCommon::replace_variables( $value, $form, $entry, false, true, false, 'text' );

		// make sure filename is "clean"
		$filename = $this->clean( basename( $value ) );
		$value    = str_replace( basename( $value ), $filename, $value );

		// append our file ext
		$value .= '.' . $info['extension'];

		return $value;
	}

	function remove_slashes( $value ) {
		return stripslashes( str_replace( '/', '', $value ) );
	}

	function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return $form_id == $this->_args['form_id'];
	}

	function is_applicable_field( $field ) {

		$is_file_upload_field   = in_array( GFFormsModel::get_input_type( $field ), array( 'fileupload', 'post_image' ) );
		$is_applicable_field_id = $this->_args['field_id'] ? $field['id'] == $this->_args['field_id'] : true;

		return $is_file_upload_field && $is_applicable_field_id;
	}

	function clean( $str ) {
		return $this->remove_slashes( sanitize_title_with_dashes( strtr(
			utf8_decode( $str ),
			utf8_decode( 'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'),
			'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy'
		), 'save' ) );
	}

	function get_url_by_path( $file, $form_id ) {

		$dir = $this->get_upload_dir( $form_id );
		$url = str_replace( $dir['path'], $dir['url'], $file );

		return $url;
	}

	function parse_files( $files, $field ) {

		if( empty( $files ) ) {
			return array();
		}

		if( $field->get_input_type() == 'post_image' ) {
			$file_bits = explode( '|:|', $files );
			$files = array( $file_bits[0] );
		} else if( $field->multipleFiles ) {
			$files = json_decode( $files );
		} else {
			$files = array( $files );
		}

		return $files;
	}

}

# Configuration

new GW_Rename_Uploaded_Files( array(
	'form_id' => $theformID,
	'field_id' => 9,
	'template' => '{Name (Last):2.6}-{Name (First):2.3}-{filename}' 
) ); //end file renaming 

/*Plugin shortcode*/
function reg_table_shortcode() {
	
	// Assets 
	wp_enqueue_style( 'hwcoe-participants-datatables' );
    wp_enqueue_style( 'participants-registered' );
    wp_enqueue_script( 'hwcoe-participants-datatables' );
    wp_enqueue_script( 'participants-registered' );
	
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