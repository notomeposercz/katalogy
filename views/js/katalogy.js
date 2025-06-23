// Katalogy Module JavaScript - CMS Version

document.addEventListener('DOMContentLoaded', function() {
    // Handle interest buttons
    const interestButtons = document.querySelectorAll('.katalogy-interest');
    const modal = document.getElementById('interestModal');
    const catalogTitle = document.getElementById('catalogTitle');
    const catalogIdInput = document.getElementById('catalog_id');
    const form = document.getElementById('interestForm');

    if (!modal || !catalogTitle || !catalogIdInput || !form) {
        console.log('Katalogy: Modal elements not found');
        return;
    }

    interestButtons.forEach(button => {
        button.addEventListener('click', function() {
            const catalogId = this.getAttribute('data-catalog-id');
            const catalogTitleText = this.getAttribute('data-catalog-title');
            
            catalogTitle.textContent = 'Zájem o katalog: ' + catalogTitleText;
            catalogIdInput.value = catalogId;
            
            // Show modal - Bootstrap detection
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                // Bootstrap 5
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                // Bootstrap 4 with jQuery
                $(modal).modal('show');
            } else {
                // Fallback for vanilla JS
                modal.style.display = 'block';
                modal.classList.add('show');
                document.body.classList.add('modal-open');
                
                // Create backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'katalogy-backdrop';
                document.body.appendChild(backdrop);
            }
        });
    });

    // Handle modal close
    const closeButtons = modal.querySelectorAll('[data-dismiss="modal"], .close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            closeModal();
        });
    });

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    function closeModal() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            // Bootstrap 5
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            // Bootstrap 4
            $(modal).modal('hide');
        } else {
            // Fallback
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            
            const backdrop = document.getElementById('katalogy-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
        
        // Reset form
        form.reset();
        catalogIdInput.value = '';
    }

    // Handle form submission
    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        
        if (!name || !email) {
            e.preventDefault();
            alert('Prosím vyplňte všechna povinná pole.');
            return false;
        }
        
        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Prosím zadejte platnou e-mailovou adresu.');
            return false;
        }
        
        // Close modal after successful submission
        setTimeout(closeModal, 100);
    });

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.querySelector('.btn-close')) {
                alert.querySelector('.btn-close').click();
            } else {
                alert.style.display = 'none';
            }
        }, 5000);
    });
});