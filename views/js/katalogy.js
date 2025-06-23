// Katalogy Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Handle interest buttons
    const interestButtons = document.querySelectorAll('.katalogy-interest');
    const modal = document.getElementById('interestModal');
    const catalogTitle = document.getElementById('catalogTitle');
    const catalogIdInput = document.getElementById('catalog_id');
    const form = document.getElementById('interestForm');

    interestButtons.forEach(button => {
        button.addEventListener('click', function() {
            const catalogId = this.getAttribute('data-catalog-id');
            const catalogTitleText = this.getAttribute('data-catalog-title');
            
            catalogTitle.textContent = 'Zájem o katalog: ' + catalogTitleText;
            catalogIdInput.value = catalogId;
            
            // Show modal
            if (typeof $ !== 'undefined' && $.fn.modal) {
                // Bootstrap 4 with jQuery
                $('#interestModal').modal('show');
            } else {
                // Fallback for vanilla JS
                modal.style.display = 'block';
                modal.classList.add('show');
                document.body.classList.add('modal-open');
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
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#interestModal').modal('hide');
        } else {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
        
        // Reset form
        form.reset();
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
    });
});