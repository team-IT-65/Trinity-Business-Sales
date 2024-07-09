<?php
$is_property = $this->getProperties(['type' => 'populate=*', 'id' => $_GET['id']]);
$data = json_decode($is_property['body']);
$dataid = $data->data;
$image_url = IMAGE_URL;
$redirect_url = get_permalink() . 'contact';
?>

<section class="splide__track section">
    <div class="row">
        <div class="col-md-6 product-left-sec">
            <?php
            if (isset($dataid->title) && isset($dataid->location)) {
                echo '<h1 class="product-titles">' . esc_html(strtoupper($dataid->title)) . '</h1>';
                echo '<h2 class="product-location">' . esc_html($dataid->location) . '</h2>';
            } else {
                echo '<h3 class="product-titles">Property details unavailable</h3>';
            }

            if (isset($dataid->media->formats->medium->url)) {
                $thumbnail_url = $image_url . esc_url($dataid->media->formats->medium->url);
                echo '<div class="property-gallery main-product-img">';
                echo '<img src="' . $thumbnail_url . '" alt="' . esc_attr($dataid->title) . '" class="product-main-img">';
                echo '</div>';
            } else {
                echo '<p>No image available</p>';
            }
            ?>
        </div>

        <div class="col-md-6 product-right-sec">
            <?php
            if (isset($dataid->categories) && is_array($dataid->categories) && count($dataid->categories) > 0) {
                $category = $dataid->categories[0]; // Assuming there's only one category
                echo '<div class="category-info">';
                echo '<h4 class="category-name">' . esc_html(strtoupper($category->name)) . '</h4>';
                echo '</div>';
            }

            if (isset($dataid->content) && !empty($dataid->content)) {
                echo '<div class="property-content">';
                echo wp_kses_post($dataid->content); // Output the content as HTML
                echo '</div>';
            } else {
                echo '<p>No content available</p>';
            }
            ?>
            <a href="<?php echo esc_url($redirect_url); ?>" class="btn btn-primary">Contact us</a>
        </div>
    </div>
</section>

<?php
if (isset($dataid->gallery) && !empty($dataid->gallery)) {
    echo '<div class="gallery-products slider">';
    foreach ($dataid->gallery as $format) {
        $thumbnail_url = $image_url . esc_url($format->url);
        echo '<div class="property-gallery">';
        echo '<img src="' . $thumbnail_url . '" class="product-main-img">';
        echo '</div>';
    }
    echo '</div>';
}
?>