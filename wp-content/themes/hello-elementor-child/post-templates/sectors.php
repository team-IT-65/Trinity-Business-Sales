<?php
/* Template Name: Sector Listing
*/ 
get_header(); ?>


<?php
$paged  = get_query_var('paged') ? get_query_var('paged') : 1;
$blogPosts = get_posts([
    'post_type' => 'sectors',
    'post_status' => 'publish',
    'order'    => 'DESC',
    'paged' => $paged,
]);

?>
<section>
 <div class="row properties-list">
    <?php foreach ($blogPosts as $post): setup_postdata($post); ?>
        <?php
            // Get ACF fields
            $type = get_field('type', $post->ID);
            $sales = get_field('sales', $post->ID);
            $price = get_field('price', $post->ID);
            $location = get_field('location', $post->ID);
            $gallery = get_field('gallery', $post->ID);

          
        ?>
     
            <div class="col-md-4 property <?php echo esc_attr($category_classes); ?>">
                <div class="cat-property">
                   
                   <?php
        $terms = get_the_terms($blog->ID, 'category');
             if ($terms && !is_wp_error($terms)) {
           foreach ($terms as $term) {
             $term_name = $term->name;
        
        // Check each category term and display content accordingly
       
    }
}
?>
                                           
                
                </div>
             
                      

                <div class="product-description"> 
               <h2><?php echo esc_html( get_field('type') ); ?></h2>        
                    <img src="https://www.trinityretailsales.com/_next/image?url=https%3A%2F%2Fcms.trinityretailsales.com%2Fuploads%2Flogo_web_small_85a5233cf9.png&w=640&q=75" class="logo_retails">
                </div>
            </div>
    
    <?php endforeach; wp_reset_postdata(); ?>
 </div>
</section>



<?php get_footer(); ?>
