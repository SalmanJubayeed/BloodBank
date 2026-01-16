// Blood Donation System - Interactive Features

document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile Menu Toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('mobile-menu-open');
            const icon = mobileMenuBtn.querySelector('i');
            if (navLinks.classList.contains('mobile-menu-open')) {
                icon.className = 'fas fa-times';
                navLinks.style.display = 'flex';
                navLinks.style.flexDirection = 'column';
                navLinks.style.position = 'absolute';
                navLinks.style.top = '100%';
                navLinks.style.left = '0';
                navLinks.style.right = '0';
                navLinks.style.background = 'var(--primary-color)';
                navLinks.style.padding = '1rem';
                navLinks.style.boxShadow = 'var(--shadow)';
                navLinks.style.zIndex = '1000';
            } else {
                icon.className = 'fas fa-bars';
                navLinks.style.display = 'none';
            }
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileMenuBtn.contains(e.target) && !navLinks.contains(e.target)) {
                navLinks.classList.remove('mobile-menu-open');
                navLinks.style.display = 'none';
                mobileMenuBtn.querySelector('i').className = 'fas fa-bars';
            }
        });
    }

    // Form Validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('input[required], select[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                    field.focus();
                } else {
                    field.style.borderColor = '#e9ecef';
                }
            });

            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                    showNotification('Please enter a valid email address', 'error');
                }
            });

            // Password validation
            const passwordFields = form.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => {
                if (field.value && field.value.length < 6) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                    showNotification('Password must be at least 6 characters long', 'error');
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // Real-time form validation
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });

        input.addEventListener('input', function() {
            if (this.style.borderColor === 'rgb(220, 53, 69)') {
                validateField(this);
            }
        });
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Confirmation dialogs for delete actions
    const deleteButtons = document.querySelectorAll('button[name="delete_user"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Table sorting (for admin tables)
    const tables = document.querySelectorAll('.admin-table');
    tables.forEach(table => {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => sortTable(table, index));
        });
    });

    // Card hover effects
    const cards = document.querySelectorAll('.donor-card, .request-card, .dashboard-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Blood type filter (if exists)
    const bloodTypeFilter = document.getElementById('blood-type-filter');
    if (bloodTypeFilter) {
        bloodTypeFilter.addEventListener('change', function() {
            filterByBloodType(this.value);
        });
    }

    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchItems(this.value);
        });

// Additional JavaScript for the simplified blood bank system
// Add this to your existing script.js or create a new one

// Modal Functions for Donor Dashboard
function showApplyModal(requestId, recipientName, bloodGroup) {
    document.getElementById('requestId').value = requestId;
    document.getElementById('recipientName').textContent = recipientName;
    document.getElementById('bloodGroup').textContent = bloodGroup;
    document.getElementById('applyModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeApplyModal() {
    document.getElementById('applyModal').style.display = 'none';
    document.getElementById('message').value = '';
    document.body.style.overflow = 'auto'; // Restore scrolling
}

// Modal Functions for Recipient Dashboard
function showRequestModal() {
    document.getElementById('requestModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeRequestModal() {
    document.getElementById('requestModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    // Reset form
    if (document.querySelector('#requestModal form')) {
        document.querySelector('#requestModal form').reset();
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const applyModal = document.getElementById('applyModal');
    const requestModal = document.getElementById('requestModal');
    
    if (event.target === applyModal) {
        closeApplyModal();
    }
    
    if (event.target === requestModal) {
        closeRequestModal();
    }
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const applyModal = document.getElementById('applyModal');
        const requestModal = document.getElementById('requestModal');
        
        if (applyModal && applyModal.style.display === 'block') {
            closeApplyModal();
        }
        
        if (requestModal && requestModal.style.display === 'block') {
            closeRequestModal();
        }
    }
});

// Form Validation for Blood Request
document.addEventListener('DOMContentLoaded', function() {
    const requestForm = document.querySelector('#requestModal form');
    if (requestForm) {
        requestForm.addEventListener('submit', function(e) {
            const bloodGroup = document.getElementById('blood_group').value;
            const unitsNeeded = document.getElementById('units_needed').value;
            const urgencyLevel = document.getElementById('urgency_level').value;
            
            if (!bloodGroup || !unitsNeeded || !urgencyLevel) {
                e.preventDefault();
                alert('Please fill in all required fields (Blood Group, Units Needed, and Urgency Level).');
                return false;
            }
            
            if (parseInt(unitsNeeded) < 1 || parseInt(unitsNeeded) > 10) {
                e.preventDefault();
                alert('Units needed must be between 1 and 10.');
                return false;
            }
        });
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });
});

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
});

