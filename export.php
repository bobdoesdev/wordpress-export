<?php
//make page and form
add_action('admin_menu', 'e11_export_video_viewers_form');

function e11_export_video_viewers_form(){
    add_submenu_page(
        'edit.php?post_type=video-viewers',
        'Export Viewers',
        'Export Viewers',
        'manage_options',
        'export-video-viewers-events',
        'e11_export_video_viewers_page',
        99
    );
}

function e11_export_video_viewers_page(){
    ?>
        <h1>Export Video Viewers</h1>
        <form method="post" id="e11_export_video_viewers" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <input type="hidden" name="e11_export_video_viewers_nonce" value="<?php echo wp_create_nonce('e11-export-video-viewers-nonce'); ?>"/>
            <input type="hidden" name="action" value="e11_export_video_viewers">
            <button class="button button-primary">Export Now</button>
        </form>
    <?php
}

function e11_export_video_viewers(){

    if( !wp_verify_nonce($_POST['e11_export_video_viewers_nonce'], 'e11-export-video-viewers-nonce')) {
        echo 'Sorry, we could not validate your security token. Please refresh the page and try again.';
        exit;
    };


    //retrieve event ids from posts with it saved as meta data
    $args = array(
        'post_type' => 'video-viewers',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );

    $posts = get_posts($args);

    //add in headers
    $data = array();
    $data[] = array(
            'First Name',
            'Last Name',
            'Email Address',
            'Date of Viewing',
            'Video Viewed',
    );


    //loop through posts
    foreach($posts as $post){
        $post_id = $post->ID;

        //get meta data we saved for each video view
        $data[] = array(
            $first_name = get_post_meta($post_id, 'video_viewer_first_name', true),
            $last_name = get_post_meta($post_id, 'video_viewer_last_name', true),
            $email = get_post_meta($post_id, 'video_viewer_email_address', true),
            $date = get_post_meta($post_id, 'video_viewer_date_of_viewing', true),
            $video = get_post_meta($post_id, 'video_viewer_video_viewed', true),
        );

    }

    header('Content-Type: application/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="video-viewers-export-'.date('M-d-Y').'.csv"');

    $fp = fopen('php://output', 'w');

    foreach ( $data as $line ) {
        fputcsv($fp, $line);
    }
    fclose($fp);

}


add_action('admin_post_e11_export_video_viewers', 'e11_export_video_viewers');
