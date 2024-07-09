 jQuery(document).ready(function($) {
  jQuery('.gallery-products.slider').slick({
    infinite: true,
    slidesToShow: 3,
    slidesToScroll: 3,
    dots: true,
    arrows: false,
    speed: 800,
    cssEase: 'linear'
  });
});