<?php get_header(); ?>
<section>
    <div class="row properties-list">
        <?php
        $term = get_queried_object();
        $images = get_field('gallery');
        $title = get_the_title();

        // Set up custom query for posts in the current term
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
        $args = array(
            'post_type' => 'sectors',
            'tax_query' => array(
                array(
                    'taxonomy' => 'categories',
                    'field'    => 'slug',
                    'terms'    => $term->slug,
                ),
            ),
            'paged' => $paged,
        );

        $loop = new WP_Query( $args );

        if ( $loop->have_posts() ) :
            while ( $loop->have_posts() ) : $loop->the_post();
                if ( has_post_thumbnail() ) { ?>

                    <div class="col-md-4 property">
                        <div class="cat-property">
                            <h5><?php single_term_title(); ?></h5>
                        </div>
                        <div class="property-gallery">
                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
                        
                        <div class="product-description">
                           <h3 class="product-title">
                            <?php the_title(); ?> <?php echo esc_html( get_field('location') ); ?>
                           </h3>
                            <h4 class="product-type"><?php echo esc_html( get_field('type') ); ?></h4>
                           <h6 class="weekly_sale_price"><strong>Weekly Sales: </strong><?php echo '£' . esc_html(get_field('sales')); ?></h6>    <h6 class="selling_price"><?php echo '£' . esc_html( get_field('price') ); ?></h6>
                            <!-- Add "View Details" button with a link to the post -->
                            <a href="<?php the_permalink(); ?>" class="view-details-button">View Details</a>
                            <img class="logo_retails" src="https://www.trinityretailsales.com/_next/image?url=https%3A%2F%2Fcms.trinityretailsales.com%2Fuploads%2Flogo_web_small_85a5233cf9.png&w=640&q=75" class="logo_retails">
                        </div>
                    </div>
                  </div>
                <?php }
            endwhile;
        else : ?>

            <p><?php _e( 'No posts found in this category.', 'textdomain' ); ?></p>
        <?php endif;
        wp_reset_postdata(); ?>

    </div>
</section>
<div class=" end-sector-tit-list-main">
<p class="end-sector-tit-list">Owner of Trinity Retail Sales Manish Jadav has been involved in the above selection of sales. Due to confidentiality & restrictions, full information is restricted on some stores. </center>
</div>
<?php get_footer(); ?>
