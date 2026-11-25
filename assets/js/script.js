/**
 * College Event Management System
 * Interactive Client-Side JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // 1. Password Visibility Toggle
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId) || this.previousElementSibling;
            if (input && (input.type === 'password' || input.type === 'text')) {
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                }
            }
        });
    });

    // 2. Mobile Sidebar Toggle for Dashboards
    const sidebarToggle = document.getElementById('sidebarToggle');
    const dashboardSidebar = document.querySelector('.dashboard-sidebar');
    if (sidebarToggle && dashboardSidebar) {
        sidebarToggle.addEventListener('click', function () {
            dashboardSidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 992) {
                if (!dashboardSidebar.contains(e.target) && !sidebarToggle.contains(e.target) && dashboardSidebar.classList.contains('show')) {
                    dashboardSidebar.classList.remove('show');
                }
            }
        });
    }

    // 3. Client-Side Form Validation (Bootstrap style)
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            // Password confirmation check if applicable
            const pass = form.querySelector('input[name="password"]');
            const confirmPass = form.querySelector('input[name="confirm_password"]');
            if (pass && confirmPass && pass.value !== confirmPass.value) {
                confirmPass.setCustomValidity('Passwords do not match');
            } else if (confirmPass) {
                confirmPass.setCustomValidity('');
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // 4. Live Events Search & Category Filtering
    const searchInput = document.getElementById('eventSearchInput');
    const categoryButtons = document.querySelectorAll('.event-filter-btn');
    const eventCards = document.querySelectorAll('.event-item');
    const noEventsMsg = document.getElementById('noEventsFound');

    function filterEvents() {
        if (!eventCards.length) return;
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const activeBtn = document.querySelector('.event-filter-btn.active');
        const selectedCat = activeBtn ? activeBtn.getAttribute('data-category') : 'all';

        let visibleCount = 0;
        eventCards.forEach(card => {
            const title = card.getAttribute('data-title') ? card.getAttribute('data-title').toLowerCase() : '';
            const desc = card.getAttribute('data-desc') ? card.getAttribute('data-desc').toLowerCase() : '';
            const venue = card.getAttribute('data-venue') ? card.getAttribute('data-venue').toLowerCase() : '';
            const category = card.getAttribute('data-category');

            const matchesQuery = !query || title.includes(query) || desc.includes(query) || venue.includes(query);
            const matchesCat = (selectedCat === 'all' || category === selectedCat);

            if (matchesQuery && matchesCat) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noEventsMsg) {
            noEventsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterEvents);
    }

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            categoryButtons.forEach(b => b.classList.remove('active', 'btn-primary'));
            categoryButtons.forEach(b => b.classList.add('btn-outline-primary'));
            this.classList.add('active', 'btn-primary');
            this.classList.remove('btn-outline-primary');
            filterEvents();
        });
    });

    // 5. Delete & Destructive Action Confirmations
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 6. Auto-dismiss Flash Alerts after 5 seconds
    const autoAlerts = document.querySelectorAll('.alert-dismissible');
    autoAlerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
            if (bsAlert) {
                try {
                    bsAlert.close();
                } catch (err) {}
            }
        }, 6000);
    });

    // 7. Event Registration Modal Dynamic Data Populate
    const registerModal = document.getElementById('registerModal');
    if (registerModal) {
        registerModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            const eventId = button.getAttribute('data-event-id');
            const eventName = button.getAttribute('data-event-name');
            const eventDate = button.getAttribute('data-event-date');
            const eventVenue = button.getAttribute('data-event-venue');
            const eventSeats = button.getAttribute('data-event-seats');

            const inputId = registerModal.querySelector('#modalEventId');
            const spanName = registerModal.querySelector('#modalEventName');
            const spanDate = registerModal.querySelector('#modalEventDate');
            const spanVenue = registerModal.querySelector('#modalEventVenue');
            const spanSeats = registerModal.querySelector('#modalEventSeats');

            if (inputId) inputId.value = eventId;
            if (spanName) spanName.textContent = eventName;
            if (spanDate) spanDate.textContent = eventDate;
            if (spanVenue) spanVenue.textContent = eventVenue;
            if (spanSeats) spanSeats.textContent = eventSeats;
        });
    }

    // 8. Event Details Modal Dynamic Data Populate
    const detailsModal = document.getElementById('eventDetailsModal');
    if (detailsModal) {
        detailsModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            const title = button.getAttribute('data-title');
            const desc = button.getAttribute('data-desc');
            const cat = button.getAttribute('data-category');
            const date = button.getAttribute('data-date');
            const time = button.getAttribute('data-time');
            const venue = button.getAttribute('data-venue');
            const seats = button.getAttribute('data-seats');
            const image = button.getAttribute('data-image');

            detailsModal.querySelector('#detailTitle').textContent = title;
            detailsModal.querySelector('#detailDesc').textContent = desc;
            detailsModal.querySelector('#detailCategory').textContent = cat;
            detailsModal.querySelector('#detailDate').textContent = date;
            detailsModal.querySelector('#detailTime').textContent = time;
            detailsModal.querySelector('#detailVenue').textContent = venue;
            detailsModal.querySelector('#detailSeats').textContent = seats;
            const imgEl = detailsModal.querySelector('#detailImage');
            if (imgEl && image) {
                imgEl.src = image;
            }
        });
    }
});

// Helper for quick print pass
function printTicketPass(elementId) {
    const printContent = document.getElementById(elementId).innerHTML;
    const originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    window.location.reload();
}

