<div class="wrap">
<?php 
// Print the PassKit screen icon
screen_icon( PKMGRPLUGIN_ID );
?>
<h2><?php printf('%s &gt;&gt; %s', PKMGRPLUGIN_PREFIX, __('Pass records', PKMGRPLUGIN_ID));?> 
<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page='.PKMGRPLUGIN_DIR.'/inc/passkit-issue-new-pass-admin-page.php&amp;id='.$_REQUEST["template_id"].'&amp;t='.$_REQUEST["t"]);?>"><?php _e('Add new pass', PKMGRPLUGIN_ID)?></a>
<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page='.$_REQUEST["page"].'&amp;action=reset_all_passes&amp;template_id='.$_REQUEST["template_id"].'&amp;t='.$_REQUEST["t"]);?>"><?php _e('Reset all passes', PKMGRPLUGIN_ID)?></a></h2>
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
		// Display the table
		$Passes_List_Table = new Passes_List_Table($template);
		// Pass the PassKit object to the prepare_items method
		$Passes_List_Table->prepare_items($pk); 

		?>
		<form id="passes-table" method="POST">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
			<?php $Passes_List_Table->display() ?>
		</form>
		<?php
	}
}
?>
</div>
<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a class for the template list table
 */
class Passes_List_Table extends WP_List_Table
{
	private $template_settings;
	private $pk;
	
	/**
     * Basic constructor
     */
    function __construct()
    {
        global $status, $page, $wpdb;

        parent::__construct(array(
            'singular' => 'pass',
            'plural' => 'passes',
        ));

		$table_name = $wpdb->prefix . PKMGRPLUGIN_ID . "_templates"; // set table name with prefix
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %s", $_REQUEST["template_id"]), ARRAY_A);

		if ($result) {
			$this->template_settings = unserialize($result["settings"]);
		}
    }

	/**
     * Default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return item value
     */
    function column_default($item, $column_name)
    {
		return $item[ $column_name ];
    }

	/**
	 * The table columns
	 *
	 * @return column array
	 */
	function get_columns(){
		$columns = array(
			//'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'    => __('Unique ID', PKMGRPLUGIN_ID),
			'pass_status' => __('Pass status', PKMGRPLUGIN_ID),
			'install_ip_country'    => __('Country installed in', PKMGRPLUGIN_ID),
			'recovery_url'    => __('Recovery URL', PKMGRPLUGIN_ID),
			'issue_date'    => __('Issue date', PKMGRPLUGIN_ID),
			'last_update_date'    => __('Last update', PKMGRPLUGIN_ID),
		);
		
		// Get dynamic fields from the settings and add them to the columns array
		if(isset($this->template_settings))
		{
			foreach($this->template_settings["fields"] as $field_id=>$value)
			{
				$columns[$field_id] = $this->template_settings["field_names"][$field_id];
			}
		}
		
		return $columns;
	}
	
	/**
     * Render the checkbox column
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    /*function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="pass_id[]" value="%s" />',
            $item['id']
        );
    }*/
	
	function process_actions()
	{
		switch($this->current_action())
		{
			case "reset_all_passes":
				// Get the template name for the URL and decode
				$template = urldecode($_REQUEST["t"]);
				$pk_result = $this->pk->reset_passes_for_template($template);
				
				// Print error message or notice on success
				if ($pk_result->success)
				{
					?>
					<div id="message" class="updated"><p><?php echo __("Successfully reset all passes in template."); ?></p></div>
					<?php
				}
				else
				{
					?>
					<div id="notice" class="error"><p><?php echo $pk_result->error ?></p></div>
					<?php
				}
			break;
		}
	}
	
	/**
     * Render the template name column, with actions array
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_id($item)
	{
        // Define all the actions
        $actions = array(		
            sprintf('<a href="'.admin_url( 'admin.php?page='.PKMGRPLUGIN_DIR.'/inc/passkit-update-pass-admin-page.php&amp;template_id='.$_REQUEST["template_id"].'&amp;pass_id='.$item["id"].'&amp;t='.urlencode($_REQUEST["t"])).'">%s</a>', __('Update pass', PKMGRPLUGIN_ID)),
        );
		
		// Return the text, and have row actions always being displayed
        return sprintf('%s %s',
			$item['id'],
            $this->row_actions($actions, true)
        );
    }

	/**
	 * Prepares the table for display. Takes PassKit object as input to communicate with the PassKit API.
	 *
	 * @param pk - PassKit object
	 * @param template - Template name
	 * @return void
	 */
	function prepare_items($pk) {
		// Set ok object for share with other functions
		$this->pk = $pk;
		
		// Process custom actions
		$this->process_actions();
		
		// Set the column headers
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$template = urldecode($_REQUEST["t"]);
		// Communicate with API to get the passes
		$passes = $pk->get_passes_for_template($template);
		
		// Add all the rows to items array
		$rows = array();

		if(isset($passes["passRecords"]) && count($passes["passRecords"]))
		{
			foreach($passes["passRecords"] as $pass_record)
			{
				$row = array(
					"id" => $pass_record["passMeta"]["uniqueID"], 
					"pass_status" => $pass_record["passMeta"]["passStatus"],
					"install_ip_country" => $pass_record["passMeta"]["installIPCountry"],
					"recovery_url" => $pass_record["passMeta"]["recoveryURL"],
					"issue_date" => $pass_record["passMeta"]["issueDate"],
					"last_update_date" => $pass_record["passMeta"]["lastDataChange"],
				);
				
				// Add the dynamic fields with pass data
				if(isset($this->template_settings))
				{
					foreach($this->template_settings["fields"] as $field_id=>$value)
					{
						$row[$field_id] = $pass_record["passData"][$this->template_settings["field_names"][$field_id]];
					}
				}

				array_push($rows, $row);
			}
		}
		
		$this->items = $rows;
	}
}