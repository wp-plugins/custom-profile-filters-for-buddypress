<?php

/*
Plugin Name: Custom CUNY Academic Commons Profile Filters
Plugin URI: http://dev.commons.gc.cuny.edu
Description: Changes the way that profile data fields get filtered into clickable URLs.
Version: 0.2
Author: Boone Gorges
Author URI: http://teleogistic.net
*/



$no_link_fields = array( // Enter the field ID of any field that you want to appear as plain, non-clickable text. Don't forget to separate with commas.

	'Skype ID ' 	,
	'Phone' 	,
	'IM'		
	
	);

$social_networking_fields = array( // Enter the field ID of any field that prompts for the username to a social networking site, followed by the URL that must be appended to username to create a link to the user's profile on that site. Thus, since the URL for the profile of awesometwitteruser is twitter.com/awesometwitteruser, you should enter 'Twitter' => 'twitter.com/'. Don't forget: 1) Leave out the 'http://', 2) Include the trailing slash (/) if needed to make a valid URL, and 3) to separate items with commas

	'Twitter' =>'twitter.com/' ,
	'Delicious ID' => 'delicious.com/' ,
	'YouTube ID ' => 'youtube.com/' ,
	'Flickr ID ' =>'flickr.com/' ,
	'FriendFeed ID' => 'friendfeed.com/'

	);


// You shouldn't need to touch anything below this line.



