/**
 * Malaysian Phone Number Input Handler
 * Automatically formats and validates Malaysian phone numbers
 */

class MalaysianPhoneInput {
    constructor() {
        this.initializePhoneInputs();
    }

    /**
     * Remove all existing flags from the page
     */
    removeAllExistingFlags() {
        const flags = document.querySelectorAll('.phone-flag');
        flags.forEach(flag => {
            const input = flag.parentNode.querySelector('input');
            if (input) {
                input.style.paddingLeft = '12px';
            }
            flag.remove();
        });
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

        console.log('Found', phoneInputs.length, 'phone inputs on', window.location.pathname);
        
        phoneInputs.forEach(input => {
            this.setupPhoneInput(input);
        });
    }

    /**
     * Setup individual phone input
     */
    setupPhoneInput(input) {
        // Set placeholder
        if (!input.placeholder) {
            input.placeholder = '+60 12-345 6789';
        }

        // Always remove existing flags for profile pages or readonly inputs
        if (input.readOnly || input.hasAttribute('readonly') || 
            window.location.pathname.includes('/profile') ||
            input.id === 'phone') {
            this.removeExistingFlag(input);
        }

        // Remove +60 prefix from display
        if (input.value) {
            input.value = this.removePrefix(input.value);
            console.log('Removed +60 prefix, new value:', input.value);
        }

        // NEVER add any overlay or flag - just remove any existing ones
        this.removeExistingFlag(input);
        console.log('No overlay added - overlay functionality disabled');

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
     * Remove +60 prefix from phone number for display
     */
    removePrefix(phone) {
        if (!phone) return '';
        
        // Remove +60 prefix and any formatting
        let cleaned = phone.replace(/[^\d]/g, ''); // Remove all non-digits
        
        // Remove 60 prefix if it starts with it
        if (cleaned.startsWith('60')) {
            cleaned = cleaned.substring(2);
        }
        
        // Add local format with leading zero if needed
        if (cleaned.length >= 8 && !cleaned.startsWith('0')) {
            cleaned = '0' + cleaned;
        }
        
        return cleaned;
    }

    /**
     * Remove existing flag from input
     */
    removeExistingFlag(input) {
        const flag = input.parentNode.querySelector('.phone-flag');
        if (flag) {
            flag.remove();
            input.style.paddingLeft = '12px'; // Reset padding
        }
    }

    /**
     * Add Malaysian flag visual indicator
     */
    addMalaysianFlag(input) {
        // Completely skip flag for profile pages or readonly inputs
        if (input.readOnly || input.hasAttribute('readonly') || 
            window.location.pathname.includes('/profile') ||
            input.id === 'phone') {
            console.log('Skipping flag for profile/readonly input');
            return;
        }

        // Check if already has flag
        if (input.parentNode.querySelector('.phone-flag')) {
            return;
        }

        // Don't add flag if input already has value with phone number
        const currentValue = input.value ? input.value.trim() : '';
        if (currentValue && (currentValue.includes('+60') || currentValue.length > 3)) {
            console.log('Skipping flag for input with existing value:', currentValue);
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
        flag.innerHTML = '+60';
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
        input.style.paddingLeft = '50px';
    }

    /**
     * Handle phone input formatting - remove +60 prefix
     */
    handlePhoneInput(event) {
        const input = event.target;
        const cursorPosition = input.selectionStart;
        const oldValue = input.value;
        
        // Remove +60 prefix from whatever they type
        const newValue = this.removePrefix(oldValue);
        
        input.value = newValue;
        
        // Maintain cursor position
        const newCursorPosition = Math.min(cursorPosition, newValue.length);
        input.setSelectionRange(newCursorPosition, newCursorPosition);
        
        // Remove validation errors when user types
        this.clearValidationError(input);
    }

    /**
     * Handle phone focus - DO NOT auto-add +60
     */
    handlePhoneFocus(event) {
        const input = event.target;
        // Remove any existing overlay when focused
        this.removeExistingFlag(input);
        // Do not auto-add +60 - let user type naturally
        console.log('Phone input focused, no auto +60 added');
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
    // Add a small delay to ensure all values are loaded
    setTimeout(() => {
        new MalaysianPhoneInput();
    }, 100);
});

// Also initialize on AJAX content updates
document.addEventListener('contentUpdated', function() {
    new MalaysianPhoneInput();
});

// Export for manual initialization
window.MalaysianPhoneInput = MalaysianPhoneInput;