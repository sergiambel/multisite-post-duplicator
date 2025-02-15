<?php

if ( ! defined( 'ABSPATH' ) ) exit('You\'re not allowed to see this page'); // Exit if accessed directly

add_action( 'add_meta_boxes', 'mpd_metaboxes' );

function mpd_metaboxes()
{
    if ( current_user_can( 'publish_posts' ) )  {
        add_meta_box( 'multisite_clone_metabox', 'Multisite Post Duplicate', 'mpd_publish_top_right', 'post', 'side', 'high' );
    }

}

function mpd_publish_top_right()
{
    $post_statuses = array('publish', 'future', 'draft', 'pending', 'private');
    $sites = wp_get_sites();

    ?>
    <div id="clone_multisite_box">

        <div class="metabox">

            <p>Duplicated post status:

                <select id="mpd-new-status" name="mpd-new-status">
                <?php foreach ($post_statuses as $post_status): ?>
                        <option value="<?php echo $post_status?>" <?php echo $post_status == 'draft' ? 'selected' : '' ?>><?php echo $post_status?></option>
                <?php endforeach ?>
                </select>
            </p>

            <p>Title prefix for new post:
                <input type="text" name="mpd-prefix" value=""/>
            </p>

            <p>Sites where you want duplicate to:
                <ul id="mpd_blogschecklist" data-wp-lists="list:category" class="mpd_blogschecklist" style="padding-left: 5px;margin-top: -8px;">
                <?php foreach ($sites as $site):
                    if (current_user_can_for_blog($site['blog_id'], publish_posts) ) {
                        ?>
                        <li id="mpd_blog_<?php echo $site['blog_id']; ?>" class="popular-category"><label
                                class="selectit"><input value="<?php echo $site['blog_id']; ?>" type="checkbox"
                                                        name="mpd_blogs[]"
                                                        id="in_blog_<?php echo $site['blog_id']; ?>"> <?php echo $site['domain']; ?>
                            </label></li>
                    <?php
                    }
                    endforeach ?>
                </ul>
            </p>
            <p>(*) The new post will be created after this has been saved.</p>
        </div>

    </div>

<?php
}

add_filter( 'save_post', 'mpd_clone_post' );

function mpd_clone_post($data )
{

    // Other "don't save" operations as remove or create new one:
    if (!count($_POST))
    {
        return $data;
    }

    if( ($_POST["post_status"] != "auto-draft")
        && ( isset($_POST['mpd_blogs'] ) )
        && ( count( $_POST['mpd_blogs'] ) )
        && ( $_POST["post_ID"] == $data ) //hack to avoid execution in cloning process
    )
    {
        $mpd_blogs = $_POST['mpd_blogs'];
        foreach( $mpd_blogs as $mpd_blog_id )
        {
            duplicate_over_multisite($_POST["ID"], $mpd_blog_id, $_POST["post_type"], $_POST["post_author"], $_POST["mpd-prefix"], $_POST["mpd-new-status"]);
        }
    }

    return $data;
}
