document.addEventListener('DOMContentLoaded', function () {
    const emiSection = document.getElementById('woocommerce-emi-options');

    // Ensure the EMI section is only displayed in the product details area
    if (emiSection) {
        const stickyCart = document.querySelector('.etheme-sticky-cart.etheme-sticky-panel.flex.align-items-center.container-width-inherit');
        if (stickyCart && stickyCart.contains(emiSection)) {
            emiSection.remove();
        }
    }
});
