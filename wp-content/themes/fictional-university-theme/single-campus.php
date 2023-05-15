<?php 
    get_header();
    pageBanner();
    while(have_posts()) {
        the_post(); ?>
        <div class="container container--narrow page-section">
            <div class="metabox metabox--position-up metabox--with-home-link">
                <p>
                    <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('campus'); ?>">
                        <i class="fa fa-home" aria-hidden="true"></i> All Campuses
                    </a> 
                    <span class="metabox__main"><?php the_title(); ?>
                </span>
                </p>
            </div>
            <div class="generic-content"><?php the_content() ?></div>
            <div class="acf-map">
                <?php $mapLocation = get_field('map_location') ?>
                <div class="marker" data-lat="<?php echo $mapLocation['lat'] ?>" data-lng="<?php echo $mapLocation['lng'] ?>" >
                    <h3><?php the_title(); ?></h3>
                    <?php echo $mapLocation['address']; ?>
                </div>
            </div>
            <?php
            $relatedPrograms = new WP_Query(array(
                'posts_per_page' => -1,
                'post_type' => 'program',
                'orderby' => 'title',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => 'related_campus',
                        'compare' => 'LIKE',
                        'value' => '"' . get_the_ID() . '"'
                    ),
                    )
                ));
                        
            ?>
            <?php if ($relatedPrograms->have_posts()) { ?>
            <hr class="section-break">
            <h2 class="headline headline--medium">Programs Available At This Campus </h2>
            <ul class="min-list link-list">
            <?php
            while ($relatedPrograms->have_posts()) {
                $relatedPrograms->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </li>
            <?php }            
            } ?>
            </ul>
            <!-- this function resets the id thingy back to the beginning (to use inbetween queries) -->
            <?php wp_reset_postdata() ?>
            
            <?php

            };

    get_footer();
?>
