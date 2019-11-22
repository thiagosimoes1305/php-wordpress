<?php
/**
 * Remove view links of menu except administrator
 * PHP Version 7.0+
 * Author: Ted k': contato@tedk.com.br
 */
add_action('admin_menu', 'remove_menu_links', 999);
function remove_menu_links(){
	remove_menu_page('edit-comments.php');
    
    if (!current_user_can('administrator')){
		remove_menu_page('upload.php');
		remove_menu_page('edit.php');
		remove_menu_page('edit.php?post_type=page');
		remove_menu_page('plugins.php');
		remove_menu_page('themes.php');
		remove_menu_page('tools.php');
		remove_menu_page('edit.php?post_type=acf');
	}
}
?>