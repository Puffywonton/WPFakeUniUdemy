<?php 
  get_header();
  pageBanner(array(
    'title' => 'Search Results',
    'subtitle' => 'You Searched for &ldquo;' . esc_html(get_search_query(false)) . '&rdquo;',
    'backgroundImage' => TRUE,
  ));
?>

<div class="container container--narrow page-section">
  <?php 
    if (have_posts()) {
        while (have_posts()) {
            the_post(); 
            get_template_part('template-parts/content', get_post_type());
        }
        echo paginate_links();    
    } else {
        ?><h2 class="headline headline--small-plus">No Results Match That Search</h2><?php
    }

    get_search_form();
  ?>
</div>

<?php
  get_footer();
?>