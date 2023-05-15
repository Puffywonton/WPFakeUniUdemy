<?php 
  get_header();
  pageBanner(array(
    'title' => 'All Upcoming Events',
    'subtitle' => 'See What is Going On Around The Campus',
    'backgroundImage' => TRUE,
  ));
?>

<div class="container container--narrow page-section">
  <?php 
    while (have_posts()) {
      the_post();
      get_template_part('template-parts/content', 'event');
    }
    echo paginate_links();
  ?>
  <hr class="section-break">
  <p>Looking for a recap of past events? <a href="<?php echo site_url('/past-events')?>">Check out our past events archives.</a></p>

</div>

<?php
  get_footer();
?>