function xprofile_filter_link_profile_data_cac( $field_value, $field_type = 'textbox' ) {
	global $no_link_fields, $social_networking_fields;
	
	$bp_this_field_name = bp_get_the_profile_field_name();

	if ( 'datebox' == $field_type ) {
		return $field_value;
	}
		
	elseif ( $bp_this_field_name == 'Email Address' ) 
		return $field_value;
		
	elseif ( isset ( $social_networking_fields[$bp_this_field_name] ) ) {
		$sp = strpos ( $field_value, $social_networking_fields[$bp_this_field_name] );
		if ( $sp === false ) {
			$field_value = '<a href="http://' . $social_networking_fields[$bp_this_field_name] . $field_value . '">' . $field_value . '</a>';
			}
		return $field_value;
		}
		
	
	
	
elseif ( strpos( $field_value, '[' ) | strpos( $field_value, ']' ) ) { // If there is a bracket in a field, this block overrides the auto-linking

	while ( strpos( $field_value, ']') ) {
		$open_delin_pos = strpos( $field_value, '[' );
		$close_delin_pos = strpos( $field_value, ']' );
		$field_value =  substr($field_value, 0, $open_delin_pos) . '<a href="' . site_url( BP_MEMBERS_SLUG ) . '/?s=' . substr($field_value, $open_delin_pos+1, $close_delin_pos - $open_delin_pos - 1) . '">' . substr($field_value, $open_delin_pos+1, $close_delin_pos - $open_delin_pos - 1) . '</a>' . substr($field_value, $close_delin_pos+1);
	}
	
	return $field_value ;
	


} else {
	
	if ( bp_get_the_profile_field_name() == 'College' )
		$max_word_count_for_link = 10;
	else 
		$max_word_count_for_link = 5;
	
		
	
	if ( !strpos( $field_value, ',' ) && ( count( explode( ' ', $field_value ) ) > $max_word_count_for_link ) )
		return $field_value;

	
	
	$values = explode( ',', $field_value );

	if ( $values ) {
		foreach ( $values as $value ) {
			$value = trim( $value );
			
			/* If the value is a URL, skip it and just make it clickable. */
			if ( preg_match( '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $value ) ) {
				$new_values[] = make_clickable( $value );
			}
			else
			{
				if ( count( explode( ' ', $value ) ) > $max_word_count_for_link )
					$new_values[] = $value;
				else
		
					if ( !in_array ( bp_get_the_profile_field_name(), $no_link_fields ) ) {
						$new_values[] = '<a href="' . site_url( BP_MEMBERS_SLUG ) . '/?s=' . $value . '">' . $value . '</a>';
					} else {
						$new_values[] = $value;
					}
					
			}
		}
		
		$values = implode( ', ', $new_values );
	}
	
	return $values;
}
}



function xprofile_edit_custom_filters( $group_id, $action ) {
	global $wpdb, $userdata, $bp;

	// Create a new group object based on the group ID.
	$group = new BP_XProfile_Group($group_id);
?>
	<div class="wrap">
		
		<h2><?php echo $group->name ?> <?php _e("Information", 'buddypress') ?></h2>
					<p class="profile-instructions">Words or phrases in your profile can be linked to the profiles of other members that contain the same phrases. To specify which words or phrases should be linked, add square brackets: e.g. "I enjoy [English literature] and [technology]." If you do not specify anything, phrases will be chosen automatically.</p>

		<?php
			// If this group has fields then continue
			if ( $group->fields ) {
				$errors    = null;
				$list_html = '<ul class="forTab" id="' . strtolower($group_name) . '">';
				
				// Loop through each field in the group
				for ( $j = 0; $j < count($group->fields); $j++ ) {
										
					// Create a new field object for this field based on the field ID.
					$field = new BP_XProfile_Field( $group->fields[$j]->id );
					
					// Add the ID for this field to the field_ids array	
					$field_ids[] = $group->fields[$j]->id;
					
					// If the user has submitted the form - validate and save the new value for this field
					if ( isset($_GET['mode']) && 'save' == $_GET['mode'] ) {
						
						/* Check the nonce */
						if ( !check_admin_referer( 'bp_xprofile_edit' ) ) 
							return false;
						
						// If the current field is a datebox, we need to append '_day' to the end of the field name
						// otherwise the field name will not exist
						$post_field_string = ( 'datebox' == $group->fields[$j]->type ) ? '_day' : null;
						
						// Explode the posted field IDs into an array so we know which fields have been submitted
						$posted_fields = explode( ',', $_POST['field_ids'] );
						
						// Fetch the current field from the _POST array based on field ID. 
						$current_field = $_POST['field_' . $posted_fields[$j] . $post_field_string];
						
						// If the field is required and has been left blank then we need to add a callback error.
						if ( ( $field->is_required && !isset($current_field) ) ||
						     ( $field->is_required && empty( $current_field ) ) ) {
							
							// Add the error message to the errors array
							$field->message = sprintf( __('%s cannot be left blank.', 'buddypress'), $field->name );
							$errors[] = $field->message . "<br />";
						
						// If the field is not required and the field has been left blank, delete any values for the
						// field from the database.
						} else if ( !$field->is_required && ( empty( $current_field ) || is_null($current_field) ) ) {
							
							// Create a new profile data object for the logged in user based on field ID.								
							$profile_data = new BP_Xprofile_ProfileData( $group->fields[$j]->id, $bp->loggedin_user->id );
							
							if ( $profile_data ) {					
								// Delete any data
								$profile_data->delete();
								
								// Also remove any selected profile field data from the $field object.
								$field->data->value = null;
							}
							
						// If we get to this point then the field validates ok and we have new data.
						} else {
							
							// Create an empty profile data object and populate it with new data
							$profile_data = new BP_Xprofile_ProfileData;
							$profile_data->field_id = $group->fields[$j]->id;
							$profile_data->user_id = $userdata->ID;
							$profile_data->last_updated = time();
							
							// If the $post_field_string we set up earlier is not null, then this is a datebox
							// we need to concatenate each of the three select boxes for day, month and year into
							// one value.
							if ( $post_field_string != null ) {
								
								// Concatenate the values.
								$date_value = $_POST['field_' . $group->fields[$j]->id . '_day'] . 
										      $_POST['field_' . $group->fields[$j]->id . '_month'] . 
											  $_POST['field_' . $group->fields[$j]->id . '_year'];
								
								// Turn the concatenated value into a timestamp
								$profile_data->value = strtotime($date_value);
								
							} else {
								
								// Checkbox and multi select box fields will submit an array as their value
								// so we need to serialize them before saving to the DB.
								if ( is_array($current_field) )
									$current_field = serialize($current_field);
									
								$profile_data->value = $current_field;
							}
							
							// Finally save the value to the database.
							if( !$profile_data->save() ) {
								$field->message = __('There was a problem saving changes to this field, please try again.', 'buddypress');
							} else {
								$field->data->value = $profile_data->value;
							}
						}
					}
					
					// Each field object comes with HTML that can be rendered to edit that field.
					// We just need to render that to the page by adding it to the $list_html variable
					// that will be rendered when the field loop has finished.
					$list_html .= '<li>' . $field->get_edit_html() . '</li>';
				}
				
				// Now that the loop has finished put the final touches on the HTML including the submit button.
				$list_html .= '</ul>';
				
				$list_html .= '<p class="submit">
								<input type="submit" name="save" id="save" value="'.__('Save Changes &raquo;', 'buddypress').'" />
							   </p>';
							
				$list_html .= wp_nonce_field( 'bp_xprofile_edit' );

				// If the user submitted the form to save new values, and there were errors, make sure we display them.
				if ( $errors && isset($_POST['save']) ) {
					$type = 'error';
					$message = __('There were problems saving your information. Please fix the following:<br />', 'buddypress');
					
					for ( $i = 0; $i < count($errors); $i++ ) {
						$message .= $errors[$i];
					}
					
				// If there were no errors then we can display a nice "Changes saved." message.
				} else if ( !$errors && isset($_POST['save'] ) ) {
					$type = 'success';
					$message = __('Changes saved.', 'buddypress');
					
					// Record in activity stream
					xprofile_record_activity( array( 'item_id' => $group->id, 'component_name' => 'profile', 'component_action' => 'updated_profile', 'is_private' => 0 ) );
					
					do_action( 'xprofile_updated_profile', $group->id ); 
				}
			}
			// If this is an invalid group, then display an error.
			else { ?>
				<div id="message" class="error fade">
					<p><?php _e('That group does not exist.', 'buddypress'); ?></p>
				</div>
			<?php
			}

		?>
		
		<?php // Finally, we can now render everything to the screen. ?>
		
		<?php
			if ( $message != '' ) {
				$type = ( 'error' == $type ) ? 'error' : 'updated';
		?>
			<div id="message" class="<?php echo $type; ?> fade">
				<p><?php echo $message; ?></p>
			</div>
		<?php } ?>

		<p><form action="<?php echo $action ?>" method="post" id="profile-edit-form" class="generic-form">
		<?php 
			if ( $field_ids )
				$field_ids = implode( ",", $field_ids );
		?>
		<input type="hidden" name="field_ids" id="field_ids" value="<?php echo $field_ids; ?>" />
		
		<?php echo $list_html; ?>

		</form>
		</p>
		
	</div> 
<?php
}





function bp_profile_custom_filters_plugin_menu() {
  add_options_page('BuddyPress Profile Custom Filters Options', 'BP Profile Custom Filters', 8, __FILE__, 'bpcf_plugin_options');
}

function bpcf_plugin_options() {
  include('bpcf_options.php');
  
 
}


function stuff_i_want_triggered_after_bp_loads(){

remove_action( 'bp_edit_profile_form', 'xprofile_edit');
add_action( 'bp_edit_profile_form', 'xprofile_edit_custom_filters');

remove_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data', 2, 2);
add_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data_cac', 3, 2);
}
add_action('wp', 'stuff_i_want_triggered_after_bp_loads'); 

/*add_action('admin_menu', 'bp_profile_custom_filters_plugin_menu');*/


?>