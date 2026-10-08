/**
 * IKE PRODUCTS LOUNGE - Main JavaScript
 * Handles: mobile menu, flash message auto-close, quantity controls,
 *          form validation, confirmation dialogs, cart updates
 */

document.addEventListener('DOMContentLoaded', function () {

    // ============================================
    // 1. Mobile Navigation Menu Toggle
    // ============================================
    const menuToggle = document.getElementById('menuToggle');
    const navLinks   = document.getElementById('navLinks');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');
            // Animate hamburger icon
            const spans = menuToggle.querySelectorAll('span');
            menuToggle.classList.toggle('active');
        });

        // Close menu when a nav link is clicked
        navLinks.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                navLinks.classList.remove('open');
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (e) {
            if (!menuToggle.contains(e.target) && !navLinks.contains(e.target)) {
                navLinks.classList.remove('open');
            }
        });
    }

    // ============================================
    // 2. Flash Message Auto-Dismiss (after 5 seconds)
    // ============================================
    const flash = document.getElementById('flashMessage');
    if (flash) {
        setTimeout(function () {
            flash.style.transition = 'opacity 0.5s ease';
            flash.style.opacity = '0';
            setTimeout(function () {
                flash.remove();
            }, 500);
        }, 5000);
    }

    // ============================================
    // 3. Confirmation Dialogs for Destructive Actions
    // ============================================
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const message = el.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // ============================================
    // 4. Quantity Increment / Decrement Buttons
    // ============================================
    document.querySelectorAll('.qty-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = btn.dataset.action; // 'increase' or 'decrease'
            const input  = btn.closest('.qty-control').querySelector('input[type="number"]');
            if (!input) return;

            let val = parseInt(input.value) || 1;
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 9999;

            if (action === 'increase' && val < max) val++;
            if (action === 'decrease' && val > min) val--;

            input.value = val;
        });
    });

    // ============================================
    // 5. Client-Side Form Validation
    // ============================================
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;

            // Clear previous errors
            form.querySelectorAll('.is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
            form.querySelectorAll('.invalid-feedback').forEach(function (el) {
                el.remove();
            });

            // Validate required fields
            form.querySelectorAll('[required]').forEach(function (field) {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('is-invalid');
                    const msg = document.createElement('div');
                    msg.className = 'invalid-feedback';
                    msg.textContent = 'This field is required.';
                    field.parentNode.appendChild(msg);
                }
            });

            // Validate email fields
            form.querySelectorAll('input[type="email"]').forEach(function (field) {
                if (field.value.trim() && !isValidEmail(field.value.trim())) {
                    valid = false;
                    field.classList.add('is-invalid');
                    const msg = document.createElement('div');
                    msg.className = 'invalid-feedback';
                    msg.textContent = 'Please enter a valid email address.';
                    field.parentNode.appendChild(msg);
                }
            });

            // Validate password match (register form)
            const pass    = form.querySelector('#password');
            const confirm = form.querySelector('#password_confirm');
            if (pass && confirm && pass.value && confirm.value) {
                if (pass.value !== confirm.value) {
                    valid = false;
                    confirm.classList.add('is-invalid');
                    const msg = document.createElement('div');
                    msg.className = 'invalid-feedback';
                    msg.textContent = 'Passwords do not match.';
                    confirm.parentNode.appendChild(msg);
                }
            }

            if (!valid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // ============================================
    // 6. Email Validation Helper
    // ============================================
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // ============================================
    // 7. Image Preview for File Uploads (Admin)
    // ============================================
    const imageInput = document.getElementById('productImage');
    const imagePreview = document.getElementById('imagePreview');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewFilename = document.getElementById('previewFilename');
    const previewFilesize = document.getElementById('previewFilesize');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    if (previewContainer) {
                        previewContainer.style.display = 'flex';
                    }
                    if (previewFilename) {
                        previewFilename.textContent = file.name;
                    }
                    if (previewFilesize) {
                        const sizeKB = (file.size / 1024).toFixed(1);
                        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        previewFilesize.textContent = file.size > 1024 * 1024 ? `${sizeMB} MB` : `${sizeKB} KB`;
                    }
                };
                reader.readAsDataURL(file);
            } else {
                if (previewContainer) {
                    previewContainer.style.display = 'none';
                }
                imagePreview.style.display = 'none';
            }
        });
    }

    // ============================================
    // 8. Auto-highlight active nav link
    // ============================================
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-links a, .sidebar-nav a').forEach(function (link) {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });

});
