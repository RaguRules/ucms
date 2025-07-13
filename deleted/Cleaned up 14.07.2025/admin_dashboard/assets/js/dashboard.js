<?php
// Main JavaScript for the admin dashboard

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const wrapper = document.querySelector('.wrapper');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            wrapper.classList.toggle('sidebar-collapsed');
            
            // Save state to localStorage
            const isCollapsed = wrapper.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed);
        });
        
        // Check localStorage for sidebar state
        const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (isCollapsed) {
            wrapper.classList.add('sidebar-collapsed');
        }
    }
    
    // Submenu Toggle
    const hasSubmenu = document.querySelectorAll('.has-submenu');
    
    hasSubmenu.forEach(function(item) {
        const link = item.querySelector('.nav-link');
        
        link.addEventListener('click', function(e) {
            if (wrapper.classList.contains('sidebar-collapsed')) {
                return;
            }
            
            e.preventDefault();
            item.classList.toggle('open');
        });
    });
    
    // Theme Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const body = document.body;
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            if (body.classList.contains('dark-mode')) {
                body.classList.replace('dark-mode', 'light-mode');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.replace('light-mode', 'dark-mode');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark');
            }
        });
        
        // Check localStorage for theme
        const theme = localStorage.getItem('theme') || 'light';
        if (theme === 'dark') {
            body.classList.replace('light-mode', 'dark-mode');
            if (themeIcon) {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }
        } else {
            body.classList.replace('dark-mode', 'light-mode');
            if (themeIcon) {
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            }
        }
    }
    
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Table Search
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.querySelector('.table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.indexOf(searchValue) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Form Validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Notification Dropdown
    const notificationBtn = document.getElementById('notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            // Mark notifications as read
            const badge = this.querySelector('.badge');
            if (badge) {
                badge.style.display = 'none';
            }
        });
    }
    
    // Mobile Menu Toggle
    const mobileToggle = document.getElementById('mobile-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
    
    // Date Formatting
    const dateElements = document.querySelectorAll('.format-date');
    dateElements.forEach(function(element) {
        const date = new Date(element.textContent);
        if (!isNaN(date)) {
            element.textContent = date.toLocaleDateString();
        }
    });
    
    // Datetime Formatting
    const datetimeElements = document.querySelectorAll('.format-datetime');
    datetimeElements.forEach(function(element) {
        const date = new Date(element.textContent);
        if (!isNaN(date)) {
            element.textContent = date.toLocaleString();
        }
    });
    
    // Confirm Delete
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // File Input Preview
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Password Toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = document.getElementById(this.dataset.target);
            
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
    
    // Dropdown Select
    const dropdownSelects = document.querySelectorAll('.dropdown-select');
    dropdownSelects.forEach(function(select) {
        const options = select.querySelectorAll('.dropdown-item');
        const input = document.getElementById(select.dataset.input);
        const display = select.querySelector('.dropdown-select-display');
        
        options.forEach(function(option) {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                
                const value = this.dataset.value;
                const text = this.textContent;
                
                input.value = value;
                display.textContent = text;
            });
        });
    });
    
    // Initialize any charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        // Case Status Chart
        const caseStatusChart = document.getElementById('caseStatusChart');
        if (caseStatusChart) {
            new Chart(caseStatusChart, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'In Progress', 'Completed', 'Dismissed'],
                    datasets: [{
                        data: [12, 19, 8, 5],
                        backgroundColor: [
                            '#ffc107',
                            '#0d6efd',
                            '#198754',
                            '#dc3545'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        // Monthly Cases Chart
        const monthlyCasesChart = document.getElementById('monthlyCasesChart');
        if (monthlyCasesChart) {
            new Chart(monthlyCasesChart, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Cases',
                        data: [12, 19, 3, 5, 2, 3],
                        backgroundColor: '#0d6efd'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }
});