// Form character counter for message fields
function setupCharacterCounter(textareaId, maxLength = 500) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;
    
    const counter = document.createElement('div');
    counter.className = 'character-counter';
    counter.style.cssText = 'text-align: right; font-size: 0.8em; color: #6c757d; margin-top: 5px;';
    
    textarea.parentNode.insertBefore(counter, textarea.nextSibling);
    
    function updateCounter() {
        const remaining = maxLength - textarea.value.length;
        counter.textContent = `${textarea.value.length}/${maxLength} characters`;
        
        if (remaining < 50) {
            counter.style.color = '#dc3545';
        } else if (remaining < 100) {
            counter.style.color = '#ffc107';
        } else {
            counter.style.color = '#6c757d';
        }
    }
    
    textarea.addEventListener('input', updateCounter);
    textarea.setAttribute('maxlength', maxLength);
    updateCounter();
}

// Initialize character counters when page loads
document.addEventListener('DOMContentLoaded', function() {
    setupCharacterCounter('message', 500);
    setupCharacterCounter('additional_notes', 1000);
});

// Confirmation dialogs for important actions
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Application approval/rejection with confirmation
document.addEventListener('DOMContentLoaded', function() {
    const approveButtons = document.querySelectorAll('button[name="handle_application"][value*="approve"]');
    const rejectButtons = document.querySelectorAll('button[name="handle_application"][value*="reject"]');
    
    approveButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to approve this donor application?')) {
                e.preventDefault();
            }
        });
    });
    
    rejectButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to reject this donor application?')) {
                e.preventDefault();
            }
        });
    });
});

// Real-time form validation
function setupRealTimeValidation() {
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
            if (this.value && !isValid) {
                this.style.borderColor = '#dc3545';
                showFieldError(this, 'Please enter a valid email address');
            } else {
                this.style.borderColor = '';
                hideFieldError(this);
            }
        });
    });
    
    // Phone validation (basic)
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const isValid = /^[\+]?[1-9][\d]{0,15}$/.test(this.value.replace(/[\s\-\(\)]/g, ''));
            if (this.value && !isValid) {
                this.style.borderColor = '#dc3545';
                showFieldError(this, 'Please enter a valid phone number');
            } else {
                this.style.borderColor = '';
                hideFieldError(this);
            }
        });
    });
}

function showFieldError(field, message) {
    hideFieldError(field); // Remove existing error
    const error = document.createElement('div');
    error.className = 'field-error';
    error.style.cssText = 'color: #dc3545; font-size: 0.8em; margin-top: 5px;';
    error.textContent = message;
    field.parentNode.appendChild(error);
}

function hideFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Initialize real-time validation
document.addEventListener('DOMContentLoaded', setupRealTimeValidation);

// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Loading state for form submissions
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButtons = this.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(button => {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                button.disabled = true;
                
                // Re-enable after 10 seconds as fallback
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 10000);
            });
        });
    });
});

// Copy to clipboard functionality (for contact info)
function copyToClipboard(text, element) {
    navigator.clipboard.writeText(text).then(function() {
        const originalText = element.innerHTML;
        element.innerHTML = '<i class="fas fa-check"></i> Copied!';
        element.style.backgroundColor = '#28a745';
        
        setTimeout(function() {
            element.innerHTML = originalText;
            element.style.backgroundColor = '';
        }, 2000);
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        const originalText = element.innerHTML;
        element.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(function() {
            element.innerHTML = originalText;
        }, 2000);
    });
}