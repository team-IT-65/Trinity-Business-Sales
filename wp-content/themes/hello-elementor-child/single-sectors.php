<?php get_header(); ?>

<section class="splide__track section">

        <div class="row">
            <?php while ( have_posts() ) : the_post(); ?>
                <div class="col-md-6 product-left-sec">
                    <div class="single-page-post-heading">
                        <h1 class="product-titles">
                            <?php the_title(); ?>
                        </h1>
                        <h2 class="product-location">
                            <?php echo esc_html( get_field('location') ); ?>
                        </h2>
                        <div class="property-gallery main-product-img">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="post-thumbnail">
                                    <?php the_post_thumbnail(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 product-right-sec">
                    <div class="category-info">
                        <h4 class="category-name">
                            <?php
                              $terms = get_the_terms( get_the_ID(), 'categories' );
                              if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                                  $term_names = array();
                                  foreach ( $terms as $term ) {
                                  $term_names[] = esc_html( $term->name );
                                 }
                           echo implode(', ', $term_names);
                               }
                             ?>

                        </h4>
                    </div>
                    <div class="property-content">
                        <?php the_content(); ?> 
                    </div>
                    <?php 
                         $link = get_field('contact');
                           if( $link ): ?>
                             <a class="btn btn-primary" target="_blank" href="<?php echo esc_url( $link ); ?>">Contact Us</a>
                         <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

</section>

<div class="gallery-products slider">
    <?php 
    $images = get_field('gallary');
    if ( $images ) : ?>
        <?php foreach ( $images as $image ) : ?>
           <div class="property-gallery">
                <a href="<?php echo esc_url($image['url']); ?>"></a>
                    <img src="<?php echo esc_url($image['sizes']['large']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="col-md-12 end-sector-tit-list-main">
<p class="end-sector-tit-list">Owner of Trinity Retail Sales Manish Jadav has been involved in the above selection of sales. Due to confidentiality & restrictions, full information is restricted on some stores. </center>
</div>


<?php get_footer(); ?>



