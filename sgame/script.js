// ===================================
//  MODERN SATTA MATKA LIVE - JAVASCRIPT
//  Interactive & Animated Features
// ===================================

// ========== INITIALIZATION ========== //
document.addEventListener('DOMContentLoaded', function() {
    initializeTheme();
    initializePreloader();
    initializeParticles();
    initializeAOS();
    initializeNavigation();
    initializeLiveTicker();
    initializeFloatingActions();
    initializeScrollTop();
    initializeLiveResults();
    initializeCounters();
    initializeCharts();
    initializeNotifications();
    initializeModals();
    initializeTooltips();
    initializeTableInteractions();
    initializeNewsletter();
    updateDateTime();
});

// ========== THEME (LIGHT/DARK) ========== //
function initializeTheme() {
    const saved = localStorage.getItem('theme');
    const prefersLight = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;
    const theme = saved || (prefersLight ? 'light' : 'light');
    applyTheme(theme);

    const btn = document.getElementById('themeToggle');
    if (btn) {
        btn.addEventListener('click', toggleTheme);
        updateThemeToggleIcon(theme);
    }
}

function applyTheme(theme) {
    document.body.setAttribute('data-theme', theme);
    updateThemeToggleIcon(theme);
}

function toggleTheme() {
    const current = document.body.getAttribute('data-theme') || 'light';
    const next = current === 'light' ? 'dark' : 'light';
    applyTheme(next);
    localStorage.setItem('theme', next);
}

function updateThemeToggleIcon(theme) {
    const btn = document.getElementById('themeToggle');
    if (!btn) return;
    btn.innerHTML = theme === 'light' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
    btn.title = theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode';
}

// ========== PRELOADER ========== //
function initializePreloader() {
    const preloader = document.getElementById('preloader');
    
    window.addEventListener('load', () => {
        setTimeout(() => {
            preloader.classList.add('hidden');
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        }, 1500);
    });
}

// ========== PARTICLES.JS INITIALIZATION ========== //
function initializeParticles() {
    if (typeof particlesJS !== 'undefined') {
        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 80,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: ['#6366f1', '#8b5cf6', '#10b981', '#f59e0b']
                },
                shape: {
                    type: 'circle',
                },
                opacity: {
                    value: 0.5,
                    random: true,
                    anim: {
                        enable: true,
                        speed: 1,
                        opacity_min: 0.1,
                        sync: false
                    }
                },
                size: {
                    value: 3,
                    random: true,
                    anim: {
                        enable: true,
                        speed: 2,
                        size_min: 0.1,
                        sync: false
                    }
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#6366f1',
                    opacity: 0.2,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: false,
                    straight: false,
                    out_mode: 'out',
                    bounce: false,
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: {
                        enable: true,
                        mode: 'repulse'
                    },
                    onclick: {
                        enable: true,
                        mode: 'push'
                    },
                    resize: true
                },
                modes: {
                    repulse: {
                        distance: 100,
                        duration: 0.4
                    },
                    push: {
                        particles_nb: 4
                    }
                }
            },
            retina_detect: true
        });
    }
}

// ========== AOS ANIMATION ========== //
function initializeAOS() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false,
            offset: 100
        });
    }
}

// ========== NAVIGATION ========== //
function initializeNavigation() {
    const navbar = document.querySelector('.navbar-glass');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Active link on scroll
    const sections = document.querySelectorAll('section[id]');
    
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.scrollY >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
    
    // Smooth scroll
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Close mobile menu
                    const navbarCollapse = document.querySelector('.navbar-collapse');
                    if (navbarCollapse.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                        bsCollapse.hide();
                    }
                }
            }
        });
    });
}

// ========== FLOATING ACTION BUTTON ========== //
function initializeFloatingActions() {
    const fabMain = document.getElementById('fabMain');
    const fabOptions = document.getElementById('fabOptions');
    let isOpen = false;
    
    fabMain.addEventListener('click', () => {
        isOpen = !isOpen;
        if (isOpen) {
            fabOptions.classList.add('active');
            fabMain.style.transform = 'rotate(45deg)';
        } else {
            fabOptions.classList.remove('active');
            fabMain.style.transform = 'rotate(0deg)';
        }
    });
    
    // Close when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.floating-actions') && isOpen) {
            fabOptions.classList.remove('active');
            fabMain.style.transform = 'rotate(0deg)';
            isOpen = false;
        }
    });
    
    // FAB option actions
    const fabOptionButtons = document.querySelectorAll('.fab-option');
    fabOptionButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            const tooltip = btn.getAttribute('data-tooltip');
            handleFabAction(tooltip);
        });
    });
}

