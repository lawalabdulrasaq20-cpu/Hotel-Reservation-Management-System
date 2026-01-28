/**
 * Hotel Reservation System - Homepage JavaScript
 * 
 * Handles homepage-specific functionality including:
 * - Hero section animations
 * - Scroll-triggered effects
 * - Video background fallback
 * - Interactive elements
 * 
 * @category Hotel_Reservation
 * @package  Scripts
 * @author   Hotel Reservation System
 * @license  MIT License
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize homepage components
    initHeroAnimations();
    initScrollEffects();
    initVideoFallback();
    initScrollIndicator();
    initStatsAnimation();
    initTestimonials();
});

/**
 * Initialize hero section animations
 */
function initHeroAnimations() {
    const heroContent = document.querySelector('.hero-content');
    const heroButtons = document.querySelector('.hero-buttons');
    
    if (heroContent) {
        // Add staggered animation to hero elements
        const elements = heroContent.querySelectorAll('[data-aos]');
        elements.forEach((el, index) => {
            el.style.animationDelay = `${index * 0.2}s`;
        });
    }
    
    // Animate hero buttons
    if (heroButtons) {
        const buttons = heroButtons.querySelectorAll('.btn');
        buttons.forEach((btn, index) => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.05)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    }
}

/**
 * Initialize scroll-triggered effects
 */
function initScrollEffects() {
    // Stats section counter animation
    const statsSection = document.querySelector('.stats-section');
    
    if (statsSection) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(statsSection);
    }
}

/**
 * Initialize video fallback for mobile devices
 */
function initVideoFallback() {
    const video = document.querySelector('.video-background');
    const fallback = document.querySelector('.video-fallback');
    
    if (video && fallback) {
        // Check if device is mobile or has reduced motion preference
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (isMobile || prefersReducedMotion) {
            video.style.display = 'none';
            fallback.style.display = 'block';
        }
        
        // Handle video errors
        video.addEventListener('error', function() {
            console.log('Video failed to load, showing fallback');
            video.style.display = 'none';
            if (fallback) fallback.style.display = 'block';
        });
    }
}

/**
 * Initialize scroll indicator
 */
function initScrollIndicator() {
    const scrollIndicator = document.querySelector('.scroll-indicator');
    
    if (scrollIndicator) {
        // Hide scroll indicator after scrolling
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                scrollIndicator.style.opacity = '0';
                scrollIndicator.style.transform = 'translateY(20px)';
            } else {
                scrollIndicator.style.opacity = '1';
                scrollIndicator.style.transform = 'translateY(0)';
            }
        });
        
        // Click to scroll
        scrollIndicator.addEventListener('click', function() {
            const nextSection = document.querySelector('.stats-section');
            if (nextSection) {
                nextSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
}

/**
 * Animate counters in stats section
 */
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/[^0-9]/g, ''));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const timer = setInterval(function() {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            // Format number with appropriate suffix
            if (counter.textContent.includes('%')) {
                counter.textContent = Math.floor(current) + '%';
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 16);
    });
}

/**
 * Initialize testimonials carousel
 */
function initTestimonials() {
    const testimonialCards = document.querySelectorAll('.testimonial-card');
    
    if (testimonialCards.length > 0) {
        // Add hover effects
        testimonialCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.05)';
            });
        });
    }
}

/**
 * Smooth scrolling for anchor links
 */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

/**
 * Parallax effect for hero section
 */
function initParallax() {
    const heroSection = document.querySelector('.hero-section');
    
    if (heroSection) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallaxElement = heroSection.querySelector('.video-background, .hero-background');
            
            if (parallaxElement) {
                const speed = scrolled * 0.5;
                parallaxElement.style.transform = `translateY(${speed}px)`;
            }
        });
    }
}

// Initialize parallax
initParallax();