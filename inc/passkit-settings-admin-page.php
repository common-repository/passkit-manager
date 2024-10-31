<div class="wrap">
    <?php 
	screen_icon( PKMGRPLUGIN_ID );
	?>
	<h2><?php printf('%s &gt;&gt; %s', PKMGRPLUGIN_PREFIX, __('Settings', PKMGRPLUGIN_ID));?></h2>
	
	<form method="post" action="options.php">
		<?php 
		settings_fields(PKMGRPLUGIN_ID.'_options');
		$options = get_option('pk_options');
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('API Key');?></th>
				<td><input type="text" class="regular-text" name="pk_options[pk_api_key]" value="<?php echo $options['pk_api_key']; ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('API Secret');?></th>
				<td><input type="text" class="regular-text" name="pk_options[pk_api_secret]" value="<?php echo $options['pk_api_secret']; ?>" /></td>
			</tr>
		</table>
		<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>