function handleFabAction(action) {
    switch(action) {
        case 'Refresh Results':
            refreshAllResults();
            showToast('Refreshing results...', 'info');
            break;
        case 'Notifications':
            toggleNotifications();
            break;
        case 'Share':
            shareWebsite();
            break;
        case 'Settings':
            showToast('Settings coming soon!', 'info');
            break;
    }
}

// ========== SCROLL TO TOP ========== //
function initializeScrollTop() {
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            scrollTopBtn.classList.add('show');
        } else {
            scrollTopBtn.classList.remove('show');
        }
    });
    
    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ========== LIVE RESULTS ========== //
function initializeLiveResults() {
    // Simulate live updates every 30 seconds
    updateLiveResults();
    setInterval(updateLiveResults, 30000);
    
    // Progress bars
    animateProgressBars();
    setInterval(animateProgressBars, 60000);
}

function updateLiveResults() {
    const resultCards = document.querySelectorAll('.result-card-modern');
    
    resultCards.forEach((card, index) => {
        const liveIndicator = card.querySelector('.live-indicator');
        if (liveIndicator && !liveIndicator.classList.contains('inactive')) {
            // Randomly update results
            if (Math.random() > 0.5) {
                updateCardResult(card);
            }
        }
    });
    
    updateLastUpdated();
}

function updateCardResult(card) {
    const resultDigits = card.querySelectorAll('.result-value .digit');
    
    resultDigits.forEach((digit, index) => {
        if (digit.textContent === '*') {
            // Animate digit reveal
            animateDigitChange(digit, Math.floor(Math.random() * 10));
        }
    });
}

function animateDigitChange(element, newValue) {
    element.style.transform = 'rotateX(90deg)';
    element.style.opacity = '0';
    
    setTimeout(() => {
        element.textContent = newValue;
        element.style.transform = 'rotateX(0deg)';
        element.style.opacity = '1';
    }, 200);
}

function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-fill');
    progressBars.forEach(bar => {
        const currentWidth = parseInt(bar.style.width) || 0;
        const newWidth = Math.min(currentWidth + 10, 100);
        bar.style.width = `${newWidth}%`;
        
        if (newWidth >= 100) {
            setTimeout(() => {
                bar.style.width = '0%';
            }, 1000);
        }
    });
}

function updateLastUpdated() {
    const lastUpdatedEl = document.getElementById('lastUpdated');
    if (lastUpdatedEl) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        lastUpdatedEl.textContent = timeString;
    }
}

function refreshAllResults() {
    const resultCards = document.querySelectorAll('.result-card-modern');
    resultCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.transform = 'rotateY(360deg)';
            updateCardResult(card);
            setTimeout(() => {
                card.style.transform = '';
            }, 600);
        }, index * 100);
    });
    
    setTimeout(() => {
        showToast('Results updated successfully!', 'success');
    }, 1000);
}

// ========== COUNTERS ========== //
function initializeCounters() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    
    const animateCounter = (counter) => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => {
        counter.textContent = '0';
        observer.observe(counter);
    });
}

// ========== CHARTS ========== //
function initializeCharts() {
    const chartCanvas = document.getElementById('weeklyChart');
    if (chartCanvas && typeof Chart !== 'undefined') {
        const ctx = chartCanvas.getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.5)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Results Published',
                    data: [65, 78, 90, 81, 96, 88, 92],
                    backgroundColor: gradient,
                    borderColor: '#6366f1',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderColor: '#6366f1',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
    }
}

// ========== NOTIFICATIONS ========== //
function initializeNotifications() {
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        setTimeout(() => {
            showToast('Enable notifications for live result updates!', 'info');
        }, 5000);
    }
    
    // Simulate random notifications
    setInterval(() => {
        if (Math.random() > 0.7) {
            const messages = [
                'New result updated for Kalyan Matka!',
                'Milan Day result is now live!',
                'Rajdhani Day result declared!',
                'Time Bazar result updated!'
            ];
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            showToast(randomMessage, 'success');
        }
    }, 120000); // Every 2 minutes
}

function toggleNotifications() {
    if ('Notification' in window) {
        if (Notification.permission === 'granted') {
            showToast('Notifications are already enabled!', 'success');
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    showToast('Notifications enabled successfully!', 'success');
                    new Notification('Satta Matka Live', {
                        body: 'You will now receive live result updates!',
                        icon: '🎲'
                    });
                }
            });
        } else {
            showToast('Notifications are blocked. Please enable them in your browser settings.', 'warning');
        }
    }
}

