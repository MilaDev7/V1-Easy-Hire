(function () {
    // Page-based dashboard bootstrapper only.
    function initializeDashboardByPage() {
        const clientDashboard = document.querySelector(".client-dashboard-main");
        const professionalDashboard = document.getElementById("professional-dashboard");
        const adminDashboard = document.getElementById("admin-dashboard");

        if (clientDashboard && window.EasyHireClient?.init) {
            window.EasyHireClient.init();
            return;
        }

        if (professionalDashboard && window.EasyHireProfessional?.init) {
            window.EasyHireProfessional.init();
            return;
        }

        if (adminDashboard && window.EasyHireAdmin?.init) {
            window.EasyHireAdmin.init();
        }
    }

    // Ensure all module initialization happens after DOM is ready.
    document.addEventListener("DOMContentLoaded", initializeDashboardByPage);
})();
