<?php

# Copyright (c) 2012 Alexey Shumkin
# Licensed under the MIT license

auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_manage_menu();

$t_version_update_urls = unserialize( plugin_config_get( 'remote_version_update_urls' ) );

?>

<br/>
<form action="<?php echo plugin_page( 'manage_config' ) ?>" method="post">
<?php echo form_security_field( 'plugin_Version_manage_config' ) ?>
<table class="width100" align="center" cellspacing="1">

<tr>
<td class="form-title" colspan="2"><?php echo plugin_lang_get( 'title' ), ': ', plugin_lang_get( 'configuration' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'update_threshold' ) ?></td>
<td><select name="update_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'update_threshold' ) ) ?></select></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'manage_threshold' ) ?></td>
<td><select name="manage_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'manage_threshold' ) ) ?></select></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'enabled_features' ) ?></td>
<td>
	<label><input type="checkbox" name="enable_change_target_version_to_next" <?php echo ( plugin_config_get( 'enable_change_target_version_to_next' ) ? 'checked="checked" ' : '' ) ?>/>
	<?php echo plugin_lang_get( 'enable_change_target_version_to_next' ) ?></label><br/>
</td>
</tr>

<tr><td class="spacer"></td></tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'api_key' ) ?></td>
<td><input name="api_key" size="50" value="<?php echo string_attribute( plugin_config_get( 'api_key' ) ) ?>"/><br/>
<?php echo plugin_lang_get( 'api_key_info' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'increment_date_by_days' ) ?></td>
<td><input name="increment_date_by_days" size="50" value="<?php echo string_attribute( plugin_config_get( 'increment_date_by_days' ) ) ?>"/></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'version_token_count' ) ?></td>
<td><input name="version_token_count" size="50" value="<?php echo string_attribute( plugin_config_get( 'version_token_count' ) ) ?>"/></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'description_template' ) ?></td>
<td><textarea name="description_template" rows="8" cols="50"><?php
	echo string_textarea( plugin_config_get( 'description_template' ) );
?></textarea><br/>
<?php echo plugin_lang_get( 'description_template_info' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'remote_version_update_urls' ) ?></td>
<td><textarea name="remote_version_update_urls" rows="8" cols="50"><?php
foreach( $t_version_update_urls as $t_ip ) {
	echo string_textarea( $t_ip ),"\n";
}
?></textarea></td>
</tr>

<tr>
<td class="center" colspan="2"><input type="submit" value="<?php echo plugin_lang_get( 'update_configuration' ) ?>"/></td>
</tr>

</table>
</form>

<?php
html_page_bottom1( __FILE__ );

