document.addEventListener("DOMContentLoaded", function () {
(function () {
    // Admin dashboard module.
    const { fetchJson, postJson } = window.EasyHireApi;
    const { toArray, setText } = window.EasyHireUtils;

    // Admin UI rendering and actions.
function loadAdminStats() {
    console.log("Loading admin stats...");
    return fetchJson("/api/admin/stats")
        .then((payload) => {
            console.log("Stats payload:", payload);
            setText("admin-pending-approvals-count", payload.pending_professionals ?? 0);
            setText("admin-active-contracts-count", payload.active_contracts ?? 0);
            setText("admin-total-users-count", payload.total_users ?? 0);
            setText("admin-open-reports-count", payload.open_reports ?? 0);
        })
        .catch((err) => {
            console.error("Error loading stats:", err);
            setText("admin-pending-approvals-count", "--");
            setText("admin-active-contracts-count", "--");
            setText("admin-total-users-count", "--");
            setText("admin-open-reports-count", "--");
        });
}

function loadPendingProfessionals() {
    return fetchJson("/api/admin/professionals/pending")
        .then((payload) => {
            renderPendingProfessionals(toArray(payload));
        })
        .catch(() => {
            renderPendingProfessionalsError();
        });
}

function renderPendingProfessionals(professionals) {
    const area = document.getElementById("admin-pending-professionals-area");

    if (!area) {
        return;
    }

    if (!professionals.length) {
        area.innerHTML = '<div class="alert alert-light border mb-0">No pending professionals.</div>';
        return;
    }

    const rows = professionals
        .map((pro) => {
            const photo = pro.profile_photo 
                ? `/storage/${pro.profile_photo}` 
                : "/images/user1.jpg";
            
            return `
                <tr data-professional-id="${pro.id}">
                    <td>
                        <img src="${photo}" alt="Photo" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                    </td>
                    <td class="fw-semibold">${pro.name || 'N/A'}</td>
                    <td>${pro.email || 'N/A'}</td>
                    <td>${pro.skill || 'N/A'}</td>
                    <td>${pro.location || 'N/A'}</td>
                    <td>
                        ${pro.cv 
                            ? `<a href="/storage/${pro.cv}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file me-1"></i> View</a>`
                            : '<span class="text-muted small">N/A</span>'
                        }
                    </td>
                    <td>
                        ${pro.certificate 
                            ? `<a href="/storage/${pro.certificate}" target="_blank" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-certificate me-1"></i> View</a>`
                            : '<span class="text-muted small">N/A</span>'
                        }
                    </td>
                    <td>
                        ${pro.id_card 
                            ? `<a href="/storage/${pro.id_card}" target="_blank" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-id-card me-1"></i> View</a>`
                            : '<span class="text-muted small">N/A</span>'
                        }
                    </td>
                    <td>
                        <span class="badge bg-warning">Pending</span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-success" onclick="window.approveProfessional(${pro.id})" data-testid="admin-approve-professional-${pro.id}">
                                <i class="fa-solid fa-check me-1"></i>Approve
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="window.rejectProfessional(${pro.id})" data-testid="admin-reject-professional-${pro.id}">
                                <i class="fa-solid fa-times me-1"></i>Reject
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        })
        .join("");

    area.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Photo</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Skill</th>
                        <th scope="col">Location</th>
                        <th scope="col">CV</th>
                        <th scope="col">Certificate</th>
                        <th scope="col">ID</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function renderPendingProfessionalsError() {
    const area = document.getElementById("admin-pending-professionals-area");

    if (area) {
        area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load pending professionals.</div>';
    }
}

function approveProfessional(id) {
    showAdminActionModal(
        'Approve Professional',
        'Are you sure you want to approve this professional?',
        'success',
        () => {
            postJson(`/api/admin/professionals/${id}/approve`)
                .then(() => {
                    removePendingRow(id);
                    loadAdminStats();
                })
                .catch((err) => {
                    alert(err.message || "Failed to approve professional.");
                });
        }
    );
}

function rejectProfessional(id) {
    showAdminActionModal(
        'Reject Professional',
        'Are you sure you want to reject this professional?',
        'danger',
        () => {
            postJson(`/api/admin/professionals/${id}/reject`)
                .then(() => {
                    removePendingRow(id);
                    loadAdminStats();
                })
                .catch((err) => {
                    alert(err.message || "Failed to reject professional.");
                });
        }
    );
}

function suspendProfessional(id) {
    showAdminActionModal(
        'Suspend Professional',
        'Are you sure you want to suspend this professional?',
        'dark',
        () => {
            postJson(`/api/admin/users/${id}/suspend`)
                .then(() => {
                    loadAllProfessionals();
                    loadAdminStats();
                })
                .catch((err) => {
                    alert(err.message || "Failed to suspend professional.");
                });
        }
    );
}

function unsuspendProfessional(id) {
    showAdminActionModal(
        'Unsuspend Professional',
        'Are you sure you want to unsuspend this professional?',
        'success',
        () => {
            postJson(`/api/admin/users/${id}/unsuspend`)
                .then(() => {
                    loadAllProfessionals();
                    loadAdminStats();
                })
                .catch((err) => {
                    alert(err.message || "Failed to unsuspend professional.");
                });
        }
    );
}

function removePendingRow(id) {
    const row = document.querySelector(`tr[data-professional-id="${id}"]`);
    if (row) {
        row.remove();
    }
    const tbody = document.querySelector('#admin-pending-professionals-area tbody');
    if (tbody && !tbody.children.length) {
        const area = document.getElementById('admin-pending-professionals-area');
        area.innerHTML = '<div class="alert alert-light border mb-0">No pending professionals.</div>';
    }
}

function showAdminActionModal(title, message, btnClass, onConfirm) {
    const modalEl = document.getElementById('admin-action-modal');
    const titleEl = document.getElementById('admin-action-modal-title');
    const bodyEl = document.getElementById('admin-action-modal-body');
    const confirmBtn = document.getElementById('admin-action-confirm-btn');

    titleEl.textContent = title;
    bodyEl.textContent = message;
    confirmBtn.className = `btn btn-${btnClass}`;
    confirmBtn.textContent = 'Confirm';

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    confirmBtn.onclick = () => {
        modal.hide();
        onConfirm();
    };
}

function viewDocument(type, id) {
    window.open(`/api/admin/professionals/${id}/document/${type}`, '_blank');
}

function initializeAdminSidebar() {
    const sidebarItems = document.querySelectorAll('.admin-sidebar-item');
    const sidebarChildren = document.querySelectorAll('.admin-sidebar-child');

    let currentOpen = null;

    sidebarItems.forEach(item => {
        item.addEventListener('click', function() {
            const toggleId = this.dataset.toggle;
            const view = this.dataset.view;

            if (toggleId) {
                const children = document.getElementById(toggleId);
                const icon = this.querySelector('.admin-collapse-icon');

                if (currentOpen && currentOpen !== children) {
                    currentOpen.classList.remove('show');
                    const prevItem = document.querySelector(`[data-toggle="${currentOpen.id}"]`);
                    if (prevItem) {
                        prevItem.querySelector('.admin-collapse-icon').classList.remove('rotated');
                    }
                }

                if (children) {
                    children.classList.toggle('show');
                    icon.classList.toggle('rotated');
                    currentOpen = children.classList.contains('show') ? children : null;
                }
            }

            if (view) {
                loadAdminSection(view);
            }
        });
    });

    sidebarChildren.forEach(child => {
        child.addEventListener('click', function(e) {
            e.stopPropagation();
            const view = this.dataset.view;

            document.querySelectorAll('.admin-sidebar-child').forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            if (view) {
                loadAdminSection(view);
            }
        });
    });
}

function loadAdminSection(view) {
    document.querySelectorAll('.admin-sidebar-item').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.admin-sidebar-child').forEach(child => child.classList.remove('active'));

    const item = document.querySelector(`.admin-sidebar-item[data-view="${view}"]`);
    const child = document.querySelector(`.admin-sidebar-child[data-view="${view}"]`);

    if (item) item.classList.add('active');
    if (child) child.classList.add('active');

    const pendingSection = document.getElementById('pending-professionals-section');
    const usersSection = document.getElementById('users-section');
    const jobsSection = document.getElementById('jobs-section');
    const contractsSection = document.getElementById('contracts-section');
    const reportsSection = document.getElementById('reports-section');
    const paymentsSection = document.getElementById('payments-section');
    const plansSection = document.getElementById('plans-section');
    const allProfessionalsSection = document.getElementById('all-professionals-section');
    
    const contentArea = document.getElementById('admin-content-area');
    
    const sections = {
        'all-users': { title: 'All Users', fetch: loadUsers },
        'suspended-users': { title: 'Suspended Users', fetch: loadSuspendedUsers },
        'deleted-users': { title: 'Deleted Users', fetch: loadDeletedUsers },
        'all-professionals': { title: 'All Professionals', fetch: loadAllProfessionals },
        'pending-professionals': { title: 'Pending Approvals', fetch: loadPendingProfessionals },
        'jobs-view': { title: 'Jobs', fetch: loadJobs },
        'contracts-view': { title: 'Contracts', fetch: loadContracts },
        'reports': { title: 'All Reports', fetch: loadReports },
        'resolved-reports': { title: 'Resolved Reports', fetch: loadResolvedReports },
        'all-payments': { title: 'All Payments', fetch: loadAllPayments },
        'pending-payments': { title: 'Pending Payments', fetch: loadPendingPayments },
        'plans': { title: 'Subscription Plans', fetch: loadPlans },
        'general-settings': { title: 'General Settings', fetch: loadGeneralSettings },
        'system-settings': { title: 'System Settings', fetch: loadSystemSettings }
    };

    if (sections[view]) {
        hideAllAdminSections();
        if (['all-users', 'suspended-users', 'deleted-users'].includes(view) && usersSection) {
            usersSection.style.display = 'block';
            document.getElementById('users-section-title').textContent = sections[view].title;
        } else if (view === 'all-professionals' && allProfessionalsSection) {
            allProfessionalsSection.style.display = 'block';
        } else if (view === 'pending-professionals' && pendingSection) {
            pendingSection.style.display = 'block';
            if (contentArea) { contentArea.innerHTML = ''; contentArea.appendChild(pendingSection); }
        } else if (['jobs-view'].includes(view) && jobsSection) {
            jobsSection.style.display = 'block';
        } else if (['contracts-view'].includes(view) && contractsSection) {
            contractsSection.style.display = 'block';
        } else if (['reports', 'resolved-reports'].includes(view) && reportsSection) {
            reportsSection.style.display = 'block';
        } else if (['all-payments', 'pending-payments'].includes(view) && paymentsSection) {
            paymentsSection.style.display = 'block';
            const titleEl = document.getElementById('payments-section-title');
            const subtitleEl = document.getElementById('payments-section-subtitle');
            if (titleEl) titleEl.textContent = sections[view].title;
            if (subtitleEl) {
                subtitleEl.textContent = view === 'pending-payments'
                    ? 'Pending subscriptions waiting for payment completion.'
                    : 'Search and audit completed payment transactions.';
            }
        } else if (['plans'].includes(view) && plansSection) {
            plansSection.style.display = 'block';
        }
        return sections[view].fetch();
    } else {
        hideAllAdminSections();
        return Promise.resolve();
    }
}

function loadAllProfessionals() { fetchJson("/api/admin/professionals").then(p => renderProfessionalsTable(toArray(p))).catch(renderProfessionalsTableError); }
function renderProfessionalsTable(pros) {
    const area = document.getElementById('all-professionals-table-area');
    if (!area) return;
    if (!pros.length) { area.innerHTML = '<div class="alert alert-light border mb-0">No professionals found.</div>'; return; }
    
    const rows = pros.map(p => {
        const statusBadge = p.status === 'approved' 
            ? '<span class="badge bg-success">Approved</span>'
            : p.status === 'rejected'
                ? '<span class="badge bg-danger">Rejected</span>'
                : '<span class="badge bg-warning">Pending</span>';
        
        const suspendedBadge = p.is_suspended 
            ? '<span class="badge bg-dark ms-1">Suspended</span>' 
            : '';

        return `<tr data-professional-id="${p.id}">
            <td class="fw-semibold">${p.name || 'N/A'}</td>
            <td>${p.email || 'N/A'}</td>
            <td><span class="badge bg-info">Professional</span></td>
            <td>${statusBadge} ${suspendedBadge}</td>
            <td>${p.is_suspended 
                ? `<button type="button" class="btn btn-sm btn-success" onclick="window.unsuspendProfessional(${p.id})"><i class="fa-solid fa-check me-1"></i>Unsuspend</button>`
                : `<button type="button" class="btn btn-sm btn-warning" onclick="window.suspendProfessional(${p.id})"><i class="fa-solid fa-ban me-1"></i>Suspend</button>`
            }</td>
        </tr>`;
    }).join('');

    area.innerHTML = `<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody>${rows}</tbody></table></div>`;
}
function renderProfessionalsTableError() {
    const area = document.getElementById('all-professionals-table-area');
    if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load professionals.</div>';
}

function loadResolvedReports() { fetchJson("/api/admin/reports?status=resolved").then(p => { reportsData = Array.isArray(p) ? p : []; renderReportsTable(reportsData, true); }).catch(renderReportsTableError); }
let currentPaymentsView = 'all-payments';
let paymentFilterState = { q: '', plan_id: '', date_from: '', date_to: '' };

function formatPaymentMoney(value, currency = 'ETB') {
    const amount = Number(value || 0);
    return `${amount.toLocaleString()} ${currency}`;
}

function renderPaymentsSummary(stats, isPendingView) {
    if (isPendingView) {
        return `
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #ffc107 !important;">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small mb-1">Pending Items</p>
                            <h4 class="mb-0">${stats.pending_count ?? 0}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #fd7e14 !important;">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small mb-1">Expected Revenue</p>
                            <h4 class="mb-0">${formatPaymentMoney(stats.expected_revenue ?? 0)}</h4>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    return `
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #198754 !important;">
                    <div class="card-body">
                        <p class="text-muted text-uppercase small mb-1">Total Revenue</p>
                        <h5 class="mb-0">${formatPaymentMoney(stats.total_revenue ?? 0)}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #0d6efd !important;">
                    <div class="card-body">
                        <p class="text-muted text-uppercase small mb-1">Payments</p>
                        <h5 class="mb-0">${stats.total_payments ?? 0}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #6f42c1 !important;">
                    <div class="card-body">
                        <p class="text-muted text-uppercase small mb-1">Unique Payers</p>
                        <h5 class="mb-0">${stats.unique_payers ?? 0}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #20c997 !important;">
                    <div class="card-body">
                        <p class="text-muted text-uppercase small mb-1">Today Revenue</p>
                        <h5 class="mb-0">${formatPaymentMoney(stats.today_revenue ?? 0)}</h5>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderPaymentsFilters(plans, isPendingView) {
    const planOptions = toArray(plans).map((plan) => (
        `<option value="${plan.id}" ${String(paymentFilterState.plan_id) === String(plan.id) ? 'selected' : ''}>${plan.name}</option>`
    )).join('');

    return `
        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="${isPendingView ? 'col-md-8' : 'col-md-4'}">
                        <input type="text" id="payments-filter-q" class="form-control" placeholder="Search tx_ref, name, email" value="${paymentFilterState.q || ''}">
                    </div>
                    ${isPendingView ? '' : `
                        <div class="col-md-2">
                            <select id="payments-filter-plan" class="form-select">
                                <option value="">All plans</option>
                                ${planOptions}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="payments-filter-date-from" class="form-control" value="${paymentFilterState.date_from || ''}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="payments-filter-date-to" class="form-control" value="${paymentFilterState.date_to || ''}">
                        </div>
                    `}
                    <div class="${isPendingView ? 'col-md-4' : 'col-md-2'} d-flex gap-2">
                        <button type="button" class="btn btn-dark w-100" id="payments-apply-filters-btn">
                            <i class="fa-solid fa-filter me-1"></i> Apply
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100" id="payments-clear-filters-btn">Clear</button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderPaymentsTable(payments, isPendingView) {
    if (!payments.length) {
        return `<div class="alert alert-light border mb-0">${isPendingView ? 'No pending payments found.' : 'No payments found.'}</div>`;
    }

    const rows = payments.map((payment) => {
        const statusBadge = (payment.status || '').toLowerCase() === 'pending'
            ? '<span class="badge bg-warning text-dark">Pending</span>'
            : '<span class="badge bg-success">Completed</span>';

        return `
            <tr>
                <td class="fw-semibold">${payment.tx_ref || 'N/A'}</td>
                <td>${payment.user_name || 'N/A'}</td>
                <td>${payment.user_email || 'N/A'}</td>
                <td>${payment.plan_name || 'N/A'}</td>
                <td>${formatPaymentMoney(payment.amount, payment.currency || 'ETB')}</td>
                <td>${statusBadge}</td>
                <td>${new Date(payment.processed_at || payment.created_at).toLocaleString()}</td>
            </tr>
        `;
    }).join('');

    return `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Tx Ref</th>
                        <th scope="col">Client</th>
                        <th scope="col">Email</th>
                        <th scope="col">Plan</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">${isPendingView ? 'Created At' : 'Processed At'}</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function bindPaymentsFilters() {
    const applyBtn = document.getElementById('payments-apply-filters-btn');
    const clearBtn = document.getElementById('payments-clear-filters-btn');

    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            paymentFilterState.q = document.getElementById('payments-filter-q')?.value?.trim() || '';
            paymentFilterState.plan_id = document.getElementById('payments-filter-plan')?.value || '';
            paymentFilterState.date_from = document.getElementById('payments-filter-date-from')?.value || '';
            paymentFilterState.date_to = document.getElementById('payments-filter-date-to')?.value || '';
            window.reloadAdminPaymentsView();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            paymentFilterState = { q: '', plan_id: '', date_from: '', date_to: '' };
            window.reloadAdminPaymentsView();
        });
    }
}

function renderPaymentsView(payload, isPendingView) {
    const area = document.getElementById('payments-table-area');
    if (!area) return;

    const plans = isPendingView ? [] : toArray(payload?.plans);
    const stats = payload?.stats || {};
    const payments = toArray(payload?.payments);

    area.innerHTML = `
        ${renderPaymentsSummary(stats, isPendingView)}
        ${renderPaymentsFilters(plans, isPendingView)}
        ${renderPaymentsTable(payments, isPendingView)}
    `;

    bindPaymentsFilters();
}

function loadAllPayments() {
    currentPaymentsView = 'all-payments';
    const query = new URLSearchParams();
    if (paymentFilterState.q) query.set('q', paymentFilterState.q);
    if (paymentFilterState.plan_id) query.set('plan_id', paymentFilterState.plan_id);
    if (paymentFilterState.date_from) query.set('date_from', paymentFilterState.date_from);
    if (paymentFilterState.date_to) query.set('date_to', paymentFilterState.date_to);

    return fetchJson(`/api/admin/payments${query.toString() ? `?${query.toString()}` : ''}`)
        .then((payload) => {
            renderPaymentsView(payload, false);
        })
        .catch(() => {
            const area = document.getElementById('payments-table-area');
            if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load payments.</div>';
        });
}

function loadPendingPayments() {
    currentPaymentsView = 'pending-payments';
    const query = new URLSearchParams();
    if (paymentFilterState.q) query.set('q', paymentFilterState.q);

    return fetchJson(`/api/admin/payments/pending${query.toString() ? `?${query.toString()}` : ''}`)
        .then((payload) => {
            renderPaymentsView(payload, true);
        })
        .catch(() => {
            const area = document.getElementById('payments-table-area');
            if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load pending payments.</div>';
        });
}

function reloadAdminPaymentsView() {
    if (currentPaymentsView === 'pending-payments') {
        return loadPendingPayments();
    }
    return loadAllPayments();
}
function loadGeneralSettings() { 
    const area = document.getElementById('users-table-area');
    if (area) area.innerHTML = '<div class="alert alert-info mb-0">General settings section coming soon.</div>';
}
function loadSystemSettings() { 
    const area = document.getElementById('users-table-area');
    if (area) area.innerHTML = '<div class="alert alert-info mb-0">System settings section coming soon.</div>';
}

function toggleAdminDarkMode() {
    console.log("Dark mode toggle clicked!");
    document.body.classList.toggle('admin-dashboard-dark');
    
    // Save preference
    const isDark = document.body.classList.contains('admin-dashboard-dark');
    localStorage.setItem('admin_dark_mode', isDark ? '1' : '0');
    
    console.log("Dark mode active:", isDark);
}

// Load saved dark mode preference on page load
function loadAdminDarkModePreference() {
    const savedPref = localStorage.getItem('admin_dark_mode');
    if (savedPref === '1') {
        document.body.classList.add('admin-dashboard-dark');
    }
}

function hideAllAdminSections() {
    document.querySelectorAll('.admin-content-section').forEach(el => el.style.display = 'none');
}

function setDefaultAdminSection() {
    const defaultMenu = document.getElementById('professionals-menu');
    const defaultToggle = document.querySelector('.admin-sidebar-item[data-toggle="professionals-menu"]');

    if (defaultMenu) {
        defaultMenu.classList.add('show');
    }

    if (defaultToggle) {
        const icon = defaultToggle.querySelector('.admin-collapse-icon');
        if (icon) {
            icon.classList.add('rotated');
        }
    }

    return loadAdminSection('pending-professionals');
}

function setAdminDashboardLoading(isLoading) {
    const loader = document.getElementById("admin-dashboard-loader");

    if (loader) {
        loader.classList.toggle("d-none", !isLoading);
    }
}

function loadUsers() {
    fetchJson("/api/admin/users")
        .then((payload) => {
            renderUsersTable(toArray(payload), 'all');
        })
        .catch(() => {
            renderUsersTableError();
        });
}

function loadSuspendedUsers() {
    fetchJson("/api/admin/users/suspended")
        .then((payload) => {
            renderUsersTable(toArray(payload), 'suspended');
        })
        .catch(() => {
            renderUsersTableError();
        });
}

function loadDeletedUsers() {
    fetchJson("/api/admin/users/deleted")
        .then((payload) => {
            renderUsersTable(toArray(payload), 'deleted');
        })
        .catch(() => {
            renderUsersTableError();
        });
}

function renderUsersTable(users, type) {
    const area = document.getElementById('users-table-area');
    if (!area) return;

    if (!users.length) {
        area.innerHTML = '<div class="alert alert-light border mb-0">No users found.</div>';
        return;
    }

    const rows = users.map(user => {
        const statusBadge = user.deleted_at 
            ? '<span class="badge bg-danger">Deleted</span>'
            : user.is_suspended 
                ? '<span class="badge bg-warning">Suspended</span>'
                : '<span class="badge bg-success">Active</span>';
        
        const roleBadge = user.role === 'admin' 
            ? '<span class="badge bg-primary">Admin</span>'
            : user.role === 'professional'
                ? '<span class="badge bg-info">Professional</span>'
                : '<span class="badge bg-secondary">Client</span>';

        let actionBtn = '';
        if (type === 'all') {
            if (!user.deleted_at && !user.is_suspended) {
                actionBtn = `<button type="button" class="btn btn-sm btn-warning" onclick="suspendUser(${user.id})" data-testid="admin-suspend-user-${user.id}"><i class="fa-solid fa-ban me-1"></i>Suspend</button>`;
            } else if (user.is_suspended && !user.deleted_at) {
                actionBtn = `<button type="button" class="btn btn-sm btn-success" onclick="unsuspendUser(${user.id})" data-testid="admin-unsuspend-user-${user.id}"><i class="fa-solid fa-check me-1"></i>Unsuspend</button>`;
            }
        } else if (type === 'suspended') {
            actionBtn = `<button type="button" class="btn btn-sm btn-success" onclick="unsuspendUser(${user.id})" data-testid="admin-unsuspend-user-${user.id}"><i class="fa-solid fa-check me-1"></i>Unsuspend</button>`;
        } else if (type === 'deleted') {
            actionBtn = `<button type="button" class="btn btn-sm btn-info" onclick="restoreUser(${user.id})"><i class="fa-solid fa-trash-restore me-1"></i>Restore</button>`;
        }

        return `
            <tr data-user-id="${user.id}">
                <td class="fw-semibold">${user.name ?? 'N/A'}</td>
                <td>${user.email ?? 'N/A'}</td>
                <td>${roleBadge}</td>
                <td>${statusBadge}</td>
                <td>${actionBtn}</td>
            </tr>
        `;
    }).join('');

    area.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function renderUsersTableError() {
    const area = document.getElementById('users-table-area');
    if (area) {
        area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load users.</div>';
    }
}

function removeUserRow(id) {
    const row = document.querySelector(`tr[data-user-id="${id}"]`);
    if (row) row.remove();
}

function showUsersActionModal(title, message, btnClass, onConfirm) {
    const modalEl = document.getElementById('users-action-modal');
    const titleEl = document.getElementById('users-action-modal-title');
    const bodyEl = document.getElementById('users-action-modal-body');
    const confirmBtn = document.getElementById('users-action-confirm-btn');

    titleEl.textContent = title;
    bodyEl.textContent = message;
    confirmBtn.className = `btn btn-${btnClass}`;
    confirmBtn.textContent = 'Confirm';

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    confirmBtn.onclick = () => {
        modal.hide();
        onConfirm();
    };
}

function suspendUser(id) {
    showUsersActionModal(
        'Suspend User',
        'Are you sure you want to suspend this user?',
        'warning',
        () => {
            postJson(`/api/admin/users/${id}/suspend`)
                .then(() => {
                    removeUserRow(id);
                    loadAdminStats();
                })
                .catch(err => alert(err.message || 'Failed to suspend user.'));
        }
    );
}

function unsuspendUser(id) {
    showUsersActionModal(
        'Unsuspend User',
        'Are you sure you want to unsuspend this user?',
        'success',
        () => {
            postJson(`/api/admin/users/${id}/unsuspend`)
                .then(() => {
                    removeUserRow(id);
                    loadAdminStats();
                })
                .catch(err => alert(err.message || 'Failed to unsuspend user.'));
        }
    );
}

function restoreUser(id) {
    showUsersActionModal(
        'Restore User',
        'Are you sure you want to restore this user?',
        'info',
        () => {
            postJson(`/api/admin/users/${id}/restore`)
                .then(() => {
                    removeUserRow(id);
                    loadAdminStats();
                })
                .catch(err => alert(err.message || 'Failed to restore user.'));
        }
    );
}

function loadJobs() {
    fetchJson("/api/admin/jobs")
        .then((payload) => {
            jobsData = Array.isArray(payload) ? payload : (payload.jobs || []);
            renderJobsTable(jobsData);
        })
        .catch(() => {
            renderJobsTableError();
        });
}

function renderJobsTable(jobs) {
    const area = document.getElementById('jobs-table-area');
    if (!area) return;

    if (!jobs.length) {
        area.innerHTML = '<div class="alert alert-light border mb-0">No jobs found.</div>';
        return;
    }

    const rows = jobs.map(job => {
        const statusBadge = job.status === 'open' 
            ? '<span class="badge bg-success">Open</span>'
            : job.status === 'assigned'
                ? '<span class="badge bg-primary">Assigned</span>'
                : job.status === 'completed'
                    ? '<span class="badge bg-info">Completed</span>'
                    : '<span class="badge bg-danger">Cancelled</span>';

        const appsCountClass = job.applications_count === 0 ? 'text-danger fw-bold' : 'text-success';
        const canCancel = job.status === 'open';
        const cancelBtn = canCancel 
            ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="window.cancelJob(${job.id})"><i class="fa-solid fa-ban me-1"></i>Cancel</button>`
            : `<button type="button" class="btn btn-sm btn-secondary" disabled><i class="fa-solid fa-ban me-1"></i>Cancel</button>`;

        return `
            <tr data-job-id="${job.id}">
                <td class="fw-semibold">${job.title || 'N/A'}</td>
                <td>${job.client?.name || 'N/A'}</td>
                <td>${job.budget ? '$' + job.budget : 'N/A'}</td>
                <td>${job.skill || 'N/A'}</td>
                <td>${job.location || 'N/A'}</td>
                <td>${statusBadge}</td>
                <td class="${appsCountClass}">${job.applications_count || 0}</td>
                <td>${job.created_at ? new Date(job.created_at).toLocaleDateString() : 'N/A'}</td>
                <td>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.viewJob(${job.id})"><i class="fa-solid fa-eye me-1"></i>View</button>
                        ${cancelBtn}
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    area.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Title</th>
                        <th scope="col">Client</th>
                        <th scope="col">Budget</th>
                        <th scope="col">Skills</th>
                        <th scope="col">Location</th>
                        <th scope="col">Status</th>
                        <th scope="col">Applications</th>
                        <th scope="col">Created</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function renderJobsTableError() {
    const area = document.getElementById('jobs-table-area');
    if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load jobs.</div>';
}

let jobsData = [];
function viewJob(id) {
    const job = jobsData.find(j => j.id === id);
    if (!job) return;

    document.getElementById('job-modal-subtitle').textContent = `Job #${job.id}`;

    const statusBadge = job.status === 'open' 
        ? '<span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check me-1"></i>Open</span>'
        : job.status === 'assigned'
            ? '<span class="badge bg-primary px-3 py-2"><i class="fa-solid fa-user-check me-1"></i>Assigned</span>'
            : job.status === 'completed'
                ? '<span class="badge bg-info px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i>Completed</span>'
                : '<span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-ban me-1"></i>Cancelled</span>';

    const appsHtml = job.applications_count === 0 
        ? `<div class="alert alert-warning d-flex align-items-center mb-0"><i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i><div><strong>No applications</strong><br><small>This job hasn't received any applications yet</small></div></div>`
        : `<div class="alert alert-success d-flex align-items-center mb-0"><i class="fa-solid fa-users me-2 fs-5"></i><div><strong>${job.applications_count} Application(s)</strong><br><small>Professionals have applied to this job</small></div></div>`;

    document.getElementById('job-view-modal-body').innerHTML = `
        <div class="text-center mb-4">
            <h3 class="fw-bold mb-2">${job.title || 'N/A'}</h3>
            ${statusBadge}
        </div>
        ${appsHtml}
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold"><i class="fa-solid fa-user me-2 text-primary"></i>Client Information</div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fa-solid fa-user text-primary fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">${job.client?.name || 'N/A'}</h6>
                                <small class="text-muted">${job.client?.email || 'N/A'}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold"><i class="fa-solid fa-info-circle me-2 text-info"></i>Job Details</div>
                    <div class="card-body">
                        <div class="mb-2"><i class="fa-solid fa-dollar-sign text-success me-2"></i><strong>Budget:</strong> <span class="text-success fw-bold fs-5">${job.budget ? '$' + job.budget : 'N/A'}</span></div>
                        <div class="mb-2"><i class="fa-solid fa-location-dot text-danger me-2"></i><strong>Location:</strong> ${job.location || 'N/A'}</div>
                        <div class="mb-2"><i class="fa-solid fa-code text-warning me-2"></i><strong>Skills:</strong> ${job.skill || 'N/A'}</div>
                        <div><i class="fa-solid fa-calendar text-secondary me-2"></i><strong>Posted:</strong> ${job.created_at ? new Date(job.created_at).toLocaleDateString() : 'N/A'}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-4 border-0 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="fa-solid fa-file-lines me-2 text-dark"></i>Job Description</div>
            <div class="card-body bg-light">
                <p class="mb-0" style="white-space: pre-wrap;">${job.description || 'No description provided.'}</p>
            </div>
        </div>
    `;

    const modal = new bootstrap.Modal(document.getElementById('job-view-modal'));
    modal.show();
}

function cancelJob(id) {
    showJobActionModal(
        'Cancel Job',
        'Are you sure you want to cancel this job? This action cannot be undone.',
        'danger',
        () => {
            postJson(`/api/admin/jobs/${id}/cancel`)
                .then(() => {
                    loadJobs();
                    loadAdminStats();
                })
                .catch(err => alert(err.message || 'Failed to cancel job.'));
        }
    );
}

function showJobActionModal(title, message, btnClass, onConfirm) {
    const modalEl = document.getElementById('job-action-modal');
    const titleEl = document.getElementById('job-action-modal-title');
    const bodyEl = document.getElementById('job-action-modal-body');
    const confirmBtn = document.getElementById('job-action-confirm-btn');

    titleEl.textContent = title;
    bodyEl.textContent = message;
    confirmBtn.className = `btn btn-${btnClass}`;
    confirmBtn.textContent = 'Confirm';

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    confirmBtn.onclick = () => {
        modal.hide();
        onConfirm();
    };
}

function loadContracts() {
    fetchJson("/api/admin/contracts")
        .then((payload) => {
            contractsData = Array.isArray(payload) ? payload : [];
            renderContractsTable(contractsData);
        })
        .catch(() => {
            renderContractsTableError();
        });
}

function renderContractsTable(contracts) {
    const area = document.getElementById('contracts-table-area');
    if (!area) return;

    if (!contracts.length) {
        area.innerHTML = '<div class="alert alert-light border mb-0">No contracts found.</div>';
        return;
    }

    const rows = contracts.map(contract => {
        const statusBadge = contract.status === 'active' 
            ? '<span class="badge bg-success">Active</span>'
            : contract.status === 'pending_completion'
                ? '<span class="badge bg-warning text-dark">Pending Completion</span>'
            : contract.status === 'completed'
                ? '<span class="badge bg-info">Completed</span>'
                : contract.status === 'cancelled'
                    ? '<span class="badge bg-danger">Cancelled</span>'
                    : '<span class="badge bg-warning">Pending</span>';

        const canCancel = contract.status === 'active';

        return `
            <tr data-contract-id="${contract.id}">
                <td class="fw-semibold">#${contract.id}</td>
                <td>${contract.job?.title || 'N/A'}</td>
                <td>${contract.client?.name || 'N/A'}</td>
                <td>${contract.professional?.name || 'N/A'}</td>
                <td>${contract.budget ? '$' + contract.budget : 'N/A'}</td>
                <td>${statusBadge}</td>
                <td>${contract.created_at ? new Date(contract.created_at).toLocaleDateString() : 'N/A'}</td>
                <td>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewContract(${contract.id})"><i class="fa-solid fa-eye me-1"></i>View</button>
                        ${canCancel ? `<button type="button" class="btn btn-sm btn-danger" onclick="forceCancelContract(${contract.id})"><i class="fa-solid fa-ban me-1"></i>Cancel</button>` : `<button type="button" class="btn btn-sm btn-secondary" disabled><i class="fa-solid fa-ban me-1"></i>Cancel</button>`}
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    area.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Job Title</th>
                        <th scope="col">Client</th>
                        <th scope="col">Professional</th>
                        <th scope="col">Budget</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function renderContractsTableError() {
    const area = document.getElementById('contracts-table-area');
    if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load contracts.</div>';
}

let contractsData = [];

function viewContract(id) {
    const contract = contractsData.find(c => c.id === id);
    if (!contract) return;

    document.getElementById('contract-modal-subtitle').textContent = `Contract #${contract.id}`;

    const statusBadge = contract.status === 'active' 
        ? '<span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Active</span>'
        : contract.status === 'pending_completion'
            ? '<span class="badge bg-warning text-dark px-3 py-2"><i class="fa-solid fa-hourglass-half me-1"></i>Pending Completion</span>'
        : contract.status === 'completed'
            ? '<span class="badge bg-info px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i>Completed</span>'
        : '<span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-ban me-1"></i>Cancelled</span>';

    document.getElementById('contract-view-modal-body').innerHTML = `
        <div class="text-center mb-4">
            <h3 class="fw-bold mb-2"><i class="fa-solid fa-file-contract me-2"></i>Contract</h3>
            ${statusBadge}
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-primary text-white fw-bold"><i class="fa-solid fa-user me-2"></i>Client</div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-block">
                                <i class="fa-solid fa-user text-primary fa-2x"></i>
                            </div>
                        </div>
                        <h5 class="text-center mb-1">${contract.client?.name || 'N/A'}</h5>
                        <p class="text-center text-muted mb-1"><i class="fa-solid fa-envelope me-2"></i>${contract.client?.email || 'N/A'}</p>
                        <p class="text-center text-muted mb-0"><i class="fa-solid fa-phone me-2"></i>${contract.client?.phone || 'N/A'}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-success text-white fw-bold"><i class="fa-solid fa-user-tie me-2"></i>Professional</div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-4 d-inline-block">
                                <i class="fa-solid fa-user-tie text-success fa-2x"></i>
                            </div>
                        </div>
                        <h5 class="text-center mb-1">${contract.professional?.name || 'N/A'}</h5>
                        <p class="text-center text-muted mb-1"><i class="fa-solid fa-envelope me-2"></i>${contract.professional?.email || 'N/A'}</p>
                        <p class="text-center text-muted mb-0"><i class="fa-solid fa-phone me-2"></i>${contract.professional?.phone || 'N/A'}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-4 border-0 shadow-sm">
            <div class="card-header bg-dark text-white fw-bold"><i class="fa-solid fa-briefcase me-2"></i>Job Information</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="fw-bold text-primary fs-4">${contract.job?.title || 'N/A'}</div>
                        <small class="text-muted">Job Title</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-success fs-4">${contract.budget ? '$' + contract.budget : 'N/A'}</div>
                        <small class="text-muted">Budget</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-info fs-5">${contract.created_at ? new Date(contract.created_at).toLocaleDateString() : 'N/A'}</div>
                        <small class="text-muted">Created</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-4 border-0 shadow-sm">
            <div class="card-header bg-secondary text-white fw-bold"><i class="fa-solid fa-file-lines me-2"></i>Job Description</div>
            <div class="card-body bg-light">
                <p class="mb-0" style="white-space: pre-wrap;">${contract.job?.description || 'No description provided.'}</p>
            </div>
        </div>
    `;

    const modal = new bootstrap.Modal(document.getElementById('contract-view-modal'));
    modal.show();
}

function forceCancelContract(id) {
    const modalEl = document.getElementById('contract-action-modal');
    const titleEl = document.getElementById('contract-action-modal-title');
    const bodyEl = document.getElementById('contract-action-modal-body');
    const confirmBtn = document.getElementById('contract-action-confirm-btn');

    titleEl.textContent = 'Force Cancel Contract';
    bodyEl.innerHTML = '<div class="alert alert-warning"><i class="fa-solid fa-exclamation-triangle me-2"></i><strong>Warning:</strong> This action should only be used for fraud, disputes, or violations!</div><p class="mb-0">Are you sure you want to force cancel this contract? This cannot be undone.</p>';
    confirmBtn.className = 'btn btn-danger';
    confirmBtn.textContent = 'Force Cancel';

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    confirmBtn.onclick = () => {
        modal.hide();
        postJson(`/api/admin/contracts/${id}/cancel`)
            .then(() => {
                loadContracts();
                loadAdminStats();
            })
            .catch(err => alert(err.message || 'Failed to cancel contract.'));
    };
}

function loadReports() {
    fetchJson("/api/admin/reports")
        .then((payload) => {
            reportsData = Array.isArray(payload) ? payload : [];
            renderReportsTable(reportsData);
        })
        .catch(() => {
            renderReportsTableError();
        });
}

function renderReportsTable(reports, isResolvedView = false) {
    const area = document.getElementById('reports-table-area');
    if (!area) return;

    if (!reports.length) {
        area.innerHTML = '<div class="alert alert-light border mb-0">No reports found.</div>';
        return;
    }

    const rows = reports.map(report => {
        if (isResolvedView) {
            const actionBadge = report.action_taken === 'suspend_user' 
                ? '<span class="badge bg-danger">Suspended User</span>'
                : report.action_taken === 'cancel_contract'
                    ? '<span class="badge bg-warning text-dark">Contract Cancelled</span>'
                    : '<span class="badge bg-secondary">No Action</span>';

            return `
                <tr data-report-id="${report.id}">
                    <td class="fw-semibold">#${report.id}</td>
                    <td>${report.contract ? '#' + report.contract.id : 'N/A'}</td>
                    <td>${report.reporter?.name || 'N/A'}</td>
                    <td>${report.reported?.name || 'N/A'}</td>
                    <td><span class="text-truncate d-inline-block" style="max-width: 120px;">${report.reason || 'N/A'}</span></td>
                    <td>${actionBadge}</td>
                    <td>${report.resolved_at ? new Date(report.resolved_at).toLocaleDateString() : 'N/A'}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewReport(${report.id})"><i class="fa-solid fa-eye me-1"></i>View</button>
                    </td>
                </tr>
            `;
        } else {
            const statusBadge = report.status === 'resolved' 
                ? '<span class="badge bg-success">Resolved</span>'
                : '<span class="badge bg-warning">Pending</span>';

            return `
                <tr data-report-id="${report.id}">
                    <td class="fw-semibold">#${report.id}</td>
                    <td>${report.contract ? '#' + report.contract.id : 'N/A'}</td>
                    <td>${report.reporter?.name || 'N/A'}</td>
                    <td>${report.reported?.name || 'N/A'}</td>
                    <td><span class="text-truncate d-inline-block" style="max-width: 150px;">${report.reason || 'N/A'}</span></td>
                    <td>${statusBadge}</td>
                    <td>${report.created_at ? new Date(report.created_at).toLocaleDateString() : 'N/A'}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewReport(${report.id})"><i class="fa-solid fa-eye me-1"></i>View</button>
                            ${report.status !== 'resolved' ? `<button type="button" class="btn btn-sm btn-success" onclick="openResolveModal(${report.id})"><i class="fa-solid fa-check me-1"></i>Resolve</button>` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }
    }).join('');

    if (isResolvedView) {
        area.innerHTML = `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Contract</th>
                            <th scope="col">Reporter</th>
                            <th scope="col">Reported</th>
                            <th scope="col">Reason</th>
                            <th scope="col">Action Taken</th>
                            <th scope="col">Resolved At</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    } else {
        area.innerHTML = `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Contract</th>
                            <th scope="col">Reporter</th>
                            <th scope="col">Reported</th>
                            <th scope="col">Reason</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }
}

function renderReportsTableError() {
    const area = document.getElementById('reports-table-area');
    if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load reports.</div>';
}

let reportsData = [];

function viewReport(id) {
    const report = reportsData.find(r => r.id === id);
    if (!report) return;

    document.getElementById('report-modal-subtitle').textContent = `Report #${report.id}`;

    const statusBadge = report.status === 'resolved' 
        ? '<span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Resolved</span>'
        : '<span class="badge bg-warning px-3 py-2"><i class="fa-solid fa-clock me-1"></i>Pending</span>';

    const actionTakenHtml = report.action_taken 
        ? `<div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-success text-white fw-bold"><i class="fa-solid fa-gavel me-2"></i>Action Taken</div>
                    <div class="card-body">
                        ${report.action_taken === 'suspend_user' 
                            ? '<span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-ban me-1"></i>Suspended User</span>'
                            : report.action_taken === 'cancel_contract'
                                ? '<span class="badge bg-warning text-dark px-3 py-2"><i class="fa-solid fa-file-circle-xmark me-1"></i>Contract Cancelled</span>'
                                : '<span class="badge bg-secondary px-3 py-2"><i class="fa-solid fa-minus me-1"></i>No Action</span>'}
                        ${report.resolved_at ? `<div class="mt-2 text-muted small"><i class="fa-solid fa-check me-1"></i>Resolved on: ${new Date(report.resolved_at).toLocaleString()}</div>` : ''}
                    </div>
                </div>
            </div>`
        : '';

    document.getElementById('report-view-modal-body').innerHTML = `
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-2">Report #${report.id}</h4>
            ${statusBadge}
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-primary text-white fw-bold"><i class="fa-solid fa-user me-2"></i>Reporter</div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fa-solid fa-user text-primary fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">${report.reporter?.name || 'N/A'}</h6>
                                <small class="text-muted">${report.reporter?.email || 'N/A'}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-danger text-white fw-bold"><i class="fa-solid fa-flag me-2"></i>Reported User</div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fa-solid fa-user-slash text-danger fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">${report.reported?.name || 'N/A'}</h6>
                                <small class="text-muted">${report.reported?.email || 'N/A'}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ${actionTakenHtml}
        </div>
        ${report.contract ? `
        <div class="card mt-4 border-0 shadow-sm">
            <div class="card-header bg-dark text-white fw-bold"><i class="fa-solid fa-file-contract me-2"></i>Contract Information</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="fw-bold text-primary fs-5">#${report.contract.id}</div>
                        <small class="text-muted">Contract ID</small>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold">${report.contract.job?.title || 'N/A'}</div>
                        <small class="text-muted">Job Title</small>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-success">${report.contract.client?.name || 'N/A'}</div>
                        <small class="text-muted">Client</small>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-info">${report.contract.professional?.name || 'N/A'}</div>
                        <small class="text-muted">Professional</small>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
        <div class="card mt-4 border-0 shadow-sm">
            <div class="card-header bg-warning text-dark fw-bold"><i class="fa-solid fa-message me-2"></i>Report Reason</div>
            <div class="card-body bg-light">
                <p class="mb-0" style="white-space: pre-wrap;">${report.reason || 'No reason provided.'}</p>
            </div>
        </div>
        <div class="text-center mt-3 text-muted">
            <small><i class="fa-solid fa-calendar me-1"></i>Reported on: ${report.created_at ? new Date(report.created_at).toLocaleString() : 'N/A'}</small>
        </div>
    `;

    const resolveBtn = document.getElementById('report-resolve-btn');
    if (report.status === 'resolved') {
        resolveBtn.style.display = 'none';
    } else {
        resolveBtn.style.display = 'inline-flex';
        resolveBtn.onclick = () => {
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('report-view-modal'));
            viewModal.hide();
            openResolveModal(report.id);
        };
    }

    const modal = new bootstrap.Modal(document.getElementById('report-view-modal'));
    modal.show();
}

function openResolveModal(id) {
    const report = reportsData.find(r => r.id === id);
    if (!report) return;

    document.getElementById('report-resolve-detail').innerHTML = `
        <div class="alert alert-secondary">
            <strong>Report #${report.id}</strong> - ${report.reported?.name || 'N/A'}
        </div>
    `;
    document.getElementById('report-action-select').value = '';
    document.getElementById('report-resolution-notes').value = '';

    const modal = new bootstrap.Modal(document.getElementById('report-resolve-modal'));
    modal.show();

    document.getElementById('report-resolve-confirm-btn').onclick = () => {
        const action = document.getElementById('report-action-select').value;
        const notes = document.getElementById('report-resolution-notes').value;

        if (!action) {
            alert('Please select an action');
            return;
        }

        postJson(`/api/admin/reports/${id}/resolve`, { action, notes })
            .then(() => {
                modal.hide();
                loadReports();
                loadAdminStats();
                alert('Report resolved successfully');
            })
            .catch(err => alert(err.message || 'Failed to resolve report.'));
    };
}

function loadPlans() {
    fetchJson("/api/admin/plans")
        .then((payload) => {
            renderPlansTable(Array.isArray(payload) ? payload : []);
        })
        .catch(() => {
            renderPlansTableError();
        });
}

function renderPlansTable(plans) {
    const area = document.getElementById('plans-table-area');
    if (!area) return;

    if (!plans.length) {
        area.innerHTML = '<div class="alert alert-light border mb-0">No plans found.</div>';
        return;
    }

    const rows = plans.map(plan => `
        <tr data-plan-id="${plan.id}">
            <td class="fw-semibold">${plan.name || 'N/A'}</td>
            <td><span class="badge bg-success px-3 py-2">$${plan.price ?? 0}</span></td>
            <td><span class="badge bg-warning text-dark px-3 py-2">${plan.job_posts_limit ?? 0} posts</span></td>
            <td><span class="badge ${plan.direct_requests_limit > 0 ? 'bg-primary' : 'bg-secondary'} px-3 py-2">${plan.direct_requests_limit ?? 0} requests</span></td>
            <td><span class="badge bg-info px-3 py-2">${plan.duration_days ?? 0} days</span></td>
            <td>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showEditPlanModal(${plan.id}, '${plan.name}', ${plan.price}, ${plan.job_posts_limit}, ${plan.duration_days}, ${plan.direct_requests_limit || 0})">
                        <i class="fa-solid fa-edit me-1"></i>Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="showDeletePlanModal(${plan.id})">
                        <i class="fa-solid fa-trash me-1"></i>Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    area.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Price</th>
                        <th scope="col">Job Posts</th>
                        <th scope="col">Direct Requests</th>
                        <th scope="col">Duration</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function renderPlansTableError() {
    const area = document.getElementById('plans-table-area');
    if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load plans.</div>';
}

function showCreatePlanModal() {
    document.getElementById('plan-id').value = '';
    document.getElementById('plan-name').value = '';
    document.getElementById('plan-price').value = '';
    document.getElementById('plan-job-limit').value = '';
    document.getElementById('plan-duration').value = '';
    document.getElementById('plan-direct-requests').value = '0';
    document.getElementById('plan-modal-title').innerHTML = '<i class="fa-solid fa-layer-group me-2"></i>Create Plan';
    new bootstrap.Modal(document.getElementById('plan-modal')).show();
}

function showEditPlanModal(id, name, price, jobLimit, duration, directRequests) {
    document.getElementById('plan-id').value = id;
    document.getElementById('plan-name').value = name;
    document.getElementById('plan-price').value = price;
    document.getElementById('plan-job-limit').value = jobLimit;
    document.getElementById('plan-duration').value = duration;
    document.getElementById('plan-direct-requests').value = directRequests || 0;
    document.getElementById('plan-modal-title').innerHTML = '<i class="fa-solid fa-edit me-2"></i>Edit Plan';
    new bootstrap.Modal(document.getElementById('plan-modal')).show();
}

function savePlan() {
    const id = document.getElementById('plan-id').value;
    const name = document.getElementById('plan-name').value;
    const price = parseFloat(document.getElementById('plan-price').value);
    const job_posts_limit = parseInt(document.getElementById('plan-job-limit').value);
    const duration_days = parseInt(document.getElementById('plan-duration').value);
    const direct_requests_limit = parseInt(document.getElementById('plan-direct-requests').value) || 0;

    if (!name || !price || !job_posts_limit || !duration_days) {
        alert('Please fill all fields');
        return;
    }

    const url = id ? `/api/admin/plans/${id}` : '/api/admin/plans';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('token') },
        body: JSON.stringify({ name, price, job_posts_limit, duration_days, direct_requests_limit })
    })
    .then(res => res.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('plan-modal')).hide();
        loadPlans();
    })
    .catch(err => alert(err.message || 'Failed to save plan.'));
}

function showDeletePlanModal(id) {
    document.getElementById('delete-plan-id').value = id;
    new bootstrap.Modal(document.getElementById('plan-delete-modal')).show();
}

function confirmDeletePlan() {
    const id = document.getElementById('delete-plan-id').value;
    
    fetch(`/api/admin/plans/${id}`, {
        method: 'DELETE',
        headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
    })
    .then(res => res.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('plan-delete-modal')).hide();
        if (data.message && data.message.includes('Cannot delete')) {
            alert(data.message);
        } else {
            loadPlans();
        }
    })
    .catch(err => alert(err.message || 'Failed to delete plan.'));
}


    // Admin dashboard entrypoint (called after DOM is ready).
    function init() {
        setAdminDashboardLoading(true);
        initializeAdminSidebar();
        loadAdminDarkModePreference();
        Promise.allSettled([
            loadAdminStats(),
            setDefaultAdminSection(),
        ]).finally(() => {
            setAdminDashboardLoading(false);
        });
    }

    window.EasyHireAdmin = { init };

    // Required globals for existing inline onclick handlers and links.
    window.loadAdminStats = loadAdminStats;
    window.loadPendingProfessionals = loadPendingProfessionals;
    window.toggleAdminDarkMode = toggleAdminDarkMode;
    window.loadUsers = loadUsers;
    window.loadSuspendedUsers = loadSuspendedUsers;
    window.loadDeletedUsers = loadDeletedUsers;
    window.loadAllProfessionals = loadAllProfessionals;
    window.suspendUser = suspendUser;
    window.unsuspendUser = unsuspendUser;
    window.restoreUser = restoreUser;
    window.approveProfessional = approveProfessional;
    window.rejectProfessional = rejectProfessional;
    window.suspendProfessional = suspendProfessional;
    window.unsuspendProfessional = unsuspendProfessional;
    window.loadJobs = loadJobs;
    window.viewJob = viewJob;
    window.cancelJob = cancelJob;
    window.loadContracts = loadContracts;
    window.viewContract = viewContract;
    window.forceCancelContract = forceCancelContract;
    window.loadReports = loadReports;
    window.loadResolvedReports = loadResolvedReports;
    window.loadAllPayments = loadAllPayments;
    window.loadPendingPayments = loadPendingPayments;
    window.reloadAdminPaymentsView = reloadAdminPaymentsView;
    window.viewReport = viewReport;
    window.openResolveModal = openResolveModal;
    window.loadPlans = loadPlans;
    window.showCreatePlanModal = showCreatePlanModal;
    window.showEditPlanModal = showEditPlanModal;
    window.savePlan = savePlan;
    window.showDeletePlanModal = showDeletePlanModal;
    window.confirmDeletePlan = confirmDeletePlan;
})();
});
