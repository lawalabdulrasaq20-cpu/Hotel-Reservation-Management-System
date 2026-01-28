/**
 * Hotel Reservation System - Main JavaScript
 * 
 * This is the main JavaScript file containing common functionality,
 * utilities, and interactions used throughout the Hotel Reservation System.
 * 
 * @category Hotel_Reservation
 * @package  Scripts
 * @author   Hotel Reservation System
 * @license  MIT License
 */

// ========================================
// DOM READY
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initNavbar();
    initMobileMenu();
    initBackToTop();
    initFlashMessages();
    initFormValidation();
    initScrollAnimations();
    initNavbarScroll();
    initTooltips();
    initLazyLoading();
    initDatePickers();
});

// ========================================
// NAVBAR FUNCTIONALITY
// ========================================

/**
 * Initialize navbar functionality
 */
function initNavbar() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;

    // Add scrolled class on page load if already scrolled
    if (window.scrollY > 50) {
        navbar.classList.add('navbar-scrolled');
        navbar.classList.remove('navbar-transparent');
    }
}

/**
 * Initialize navbar scroll behavior
 */
function initNavbarScroll() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;

    let lastScrollY = window.scrollY;
    let ticking = false;

    function updateNavbar() {
        const currentScrollY = window.scrollY;
        
        // Add/remove scrolled class
        if (currentScrollY > 50) {
            navbar.classList.add('navbar-scrolled');
            navbar.classList.remove('navbar-transparent');
        } else {
            navbar.classList.remove('navbar-scrolled');
            navbar.classList.add('navbar-transparent');
        }

        lastScrollY = currentScrollY;
        ticking = false;
    }

    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(updateNavbar);
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
}

// ========================================
// MOBILE MENU
// ========================================

/**
 * Initialize mobile menu functionality
 */
