<?php
/*
Plugin Name: PassKit Manager
Plugin URI: http://www.passkit.com/wp
Description: "PassKit Manager" plugin by PassKit Inc, can be used to manage PassKit templates & passes for Apple Passbook.
Version: 1.0
Author: PassKit Inc
Author URI: http://www.passkit.com
License: GPL2

Copyright 2013 PassKit Inc (email: info@passkit.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
// Define constant for the plugin path
define( 'PASSKIT_MANAGER_PATH', plugin_dir_path(__FILE__) );


// Generate the admin menu
include_once(PASSKIT_MANAGER_PATH . "/inc/admin-menu.php");
*/

// Make sure we unique
if(!class_exists('pkManagerPluginOptions'))
{
	// Define plugin ID
	define('PKMGRPLUGIN_ID', 'passkit_manager');
	// Define plugin prefix
	define('PKMGRPLUGIN_PREFIX', 'PassKit Manager Plugin');
	// Plugin dir name
	define('PKMGRPLUGIN_DIR', basename(dirname(__FILE__)));
	
	// Include the stylesheet of the plugin
	wp_enqueue_style( 'passkit_style', plugins_url( 'passkit_manager/css/style.css' ));
	
    class pkManagerPluginOptions
    {
		/**
		* Return absolute file path
		*
		* @param string $file The file to return the absolute path for $file
		* @return string
		*/
		public static function plugin_dir($file)
		{
			return basename(plugin_basename(__FILE__));
		}
		
		/**
		 * Runs on activation of the plugin.
		 *
		 * Creates the databases table that the plugin uses. Plugin uses custom tables instead of the Wordpress option tables,
		 * because of the (potential) high volume of meta & statistics data.
		 *
		 * @return void
		 */
		public static function install()
		{
			global $wpdb;

			$table_name = $wpdb->prefix . PKMGRPLUGIN_ID . "_templates";
			
			$sql = "CREATE TABLE $table_name (
				id char(32) NOT NULL COMMENT 'unique ID is md5 hash, for easier lookup of template name that contain special characters',
				name varchar(255) NOT NULL,
				settings text NOT NULL,
				UNIQUE KEY id (id)
			) DEFAULT CHARSET=utf8;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
		
		/**
		 * Runs on de-activation of the plugin.
		 *
		 * Drops the database tables that were created by the plugin.
		 *
		 * @return void
		 */
		public static function uninstall()
		{
			global $wpdb;

			$table_name = $wpdb->prefix . PKMGRPLUGIN_ID . "_templates";

			$wpdb->query("DROP TABLE IF EXISTS $table_name");
		}
		
		/** 
		* Register the plugin options
		*
		* Return: void
		*/
		public static function register_options()
		{		
			register_setting(PKMGRPLUGIN_ID.'_options', 'pk_options');
		}
		
		/**
		* Hook the plugin menu
		*
		* @return void
		*/
		public static function menu()
		{
			// Check if the user is allowed to do this
			if (!current_user_can('manage_options'))
			{
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
		
			// Add main item and sub items
			add_menu_page( 'PassKit', 'PassKit', 'manage_options', PKMGRPLUGIN_DIR.'/inc/passkit-templates-admin-page.php', '', plugins_url( PKMGRPLUGIN_DIR.'/img/favicon16x16.ico' ), 26);
			add_submenu_page( PKMGRPLUGIN_DIR.'/inc/passkit-templates-admin-page.php', PKMGRPLUGIN_PREFIX . ' - ' . __('Templates', PKMGRPLUGIN_ID), __('Templates', PKMGRPLUGIN_ID), 'manage_options', PKMGRPLUGIN_DIR.'/inc/passkit-templates-admin-page.php', '');
			add_submenu_page( PKMGRPLUGIN_DIR.'/inc/passkit-templates-admin-page.php', PKMGRPLUGIN_PREFIX . ' - ' . __('Settings', PKMGRPLUGIN_ID), __('Settings', PKMGRPLUGIN_ID), 'manage_options',PKMGRPLUGIN_DIR.'/inc/passkit-settings-admin-page.php', '');
			

			// Add the submenu with parent slug null, for the sub-pages that can only be navigated from another admin page
			add_submenu_page( '', PKMGRPLUGIN_PREFIX . ' - ' . __('Template settings', PKMGRPLUGIN_ID), __('Template settings', PKMGRPLUGIN_ID), 'manage_options', PKMGRPLUGIN_DIR.'/inc/passkit-template-settings-admin-page.php', '');
			add_submenu_page( '', PKMGRPLUGIN_PREFIX . ' - ' . __('Issue new pass', PKMGRPLUGIN_ID), __('Issue new pass', PKMGRPLUGIN_ID), 'manage_options', PKMGRPLUGIN_DIR.'/inc/passkit-issue-new-pass-admin-page.php', '');
			add_submenu_page( '', PKMGRPLUGIN_PREFIX . ' - ' . __('Pass records', PKMGRPLUGIN_ID), __('Pass records', PKMGRPLUGIN_ID), 'manage_options', PKMGRPLUGIN_DIR.'/inc/passkit-pass-records-admin-page.php', '');
			add_submenu_page( '', PKMGRPLUGIN_PREFIX . ' - ' . __('Update pass', PKMGRPLUGIN_ID), __('Update pass', PKMGRPLUGIN_ID), 'manage_options', PKMGRPLUGIN_DIR.'/inc/passkit-update-pass-admin-page.php', '');
		}
		
		public static function loadHelp()
		{
			ECHO "HIER";
			die;
			// Add the help text for the page
			$text = '<p>' . __( 'This is an example of contextual help in WordPress, you could edit this to put information about your plugin or theme so that users will understand what the heck is going on.', 'example-textdomain' ) . '</p>';

			/* Add documentation and support forum links. */
			$text .= '<p><strong>' . __( 'For more information:', 'example-textdomain' ) . '</strong></p>';

			$text .= '<ul>';
			$text .= '<li><a href="http://yoursite.com/theme-documentation">' . __( 'Documentation', 'example-textdomain' ) . '</a></li>';
			$text .= '<li><a href="http://yoursite.com/support">' . __( 'Support Forums', 'example-textdomain' ) . '</a></li>';
			$text .= '</ul>';

			get_current_screen()->add_help_tab( array(
				 'id'       => "cuntsss",
				'title'    => 'some_textdomain',
				// Use the content only if you want to add something
				// static on every help tab. Example: Another title inside the tab
				'content'  => $text
			));
		}

		/**
		* The function to print & process the issue/update pass forms
		*/
		public static function print_pass_form($atts)
		{
			// 2 arguments are allowed: type & template_id
			extract(shortcode_atts( array(
				'type' => '',
				'template_id' => '',
				'pass_id' => '',
				'use_admin_style' => false
			), $atts ));

			// type has to be issue or update
			if($type != "issue" && $type != "update") {
				return __("Invalid type argument for passkit shortcode");
			}
			elseif(empty($template_id))
			{
				return __("Invalid template_id argument for passkit shortcode");
			}
			else {
				// check if the template is valid
				global $wpdb;
				$table_name = $wpdb->prefix . PKMGRPLUGIN_ID . "_templates"; // define table name with prefix
				
				// there should be a settings record for this template. If not we give the user the message to configure the template first
				$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %s", $template_id), ARRAY_A);
				
				if(!$result)
				{
					// no settings found
					return __("Template not found. Please configure the PassKit template first at the template settings in the WordPress admin panel.");
				}
				else
				{
					// define output string
					$output_string = "";
					
					// get api key and secret
					$options = get_option('pk_options');
					
					// if the api key & secret are not set then throw an error, else continue with the pass form
					if(!isset($options["pk_api_key"]) || empty($options["pk_api_key"]) || !isset($options["pk_api_secret"]) || empty($options["pk_api_secret"]))
					{
						return __('PassKit API key and/or secret are not set.', PKMGRPLUGIN_ID);
					}
					else
					{
						// Include the PassKit PHP Wrapper class
						include_once('inc/class-PassKit.php');
						
						// Create new PassKit instance
						$pk = new PassKit($options["pk_api_key"], $options["pk_api_secret"]);
						
						// Test the connection
						if(!$pk->pk_test_connection())
						{
							// User not authenticated, show error message
							return __('PassKit API key and/or secret are not set. Please set these first at the <a href="%s">settings</a>', PKMGRPLUGIN_ID);
						}
						else
						{
							// get the settings array and unserialize
							$settings = unserialize($result["settings"]);
						
							// check if it is a valid, then we process the postback
							if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__)))
							{
								// populate pass data array, only with fields allowed from settings["field_names"] array
								$allowed_data = array_intersect_key($_REQUEST, $settings["field_names"]);
								
								// Replace the md5(fieldname) id's with their normal text representation
								$pass_data = array();
								foreach($allowed_data as $field_id=>$value)
								{
									$pass_data[$settings["field_names"][$field_id]] = $value;
								}

								// depending on type we do issue or update
								if($type == "issue")
								{
									// Issue the pass
									$pk_result = $pk->pk_issue_pass($result["name"], $pass_data);
									
									// if in admin mode then always do return_link
									if($use_admin_style)
									{
										$settings["after_issue_action"] = "return_link";
									}
									
									if($pk_result->success)
									{
										// depending on template setting print something different
										switch($settings["after_issue_action"])
										{
											case "add_to_passbook_button":
												$output_string .= '<div class="custom_message" style="width: '.$settings["form_width"].'%;">';
												$output_string .= '<p>'.__("We have created your pass for you!")."</p>";
												$output_string .= '<a href="'.$pk_result->url.'" target="_blank">';
												$output_string .= '<img src="https://d321ofrgjjwiwz.cloudfront.net/images/Add_to_Passbook_US_UK@2x.png" width="120" height="40" alt="Add to Passbook"/>';
												$output_string .= '</a>';
												$output_string .= '</div>';
											break;
											case "return_link":
												$output_string .= '<div class="'.(($use_admin_style) ? 'updated' : 'custom_message').'">';
												$output_string .= '<p>'.__("We have created your pass for you!")."</p>";
												$output_string .= '<a href="'.$pk_result->url.'" target="_blank">'.$pk_result->url.'</a>';
												$output_string .= '</div>';
											break;
											case "forward_to_pass":
												header('location: '.$pk_result->url);
												exit;
											break;
										}
									}
									else
									{
										$output_string .= '<div id="notice" class="error"><p>'.$pk_result->error.'</p></div>';
									}
								}
								else
								{
									// Update the pass
									$pk->set_pass_id($pass_id);
									// Validate pass
									$pk->pass_validate();
									// Update pass
									$pk_result = $pk->pass_update($pass_data);
									
									if($pk_result)
									{
										$output_string .= '<div class="'.(($use_admin_style) ? 'updated' : 'custom_message').'">';
										$output_string .= '<p>'.__("The pass was updated.")."</p>";
										$output_string .= '</div>';
									}
									else
									{
										$output_string .= '<div id="notice" class="error"><p>'.__("Error while updating the pass.").'</p></div>';
									}
								}								
							}
							// if update then we need to request the pass fields
							elseif($type == "update" && !empty($pass_id))
							{
								// Get the pass details via the unique pass id
								$pk->set_pass_id($pass_id);
								// Need to call pass_validate before get_pass_details
								$pk->pass_validate();
								$pass_details = $pk->get_pass_details();
							}

							// Define the form style -or class (depending if the result is displayed in admin style)
							$form_style = null;
							$form_class = null;
							if($use_admin_style)
							{
								$form_class = 'class="form-table"';
							}
							else
							{
								$form_style = 'style="width: '.$settings["form_width"].'%; padding: 10px; background: '.$settings["background_color"].'; border: 2px solid '.$settings["border_color"].'; border-radius: 10px;"';
							}

							// print the form
							$output_string .= '<form name="'.$template_id.'" name="'.$template_id.'" '.(($use_admin_style) ? $form_class : $form_style).' method="POST">';
							// Only include the logo if we not using admin style
							if(!$use_admin_style) 
							{
								$output_string .= '<img src="'.$settings["logo_url"].'" width="'.$settings["logo_width"].'" height="'.$settings["logo_height"].'" alt="Logo"/>';
							}
							$output_string .= '<input type="hidden" name="nonce" value="'.wp_create_nonce(basename(__FILE__)).'"/>';
							$output_string .= '<h3>'.(($type == "issue") ? sprintf(__("Issue new pass for template: %s"), $result["name"]) : sprintf(__("Update pass for template: %s"), $result["name"])).'</h3>';
							$output_string .= '<table>';
							foreach($settings["fields"] as $field_id => $field)
							{
								$output_string .= '<tr valign="top">';
								$output_string .= '<td scope="row">'.$settings["field_names"][$field_id].'</td>';
								$output_string .= '<td><input type="text" class="regular-text" name="'.$field_id.'"'.(isset($pass_details["pass_data"][$settings["field_names"][$field_id]]) ? 'value="'.$pass_details["pass_data"][$settings["field_names"][$field_id]].'"' : '').' required/></td>';
								$output_string .= '</tr>';
							}
							$output_string .= '</table>';
							$output_string .= '<p class="submit">';
							// depending on admin style we will use the text as defined in the settings
							$output_string .= '<input type="submit" class="button-primary" value="'.(($type == "issue") ? $settings["issue_button_text"] : $settings["update_button_text"]).'" />';
							$output_string .= '</p>';
							$output_string .= '</form>';
							
							return $output_string;
						}
					}
				}
			}
		}
		
		/**
		 * Function is used to add the help for the plugin
		 */
		function passkit_manager_plugin_help($contextual_help, $screen_id, $screen) {
			// Switch screen ID to be sure we only add the help on the correct page
			switch($screen_id)
			{
				case "passkit_manager/inc/passkit-settings-admin-page":
					$screen->add_help_tab( array(
						'id'      => PKMGRPLUGIN_ID.'settings-overview-tab',
						'title'   => __( 'Overview', PKMGRPLUGIN_ID ),
						'content' => '<p>' . __( 'This screen provides access to your PassKit API settings.', PKMGRPLUGIN_ID ) . '</p>',
					));
				break;
				case "passkit_manager/inc/passkit-templates-admin-page":
					$screen->add_help_tab( array(
						'id'      => PKMGRPLUGIN_ID.'templates-overview-tab',
						'title'   => __( 'Overview', PKMGRPLUGIN_ID ),
						'content' => '<p>' . __( 'This screen provides access to your PassKit templates.', PKMGRPLUGIN_ID ) . '</p>',
					));
					
					$list_content = "<ul>";
					$list_content .= "<li>".__('<strong>Issue new pass</strong>: You can issue new passes for each template', PKMGRPLUGIN_ID).";</li>";
					$list_content .= "<li>".__('<strong>Pass records</strong>: You can browse all the pass records for each template', PKMGRPLUGIN_ID).";</li>";
					$list_content .= "<li>".__('<strong>Reset all passes</strong>: You can reset all the pass records for each template', PKMGRPLUGIN_ID).";</li>";
					$list_content .= "<li>".__('<strong>Template settings</strong>: You can manage the WordPress settings for each template', PKMGRPLUGIN_ID).";</li>";
					$list_content .= "</ul>";
					
					$screen->add_help_tab( array(
						'id'      => PKMGRPLUGIN_ID.'templates-actions-tab',
						'title'   => __( 'Template actions', PKMGRPLUGIN_ID ),
						'content' => $list_content,
					));
				break;
				case "passkit_manager/inc/passkit-issue-new-pass-admin-page":
					$screen->add_help_tab( array(
						'id'      => PKMGRPLUGIN_ID.'issue-pass-overview-tab',
						'title'   => __( 'Overview', PKMGRPLUGIN_ID ),
						'content' => '<p>' . __( 'This screen allows you to issue new passes for the selected PassKit template. It will display only the fields selected in the "Template settings".', PKMGRPLUGIN_ID ) . '</p>',
					)); 
				break;
				case "passkit_manager/inc/passkit-pass-records-admin-page":
					$screen->add_help_tab( array(
						'id'      => PKMGRPLUGIN_ID.'issue-pass-overview-tab',
						'title'   => __( 'Overview', PKMGRPLUGIN_ID ),
						'content' => '<p>' . __( 'This screen provides access to all pass records for the selected PassKit template.', PKMGRPLUGIN_ID ) . '</p>',
					)); 
					
					$list_content = "<ul>";
					$list_content .= "<li>".__('<strong>Update pass record</strong>: You can update pass records for existing pass.', PKMGRPLUGIN_ID).";</li>";
					$list_content .= "</ul>";
					
					$screen->add_help_tab( array(
						'id'      => PKMGRPLUGIN_ID.'pass-actions-tab',
						'title'   => __( 'Pass actions', PKMGRPLUGIN_ID ),
						'content' => $list_content,
					));
				break;
				case "passkit_manager/inc/passkit-update-pass-admin-page":
					$screen->add_help_tab( array(
						'id'      => PKMGRPLUGIN_ID.'update-pass-overview-tab',
						'title'   => __( 'Overview', PKMGRPLUGIN_ID ),
						'content' => '<p>' . __( 'This screen allows you to update the pass record of the selected pass.', PKMGRPLUGIN_ID ) . '</p>',
					)); 
				break;
				case "passkit_manager/inc/passkit-template-settings-admin-page":
					$screen->add_help_tab( array(
						'id'      => PKMGRPLUGIN_ID.'template-settings-tab',
						'title'   => __( 'Overview', PKMGRPLUGIN_ID ),
						'content' => '<p>' . __( 'This screen allows you to edit the WordPress settings for this template.', PKMGRPLUGIN_ID ) . '</p>',
					)); 
					
					$list_content = "<ul>";
					$list_content .= "<li>".__('<strong>Generic form options</strong>: Update these fields to change the behavior of the issue/update pass forms', PKMGRPLUGIN_ID).";</li>";
					$list_content .= "<li>".__('<strong>Form fields</strong>: Select what fields to display on the issue/update pass forms', PKMGRPLUGIN_ID).";</li>";
					$list_content .= "<li>".__('<strong>Form style</strong>: Update these fields to change the style of the issue/update pass form', PKMGRPLUGIN_ID).";</li>";
					$list_content .= "<li>".__('<strong>Short codes</strong>: The short-codes for the issue/update forms are listed at the bottom of the page', PKMGRPLUGIN_ID).";</li>";
					$list_content .= "</ul>";
					
					$screen->add_help_tab( array(
						'id'      => PKMGRPLUGIN_ID.'pass-actions-tab',
						'title'   => __( 'Pass actions', PKMGRPLUGIN_ID ),
						'content' => $list_content,
					));
				break;
			}
			
			return $contextual_help;
		}
    }
	
    if ( is_admin() )
	{
		// On activation, install DB
		register_activation_hook( __FILE__, array('pkManagerPluginOptions', 'install'));
		// On deactivation, uninstall DB
		register_deactivation_hook( __FILE__, array('pkManagerPluginOptions', 'uninstall'));
	
		// Init menu & set menu options
		add_action('admin_init', array('pkManagerPluginOptions', 'register_options'));
		add_action('admin_menu', array('pkManagerPluginOptions', 'menu'));
		
		// Add help for the plugin
		add_filter('contextual_help', array('pkManagerPluginOptions', 'passkit_manager_plugin_help'), 10, 3);
	}

	// Add the short code for the pass forms
	add_shortcode( 'passkit', array('pkManagerPluginOptions', 'print_pass_form'));
}
?>