<div class="wrap">
<?php 
// Print the PassKit screen icon
screen_icon( PKMGRPLUGIN_ID );
?>
<h2><?php printf('%s &gt;&gt; %s', PKMGRPLUGIN_PREFIX, __('Update pass', PKMGRPLUGIN_ID));?> 
<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page='.PKMGRPLUGIN_DIR.'/inc/passkit-pass-records-admin-page.php&amp;template_id='.$_REQUEST["template_id"].'&amp;t='.$_REQUEST["t"]);?>"><?php _e('back to list', PKMGRPLUGIN_ID)?></a></h2>

<?php
// Check if template is set. If not we throw an error message
if(!isset($_REQUEST["t"]) || empty($_REQUEST["t"]))
{
	?>
	<p><?php _e("Invalid template", PKMGRPLUGIN_ID);?></p>
	<?php
}
else
{
	// Print short code for our issue pass, we will add the admin-style variable. This will render the table admin style, and ignore all custom settings.
	echo do_shortcode('[passkit type="update" template_id="'.$_REQUEST["template_id"].'" pass_id="'.$_REQUEST["pass_id"].'" use_admin_style="true"]');
}
?>
</div>