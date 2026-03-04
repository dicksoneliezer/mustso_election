/* ===============================
   MUSTSO Registration Logic
   Enhanced with Modern Features
================================*/

// Wait for DOM to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ MUSTSO Registration System Initialized");
    
    // Initialize all components
    initRegistrationForm();
    initPasswordToggle();
    initFormAnimations();
    initInputEffects();
    initFormValidation();
});

/* ===============================
   Registration Form Initialization
================================*/
function initRegistrationForm() {
    const studyMode = document.getElementById("studyMode");
    const registrationForm = document.getElementById("registrationForm");
    
    if (studyMode) {
        // Add event listener for changes
        studyMode.addEventListener("change", toggleJimbo);
        
        // Run once when page loads
        toggleJimbo();
    }
    
    if (registrationForm) {
        registrationForm.addEventListener("submit", handleFormSubmit);
    }
}

/* ===============================
   Jimbo Section Toggle
================================*/
function toggleJimbo() {
    const studyMode = document.getElementById("studyMode");
    const jimboSection = document.getElementById("jimboSection");
    const jimboSelect = document.getElementById("jimboSelect");

    // Check if all elements exist
    if (!studyMode || !jimboSection || !jimboSelect) {
        console.warn("⚠️ Required DOM elements not found");
        return;
    }

    if (studyMode.value === "off_campus") {
        // Show with animation
        jimboSection.style.display = "block";
        setTimeout(() => {
            jimboSection.style.opacity = "1";
            jimboSection.style.transform = "translateY(0)";
        }, 10);
        
        jimboSelect.disabled = false;
        jimboSelect.required = true;
        
        // Add focus for better UX
        setTimeout(() => jimboSelect.focus(), 300);
        
    } else {
        // Hide with animation
        jimboSection.style.opacity = "0";
        jimboSection.style.transform = "translateY(-10px)";
        
        setTimeout(() => {
            jimboSection.style.display = "none";
        }, 300);
        
        jimboSelect.disabled = true;
        jimboSelect.required = false;
        jimboSelect.value = ""; // Reset selection when hidden
    }
}

/* ===============================
   Password Toggle Visibility
================================*/
function initPasswordToggle() {
    const passwordField = document.querySelector('input[name="password"]');
    
    if (passwordField && !document.querySelector('.password-toggle')) {
        // Create toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'password-toggle';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
        toggleBtn.setAttribute('aria-label', 'Toggle password visibility');
        
        // Style the toggle button
        toggleBtn.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            padding: 5px;
            z-index: 10;
        `;
        
        // Wrap password field in relative container
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.width = '100%';
        
        passwordField.parentNode.insertBefore(wrapper, passwordField);
        wrapper.appendChild(passwordField);
        wrapper.appendChild(toggleBtn);
        
        // Toggle functionality
        toggleBtn.addEventListener('click', function() {
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }
}

/* ===============================
   Form Animations
================================*/
function initFormAnimations() {
    // Add floating labels effect
    const formInputs = document.querySelectorAll('form input, form select');
    
    formInputs.forEach(input => {
        // Add wrapper for floating labels
        if (input.type !== 'hidden' && !input.closest('.password-wrapper')) {
            input.addEventListener('focus', function() {
                this.style.transform = 'scale(1.02)';
                this.style.boxShadow = '0 0 0 3px rgba(0, 198, 255, 0.3)';
            });
            
            input.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = 'none';
            });
            
            // Add character counter for inputs with maxlength
            if (input.hasAttribute('maxlength')) {
                addCharacterCounter(input);
            }
        }
    });
}

/* ===============================
   Character Counter
================================*/
function addCharacterCounter(input) {
    const counter = document.createElement('small');
    counter.className = 'char-counter';
    counter.style.cssText = `
        display: block;
        text-align: right;
        font-size: 11px;
        color: rgba(255, 255, 255, 0.7);
        margin-top: -12px;
        margin-bottom: 10px;
    `;
    
    const maxLength = input.getAttribute('maxlength');
    
    function updateCounter() {
        const remaining = maxLength - input.value.length;
        counter.textContent = `${remaining} characters remaining`;
        
        if (remaining < 10) {
            counter.style.color = '#ff6b6b';
        } else {
            counter.style.color = 'rgba(255, 255, 255, 0.7)';
        }
    }
    
    input.addEventListener('input', updateCounter);
    input.parentNode.insertBefore(counter, input.nextSibling);
    updateCounter();
}

/* ===============================
   Input Effects
================================*/
function initInputEffects() {
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('form button');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            
            ripple.style.cssText = `
                width: ${size}px;
                height: ${size}px;
                left: ${e.clientX - rect.left - size/2}px;
                top: ${e.clientY - rect.top - size/2}px;
                position: absolute;
                background: rgba(255, 255, 255, 0.5);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Add keyframe animation for ripple
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

/* ===============================
   Form Validation
================================*/
function initFormValidation() {
    const form = document.getElementById('registrationForm');
    
    if (form) {
        // Real-time validation
        const inputs = form.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                validateField(this);
            });
            
            input.addEventListener('blur', function() {
                validateField(this, true);
            });
        });
    }
}

