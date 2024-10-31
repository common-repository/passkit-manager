<div class="wrap">
<?php 
// Print the PassKit screen icon
screen_icon( PKMGRPLUGIN_ID );
?>
<h2><?php printf('%s &gt;&gt; %s', PKMGRPLUGIN_PREFIX, __('Template settings', PKMGRPLUGIN_ID));?> 
<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page='.PKMGRPLUGIN_DIR.'/inc/passkit-issue-new-pass-admin-page.php&amp;id='.$_REQUEST["id"].'&amp;t='.$_REQUEST["t"]);?>"><?php _e('Add new pass', PKMGRPLUGIN_ID)?></a></h2>
<?php
// Variable for settings URL
$settings_url = admin_url( 'admin.php?page='.PKMGRPLUGIN_DIR.'/inc/passkit-settings-admin-page.php');

// Get API Key and Secret
$options = get_option('pk_options');

// Check if keys are set
if(!isset($options["pk_api_key"]) || empty($options["pk_api_key"]) || !isset($options["pk_api_secret"]) || empty($options["pk_api_secret"]))
{
	?>
	<p><?php printf(__('PassKit API key and/or secret are not set. Please set these first at the <a href="%s">settings</a>', PKMGRPLUGIN_ID), $settings_url);?></p>
	<?php

}
// Check if template is set. If not we throw an error message
elseif(!isset($_REQUEST["t"]) || empty($_REQUEST["t"]))
{
	?>
	<p><?php _e("Invalid template", PKMGRPLUGIN_ID);?></p>
	<?php
}
else
{
	// Include the PassKit PHP Wrapper class
	include_once('class-PassKit.php');
	
	// Create new PassKit instance
	$pk = new PassKit($options["pk_api_key"], $options["pk_api_secret"]);
	if(!$pk->pk_test_connection())
	{
		// User not authenticated, show error message
		?>
		<p><?php printf(__('PassKit API key and/or secret are not set. Please set these first at the <a href="%s">settings</a>', PKMGRPLUGIN_ID), $settings_url);?></p>
		<?php
	}
	else
	{
		// Get the template details
		$template = urldecode($_REQUEST["t"]);
		$pk_result = $pk->get_dynamic_template_fields($template);
		
		if(count($pk_result) > 0)
		{
			global $wpdb;
			$table_name = $wpdb->prefix . PKMGRPLUGIN_ID . "_templates"; // set table name with prefix
			
			$message = '';
			$notice = '';

			// Define the default settings array
			$settings_default = array(
				'issue_button_text' => '',
				'update_button_text' => '',
				'after_issue_action' => '',
				'form_width' => '100',
				'logo_url' => 'https://d321ofrgjjwiwz.cloudfront.net/images/passkit_logo.svgz',
				'logo_width' => '150',
				'logo_height' => '43',
				'background_color' => '#F9F9F9',
				'border_color' => '#0071BB',
				'fields' => array(),
				'field_names' => array()
			);
			
			// this is default $item which will be used for new records
			$db_record = array(
				'id' => '',
				'settings' => ''
			);

			$settings = $settings_default;
			
			// here we are verifying does this request is post back and have correct nonce
			if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
				//combine settings with the request parameters
				$settings = shortcode_atts($settings_default, $_REQUEST);

				// setup the DB record
				if(isset($_REQUEST["id"]))
				{
					$db_record["id"] = $_REQUEST['id'];
				}
				$db_record['name'] = $_REQUEST['t'];
				$db_record["settings"] = serialize($settings);

				// validate settings, and if all ok save db-record to database
				// if id is zero insert otherwise update
				$settings_valid = passkit_template_validate_setting($settings);
				
				if ($settings_valid === true) {
					$result = null;
					
					if ($db_record["id"] == '') {
						// Predefine the ID for the record. It will be the md5 has of the username + template name.
						$db_record['id'] = md5($options["pk_api_key"].$_REQUEST["t"]);
						// Do insert
						$result = $wpdb->insert($table_name, $db_record);
					} 
					else {
						$result = $wpdb->update($table_name, $db_record, array('id' => $db_record['id']));
					}

					if ($result) {
						$message = __('Settings were successfully saved', PKMGRPLUGIN_ID);
					} 
					else {
						$notice = __('There was an error while saving the settings for the template', PKMGRPLUGIN_ID);
					}

				} else {
					// if $item_valid not true it contains error message(s)
					$notice = $settings_valid;
				}
			}
			else {
				// if this is not post back we load db_record to edit or give new one to create
				if (isset($_REQUEST['id'])) {
					$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %s", $_REQUEST["id"]), ARRAY_A);
					if ($result) {
						
						$db_record = $result;
						$settings = unserialize($result["settings"]);
					}
					// If no record found then that is OK, that means it is the first time the user configures this template
				}
			}
			?>
			
			<?php if (!empty($notice)): ?>
			<div id="notice" class="error"><p><?php echo $notice ?></p></div>
			<?php endif;?>
			<?php if (!empty($message)): ?>
			<div id="message" class="updated"><p><?php echo $message ?></p></div>
			<?php endif;?>

			<h3><?php printf(__("Template: %s"), $_REQUEST["t"]);?></h3>
			<form id="template_settings_form" method="post">
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
				<input type="hidden" name="id" value="<?php echo $db_record['id'] ?>"/>
				
				<h4><?php _e("Generic form options", PKMGRPLUGIN_ID);?></h4>
				<p><?php _e("Generic options for the issue/update pass forms", PKMGRPLUGIN_ID);?></p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e("Issue button text", PKMGRPLUGIN_ID);?></th>
						<td>
							<input id="issue_button_text" name="issue_button_text" class="regular-text"  type="text" value="<?php echo esc_attr($settings['issue_button_text'])?>" 
							size="50" placeholder="<?php _e('Enter issue button text', PKMGRPLUGIN_ID)?>" required>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e("Update button text", PKMGRPLUGIN_ID);?></th>
						<td>
							<input id="update_button_text" name="update_button_text" class="regular-text"  type="text" value="<?php echo esc_attr($settings['update_button_text'])?>" 
							size="50" placeholder="<?php _e('Enter update button text', PKMGRPLUGIN_ID)?>" required>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e("After issue actions", PKMGRPLUGIN_ID);?></th>
						<td>
							<select name="after_issue_action" id="after_issue_action">
								<option value='add_to_passbook_button' <?php echo ($settings['after_issue_action'] == 'add_to_passbook_button') ? 'selected="selected"' : '';?>><?php _e("Display add to Passbook button", PKMGRPLUGIN_ID);?></option>
								<option value='return_link' <?php echo ($settings['after_issue_action'] == 'return_link') ? 'selected="selected"' : '';?>><?php _e("Display the pass URL", PKMGRPLUGIN_ID);?></option>
								<option value='forward_to_pass' <?php echo ($settings['after_issue_action'] == 'forward_to_pass') ? 'selected="selected"' : '';?>><?php _e("Forward to pass", PKMGRPLUGIN_ID);?></option>
							</select>
						</td>
					</tr>
				</table>
				<br/>
				
				<h4><?php _e("Form fields", PKMGRPLUGIN_ID);?></h4>
				<p><?php _e("Indicate the form fields that will show up on the issue/update pass forms.", PKMGRPLUGIN_ID);?></p>
				<table class="widefat" style="margin-top: 10px">
					<thead>
						<tr>
							<th><?php _e("Field name", PKMGRPLUGIN_ID);?></th>
							<th><?php _e("Default value", PKMGRPLUGIN_ID);?></th>       
							<th><?php _e("Available on issue/update forms", PKMGRPLUGIN_ID);?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><?php _e("Field name", PKMGRPLUGIN_ID);?></th>
							<th><?php _e("Default value", PKMGRPLUGIN_ID);?></th>       
							<th><?php _e("Available on issue/update forms", PKMGRPLUGIN_ID);?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php
						foreach($pk_result as $field_name => $field_contents)
						{
							// We only want to have the fields that are editable
							if($field_contents["userEditable"])
							{
							?>
								<tr>
									<td><?php echo $field_name; ?></td>
									<td><?php echo (isset($field_contents["default"])) ? $field_contents["default"] : ""; ?></td>
									<td>
										<?php
										$field_id = md5($field_name);
										?>
										<input type="checkbox" id="<?php echo $field_id;?>" name="fields[<?php echo $field_id;?>]" <?php echo (isset($settings["fields"][$field_id])) ? 'checked' : '';?> />
										<input type="hidden" name="field_names[<?php echo $field_id;?>]" value="<?php echo htmlspecialchars($field_name);?>"/>
									</td>
								</tr>
							<?php
							}
						}
						?>
					</tbody>
				</table>
				<br/>
				
				<h4><?php _e("Form style", PKMGRPLUGIN_ID);?></h4>
				<p><?php _e("If defined will use these variables to style the form when it's being display in a post or page.", PKMGRPLUGIN_ID);?></p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e("Form width (in %)", PKMGRPLUGIN_ID);?></th>
						<td>
							<input id="form_width" name="form_width" class="regular-text"  type="number" min="1" max="100" value="<?php echo esc_attr($settings['form_width'])?>" 
							size="50" placeholder="<?php _e('Enter form width', PKMGRPLUGIN_ID)?>" required>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e("Logo URL", PKMGRPLUGIN_ID);?></th>
						<td>
							<input id="logo_url" name="logo_url" class="regular-text"  type="text" value="<?php echo esc_attr($settings['logo_url'])?>" 
							size="50" placeholder="<?php _e('Enter logo URL', PKMGRPLUGIN_ID)?>" required>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e("Logo width (in px)", PKMGRPLUGIN_ID);?></th>
						<td>
							<input id="logo_width" name="logo_width" class="regular-text"  type="number" value="<?php echo esc_attr($settings['logo_width'])?>" 
							size="50" placeholder="<?php _e('Enter logo width', PKMGRPLUGIN_ID)?>" required>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e("Logo height (in px)", PKMGRPLUGIN_ID);?></th>
						<td>
							<input id="logo_height" name="logo_height" class="regular-text"  type="number" value="<?php echo esc_attr($settings['logo_height'])?>" 
							size="50" placeholder="<?php _e('Enter logo height', PKMGRPLUGIN_ID)?>" required>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e("Background color", PKMGRPLUGIN_ID);?></th>
						<td>
							<input id="background_color" name="background_color" class="regular-text"  type="text" value="<?php echo esc_attr($settings['background_color'])?>" 
							size="50" placeholder="<?php _e('Enter background color', PKMGRPLUGIN_ID)?>" required>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e("Border color", PKMGRPLUGIN_ID);?></th>
						<td>
							<input id="border_color" name="border_color" class="regular-text"  type="text" value="<?php echo esc_attr($settings['border_color'])?>" 
							size="50" placeholder="<?php _e('Enter border color', PKMGRPLUGIN_ID)?>" required>
						</td>
					</tr>
				</table>
				<br/>

				<p><strong><?php _e("Use the following short-codes for this template", PKMGRPLUGIN_ID);?>:</strong></p>
				<ul>
					<li><?php _e('[passkit type="issue" template_id="'.$_REQUEST["id"].'"]');?></li>
					<li><?php _e('[passkit type="update" template_id="'.$_REQUEST["id"].'" pass_id="x"]');?></li>
				</ul>

				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>
		
		<?php
		}
		else
		{
			?>
			<p><?php _e("Invalid template", PKMGRPLUGIN_ID);?></p>
			<?php
		}
	}
}
?>
</div>

<?php

// Functions that we need

/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function passkit_template_validate_setting($settings)
{
    $messages = array();

    if (empty($settings['issue_button_text'])) $messages[] = __('Issue button text is required', PKMGRPLUGIN_ID);
	if (empty($settings['update_button_text'])) $messages[] = __('Update button text is required', PKMGRPLUGIN_ID);
    if (empty($settings['after_issue_action'])) $messages[] = __('After issue action is required', PKMGRPLUGIN_ID);
	if (!ctype_digit($settings['form_width'])) $messages[] = __('Form width needs to be numeric', PKMGRPLUGIN_ID);
	if (!ctype_digit($settings['logo_width'])) $messages[] = __('Logo width needs to be numeric', PKMGRPLUGIN_ID);
	if (!ctype_digit($settings['logo_height'])) $messages[] = __('Logo height needs to be numeric', PKMGRPLUGIN_ID);

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}
?>