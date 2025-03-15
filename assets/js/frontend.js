document.addEventListener('DOMContentLoaded', function () {
    console.log('WooCommerce EMI frontend script loaded.');
    // Add frontend functionality here if needed
});
jQuery(document).ready(function ($) {
    $(".emi-popup-trigger").click(function (e) {
        e.preventDefault();
        let bankId = $(this).data("bank-id");
        $("#emi-popup-" + bankId).fadeIn();
    });

    $(".emi-popup-close").click(function () {
        $(".emi-popup").fadeOut();
    });

    $(document).mouseup(function (e) {
        if (!$(".emi-popup").is(e.target) && $(".emi-popup").has(e.target).length === 0) {
            $(".emi-popup").fadeOut();
        }
    });
});
