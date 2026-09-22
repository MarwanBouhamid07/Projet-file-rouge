/**
 * SmartMatch Admin Scripts
 * Plain JavaScript (no framework)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Mobile Toggle
    const sidebar = document.getElementById('adminSidebar');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.add('open');
        });
    }

    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('open');
        });
    }

    // 2. Delete Confirmation with JS confirm()
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const message = btn.getAttribute('data-confirm-delete') || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 3. Image Upload Preview
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check if file is an image
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    }

    // 4. Auto-hide alerts after 5 seconds
    const flashAlerts = document.querySelectorAll('.alert');
    flashAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
