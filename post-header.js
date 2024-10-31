jQuery(document).ready(function($) {
	$('#post_head_box #add-post-head-file').click(function() {
		var _i = $('#post_head_box table tbody tr').length;
		var _html = '<tr id="post-head-'+_i+'"><td><select name="post-head['+_i+'][type]"><option value="css">CSS</option><option value="js">JS</option></select></td><td><input type="text" name="post-head['+_i+'][script]" /></td><td><select name="post-head['+_i+'][position]"><option value="top">Header</option><option value="foot">Footer</option></select></td><td><a href="#" onclick="post_head_remove('+_i+'); return false;" class="button">X</a></td></tr>';
		$('#post_head_box table tbody').append(_html);
		return false;
	});
});
function post_head_remove(e) {
	jQuery('#post_head_box tr').remove('#post-head-'+e);
}