// ========== TOAST NOTIFICATIONS ========== //
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };
    
    toast.innerHTML = `
        ${icons[type]}
        <span>${message}</span>
        <button class="toast-close">&times;</button>
    `;
    
    document.body.appendChild(toast);
    
    // Add styles dynamically if not exists
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            .toast-notification {
                position: fixed;
                top: 100px;
                right: 20px;
                min-width: 300px;
                padding: 1rem 1.5rem;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                display: flex;
                align-items: center;
                gap: 1rem;
                z-index: 10000;
                animation: slideInRight 0.3s ease;
                border-left: 4px solid;
            }
            
            .toast-success { border-color: #10b981; color: #10b981; }
            .toast-error { border-color: #ef4444; color: #ef4444; }
            .toast-warning { border-color: #f59e0b; color: #f59e0b; }
            .toast-info { border-color: #6366f1; color: #6366f1; }
            
            .toast-notification i { font-size: 1.5rem; }
            .toast-notification span { flex: 1; color: #333; }
            .toast-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                color: #999;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @media (max-width: 576px) {
                .toast-notification {
                    right: 10px;
                    left: 10px;
                    min-width: auto;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 5000);
    
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.remove();
    });
}

// ========== MODALS ========== //
function initializeModals() {
    // Show disclaimer modal on first visit
    if (!localStorage.getItem('disclaimerShown')) {
        setTimeout(() => {
            const disclaimerModal = new bootstrap.Modal(document.getElementById('disclaimerModal'));
            disclaimerModal.show();
            localStorage.setItem('disclaimerShown', 'true');
        }, 2000);
    }
}

// ========== TOOLTIPS ========== //
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// ========== TABLE INTERACTIONS ========== //
function initializeTableInteractions() {
    const tableRows = document.querySelectorAll('.table-row-animated');
    
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            const marketBadge = this.querySelector('.market-badge');
            if (marketBadge) {
                const marketName = marketBadge.textContent.trim();
                showToast(`Viewing details for ${marketName}`, 'info');
            }
        });
    });
    
    // Filter buttons
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.textContent.trim();
            showToast(`Filtering results: ${filter}`, 'info');
            
            // Simulate filtering animation
            const tableRows = document.querySelectorAll('.table-row-animated');
            tableRows.forEach((row, index) => {
                row.style.animation = 'none';
                setTimeout(() => {
                    row.style.animation = `fadeInUp 0.3s ease ${index * 0.05}s both`;
                }, 10);
            });
        });
    });
    
    // Pagination
    const paginationBtns = document.querySelectorAll('.pagination-btn:not(:disabled)');
    paginationBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.classList.contains('active')) {
                paginationBtns.forEach(b => b.classList.remove('active'));
                if (!this.querySelector('i')) {
                    this.classList.add('active');
                }
                
                // Scroll to top of table
                document.querySelector('.table-card-modern').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ========== NEWSLETTER ========== //
function initializeNewsletter() {
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value;
            
            if (email) {
                showToast('Thank you for subscribing!', 'success');
                emailInput.value = '';
                
                // Animate subscription success
                const newsletterIcon = document.querySelector('.newsletter-icon');
                newsletterIcon.style.transform = 'scale(1.2) rotate(360deg)';
                setTimeout(() => {
                    newsletterIcon.style.transform = '';
                }, 600);
            }
        });
    }
}

// ========== SHARE FUNCTIONALITY ========== //
function shareWebsite() {
    if (navigator.share) {
        navigator.share({
            title: 'Satta Matka Live Results',
            text: 'Check out real-time satta matka results!',
            url: window.location.href
        })
        .then(() => showToast('Thanks for sharing!', 'success'))
        .catch(() => {});
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href)
            .then(() => showToast('Link copied to clipboard!', 'success'))
            .catch(() => showToast('Unable to share', 'error'));
    }
}

// ========== DATE TIME UPDATE ========== //
function updateDateTime() {
    setInterval(() => {
        updateLastUpdated();
    }, 1000);
}

// ========== LIVE TICKER ========== //
function initializeLiveTicker() {
    const tickerWrapper = document.querySelector('.ticker-wrapper');
    if (tickerWrapper) {
        // Clone content for seamless loop
        const tickerContent = tickerWrapper.innerHTML;
        tickerWrapper.innerHTML = tickerContent + tickerContent;
    }
}

// ========== KEYBOARD SHORTCUTS ========== //
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + R = Refresh Results
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        refreshAllResults();
    }
    
    // Escape = Close FAB menu
    if (e.key === 'Escape') {
        const fabOptions = document.getElementById('fabOptions');
        const fabMain = document.getElementById('fabMain');
        if (fabOptions.classList.contains('active')) {
            fabOptions.classList.remove('active');
            fabMain.style.transform = 'rotate(0deg)';
        }
    }
});

// ========== PERFORMANCE OPTIMIZATION ========== //
// Lazy load images
const lazyImages = document.querySelectorAll('img[loading="lazy"]');
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                imageObserver.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
}

