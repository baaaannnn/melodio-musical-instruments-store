


// ── Helpers ─────────────────────────────────

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    let error = document.getElementById(fieldId + '_error');
    if (!error) {
        error = document.createElement('span');
        error.id = fieldId + '_error';
        error.className = 'validation-error';
        error.style.cssText = 'color:#e74c3c; font-size:12px; display:block; margin-top:4px;';
        field.parentNode.appendChild(error);
    }

    error.textContent = message;
    field.style.borderColor = '#e74c3c';
}

function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    const error = document.getElementById(fieldId + '_error');
    if (error) error.textContent = '';
    field.style.borderColor = '';
}

function clearAllErrors(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    form.querySelectorAll('.validation-error').forEach(el => el.textContent = '');
    form.querySelectorAll('input, select, textarea').forEach(el => el.style.borderColor = '');
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhone(phone) {
    // Saudi numbers only: 05xxxxxxxx or +9665xxxxxxxx
    return phone.replace(/\s/g, '').length >= 7;
}


// ── 1. Login Form ────────────────────────────

function validateLogin() {
    clearAllErrors('loginForm');
    let valid = true;

    const email    = document.getElementById('email')?.value.trim();
    const password = document.getElementById('password')?.value;

    if (!email) {
        showError('email', 'Email is required.');
        valid = false;
    } else if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email address.');
        valid = false;
    }

    if (!password) {
        showError('password', 'Password is required.');
        valid = false;
    } else if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters.');
        valid = false;
    }

    return valid;
}


// 2. Register Form

function validateRegister() {
    clearAllErrors('registerForm');
    let valid = true;

    const full_name        = document.getElementById('full_name')?.value.trim();
    const email            = document.getElementById('email')?.value.trim();
    const password         = document.getElementById('password')?.value;
    const confirm_password = document.getElementById('confirm_password')?.value;

    if (!full_name) {
        showError('full_name', 'Full name is required.');
        valid = false;
    } else if (full_name.length < 3) {
        showError('full_name', 'Name must be at least 3 characters.');
        valid = false;
    }

    if (!email) {
        showError('email', 'Email is required.');
        valid = false;
    } else if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email address.');
        valid = false;
    }

    if (!password) {
        showError('password', 'Password is required.');
        valid = false;
    } else if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters.');
        valid = false;
    }

    if (!confirm_password) {
        showError('confirm_password', 'Please confirm your password.');
        valid = false;
    } else if (password !== confirm_password) {
        showError('confirm_password', 'Passwords do not match.');
        valid = false;
    }

    return valid;
}


// 3. Checkout Form

function validateCheckout() {
    clearAllErrors('checkoutForm');
    let valid = true;

    const phone = document.getElementById('phone')?.value.trim();

    if (!phone) {
        showError('phone', 'Phone number is required.');
        valid = false;
    } else if (!isValidPhone(phone)) {
        showError('phone', 'Enter a valid Saudi phone number (e.g. 0512345678).');
        valid = false;
    }

    // Check that cart is not empty
    const cartItems = document.querySelectorAll('.cart-item');
    if (cartItems.length === 0) {
        const cartError = document.getElementById('cart_error');
        if (cartError) cartError.textContent = 'Your cart is empty. Add products before placing an order.';
        valid = false;
    }

    return valid;
}



// 4. Update Order Status (Delivery / Admin)

function validateStatusUpdate() {
    clearAllErrors('statusForm');
    let valid = true;

    const status = document.getElementById('status')?.value;

    if (!status) {
        showError('status', 'Please select a status.');
        valid = false;
    }

    return valid;
}


// Add to forms on page load

document.addEventListener('DOMContentLoaded', function () {

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            if (!validateLogin()) e.preventDefault();
        });
    }

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            if (!validateRegister()) e.preventDefault();
        });
    }

    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            if (!validateCheckout()) e.preventDefault();
        });
    }


    const statusForm = document.getElementById('statusForm');
    if (statusForm) {
        statusForm.addEventListener('submit', function (e) {
            if (!validateStatusUpdate()) e.preventDefault();
        });
    }

    // Clear Error
    document.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('input', function () {
            clearError(el.id);
        });
    });

});
