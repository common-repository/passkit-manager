<div class="wrap">
<?php 
// Print the PassKit screen icon
screen_icon( PKMGRPLUGIN_ID );
?>
<h2><?php printf('%s &gt;&gt; %s', PKMGRPLUGIN_PREFIX, __('Templates', PKMGRPLUGIN_ID));?></h2>

<?php
// Variable for settings URL
$settings_url = admin_url( 'admin.php?page='.PKMGRPLUGIN_DIR.'/inc/passkit-settings-admin-page.php');

// Check if API key and secret are set
$options = get_option('pk_options');

// If any of these are not set or empty, then refer user to settings page
if(!isset($options["pk_api_key"]) || empty($options["pk_api_key"]) || !isset($options["pk_api_secret"]) || empty($options["pk_api_secret"]))
{
	?>
	<p><?php printf(__('PassKit API key and/or secret are not set. Please set these first at the <a href="%s">settings</a>', PKMGRPLUGIN_ID), $settings_url);?></p>
	<?php
}
else
{
	// Include the PassKit PHP Wrapper class
	include_once('class-PassKit.php');
	
	// Create new PassKit instance
	$pk = new PassKit($options["pk_api_key"], $options["pk_api_secret"]);
	
	// Test the connection
	if(!$pk->pk_test_connection())
	{
		// User not authenticated, show error message
		?>
		<p><?php printf(__('PassKit API key and/or secret are not set. Please set these first at the <a href="%s">settings</a>', PKMGRPLUGIN_ID), $settings_url);?></p>
		<?php
	}
	else
	{
		// Display the table
		$Templates_List_Table = new Templates_List_Table();
		// Pass the PassKit object to the prepare_items method
		$Templates_List_Table->prepare_items($pk); 
		$Templates_List_Table->display(); 
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
class Templates_List_Table extends WP_List_Table
{
	private $pk;

	/**
     * Basic constructor
     */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'template',
            'plural' => 'templates',
        ));
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
			'template_name' => __('Template', PKMGRPLUGIN_ID),
			'template_description' => __('Description', PKMGRPLUGIN_ID),
			'template_issued'    => __('# of passes issued', PKMGRPLUGIN_ID),
			'template_in_circulation'    => __('# of passes in circulation', PKMGRPLUGIN_ID),
		);
		return $columns;
	}
	
	/**
     * Render the checkbox column
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
   /* function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }*/
	
	/**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
	 /*
    function get_bulk_actions()
    {
        $actions = array(
            'reset_all' => 'Reset all'
        );
        return $actions;
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
    function column_template_name($item)
    {
		global $wp;

		// Get options so we can make the hash
		$options = get_option('pk_options');
	
		// Define template id, we will use this in all the links
		$template_id = md5($options["pk_api_key"].$item['template_name']);
	
        // Define all the actions
        $actions = array(		
            'issue_new_pass' => sprintf('<a href="'.admin_url( 'admin.php?page='.PKMGRPLUGIN_DIR.'/inc/passkit-issue-new-pass-admin-page.php&amp;id='.$template_id.'&amp;t='.urlencode($item["template_name"])).'">%s</a>', __('Issue new pass', PKMGRPLUGIN_ID)),
            'pass_records' => sprintf('<a href="'.admin_url( 'admin.php?page='.PKMGRPLUGIN_DIR.'/inc/passkit-pass-records-admin-page.php&amp;template_id='.$template_id.'&amp;t='.urlencode($item["template_name"])).'">%s</a>', __('Pass records', PKMGRPLUGIN_ID)),
			'reset_all_passes' => sprintf('<a href="'.admin_url( 'admin.php?page=%s&amp;action=%s&amp;t=%s ').'">%s</a>', $_REQUEST["page"], "reset_all_passes", urlencode($item["template_name"]), __('Reset all passes', PKMGRPLUGIN_ID)),
			'template_settings' => sprintf('<a href="'.admin_url( 'admin.php?page='.PKMGRPLUGIN_DIR.'/inc/passkit-template-settings-admin-page.php&amp;id='.$template_id.'&amp;t='.urlencode($item["template_name"])).'">%s</a>', __('Template settings', PKMGRPLUGIN_ID)),
        );

		// Return the text, and have row actions only being displayed on hover
        return sprintf('%s %s',
            $item['template_name'],
            $this->row_actions($actions, true)
        );
    }

	/**
	 * Prepares the table for display. Takes PassKit object as input to communicate with the PassKit API.
	 *
	 * @param pk - PassKit object
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
		
		// Communicate with API to get the templates
		$templates = $pk->get_templates();

		// Add all the rows to items array
		$rows = array();
		foreach($templates as $template)
		{
			$row = array(
				"id" => md5($template["templateName"]), 
				"template_name" => $template["templateName"], 
				"template_description" => $template["templateFields"]["passDescription"]["value"], 
				"template_issued" => $template["issued"],
				"template_in_circulation" => $template["inCirculation"],
			);
			array_push($rows, $row);
		}
		
		$this->items = $rows;
	}
}
?>