// ========== ERROR HANDLING ========== //
window.addEventListener('error', function(e) {
    console.error('Error occurred:', e.error);
});

// ========== SERVICE WORKER ========== //
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registered');
            })
            .catch(error => {
                console.log('ServiceWorker registration failed');
            });
    });
}

// ========== EXPORT FUNCTIONS ========== //
window.sattaMatkaLive = {
    refreshResults: refreshAllResults,
    showToast: showToast,
    shareWebsite: shareWebsite,
    toggleNotifications: toggleNotifications
};

console.log('%c🎲 Satta Matka Live - Loaded Successfully! 🎲', 'color: #6366f1; font-size: 20px; font-weight: bold;');// Satta Matka Live - Interactive JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initializeCarousel();
    initializeLiveResults();
    initializeScrollEffects();
    initializeResponsiveTable();
});

// Carousel Initialization
function initializeCarousel() {
    const carousel = document.getElementById('heroCarousel');
    if (carousel) {
        // Auto-play with custom interval
        setInterval(() => {
            const nextBtn = carousel.querySelector('.carousel-control-next');
            if (nextBtn) {
                nextBtn.click();
            }
        }, 5000);
    }
}

// Live Results Simulation
function initializeLiveResults() {
    const liveCards = document.querySelectorAll('.live-card');
    
    // Simulate live updates every 30 seconds
    setInterval(() => {
        updateLiveResults();
    }, 30000);
    
    // Initial update
    setTimeout(updateLiveResults, 2000);
}

function updateLiveResults() {
    const resultDisplays = document.querySelectorAll('.result-display h3');
    const liveIndicators = document.querySelectorAll('.live-indicator .badge');
    
    resultDisplays.forEach((display, index) => {
        if (display.textContent.includes('***')) {
            // Simulate random number generation
            const randomResult = generateRandomResult();
            
            // Add loading animation
            display.innerHTML = '<span class="loading"></span>';
            
            // Update after 2 seconds
            setTimeout(() => {
                display.textContent = randomResult;
                display.style.color = getRandomColor();
                
                // Update indicator
                if (liveIndicators[index]) {
                    liveIndicators[index].textContent = 'UPDATED';
                    liveIndicators[index].className = 'badge bg-info pulse';
                }
            }, 2000);
        }
    });
}

function generateRandomResult() {
    const part1 = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    const part2 = Math.floor(Math.random() * 100).toString().padStart(2, '0');
    const part3 = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    return `${part1}-${part2}-${part3}`;
}

function getRandomColor() {
    const colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#6f42c1'];
    return colors[Math.floor(Math.random() * colors.length)];
}

// Smooth Scrolling for Navigation Links
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

// Scroll Effects
function initializeScrollEffects() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all cards and sections
    document.querySelectorAll('.live-card, .info-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// Responsive Table Enhancement
function initializeResponsiveTable() {
    const table = document.querySelector('.table-responsive table');
    if (table && window.innerWidth <= 768) {
        // Add mobile-friendly features
        addMobileTableFeatures();
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            addMobileTableFeatures();
        }
    });
}

function addMobileTableFeatures() {
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            // Highlight selected row on mobile
            tableRows.forEach(r => r.classList.remove('table-active'));
            this.classList.add('table-active');
        });
    });
}

// Real-time Clock
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-IN', {
        timeZone: 'Asia/Kolkata',
        hour12: true
    });
    
    // Update any clock elements if they exist
    const clockElements = document.querySelectorAll('.live-clock');
    clockElements.forEach(clock => {
        clock.textContent = timeString;
    });
}

// Update clock every second
setInterval(updateClock, 1000);

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.top = '100px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Simulate notifications for live updates
setInterval(() => {
    const messages = [
        'New result updated for Kalyan Matka!',
        'Milan Day result is now live!',
        'Live updates available for all markets!'
    ];
    
    const randomMessage = messages[Math.floor(Math.random() * messages.length)];
    showNotification(randomMessage, 'success');
}, 60000); // Every minute

// Performance Optimization
function optimizeImages() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.loading = 'lazy';
    });
}

// Initialize optimizations
optimizeImages();

// Error Handling
window.addEventListener('error', function(e) {
    console.error('An error occurred:', e.error);
});

// Service Worker Registration (for offline support)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registration successful');
            })
            .catch(error => {
                console.log('ServiceWorker registration failed');
            });
    });
}

// Print functionality
function printResults() {
    const printWindow = window.open('', '_blank');
    const tableHtml = document.querySelector('#recent-results').innerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Satta Matka Results</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { font-family: Arial, sans-serif; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h2 class="text-center mb-4">Satta Matka Results</h2>
                ${tableHtml}
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

// Export functions for global access
window.printResults = printResults;
window.showNotification = showNotification;