function initMobileMenu() {
    const mobileMenuToggle = document.getElementById('mobile-menu');
    const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
    const navToggle = document.querySelector('.nav-toggle');

    if (!mobileMenuToggle || !mobileMenuOverlay) return;

    // Toggle mobile menu
    mobileMenuToggle.addEventListener('click', function() {
        navToggle.classList.toggle('active');
        mobileMenuOverlay.classList.toggle('active');
        document.body.style.overflow = mobileMenuOverlay.classList.contains('active') ? 'hidden' : '';
    });

    // Close menu when clicking outside
    mobileMenuOverlay.addEventListener('click', function(e) {
        if (e.target === mobileMenuOverlay) {
            closeMobileMenu();
        }
    });

    // Close menu when clicking on links
    const mobileLinks = mobileMenuOverlay.querySelectorAll('a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });

    function closeMobileMenu() {
        navToggle.classList.remove('active');
        mobileMenuOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// ========================================
// BACK TO TOP BUTTON
// ========================================

/**
 * Initialize back to top button
 */
function initBackToTop() {
    const backToTopBtn = document.getElementById('back-to-top');
    if (!backToTopBtn) return;

    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    }, { passive: true });

    // Smooth scroll to top
    backToTopBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ========================================
// FLASH MESSAGES
// ========================================

/**
 * Initialize flash message functionality
 */
function initFlashMessages() {
    const flashMessage = document.getElementById('flash-message');
    if (!flashMessage) return;

    // Auto-close after 5 seconds
    setTimeout(function() {
        closeFlashMessage();
    }, 5000);
}

/**
 * Close flash message with animation
 */
function closeFlashMessage() {
    const flashMessage = document.getElementById('flash-message');
    if (!flashMessage) return;

    flashMessage.style.opacity = '0';
    flashMessage.style.transform = 'translateY(-20px)';
    
    setTimeout(function() {
        if (flashMessage.parentNode) {
            flashMessage.parentNode.removeChild(flashMessage);
        }
    }, 300);
}

/**
 * Show flash message dynamically
 * @param {string} message - The message to display
 * @param {string} type - Message type (success, error, warning)
 */
function showFlashMessage(message, type = 'success') {
    // Remove existing flash messages
    const existingFlash = document.getElementById('flash-message');
    if (existingFlash) {
        existingFlash.remove();
    }

    // Create new flash message
    const flashDiv = document.createElement('div');
    flashDiv.className = `flash-message flash-${type}`;
    flashDiv.id = 'flash-message';
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-circle' : 'exclamation-triangle';
    
    flashDiv.innerHTML = `
        <div class="flash-content">
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
            <button class="flash-close" onclick="closeFlashMessage()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(flashDiv);
    
    // Auto-close after 5 seconds
    setTimeout(function() {
        closeFlashMessage();
    }, 5000);
}

// ========================================
// FORM VALIDATION
// ========================================

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @returns {boolean} True if valid
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate phone number
 * @param {string} phone - Phone number to validate
 * @returns {boolean} True if valid
 */
function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
    return phoneRegex.test(phone);
}

/**
 * Show field error
 * @param {HTMLElement} field - Form field element
 * @param {string} message - Error message
 */
function showFieldError(field, message) {
    field.classList.add('error');
    field.classList.remove('success');
    
    const feedback = field.parentNode.querySelector('.form-feedback');
    if (feedback) {
        feedback.textContent = message;
        feedback.classList.add('error');
        feedback.classList.remove('success');
    }
}

/**
 * Show field success
 * @param {HTMLElement} field - Form field element
 */
function showFieldSuccess(field) {
    field.classList.add('success');
    field.classList.remove('error');
    
    const feedback = field.parentNode.querySelector('.form-feedback');
    if (feedback) {
        feedback.textContent = '';
        feedback.classList.remove('error');
    }
}

// ========================================
// SCROLL ANIMATIONS
// ========================================

/**
 * Initialize scroll-triggered animations
 */
function initScrollAnimations() {
    // Intersection Observer for scroll reveals
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe elements with scroll-reveal class
    const revealElements = document.querySelectorAll('.scroll-reveal');
    revealElements.forEach(el => {
        observer.observe(el);
    });
}

// ========================================
// TOOLTIPS
// ========================================

/**
 * Initialize tooltips
 */
function initTooltips() {
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    
    tooltipTriggers.forEach(trigger => {
        const tooltipText = trigger.getAttribute('data-tooltip');
        
        // Create tooltip element
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = tooltipText;
        document.body.appendChild(tooltip);
        
        // Position tooltip
        trigger.addEventListener('mouseenter', function() {
            const rect = trigger.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
            tooltip.classList.add('show');
        });
        
        trigger.addEventListener('mouseleave', function() {
            tooltip.classList.remove('show');
        });
    });
}

// ========================================
// LAZY LOADING
// ========================================

/**
 * Initialize lazy loading for images
 */
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => {
        imageObserver.observe(img);
    });
}

// ========================================
// DATE PICKERS
// ========================================

/**
 * Initialize date pickers
 */
function initDatePickers() {
    const today = new Date().toISOString().split('T')[0];
    
    // Set minimum date for check-in inputs
    const checkinInputs = document.querySelectorAll('input[name="check_in"]');
    checkinInputs.forEach(input => {
        input.setAttribute('min', today);
        
        // Update checkout min date when check-in changes
        input.addEventListener('change', function() {
            const checkoutInput = document.querySelector('input[name="check_out"]');
            if (checkoutInput) {
                checkoutInput.setAttribute('min', this.value);
                
                // If checkout is before checkin, update it
                if (checkoutInput.value && checkoutInput.value <= this.value) {
                    const nextDay = new Date(this.value);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkoutInput.value = nextDay.toISOString().split('T')[0];
                }
            }
        });
    });
    
    // Set minimum date for checkout inputs
    const checkoutInputs = document.querySelectorAll('input[name="check_out"]');
    checkoutInputs.forEach(input => {
        const checkinInput = document.querySelector('input[name="check_in"]');
        if (checkinInput && checkinInput.value) {
            const nextDay = new Date(checkinInput.value);
            nextDay.setDate(nextDay.getDate() + 1);
            input.setAttribute('min', nextDay.toISOString().split('T')[0]);
        } else {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            input.setAttribute('min', tomorrow.toISOString().split('T')[0]);
        }
    });
}

// ========================================
// MODAL FUNCTIONALITY
// ========================================

/**
 * Open modal
 * @param {string} modalId - ID of modal to open
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

/**
 * Close modal
 * @param {string} modalId - ID of modal to close
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

/**
 * Close all modals
 */
function closeAllModals() {
    const modals = document.querySelectorAll('.modal.active');
    modals.forEach(modal => {
        modal.classList.remove('active');
    });
    document.body.style.overflow = '';
}

// Close modal when clicking backdrop
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeAllModals();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllModals();
    }
});

// ========================================
// UTILITY FUNCTIONS
// ========================================

/**
 * Debounce function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 * @param {Function} func - Function to throttle
 * @param {number} limit - Time limit in milliseconds
 * @returns {Function} Throttled function
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @param {string} currency - Currency code
 * @returns {string} Formatted currency
 */
function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Format date
 * @param {string|Date} date - Date to format
 * @param {object} options - Format options
 * @returns {string} Formatted date
 */
function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    
    return new Date(date).toLocaleDateString('en-US', { ...defaultOptions, ...options });
}

/**
 * Calculate days between two dates
 * @param {string|Date} date1 - First date
 * @param {string|Date} date2 - Second date
 * @returns {number} Number of days
 */
function daysBetween(date1, date2) {
    const oneDay = 24 * 60 * 60 * 1000; // milliseconds in a day
    const firstDate = new Date(date1);
    const secondDate = new Date(date2);
    
    return Math.round(Math.abs((firstDate - secondDate) / oneDay));
}

/**
 * Get CSRF token
 * @returns {string|null} CSRF token or null
 */
function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : null;
}

/**
 * Show loading spinner on button
 * @param {HTMLElement} button - Button element
 */
function showButtonLoading(button) {
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner"></span> Loading...';
    button.dataset.originalText = originalText;
}

/**
 * Hide loading spinner on button
 * @param {HTMLElement} button - Button element
 */
function hideButtonLoading(button) {
    button.disabled = false;
    button.innerHTML = button.dataset.originalText || button.innerHTML;
}

// ========================================
// GLOBAL ERROR HANDLING
// ========================================

// Handle JavaScript errors
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // Log to server in production
    // logErrorToServer(e.error);
});

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
    // Log to server in production
    // logErrorToServer(e.reason);
});

// ========================================
// BROWSER COMPATIBILITY
// ========================================

// Polyfill for IntersectionObserver (for older browsers)
if (!window.IntersectionObserver) {
    // Fallback implementation
    window.IntersectionObserver = function() {
        this.observe = function() {};
        this.unobserve = function() {};
    };
}

// Polyfill for smooth scrolling
if (!('scrollBehavior' in document.documentElement.style)) {
    // Fallback for smooth scrolling
    window.scrollTo = function(options) {
        if (typeof options === 'object' && options.behavior === 'smooth') {
            const start = window.pageYOffset;
            const end = options.top || 0;
            const duration = 500;
            const startTime = performance.now();
            
            function scroll() {
                const now = performance.now();
                const progress = Math.min((now - startTime) / duration, 1);
                const easeProgress = 0.5 * (1 - Math.cos(Math.PI * progress));
                window.scrollTo(0, start + (end - start) * easeProgress);
                
                if (progress < 1) {
                    requestAnimationFrame(scroll);
                }
            }
            
            requestAnimationFrame(scroll);
        } else {
            // Use original scrollTo
            Element.prototype.scrollTo.apply(window, arguments);
        }
    };
}