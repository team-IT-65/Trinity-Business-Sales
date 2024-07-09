jQuery(document).ready(function ($) {
    function initializeSlider() {
        $('.review_inner .elementor-container').slick({
            slidesToShow: 1,
            dots: false,
            arrows: false,
        });
    }
    if (window.matchMedia('(max-width: 767px)').matches) {
        initializeSlider();
    }
    $(window).on('resize', function () {
        if (window.matchMedia('(max-width: 767px)').matches) {
            initializeSlider();
        } else if ($('.review_inner').hasClass('slick-initialized')) {
            $('.review_inner').slick('unslick');
        }
    });
    $('.retail_review_inner .elementor-container').slick({
        slidesToShow: 3,
        dots: true,
        arrows: true,
        prevArrow: '<button type="button" class="custom-arrow slick-prev">' +
            '<svg width="30" height="30" viewBox="0 0 1024 1024" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#fff" stroke="#fff">' +
            '<g id="SVGRepo_bgCarrier" stroke-width="0"/>' +
            '<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>' +
            '<g id="SVGRepo_iconCarrier">' +
            '<path d="M768 903.232l-50.432 56.768L256 512l461.568-448 50.432 56.768L364.928 512z" fill="#fff"/>' +
            '</g>' +
            '</svg>' +
            '</button>',
        nextArrow: '<button type="button" class="custom-arrow slick-next">' +
            '<svg width="30" height="30" viewBox="0 0 1024 1024" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#fff" stroke="#fff">' +
            '<g id="SVGRepo_bgCarrier" stroke-width="0"/>' +
            '<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>' +
            '<g id="SVGRepo_iconCarrier">' +
            '<path d="M768 903.232l-50.432 56.768L256 512l461.568-448 50.432 56.768L364.928 512z" fill="#fff"/>' +
            '</g>' +
            '</svg>' +
            '</button>',
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    arrows: false,
                    dots: false,
                }
            }
        ]
    });
});

jQuery(document).ready(function () {
    jQuery('.hfe-nav-menu__toggle .hfe-nav-menu-icon').on('click', function () {
        jQuery('html').toggleClass('active_menu');
    });
    jQuery('.retail_review_read_more').click(function () {
        var reviewDesc = jQuery(this).prev('.retail_review_desc');
        reviewDesc.addClass('active');
        if (reviewDesc.find('.close').length === 0) {
            reviewDesc.append('<div class="close"><svg viewBox="0 0 24 24" fill="currentColor" width="40px" height="40px" data-ux="CloseIcon" data-edit-interactive="true" data-close="true" class="x-el x-el-svg c1-1 c1-2 c1-1p c1-1u c1-34 c1-1w c1-1x c1-1y c1-1z c1-1j c1-3u c1-3v c1-3a c1-3w c1-3x c1-dl c1-b c1-1r"><path fill-rule="evenodd" d="M17.999 4l-6.293 6.293L5.413 4 4 5.414l6.292 6.293L4 18l1.413 1.414 6.293-6.292 6.293 6.292L19.414 18l-6.294-6.293 6.294-6.293z"></path></svg></div>');
        }
    });
    jQuery('.retail_review_inner').on('click', '.retail_review_desc.active .close svg', function () {
        jQuery('.retail_review_desc.active').removeClass('active');
    });
});

// For Retail page three col
var highestHeight = 0;
jQuery('.retail_single_col h2.elementor-heading-title').each(function () {
    var currentHeight = jQuery(this).height();
    highestHeight = Math.max(highestHeight, currentHeight);
});
jQuery('.retail_single_col h2.elementor-heading-title').height(highestHeight);

// jQuery(document).ready(function ($) {
//     var path = $('path');

//     path.each(function (i) {
//         var title = $(this).attr('title');
// 		console.log(title);
//         if (title != undefined) {
//             $('.input_boxes').append('<input class="radio_check" type="checkbox" id="' + title + '" name="states" value="' + title + '"><label for="' + title + '">' + title + '</label><br>');
//         }

//     })

//     $('.state_area').click(function () {
//         var title = $(this).attr('title');
//         jQuery('.radio_check[id="' + title + '"]').prop('checked', true);
//         //       jQuery('.state_area').removeClass('checked');
//         jQuery('.state_area[title="' + title + '"]').addClass('checked');
//     })
// })

document.addEventListener('DOMContentLoaded', function () {
    var selectField = document.querySelector('.company-drop-selct');
    var additionalInfoBox = document.getElementById('additional-info-box');

    selectField.addEventListener('change', function () {
        var selectedOption = selectField.options[selectField.selectedIndex].value;

        if (selectedOption === 'SME' || selectedOption === 'OTHER') {
            additionalInfoBox.style.display = 'block';
        } else {
            additionalInfoBox.style.display = 'none';
        }
    });
});

// jQuery(document).ready(function ($) {
// 	$('.state_area').hover(function (e) {
// 		var title = $(this).attr('title');
// 		var tooltip = $('.hovered-tooltip');
// 		tooltip.text(title);
// 		tooltip.css({
// 			'top': (e.pageY - 600) + 'px',
// 			'left': (e.pageX - 450) + 'px'
// 		});

// 		tooltip.show();
// 	}, function () {
// 		$('.hovered-tooltip').hide();
// 	});

// 	$(document).mousemove(function (e) {
// 		$('.hovered-tooltip').css({
// 			'top': (e.pageY - 600) + 'px',
// 			'left': (e.pageX - 450) + 'px'
// 		});
// 	});
// });
jQuery(document).ready(function($){
	$('.country-drop-selct').select2();

	$('.country-drop-selct').on('change', function() {
		var selectedOptions = $(this).val();
		if(selectedOptions){
			$.each(selectedOptions, function(index, value){
				 jQuery('.state_area[title="' + value + '"]').addClass('checked');
			});
		}
	});

})
	jQuery(document).on('click','.select2-selection__choice__remove', function() {
		var selection = jQuery(this).parent().attr('title');
		jQuery('.state_area[title="' + selection + '"]').removeClass('checked');
	});

//---slick slider(Sector Post)----//

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