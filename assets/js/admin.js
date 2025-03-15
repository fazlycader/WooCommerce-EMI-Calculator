document.addEventListener('DOMContentLoaded', function () {
    function openTab(evt, tabName) {
        // Hide all tab content
        const tabContents = document.querySelectorAll('.tabcontent');
        tabContents.forEach(tab => {
            tab.style.display = 'none';
        });

        // Remove active class from all tabs
        const tabLinks = document.querySelectorAll('.tablinks');
        tabLinks.forEach(link => {
            link.classList.remove('active');
        });

        // Find the tab content by ID
        const tabContent = document.getElementById(tabName);
        if (tabContent) {
            // Show current tab content and add active class
            tabContent.style.display = 'block';
        } else {
            console.error(`Tab content with ID "${tabName}" not found.`);
        }

        evt.currentTarget.classList.add('active');
    }

    // Attach click events to all tab links
    const tabs = document.querySelectorAll('.tablinks');
    tabs.forEach(tab => {
        tab.addEventListener('click', function (e) {
            openTab(e, tab.getAttribute('data-tab'));
        });
    });

    // Simulate click on the default tab
    const defaultTab = document.querySelector('.tablinks[data-tab="Banks"]'); // Update with your default tab ID
    if (defaultTab) {
        defaultTab.click();
    } else {
        console.error('Default tab not found.');
    }

    // AJAX for adding a bank
    document.getElementById('add-bank-button').addEventListener('click', function () {
        const form = new FormData(document.getElementById('add-bank-form'));
        form.append('action', 'add_bank');
        form.append('nonce', wcEmiAjax.nonce);

        fetch(wcEmiAjax.ajaxUrl, {
            method: 'POST',
            body: form,
            mode: 'no-cors', // You can also try 'no-cors' for less strict handling (if CORS server-side isn't fixed)
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server responded with status ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.data.message);
                // Refresh the bank list if necessary
            } else {
                alert(data.data.message);
            }
        })
        .catch(error => {
            // console.error('Error during fetch operation:', error);
            alert('There was an error with your request. Please try again.');
        });
    });

    // AJAX for adding a payment plan
    document.getElementById('add-payment-plan-button').addEventListener('click', function () {
        const form = new FormData(document.getElementById('add-payment-plan-form'));
        form.append('action', 'add_payment_plan');
        form.append('nonce', wcEmiAjax.nonce);

        fetch(wcEmiAjax.ajaxUrl, {
            method: 'POST',
            body: form,
            mode: 'cors', // Again, can try 'no-cors' if needed
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server responded with status ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.data.message);
                // Refresh the payment plans list if necessary
            } else {
                alert(data.data.message);
            }
        })
        .catch(error => {
            console.error('Error during fetch operation:', error);
            alert('There was an error with your request. Please try again.');
        });
    });
});
