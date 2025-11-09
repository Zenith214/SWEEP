<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWEEP - Solid Waste Evaluation and Efficiency Platform</title>
    <meta name="description" content="Streamline your waste management operations with SWEEP - a comprehensive platform for administrators, collection crews, and residents.">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="logo-text">SWEEP</span>
                </div>
                <div class="nav-links">
                    <a href="#features" class="nav-link">Features</a>
                    <a href="#benefits" class="nav-link">Benefits</a>
                    <a href="#how-it-works" class="nav-link">How It Works</a>
                    <a href="#contact" class="nav-link">Contact</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
                        <a href="#contact" class="btn btn-primary">Contact Us</a>
                    @endauth
                </div>
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="#features" class="mobile-menu-link">Features</a>
        <a href="#benefits" class="mobile-menu-link">Benefits</a>
        <a href="#how-it-works" class="mobile-menu-link">How It Works</a>
        <a href="#contact" class="mobile-menu-link">Contact</a>
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-block">Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-outline btn-block">Login</a>
            <a href="#contact" class="btn btn-primary btn-block">Contact Us</a>
        @endauth
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Revolutionize Your <span class="text-gradient">Waste Management</span>
                    </h1>
                    <p class="hero-subtitle">
                        SWEEP brings efficiency, transparency, and sustainability to waste collection operations. 
                        Manage routes, track collections, and engage residents—all in one powerful platform.
                    </p>
                    <div class="hero-actions">
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                Sign In
                                <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="#how-it-works" class="btn btn-outline btn-lg">Learn More</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Go to Dashboard</a>
                        @endguest
                    </div>
                    <div class="hero-stats">
                        <div class="stat">
                            <div class="stat-value">99.5%</div>
                            <div class="stat-label">Collection Rate</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">50%</div>
                            <div class="stat-label">Time Saved</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">24/7</div>
                            <div class="stat-label">Real-time Tracking</div>
                        </div>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="dashboard-preview">
                        <div class="preview-header">
                            <div class="preview-dots">
                                <span></span><span></span><span></span>
                            </div>
                        </div>
                        <div class="preview-content">
                            <div class="preview-sidebar"></div>
                            <div class="preview-main">
                                <div class="preview-card"></div>
                                <div class="preview-card"></div>
                                <div class="preview-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Powerful Features for Every User</h2>
                <p class="section-subtitle">Everything you need to manage waste collection efficiently</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon feature-icon-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Real-time Analytics</h3>
                    <p class="feature-description">
                        Track collection rates, recycling metrics, and crew performance with interactive dashboards and detailed reports.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Smart Route Planning</h3>
                    <p class="feature-description">
                        Optimize collection routes, assign crews efficiently, and manage schedules with our intelligent planning system.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Mobile-First Design</h3>
                    <p class="feature-description">
                        Access SWEEP from any device. Crews can log collections on-the-go with photo documentation and GPS tracking.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Resident Engagement</h3>
                    <p class="feature-description">
                        Empower residents to view schedules, submit reports, and track issue resolution through an intuitive portal.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Recycling Tracking</h3>
                    <p class="feature-description">
                        Monitor recycling rates, set targets, and track material types to promote sustainability and meet environmental goals.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon feature-icon-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Enterprise Security</h3>
                    <p class="feature-description">
                        Role-based access control, encrypted data, and comprehensive audit trails ensure your data stays secure.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="benefits">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Why Choose SWEEP?</h2>
                <p class="section-subtitle">Transform your waste management operations</p>
            </div>

            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-number">01</div>
                    <h3 class="benefit-title">Increase Efficiency</h3>
                    <p class="benefit-description">
                        Reduce operational costs by up to 30% with optimized routes, automated scheduling, and real-time tracking.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-number">02</div>
                    <h3 class="benefit-title">Improve Transparency</h3>
                    <p class="benefit-description">
                        Keep residents informed with real-time updates, schedule notifications, and instant issue resolution tracking.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-number">03</div>
                    <h3 class="benefit-title">Data-Driven Decisions</h3>
                    <p class="benefit-description">
                        Make informed decisions with comprehensive analytics, performance metrics, and customizable reports.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-number">04</div>
                    <h3 class="benefit-title">Environmental Impact</h3>
                    <p class="benefit-description">
                        Track and improve recycling rates, reduce waste, and meet sustainability goals with detailed environmental metrics.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">How SWEEP Works</h2>
                <p class="section-subtitle">Simple, powerful, and designed for everyone</p>
            </div>

            <div class="roles-tabs">
                <button class="role-tab active" data-role="admin">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Administrator
                </button>
                <button class="role-tab" data-role="crew">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Collection Crew
                </button>
                <button class="role-tab" data-role="resident">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Resident
                </button>
            </div>

            <div class="role-content active" data-role-content="admin">
                <div class="role-features">
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Comprehensive Dashboard</h4>
                            <p>Monitor all operations with real-time metrics and analytics</p>
                        </div>
                    </div>
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Route & Schedule Management</h4>
                            <p>Create, optimize, and manage collection routes and schedules</p>
                        </div>
                    </div>
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Crew & Fleet Management</h4>
                            <p>Assign crews, track trucks, and monitor performance</p>
                        </div>
                    </div>
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Advanced Reporting</h4>
                            <p>Generate detailed reports and export data in multiple formats</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="role-content" data-role-content="crew">
                <div class="role-features">
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Daily Route Assignments</h4>
                            <p>View assigned routes and collection schedules</p>
                        </div>
                    </div>
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Collection Logging</h4>
                            <p>Mark collections as complete with photo documentation</p>
                        </div>
                    </div>
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Issue Reporting</h4>
                            <p>Report route issues, blocked roads, or equipment problems</p>
                        </div>
                    </div>
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Performance Tracking</h4>
                            <p>View personal performance metrics and collection history</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="role-content" data-role-content="resident">
                <div class="role-features">
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Collection Schedule</h4>
                            <p>View pickup schedules for your area with calendar integration</p>
                        </div>
                    </div>
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Submit Reports</h4>
                            <p>Report missed pickups or issues with photo uploads</p>
                        </div>
                    </div>
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Track Status</h4>
                            <p>Monitor the status of your submitted reports in real-time</p>
                        </div>
                    </div>
                    <div class="role-feature">
                        <div class="role-feature-icon">✓</div>
                        <div>
                            <h4>Notifications</h4>
                            <p>Receive updates about schedule changes and report resolutions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Ready to Transform Your Waste Management?</h2>
                <p class="cta-subtitle">Join communities already using SWEEP to streamline operations and improve service delivery.</p>
                @guest
                    <div class="cta-actions">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            Sign In Now
                            <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#contact" class="btn btn-outline-white btn-lg">Contact Us</a>
                    </div>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Go to Dashboard</a>
                @endguest
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand">
                        <svg class="footer-logo" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>SWEEP</span>
                    </div>
                    <p class="footer-description">
                        Making waste management efficient, transparent, and sustainable for communities worldwide.
                    </p>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Product</h4>
                    <ul class="footer-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#benefits">Benefits</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="{{ route('login') }}">Sign In</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Resources</h4>
                    <ul class="footer-links">
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">API Reference</a></li>
                        <li><a href="#">Support</a></li>
                        <li><a href="#">System Status</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Company</h4>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} SWEEP. All rights reserved.</p>
                <p>Built with Laravel & Tailwind CSS</p>
            </div>
        </div>
    </footer>
</body>
</html>
