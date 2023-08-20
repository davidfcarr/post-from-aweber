<?php
/*
Plugin Name: Post from AWeber
Plugin URI: https://github.com/davidfcarr/post-from-aweber
Description: Create a blog post based on the archive page for an AWeber email broadcast. Strips out extraneous HTML such as table coding. Allows you to edit the results in the WordPress editor. Appears as a Classic Block in the Gutenberg editor, but you can "convert to blocks" if desired.
Author: David F. Carr
Author URI: http://www.carrcommunications.com
Version: 1.2
*/

function post_from_aweber_fetch () {
global $current_user;

if(isset($_POST['archive']))
{
if(wp_verify_nonce($_POST['_wpnonce'],'post_from_aweber') && current_user_can('edit_posts'))
    {
        echo '<div class="notice notice-info"><p>Security check: OK</p></div>';

        $content = file_get_contents(esc_url_raw($_POST['archive']));
        preg_match('/<h2.+>(.+)<\/h2>/',$content,$matches);
        $title = $matches[1];

        $parts = explode('</section>',$content);
        array_shift($parts);
        $content = implode('',$parts);
        $content = str_replace('align="center"','',$content);
        $content = strip_tags($content,'<p><a><img><ol><ul><li><div>');
        $content = wp_kses_post($content);
        $post['post_title'] = sanitize_text_field($title);
        $post['post_content'] = $content;
        $post['post_status'] = 'draft';
        $post['post_author'] = $current_user->ID;
        $id = wp_insert_post($post);
        
        printf('<h1>Draft Post Created</h1><p><a href="%s">Edit / Publish</a></p><p>Showing Preview Below</p>',admin_url('post.php?action=edit&post='.$id));
        
        printf('<h2>%s</h2>',$title);
        echo wpautop($content);        
    }
    else
        echo '<div class="notice notice-error"><p>Security error</p></div>';
}

printf('<h1>Fetch an AWeber archive page ...</h1> <p>...and turn it into a WordPress blog post.</p><form method="post" action="%s">Archive url: <input name="archive" type="text" value="" />%s<button>Get</button></form>',admin_url('edit.php?page=post_from_aweber_fetch'),wp_nonce_field('post_from_aweber'));
//echo 'http://archive.aweber.com/geeknews/HmWs_/h/March_2020_News_Map_of_Dive.htm';

}// end function

function post_from_aweber_archive_menu() {
    add_submenu_page('edit.php', __("AWeber Post",'rsvpmaker'), __("AWeber Post",'rsvpmaker'), 'edit_posts', "post_from_aweber_fetch", "post_from_aweber_fetch" );  
}

add_action('admin_menu','post_from_aweber_archive_menu');
?>
