/**
 * Hotel Reservation System - Rooms Page JavaScript
 * 
 * Handles rooms page functionality including:
 * - Room search and filtering
 * - Date validation
 * - Price calculations
 * - Interactive elements
 * 
 * @category Hotel_Reservation
 * @package  Scripts
 * @author   Hotel Reservation System
 * @license  MIT License
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize rooms page components
    initDateValidation();
    initRoomCards();
    initSearchForm();
    initFilterForm();
});

/**
 * Initialize date validation for search form
 */
function initDateValidation() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (checkInInput && checkOutInput) {
        // Set minimum date for check-in to today
        const today = new Date().toISOString().split('T')[0];
        checkInInput.setAttribute('min', today);
        
        // Update check-out minimum date when check-in changes
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const nextDay = new Date(checkInDate);
            nextDay.setDate(nextDay.getDate() + 1);
            
            const nextDayStr = nextDay.toISOString().split('T')[0];
            checkOutInput.setAttribute('min', nextDayStr);
            
            // If check-out is before or same as check-in, update it
            if (checkOutInput.value && checkOutInput.value <= this.value) {
                checkOutInput.value = nextDayStr;
            }
        });
        
        // Validate check-out date on change
        checkOutInput.addEventListener('change', function() {
            if (checkInInput.value && this.value <= checkInInput.value) {
                showError('check_out', 'Check-out date must be after check-in date');
                this.value = '';
            }
        });
    }
}

/**
 * Initialize room card interactions
 */
function initRoomCards() {
    const roomCards = document.querySelectorAll('.room-card');
    
    roomCards.forEach(card => {
        // Add click handler for entire card
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons or links
            if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.closest('button') || e.target.closest('a')) {
                return;
            }
            
            // Get room details
            const roomId = this.dataset.roomId;
            const roomType = this.dataset.roomType;
            
            if (roomId) {
                // Navigate to room details or booking
                const bookBtn = this.querySelector('.btn-primary');
                if (bookBtn) {
                    bookBtn.click();
                }
            }
        });
        
        // Add keyboard accessibility
        card.setAttribute('tabindex', '0');
        card.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                this.click();
            }
        });
    });
}

/**
 * Initialize search form functionality
 */
function initSearchForm() {
    const searchForm = document.getElementById('room-search-form');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const checkIn = document.getElementById('check_in');
            const checkOut = document.getElementById('check_out');
            const guests = document.getElementById('guests');
            
            let hasErrors = false;
            
            // Clear previous errors
            clearErrors();
            
            // Validate required fields
            if (!checkIn.value) {
                showError('check_in', 'Please select a check-in date');
                hasErrors = true;
            }
            
            if (!checkOut.value) {
                showError('check_out', 'Please select a check-out date');
                hasErrors = true;
            }
            
            if (checkIn.value && checkOut.value && checkOut.value <= checkIn.value) {
                showError('check_out', 'Check-out date must be after check-in date');
                hasErrors = true;
            }
            
            if (!guests.value || guests.value < 1) {
                showError('guests', 'Please select number of guests');
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
                return false;
            }
        });
    }
}

/**
 * Initialize filter form functionality
 */
function initFilterForm() {
    const filterForm = document.querySelector('.search-filter-form');
    
    if (filterForm) {
        // Auto-submit form when filter changes
        const filterInputs = filterForm.querySelectorAll('select, input');
        
        filterInputs.forEach(input => {
            if (input.type !== 'text' && input.type !== 'search') {
                input.addEventListener('change', function() {
                    // Add small delay for better UX
                    setTimeout(() => {
                        filterForm.submit();
                    }, 100);
                });
            }
        });
    }
}

/**
 * Show error message for a field
 * @param {string} fieldId - ID of the field
 * @param {string} message - Error message
 */
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Add error class to field
    field.classList.add('error');
    
    // Create or update error message
    let errorElement = field.parentNode.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        field.parentNode.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
}

/**
 * Clear all error messages
 */
function clearErrors() {
    const errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach(msg => msg.remove());
    
    const errorFields = document.querySelectorAll('.form-control.error');
    errorFields.forEach(field => field.classList.remove('error'));
}

/**
 * Calculate and display total price for room cards
 */
function updateRoomCardPrices() {
    const roomCards = document.querySelectorAll('.room-card');
    const urlParams = new URLSearchParams(window.location.search);
    const checkIn = urlParams.get('check_in');
    const checkOut = urlParams.get('check_out');
    
    if (checkIn && checkOut) {
        const nights = daysBetween(checkIn, checkOut);
        
        roomCards.forEach(card => {
            const priceElement = card.querySelector('.price-amount');
            const unitElement = card.querySelector('.price-unit');
            const basePrice = parseFloat(priceElement.dataset.price);
            
            if (basePrice && nights > 0) {
                const totalPrice = basePrice * nights;
                priceElement.textContent = '$' + totalPrice.toFixed(2);
                unitElement.textContent = `for ${nights} night${nights > 1 ? 's' : ''}`;
            }
        });
    }
}

/**
 * Calculate days between two dates
 * @param {string} date1 - First date
 * @param {string} date2 - Second date
 * @returns {number} Number of days
 */
function daysBetween(date1, date2) {
    const oneDay = 24 * 60 * 60 * 1000;
    const firstDate = new Date(date1);
    const secondDate = new Date(date2);
    
    return Math.round(Math.abs((secondDate - firstDate) / oneDay));
}

/**
 * Initialize room comparison feature
 */
function initRoomComparison() {
    const compareButtons = document.querySelectorAll('.compare-btn');
    
    compareButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const roomId = this.dataset.roomId;
            const roomName = this.dataset.roomName;
            
            // Add to comparison list
            addToComparison(roomId, roomName);
            
            // Update button state
            this.classList.add('active');
            this.innerHTML = '<i class="fas fa-check"></i> Added';
        });
    });
}

/**
 * Add room to comparison list
 * @param {string} roomId - Room ID
 * @param {string} roomName - Room name
 */
function addToComparison(roomId, roomName) {
    let comparisonList = JSON.parse(sessionStorage.getItem('roomComparison') || '[]');
    
    // Check if already in comparison
    if (!comparisonList.find(room => room.id === roomId)) {
        comparisonList.push({
            id: roomId,
            name: roomName
        });
        
        sessionStorage.setItem('roomComparison', JSON.stringify(comparisonList));
        
        // Update comparison badge
        updateComparisonBadge(comparisonList.length);
    }
}

/**
 * Update comparison badge
 * @param {number} count - Number of rooms in comparison
 */
function updateComparisonBadge(count) {
    let badge = document.querySelector('.comparison-badge');
    
    if (!badge) {
        badge = document.createElement('div');
        badge.className = 'comparison-badge';
        badge.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            cursor: pointer;
        `;
        document.body.appendChild(badge);
    }
    
    badge.innerHTML = `<i class="fas fa-balance-scale"></i> Compare (${count})`;
    badge.style.display = count > 0 ? 'block' : 'none';
}

// Initialize room comparison
initRoomComparison();

// Update room card prices on page load
updateRoomCardPrices();