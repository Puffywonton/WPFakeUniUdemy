<?php 
  get_header();
  pageBanner(array(
    'title' => 'Past Events',
    'subtitle' => 'A Recap Of Our Past Events.',
    'backgroundImage' => TRUE,
  ));
?>

<div class="container container--narrow page-section">
  <?php 
    $today = date('Ymd');
    $pastEvents = new WP_Query(array(
        'paged' => get_query_var('paged', 1),
        // 'posts_per_page' => -1,
        'post_type' => 'event',
        'meta_key' => 'event_date',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'meta_query' => array(
            array(
            'key' => 'event_date',
            'compare' => '<=',
            'value' => $today,
            'type' => 'numeric'
            ),
        )
    ));
    while ($pastEvents->have_posts()) {
      $pastEvents->the_post(); ?>
              <div class="metabox metabox--position-up metabox--with-home-link">
                  <p>
                      <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('event'); ?>"><i class="fa fa-home" aria-hidden="true"></i> Back to Events</a> <span class="metabox__main">Past Events</span>
                  </p>
              </div>
        <?php get_template_part('template-parts/content', 'event');
    }
    echo paginate_links(array(
        'total' => $pastEvents->max_num_pages
    ));
  ?>
</div>

<?php
  get_footer();
?>