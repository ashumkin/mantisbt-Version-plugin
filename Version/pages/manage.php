<?php

auth_reauthenticate( );

html_page_top( plugin_lang_get( 'version_title' ) );

$t_this_page = plugin_page('version_title'); //FIXME with plugins this does not work...
print_manage_menu( $t_this_page );

# We need a project to import into
$f_project_id = helper_get_current_project();

?>
<!-- PROJECT VERSIONS -->
<a name="versions" />
<div align="center">
<table class="width75" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'versions' ) ?>
	</td>
</tr>
<?php
	$t_versions = version_get_all_rows( $f_project_id, /* released = */ null, /* obsolete = */ null );

	if ( count( $t_versions ) > 0 ) {
?>
		<tr class="row-category">
			<td>
				<?php echo lang_get( 'version' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'released' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'obsolete' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'timestamp' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'actions' ) ?>
			</td>
		</tr>
<?php
	}

	foreach ( $t_versions as $t_version ) {
		if ( $t_version['project_id'] != $f_project_id ) {
			$t_inherited = true;
		} else {
			$t_inherited = false;
		}

		$t_name = version_full_name( $t_version['id'], /* showProject */ $t_inherited, $f_project_id );

		$t_released = $t_version['released'];
		$t_obsolete = $t_version['obsolete'];
		if( !date_is_null( $t_version['date_order'] ) ) {
			$t_date_formatted = date( config_get( 'complete_date_format' ), $t_version['date_order'] );		
		} else {
			$t_date_formatted = ' ';
		}
?>
<!-- Repeated Info Rows -->
		<tr <?php echo helper_alternate_class() ?>>
			<td>
				<?php echo string_display( $t_name ) ?>
			</td>
			<td class="center">
				<?php echo trans_bool( $t_released ) ?>
			</td>
			<td class="center">
				<?php echo trans_bool( $t_obsolete ) ?>
			</td>
			<td class="center">
				<?php echo $t_date_formatted ?>
			</td>
			<td class="center">
				<?php
					$t_version_id = version_get_id( $t_name, $f_project_id );

					if ( !$t_inherited ) {
						print_button( 'manage_proj_ver_edit_page.php?version_id=' . $t_version_id, lang_get( 'edit_link' ) );
						echo '&#160;';
						print_button( 'manage_proj_ver_delete.php?version_id=' . $t_version_id, lang_get( 'delete_link' ) );
					}
				?>
			</td>
		</tr>
<?php
	} # end for loop
?>

<!-- Version Add Form -->
<tr>
	<td class="left" colspan="3">
		<form method="post" action="manage_proj_ver_add.php">
			<?php echo form_security_field( 'manage_proj_ver_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<input type="text" name="version" size="32" maxlength="64" />
			<input type="submit" name="add_version" class="button" value="<?php echo lang_get( 'add_version_button' ) ?>" />
			<input type="submit" name="add_and_edit_version" class="button" value="<?php echo lang_get( 'add_and_edit_version_button' ) ?>" />
		</form>
	</td>
</tr>
<tr>
	<td class="left" colspan="3">
		<form method="post" action="manage_proj_ver_copy.php">
			<?php echo form_security_field( 'manage_proj_ver_copy' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<select name="other_project_id">
				<?php print_project_option_list( null, false, $f_project_id ); ?>
			</select>
			<input type="submit" name="copy_from" class="button" value="<?php echo lang_get( 'copy_versions_from' ) ?>" />
			<input type="submit" name="copy_to" class="button" value="<?php echo lang_get( 'copy_versions_to' ) ?>" />
		</form>
	</td>
</tr>
</table>
</div>

<?php
	html_page_bottom();
