// ULAB FoodCafe - Main JavaScript

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// Cart quantity update confirmation
function updateCartQuantity(itemId, quantity) {
    if (quantity < 1) {
        if (confirm('Remove this item from cart?')) {
            return true;
        }
        return false;
    }
    return true;
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = 'var(--ulab-danger)';
        } else {
            field.style.borderColor = '';
        }
    });
    
    return isValid;
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(function(modal) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
}

// Real-time order status check (for future AJAX implementation)
function checkOrderStatus(orderId) {
    // This can be implemented with AJAX for real-time updates
    console.log('Checking order status for:', orderId);
}

// Notification badge update
function updateNotificationBadge() {
    // This can be implemented with AJAX polling or WebSockets
    console.log('Updating notification badge');
}

