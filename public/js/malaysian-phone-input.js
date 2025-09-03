/**
 * Malaysian Phone Number Input Handler
 * Automatically formats and validates Malaysian phone numbers
 */

class MalaysianPhoneInput {
    constructor() {
        this.initializePhoneInputs();
    }

    /**
     * Initialize all phone inputs on the page
     */
    initializePhoneInputs() {
        // Find all phone input fields
        const phoneInputs = document.querySelectorAll(
            'input[name*="phone"], input[name*="mobile"], input[name*="telephone"], ' +
            'input[type="tel"], input[data-phone="true"], ' +
            '.phone-input, #trainee_phone_number, #guardian_phone, #emergency_contact_phone'
        );

        phoneInputs.forEach(input => {
            this.setupPhoneInput(input);
        });
    }

    /**
     * Setup individual phone input
     */
    setupPhoneInput(input) {
        // Add visual indicator
        this.addMalaysianFlag(input);
        
        // Set placeholder
        if (!input.placeholder) {
            input.placeholder = '+60 12-345 6789';
        }

        // Set initial value if exists
        if (input.value) {
            input.value = this.formatPhoneNumber(input.value);
        }

        // Add event listeners
        input.addEventListener('input', (e) => {
            this.handlePhoneInput(e);
        });

        input.addEventListener('blur', (e) => {
            this.validatePhoneInput(e);
        });

        input.addEventListener('focus', (e) => {
            this.handlePhoneFocus(e);
        });
    }

    /**
     * Add Malaysian flag visual indicator
     */
    addMalaysianFlag(input) {
        // Check if already has flag
        if (input.parentNode.querySelector('.phone-flag')) {
            return;
        }

        // Create wrapper if not exists
        if (!input.parentNode.classList.contains('phone-input-wrapper')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'phone-input-wrapper position-relative';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
        }

        // Add flag indicator
        const flag = document.createElement('span');
        flag.className = 'phone-flag';
        flag.innerHTML = '🇲🇾 +60';
        flag.style.cssText = `
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #666;
            pointer-events: none;
            z-index: 2;
        `;

        input.parentNode.appendChild(flag);
        input.style.paddingLeft = '70px';
    }

    /**
     * Handle phone input formatting
     */
    handlePhoneInput(event) {
        const input = event.target;
        const cursorPosition = input.selectionStart;
        const oldValue = input.value;
        const newValue = this.formatPhoneNumber(oldValue);
        
        input.value = newValue;
        
        // Maintain cursor position
        const newCursorPosition = cursorPosition + (newValue.length - oldValue.length);
        input.setSelectionRange(newCursorPosition, newCursorPosition);
        
        // Remove validation errors when user types
        this.clearValidationError(input);
    }

    /**
     * Handle phone focus - ensure +60 prefix
     */
    handlePhoneFocus(event) {
        const input = event.target;
        if (!input.value || input.value.trim() === '') {
            input.value = '+60 ';
        }
    }

    /**
     * Format phone number for display
     */
    formatPhoneNumber(phone) {
        if (!phone) return '';
        
        // Remove all non-digit and non-plus characters
        let cleaned = phone.replace(/[^\d+]/g, '');
        
        // Ensure starts with +60
        if (!cleaned.startsWith('+60')) {
            if (cleaned.startsWith('60')) {
                cleaned = '+' + cleaned;
            } else if (cleaned.startsWith('0')) {
                cleaned = '+6' + cleaned;
            } else if (cleaned.match(/^[1-9]/)) {
                cleaned = '+60' + cleaned;
            } else if (!cleaned.startsWith('+')) {
                cleaned = '+60';
            }
        }

        // Remove +60 prefix for formatting
        const number = cleaned.replace('+60', '');
        
        // Format based on length
        if (number.length === 0) {
            return '+60 ';
        } else if (number.length <= 2) {
            return '+60 ' + number;
        } else if (number.length <= 5) {
            return '+60 ' + number.slice(0, 2) + '-' + number.slice(2);
        } else if (number.length <= 9) {
            return '+60 ' + number.slice(0, 2) + '-' + number.slice(2, 5) + ' ' + number.slice(5);
        } else {
            // Limit to maximum valid length
            return '+60 ' + number.slice(0, 2) + '-' + number.slice(2, 5) + ' ' + number.slice(5, 9);
        }
    }

    /**
     * Validate phone input
     */
    validatePhoneInput(event) {
        const input = event.target;
        const phone = input.value;
        
        if (!phone || phone.trim() === '' || phone.trim() === '+60') {
            if (input.required) {
                this.showValidationError(input, 'Phone number is required');
                return false;
            }
            return true;
        }

        const isValid = this.isValidMalaysianPhone(phone);
        
        if (!isValid) {
            this.showValidationError(input, 'Please enter a valid Malaysian phone number');
            return false;
        }
        
        this.clearValidationError(input);
        return true;
    }

    /**
     * Check if phone number is valid Malaysian format
     */
    isValidMalaysianPhone(phone) {
        // Remove formatting
        const cleaned = phone.replace(/[^\d+]/g, '');
        
        if (!cleaned.startsWith('+60')) {
            return false;
        }

        const number = cleaned.replace('+60', '');
        
        // Mobile numbers: 10-11 digits starting with 1
        if (number.length >= 9 && number.length <= 10 && number.startsWith('1')) {
            return /^1[0-9]{8,9}$/.test(number);
        }
        
        // Landline numbers: 8-9 digits starting with 2-9
        if (number.length >= 8 && number.length <= 9) {
            return /^[2-9][0-9]{7,8}$/.test(number);
        }
        
        return false;
    }

    /**
     * Show validation error
     */
    showValidationError(input, message) {
        // Remove existing error
        this.clearValidationError(input);
        
        // Add error class
        input.classList.add('is-invalid');
        
        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback phone-error';
        errorDiv.textContent = message;
        
        // Insert after input
        input.parentNode.appendChild(errorDiv);
    }

    /**
     * Clear validation error
     */
    clearValidationError(input) {
        input.classList.remove('is-invalid');
        const existingError = input.parentNode.querySelector('.phone-error');
        if (existingError) {
            existingError.remove();
        }
    }

    /**
     * Get normalized phone number for form submission
     */
    static normalize(phone) {
        if (!phone) return phone;
        
        // Remove all non-digit and non-plus characters
        let cleaned = phone.replace(/[^\d+]/g, '');
        
        // Handle different input formats
        if (cleaned.startsWith('+60')) {
            return cleaned;
        } else if (cleaned.startsWith('60')) {
            return '+' + cleaned;
        } else if (cleaned.startsWith('0')) {
            return '+6' + cleaned;
        } else if (cleaned.match(/^[1-9]/) && cleaned.length >= 8) {
            return '+60' + cleaned;
        }
        
        return phone;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new MalaysianPhoneInput();
});

// Also initialize on AJAX content updates
document.addEventListener('contentUpdated', function() {
    new MalaysianPhoneInput();
});

// Export for manual initialization
window.MalaysianPhoneInput = MalaysianPhoneInput;