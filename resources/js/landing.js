// Landing Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            
            // Animate hamburger icon
            const spans = mobileMenuBtn.querySelectorAll('span');
            if (mobileMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translateY(8px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translateY(-8px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
        
        // Close mobile menu when clicking on a link
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                const spans = mobileMenuBtn.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            });
        });
    }
    
    // Navbar Scroll Effect
    const navbar = document.getElementById('navbar');
    let lastScroll = 0;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    });
    
    // Role Tabs
    const roleTabs = document.querySelectorAll('.role-tab');
    const roleContents = document.querySelectorAll('.role-content');
    
    roleTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const role = this.getAttribute('data-role');
            
            // Remove active class from all tabs and contents
            roleTabs.forEach(t => t.classList.remove('active'));
            roleContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            const content = document.querySelector(`[data-role-content="${role}"]`);
            if (content) {
                content.classList.add('active');
            }
        });
    });
    
    // Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Don't prevent default for empty hash
            if (href === '#') return;
            
            e.preventDefault();
            
            const target = document.querySelector(href);
            if (target) {
                const offsetTop = target.offsetTop - 80; // Account for fixed navbar
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Intersection Observer for Fade-in Animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe feature cards, benefit cards, and role features
    const animatedElements = document.querySelectorAll('.feature-card, .benefit-card, .role-feature');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
    
    // Stats Counter Animation
    const stats = document.querySelectorAll('.stat-value');
    let statsAnimated = false;
    
    const statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting && !statsAnimated) {
                statsAnimated = true;
                animateStats();
            }
        });
    }, { threshold: 0.5 });
    
    if (stats.length > 0) {
        statsObserver.observe(stats[0].parentElement.parentElement);
    }
    
    function animateStats() {
        stats.forEach(stat => {
            const text = stat.textContent;
            const hasPercent = text.includes('%');
            const hasSlash = text.includes('/');
            
            if (hasPercent) {
                const value = parseFloat(text);
                animateValue(stat, 0, value, 1500, '%');
            } else if (hasSlash) {
                // For "24/7" type stats, just fade in
                stat.style.opacity = '0';
                setTimeout(() => {
                    stat.style.transition = 'opacity 0.5s';
                    stat.style.opacity = '1';
                }, 300);
            }
        });
    }
    
    function animateValue(element, start, end, duration, suffix = '') {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= end) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = current.toFixed(1) + suffix;
        }, 16);
    }
    
    // Keyboard Navigation for Role Tabs
    roleTabs.forEach((tab, index) => {
        tab.addEventListener('keydown', function(e) {
            let newIndex;
            
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                newIndex = (index + 1) % roleTabs.length;
                roleTabs[newIndex].focus();
                roleTabs[newIndex].click();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                newIndex = (index - 1 + roleTabs.length) % roleTabs.length;
                roleTabs[newIndex].focus();
                roleTabs[newIndex].click();
            }
        });
    });
    
    // Add ARIA attributes for accessibility
    roleTabs.forEach((tab, index) => {
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-selected', tab.classList.contains('active') ? 'true' : 'false');
        tab.setAttribute('tabindex', tab.classList.contains('active') ? '0' : '-1');
    });
    
    roleContents.forEach(content => {
        content.setAttribute('role', 'tabpanel');
    });
    
    // Update ARIA attributes when tabs change
    const originalTabClick = roleTabs[0].click;
    roleTabs.forEach(tab => {
        const originalClick = tab.onclick;
        tab.onclick = function() {
            if (originalClick) originalClick.call(this);
            
            roleTabs.forEach(t => {
                t.setAttribute('aria-selected', 'false');
                t.setAttribute('tabindex', '-1');
            });
            
            this.setAttribute('aria-selected', 'true');
            this.setAttribute('tabindex', '0');
        };
    });
});
