<?php

    
    require get_theme_file_path('/inc/search-route.php');
    require get_theme_file_path('/inc/like-route.php');

    function university_custom_rest() {
        register_rest_field('post', 'authorName', array(
            'get_callback' => function() {
                return get_the_author();
            }
        ));
        register_rest_field('note', 'userNoteCount', array(
            'get_callback' => function() {
                return count_user_posts(get_current_user_id(), 'note');
            }
        ));        
    }

    add_action('rest_api_init', 'university_custom_rest');

    function pageBanner($args = NULL) {
        // php logic will live here
        if (!isset($args['title'])) {
            $args['title'] = get_the_title();
        }
        if (!isset($args['subtitle'])) {
            $args['subtitle'] = get_field('page_banner_subtitle');
        }
        $test = get_field('page_banner_background_image');
        if (isset($test) AND !is_archive()) {
            $args['backgroundImage'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
        } else {
            $args['backgroundImage'] = get_theme_file_uri('/images/ocean.jpg');
        }
        ?>
        <div class="page-banner">
            <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['backgroundImage'] ?>)"></div>
            <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php 
                echo $args['title'];
            ?></h1>
            <div class="page-banner__intro">
                <p><?php 
                echo $args['subtitle'];
                ?></p>
            </div>
            </div>
        </div>
    <?php }

    $env_file_path = realpath(__DIR__."/.env");
    // $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    // $dotenv->load();    
     


    //first step

    //Check .envenvironment file exists
    if(!is_file($env_file_path)){
        throw new ErrorException("Environment File is Missing.");
    }
    //Check .envenvironment file is readable
    if(!is_readable($env_file_path)){
        throw new ErrorException("Permission Denied for reading the ".($env_file_path).".");
    }
    //Check .envenvironment file is writable
    if(!is_writable($env_file_path)){
        throw new ErrorException("Permission Denied for writing on the ".($env_file_path).".");
    }

    //next step

    $var_arrs = array();
    // Open the .en file using the reading mode
    $fopen = fopen($env_file_path, 'r');
    if($fopen){
        //Loop the lines of the file
        while (($line = fgets($fopen)) !== false){
            // Check if line is a comment
            $line_is_comment = (substr(trim($line),0 , 1) == '#') ? true: false;
            // If line is a comment or empty, then skip
            if($line_is_comment || empty(trim($line)))
                continue;
 
            // Split the line variable and succeeding comment on line if exists
            $line_no_comment = explode("#", $line, 2)[0];
            // Split the variable name and value
            $env_ex = preg_split('/(\s?)\=(\s?)/', $line_no_comment);
            $env_name = trim($env_ex[0]);
            $env_value = isset($env_ex[1]) ? trim($env_ex[1]) : "";
            $var_arrs[$env_name] = $env_value;
        }
        // Close the file
        fclose($fopen);
    }

    foreach($var_arrs as $name => $value){
        $_ENV[$name] = $value;
    }

    // $key = $_ENV['FULL_ADDRESS'];
    // $test = "//maps.googleapis.com/maps/api/js?key=" . $_ENV['SECRET_KEY'];
    // echo '<script>console.log('.$test.')</script>';

    function university_files() {
        $test = "//maps.googleapis.com/maps/api/js?key=" . $_ENV['SECRET_KEY'];
        echo $_ENV['SECRET_KEY'];
        echo '<script>console.log(' . $test . ')</script>';
        wp_enqueue_script('googleMap', $test, NULL, '1.0', true);
        wp_enqueue_script('university-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
        wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
        wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
        wp_localize_script('university-js', 'universityData', array(
            'root_url' => get_site_url(),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    };

    add_action('wp_enqueue_scripts', 'university_files');
    
    function university_features() {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_image_size('professorLandscape', 400, 260, true);
        add_image_size('professorPortrait', 480, 650, true);
        add_image_size('pageBanner', 1500, 350, true);
    }
    
    add_action('after_setup_theme', 'university_features');

    function university_adjust_queries($query) {

        if (!is_admin() AND is_post_type_archive('campus') AND $query->is_main_query()) {
            $query->set('posts_per_page', -1);
        }

        if (!is_admin() AND is_post_type_archive('program') AND $query->is_main_query()) {
            $query->set('orderby', 'title');
            $query->set('order', 'ASC');
            $query->set('posts_per_page', -1);
        }

        
        $today = date('Ymd');
        if (!is_admin() AND is_post_type_archive('event') AND $query->is_main_query()) {
            $query->set('meta_key', 'event_date');
            $query->set('orderby' , 'meta_value_num');
            $query->set('order' , 'ASC');
            $query->set('meta_query' , array(
              array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => $today,
                'type' => 'numeric'
              )
            ));
        }
    }

    add_action('pre_get_posts', 'university_adjust_queries');

    function universityMapKey($api) {
        $api['key'] = $_ENV['SECRET_KEY'];
        return $api;
    }

    add_filter('acf/fields/google_map/api', 'universityMapKey');


    //redirect sub accounts out of admin and onto homepage:

    add_action('admin_init', 'redirectSubsToFrontend');

    function redirectSubsToFrontend() {
        $ourCurrentUser = wp_get_current_user();

        if (count($ourCurrentUser->roles) == 1 AND $ourCurrentUser->roles[0] == 'subscriber') {
            wp_redirect(site_url('/'));
            exit;
        }
    }
    add_action('wp_loaded', 'noSubsAdminBar');

    function noSubsAdminBar() {
        $ourCurrentUser = wp_get_current_user();

        if (count($ourCurrentUser->roles) == 1 AND $ourCurrentUser->roles[0] == 'subscriber') {
            show_admin_bar(false);
        }
    }

    //customize login screen

    add_filter('login_headerurl', 'ourHeaderUrl');

    function ourHeaderUrl() {
        return esc_url(site_url('/'));
    }

    add_action('login_enqueue_scripts', 'ourLoginCSS');

    function ourLoginCSS() {
        wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
        wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    }

    add_filter('login_headertitle', 'ourLoginTitle');

    function ourLoginTitle() {
        return get_bloginfo('name');
    }

    //force private note

    add_filter('wp_insert_post_data', 'makeNotePrivate', 10, 2);

    function makeNotePrivate($data, $postarr) {
        if ($data['post_type'] == 'note') {
            if (count_user_posts(get_current_user_id(), 'note') > 4 AND !$postarr['ID']) {
                die("You have reached your note limit.");
            }
            $data['post_content'] = sanitize_textarea_field($data['post_content']);
            $data['post_title'] = sanitize_text_field($data['post_title']);
        }
        if ($data['post_type'] == 'note' AND $data['post_status'] != 'trash') {
            $data['post_status'] = "private";
        }
        return $data;
    }
?>