function validateField(field, showMessage = false) {
    let isValid = true;
    let message = '';
    
    if (field.required && !field.value.trim()) {
        isValid = false;
        message = 'This field is required';
    }
    
    // Specific validations
    if (field.name === 'reg_no' && field.value.trim()) {
        const regNoRegex = /^[A-Z0-9-]+$/i;
        if (!regNoRegex.test(field.value)) {
            isValid = false;
            message = 'Invalid registration number format';
        }
    }
    
    if (field.name === 'password' && field.value.trim()) {
        if (field.value.length < 6) {
            isValid = false;
            message = 'Password must be at least 6 characters';
        }
    }
    
    // Update UI
    if (!isValid) {
        field.style.border = '2px solid #ff6b6b';
        
        if (showMessage && !field.nextElementSibling?.classList.contains('error-message')) {
            const errorMsg = document.createElement('span');
            errorMsg.className = 'error-message';
            errorMsg.style.cssText = `
                display: block;
                color: #ff6b6b;
                font-size: 11px;
                margin-top: -15px;
                margin-bottom: 10px;
            `;
            errorMsg.textContent = message;
            field.parentNode.insertBefore(errorMsg, field.nextSibling);
        }
    } else {
        field.style.border = '2px solid #51cf66';
        
        // Remove error message if exists
        const nextElement = field.nextElementSibling;
        if (nextElement?.classList.contains('error-message')) {
            nextElement.remove();
        }
    }
    
    return isValid;
}

/* ===============================
   Form Submit Handler - FIXED VERSION
   Now properly submits to register.php
================================*/
function handleFormSubmit(e) {
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Validate all fields first
    const requiredFields = form.querySelectorAll('[required]');
    let isFormValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField(field, true)) {
            isFormValid = false;
        }
    });
    
    // Special validation for off-campus students
    const studyMode = document.getElementById('studyMode');
    const jimboSelect = document.getElementById('jimboSelect');
    
    if (studyMode && studyMode.value === 'off_campus') {
        if (!jimboSelect || !jimboSelect.value) {
            isFormValid = false;
            if (jimboSelect) {
                jimboSelect.style.border = '2px solid #ff6b6b';
                
                // Show error message for jimbo
                const errorMsg = document.createElement('span');
                errorMsg.className = 'error-message';
                errorMsg.style.cssText = `
                    display: block;
                    color: #ff6b6b;
                    font-size: 11px;
                    margin-top: -15px;
                    margin-bottom: 10px;
                `;
                errorMsg.textContent = 'Please select your Jimbo';
                
                // Remove existing error message if any
                const existingError = jimboSelect.parentNode.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
                
                jimboSelect.parentNode.insertBefore(errorMsg, jimboSelect.nextSibling);
            }
            showNotification('Please select your Jimbo', 'error');
        }
    }
    
    // If form is not valid, prevent submission
    if (!isFormValid) {
        e.preventDefault();
        showNotification('Please fix the errors in the form', 'error');
        return;
    }
    
    // Show loading state on button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Allow the form to submit normally to register.php
    // No e.preventDefault() - form submits naturally
    console.log("✅ Form validation passed. Submitting to register.php...");
}

/* ===============================
   Notification System
================================*/
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existingNotif = document.querySelector('.notification');
    if (existingNotif) {
        existingNotif.remove();
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Set icon based on type
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    
    notification.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
    `;
    
    // Style notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#51cf66' : type === 'error' ? '#ff6b6b' : '#ffd43b'};
        color: ${type === 'warning' ? '#000' : '#fff'};
        border-radius: 50px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        font-size: 14px;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/* ===============================
   Add CSS Animations
================================*/
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
    
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
    
    #jimboSection {
        transition: opacity 0.3s ease, transform 0.3s ease;
        opacity: 0;
        transform: translateY(-10px);
    }
    
    #jimboSection[style*="display: block"] {
        opacity: 1;
        transform: translateY(0);
    }
    
    form input, form select {
        transition: all 0.3s ease;
    }
    
    form button {
        position: relative;
        overflow: hidden;
    }
    
    .password-toggle {
        transition: color 0.3s ease;
    }
    
    .password-toggle:hover {
        color: #00c6ff;
    }
    
    .notification {
        font-weight: 500;
        backdrop-filter: blur(10px);
    }
    
    .error-message {
        animation: fadeIn 0.3s ease;
    }
`;

document.head.appendChild(style);

/* ===============================
   Loading Screen (Optional)
================================*/
function showLoadingScreen() {
    const loadingScreen = document.createElement('div');
    loadingScreen.className = 'loading-screen';
    loadingScreen.innerHTML = `
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>Loading MUSTSO Voting System...</p>
        </div>
    `;
    
    loadingScreen.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        transition: opacity 0.5s ease;
    `;
    
    const spinnerStyle = document.createElement('style');
    spinnerStyle.textContent = `
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #ffd700;
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 20px;
        }
        
        .loading-content {
            text-align: center;
            color: white;
        }
    `;
    
    document.head.appendChild(spinnerStyle);
    document.body.appendChild(loadingScreen);
    
    // Remove loading screen after page loads
    window.addEventListener('load', () => {
        setTimeout(() => {
            loadingScreen.style.opacity = '0';
            setTimeout(() => loadingScreen.remove(), 500);
        }, 500);
    });
}

// Uncomment to enable loading screen
// showLoadingScreen();

/* ===============================
   Export functions for testing
================================*/
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        toggleJimbo,
        validateField,
        showNotification
    };
}