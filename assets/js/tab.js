document.addEventListener("DOMContentLoaded", function () {
    function openTab(tabName) {
        // Hide all tab content
        document.querySelectorAll(".tabcontent").forEach(tab => tab.style.display = "none");

        // Remove active class from all tab buttons
        document.querySelectorAll(".tablinks").forEach(tab => tab.classList.remove("active"));

        // Show selected tab and mark it as active
        let activeTab = document.getElementById(tabName);
        if (activeTab) {
            activeTab.style.display = "block";
            document.querySelector(`.tablinks[data-tab="${tabName}"]`).classList.add("active");

            // Update URL without reloading the page
            const newUrl = new URL(window.location);
            newUrl.searchParams.set("tab", tabName);
            history.pushState(null, "", newUrl);
        }
    }

    // Get tab from URL parameter or set default to 'Banks'
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get("tab") || "Banks"; // Default to Banks tab

    // Ensure the default tab is visible if no tab exists in the URL
    document.querySelectorAll(".tabcontent").forEach(tab => {
        if (tab.id === activeTab) {
            tab.style.display = "block";
        }
    });

    // Attach click event to tab buttons
    document.querySelectorAll(".tablinks").forEach(button => {
        button.addEventListener("click", function () {
            const tab = this.getAttribute("data-tab");
            openTab(tab);
        });
    });

    // Open the active tab
    openTab(activeTab);
});
