<?php
/*
Plugin Name: Post/Page Headers
Plugin URI: http://www.aarongloege.com/blog/web-development/wordpress-blog/plugins/wordpress-plugin-postpage-headers/
Description: Attach CSS and JS files to individual posts and pages.
Version: 1.0.1
Author: Aaron Gloege
Author URI: http://www.aarongloege.com/

===============================================================================

Copyright 2009  Aaron Gloege  (contact@aarongloege.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

===============================================================================
*/

function post_head_menus() {
	if (function_exists('add_meta_box')) {
		add_meta_box('post_head_box','Post Headers','post_head_meta','post');
		add_meta_box('post_head_box','Page Headers','post_head_meta','page');
	}
}

function post_head_meta() {
	global $wpdb, $post_ID;
	$files = post_head_get_files($post_ID);
	?>
	
	<table>
		<thead>
			<tr>
				<th width="10%">Type</th>
				<th width="80%">File</th>
				<th width="10%">Placement</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php
$i = 0;
if ($files && $post_ID) {
foreach ((array)$files as $file) { ?>
			<tr id="post-head-<?php echo $i; ?>">
				<td><select name="post-head[<?php echo $i; ?>][type]">
					<option value="css"<?php if ($file['type'] == "css") echo ' selected="selected"';?>>CSS</option>
					<option value="js"<?php if ($file['type'] == "js") echo ' selected="selected"';?>>JS</option>
				</select></td>
				<td><input type="text" name="post-head[<?php echo $i; ?>][script]" value="<?php echo $file['script']; ?>" /></td>
				<td><select name="post-head[<?php echo $i; ?>][position]">
					<option value="top"<?php if ($file['position'] == "top") echo ' selected="selected"';?>>Header</option>
					<option value="foot"<?php if ($file['position'] == "foot") echo ' selected="selected"';?>>Footer</option>
				</select></td>
				<td><a href="#" class="button" onclick="post_head_remove(<?php echo $i; ?>); return false;">X</a></td>
			</tr>
<?php $i++;
}; }; ?>
			<tr id="post-head-<?php echo $i; ?>">
				<td><select name="post-head[<?php echo $i; ?>][type]">
					<option value="css">CSS</option>
					<option value="js">JS</option>
				</select></td>
				<td><input type="text" name="post-head[<?php echo $i; ?>][script]" /></td>
				<td><select name="post-head[<?php echo $i; ?>][position]">
					<option value="top">Header</option>
					<option value="foot">Footer</option>
				</select></td>
				<td><a href="#" class="button" onclick="post_head_remove(<?php echo $i; ?>); return false;">X</a></td>
			</tr>
		</tbody>
	</table>
	<p align="right" style="padding:10px 0 5px; margin:0;"><a href="#" class="button" id="add-post-head-file">Add Another File</a> <a href="media-upload.php?post_id=<?php echo $post_ID; ?>&TB_iframe=true" class="thickbox button" title="Upload Script">Upload Script</a></p>
	<?php

}

function post_head_submit($post_ID) {
  global $wpdb;
	$data = post_head_get_files();
	foreach((array)$_POST['post-head'] as $file) {
		if ($file['script'] != "" && post_head_validate_file($file['script'])) $t[] = $file;
	}
	if ($t) {
		$data[$post_ID] = $t;
	} else {
		unset($data[$post_ID]);
	}
	post_head_update_files($data);
}

function post_head_get_files($ID) {
	$data = unserialize(get_option("post-head-files"));
	return ($ID) ? $data[$ID] : $data;
}

function post_head_update_files($data) {
	return update_option('post-head-files', serialize($data));
}

function post_head_validate_file($file) {
	$ext = pathinfo(strtolower($file), PATHINFO_EXTENSION);
	return ($ext == "css" || $ext == "js" || $ext == "php") ? true : false;
}

function post_head_delete() {
	delete_option('post-head-files');
}

function post_head() {
	global $wp_query;
	$post_obj = $wp_query->get_queried_object();
	$data = post_head_get_files($post_obj->ID);
	
	// Queue Scripts
	if ($data) {
		$i = 0;
		foreach ((array)$data as $file) {
			if ($file['position'] == "top") {
				if ($file['type'] == "css") {
					wp_enqueue_style('post-head-'.$i, $file['script'], false, '1.0', 'all');
				} else {
					wp_enqueue_script('post-head-'.$i, $file['script'], false, '1.0');
				}
				$i++;
			}
		}
	}
}
function post_head_foot() {
	global $wp_query;
	$post_obj = $wp_query->get_queried_object();
	$data = post_head_get_files($post_obj->ID);
	
	// Queue Scripts
	if ($data) {
		foreach ((array)$data as $file) {
			if ($file['position'] == "foot") {
				if ($file['type'] == "css") {
					echo '<link type="text/css" rel="stylesheet" media="all" href="'.$file['script'].'" />'."\n";
				} else {
					echo '<script type="text/javascript" src="'.$file['script'].'"></script>'."\n";
				}
			}
		}
	}
}

function post_head_admin_head() {
	echo '<link type="text/css" rel="stylesheet" media="all" href="'.plugins_url('postpage-headers/admin.css').'" />'."\n";
	echo '<script type="text/javascript" src="'.plugins_url('postpage-headers/post-header.js').'"></script>'."\n";
}

add_action('admin_head', 'post_head_admin_head');
add_action('admin_menu', 'post_head_menus');
add_action('save_post', 'post_head_submit');
add_action('wp_enqueue_scripts', 'post_head');
add_action('wp_footer', 'post_head_foot')

?>