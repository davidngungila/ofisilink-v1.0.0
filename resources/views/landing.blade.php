<!DOCTYPE html>
<html
  lang="en"
  class="light-style"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('assets/') }}"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>OfisiLink - Office Management System</title>

    <meta name="description" content="OfisiLink - Comprehensive Office Management System" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Custom OfisiLink Advanced Styles -->
    <style>
      :root {
        --bs-primary: #940000 !important;
        --bs-primary-rgb: 148, 0, 0 !important;
        --bs-primary-hover: #a80000 !important;
        --animation-speed: 0.3s;
      }
      
      * {
        scroll-behavior: smooth;
      }
      
      body {
        overflow-x: hidden;
      }
      
      /* Primary Color Overrides */
      .btn-primary, .bg-primary { 
        background-color: #940000 !important; 
        border-color: #940000 !important; 
      }
      .btn-primary:hover, .bg-primary:hover { 
        background-color: #a80000 !important; 
        border-color: #a80000 !important; 
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(148, 0, 0, 0.3);
      }
      .text-primary { color: #940000 !important; }
      
      /* Advanced Navigation */
      .navbar {
        transition: all 0.3s ease;
        padding: 1rem 0;
      }
      
      .navbar.scrolled {
        background-color: rgba(255, 255, 255, 0.98) !important;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        padding: 0.5rem 0;
      }
      
      .navbar-brand {
        color: #940000 !important;
        font-weight: 700;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
        padding: 0;
      }
      
      .navbar-brand:hover {
        transform: scale(1.05);
      }
      
      .navbar-brand img {
        height: 50px;
        width: auto;
        transition: transform 0.3s ease;
      }
      
      .navbar-brand:hover img {
        transform: rotate(5deg);
      }
      
      .navbar-brand-text {
        display: none !important;
      }
      
      .nav-link {
        font-weight: 500;
        color: #333 !important;
        margin: 0 0.5rem;
        position: relative;
        transition: color 0.3s ease;
      }
      
      .nav-link::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        width: 0;
        height: 2px;
        background: #940000;
        transition: all 0.3s ease;
        transform: translateX(-50%);
      }
      
      .nav-link:hover::after {
        width: 100%;
      }
      
      .nav-link:hover {
        color: #940000 !important;
      }
      
      /* Advanced Hero Section */
      .hero-section {
        position: relative;
        overflow: hidden;
        min-height: 100vh;
        padding: 0;
      }
      
      .hero-section .carousel {
        min-height: 100vh;
      }
      
      .hero-section .carousel-inner {
        min-height: 100vh;
      }
      
      .hero-slide {
        padding: 120px 0 100px;
        position: relative;
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
      }
      
      .hero-slide::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
        animation: float 20s infinite linear;
      }
      
      @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
      }
      
      .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
        animation: float 20s infinite linear;
      }
      
      @keyframes float {
        0% { transform: translateY(0); }
        100% { transform: translateY(-100px); }
      }
      
      .hero-content {
        position: relative;
        z-index: 2;
      }
      
      .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        animation: fadeInUp 1s ease;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
      }
      
      .hero-subtitle {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        animation: fadeInUp 1s ease 0.2s both;
        opacity: 0.95;
      }
      
      .hero-description {
        font-size: 1.1rem;
        line-height: 1.8;
        margin-bottom: 2rem;
        animation: fadeInUp 1s ease 0.4s both;
        opacity: 0.9;
      }
      
      .hero-image {
        animation: fadeInRight 1s ease 0.6s both, floatImage 3s ease-in-out infinite;
        position: relative;
        z-index: 2;
      }
      
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      @keyframes fadeInRight {
        from {
          opacity: 0;
          transform: translateX(30px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }
      
      @keyframes floatImage {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
      }
      
      /* Advanced Feature Cards */
      .feature-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(148, 0, 0, 0.1);
        overflow: hidden;
        position: relative;
        background: white;
      }
      
      .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #940000, #a80000);
        transform: scaleX(0);
        transition: transform 0.4s ease;
      }
      
      .feature-card:hover::before {
        transform: scaleX(1);
      }
      
      .feature-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 15px 35px rgba(148, 0, 0, 0.2);
      }
      
      .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #940000 0%, #a80000 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        color: white;
        font-size: 32px;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
      }
      
      .feature-icon::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
      }
      
      .feature-card:hover .feature-icon {
        transform: rotate(360deg) scale(1.1);
      }
      
      .feature-card:hover .feature-icon::before {
        width: 200px;
        height: 200px;
      }
      
      /* Statistics Section */
      .stats-section {
        background: linear-gradient(135deg, #940000 0%, #a80000 100%);
        color: white;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
      }
      
      .stats-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 50px 50px;
        animation: rotate 20s linear infinite;
      }
      
      @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
      }
      
      .stat-card {
        text-align: center;
        padding: 30px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
      }
      
      .stat-card:hover {
        transform: translateY(-10px);
        background: rgba(255, 255, 255, 0.15);
      }
      
      .stat-number {
        font-size: 3.5rem;
        font-weight: 800;
        margin: 15px 0;
        display: block;
      }
      
      .stat-label {
        font-size: 1.1rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
      }
      
      /* Benefits Section */
      .benefit-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #940000 0%, #a80000 100%);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 28px;
        transition: all 0.4s ease;
        box-shadow: 0 5px 15px rgba(148, 0, 0, 0.3);
      }
      
      .benefit-icon:hover {
        transform: rotate(-10deg) scale(1.1);
        box-shadow: 0 10px 25px rgba(148, 0, 0, 0.4);
      }
      
      /* Testimonials Section */
      .testimonial-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        border-left: 4px solid #940000;
      }
      
      .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(148, 0, 0, 0.2);
      }
      
      .testimonial-quote {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #555;
        margin-bottom: 20px;
        font-style: italic;
      }
      
      .testimonial-author {
        font-weight: 600;
        color: #940000;
      }
      
      /* CTA Section */
      .cta-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 100px 0;
        position: relative;
      }
      
      .cta-content {
        text-align: center;
        position: relative;
        z-index: 2;
      }
      
      .cta-button {
        padding: 15px 40px;
        font-size: 1.2rem;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }
      
      .cta-button::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
      }
      
      .cta-button:hover::before {
        width: 300px;
        height: 300px;
      }
      
      /* Footer */
      footer {
        background: #1a1a1a !important;
        padding: 50px 0 30px;
      }
      
      .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
      }
      
      .footer-logo {
        height: 50px;
        margin-bottom: 15px;
      }
      
      /* Animations */
      .fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease;
      }
      
      .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
      }
      
      /* Responsive */
      @media (max-width: 768px) {
        .hero-title {
          font-size: 2.5rem;
        }
        
        .hero-subtitle {
          font-size: 1.2rem;
        }
        
        .stat-number {
          font-size: 2.5rem;
        }
      }
      
      /* Scroll Progress Bar */
      .scroll-progress {
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 4px;
        background: linear-gradient(90deg, #940000, #a80000);
        z-index: 9999;
        transition: width 0.1s ease;
      }
      
      /* Pricing Cards */
      .pricing-card {
        transition: all 0.3s ease;
      }
      
      .pricing-card:hover {
        transform: translateY(-10px);
      }
      
      /* Accordion Styles */
      .accordion-button {
        background-color: white;
        border: none;
        font-weight: 600;
        color: #333;
      }
      
      .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #940000;
      }
      
      .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(148, 0, 0, 0.25);
      }
      
      /* Integration Cards */
      .integration-card {
        transition: all 0.3s ease;
        cursor: pointer;
      }
      
      .integration-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(148, 0, 0, 0.15) !important;
      }
      
      /* Video Demo Section */
      .video-placeholder {
        background: linear-gradient(135deg, #940000 0%, #a80000 100%);
      }
      
      /* Mobile App Section */
      .mobile-feature {
        transition: all 0.3s ease;
      }
      
      .mobile-feature:hover {
        transform: scale(1.05);
      }
      
      /* Workflow Steps */
      .workflow-steps {
        position: relative;
      }
      
      .workflow-step-number {
        flex-shrink: 0;
        position: relative;
        z-index: 2;
      }
      
      .workflow-steps .d-flex:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 20px;
        top: 40px;
        width: 2px;
        height: calc(100% - 20px);
        background: linear-gradient(180deg, #940000, #a80000);
        z-index: 1;
      }
      
      .workflow-steps .d-flex:last-child .workflow-step-number {
        border: 3px solid #28a745;
      }
      
      /* Workflow Card Expandable */
      .workflow-card {
        transition: all 0.3s ease;
      }
      
      .workflow-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(148, 0, 0, 0.15) !important;
      }
      
      .workflow-card[aria-expanded="true"] .workflow-arrow {
        transform: rotate(180deg);
      }
      
      .workflow-card[aria-expanded="true"] {
        border: 2px solid #940000;
      }
      
      /* Workflow Section Improvements */
      #workflows .row.mb-5 {
        margin-bottom: 3rem !important;
      }
      
      #workflows h3 {
        font-size: 1.75rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
      }
      
      .workflow-steps {
        padding: 1rem 0;
      }
      
      .workflow-steps .d-flex {
        padding: 0.75rem;
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
      }
      
      .workflow-steps .d-flex:hover {
        background-color: #f8f9fa;
        border-left-color: #940000;
        border-radius: 5px;
      }
      
      .workflow-step-number {
        flex-shrink: 0;
      }
      
      @media (max-width: 768px) {
        #workflows h3 {
          font-size: 1.5rem;
        }
        
        .workflow-card {
          margin-bottom: 1rem;
        }
      }
      
      /* Timeline Styles */
      .timeline {
        position: relative;
        padding-left: 0;
      }
      
      .timeline-item {
        position: relative;
      }
      
      .timeline-marker {
        flex-shrink: 0;
        box-shadow: 0 4px 15px rgba(148, 0, 0, 0.3);
      }
      
      /* Comparison Table */
      .table thead th {
        border-bottom: 3px solid #940000;
      }
      
      .table tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.3s ease;
      }
      
      /* ROI Cards */
      .roi-card {
        transition: all 0.3s ease;
      }
      
      .roi-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(148, 0, 0, 0.2);
      }
      
      /* Slider Styles */
      .carousel-fade .carousel-item {
        opacity: 0;
        transition-property: opacity;
        transform: none;
      }
      
      .carousel-fade .carousel-item.active,
      .carousel-fade .carousel-item-next.carousel-item-start,
      .carousel-fade .carousel-item-prev.carousel-item-end {
        opacity: 1;
      }
      
      .carousel-fade .active.carousel-item-start,
      .carousel-fade .active.carousel-item-end {
        opacity: 0;
      }
      
      .slider-content-wrapper {
        background-size: cover;
        background-position: center;
      }
      
      .carousel-control-prev,
      .carousel-control-next {
        width: 50px;
        height: 50px;
        background: rgba(148, 0, 0, 0.8);
        border-radius: 50%;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.8;
        transition: all 0.3s ease;
      }
      
      .carousel-control-prev {
        left: 20px;
      }
      
      .carousel-control-next {
        right: 20px;
      }
      
      .carousel-control-prev:hover,
      .carousel-control-next:hover {
        opacity: 1;
        background: rgba(148, 0, 0, 1);
        transform: translateY(-50%) scale(1.1);
      }
      
      /* Hero Slider Specific Styles */
      .hero-section .carousel-control-prev,
      .hero-section .carousel-control-next {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
      }
      
      .hero-section .carousel-control-prev:hover,
      .hero-section .carousel-control-next:hover {
        background: rgba(255, 255, 255, 0.3);
      }
      
      .hero-section .carousel-indicators {
        bottom: 30px;
      }
      
      .hero-section .carousel-indicators [data-bs-target] {
        width: 50px;
        height: 4px;
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 2px;
      }
      
      .hero-section .carousel-indicators .active {
        background-color: #fff;
        width: 70px;
      }
      
      .hero-content {
        position: relative;
        z-index: 2;
      }
      
      .carousel-indicators [data-bs-target] {
        width: 40px;
        height: 4px;
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 2px;
        transition: all 0.3s ease;
      }
      
      .carousel-indicators .active {
        background-color: #fff;
        width: 60px;
      }
      
      @media (max-width: 768px) {
        .slider-content-wrapper {
          min-height: 400px !important;
        }
        
        .slider-content-wrapper .display-5 {
          font-size: 1.8rem;
        }
        
        .slider-content-wrapper .lead {
          font-size: 1rem;
        }
        
        .slider-content-wrapper i[style*="font-size: 200px"] {
          font-size: 120px !important;
        }
      }
    </style>
  </head>

  <body>
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- Advanced Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top" id="mainNav">
      <div class="container">
        <a class="navbar-brand" href="#">
          <img src="{{ asset('assets/img/office_link_logo.png') }}" alt="OfisiLink Logo" />
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
              <a class="nav-link" href="#home">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#features">Features</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#stats">Statistics</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#benefits">Benefits</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#testimonials">Testimonials</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#workflows">Workflows</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#pricing">Pricing</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#faq">FAQ</a>
            </li>
            <li class="nav-item">
              <a class="btn btn-primary ms-3 px-4" href="{{ route('login') }}">
                <i class="bx bx-log-in me-2"></i>Login
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Advanced Hero Section with Slider -->
    <section class="hero-section" id="home">
      <div id="heroSlider" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
          <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="1" aria-label="Slide 2"></button>
          <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="2" aria-label="Slide 3"></button>
          <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="3" aria-label="Slide 4"></button>
          <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="4" aria-label="Slide 5"></button>
          <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="5" aria-label="Slide 6"></button>
        </div>
        <div class="carousel-inner">
          <!-- Slide 1 - Overview -->
          <div class="carousel-item active">
            <div class="hero-slide position-relative" style="min-height: 100vh; display: flex; align-items: center; overflow: hidden;">
              <!-- Full-width background image -->
              <div class="hero-bg-image position-absolute w-100 h-100" style="top: 0; left: 0; z-index: 0;">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=1920&h=1080&fit=crop" 
                     alt="Office Management" 
                     class="w-100 h-100"
                     style="object-fit: cover; filter: brightness(0.4);"
                     onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, rgba(148, 0, 0, 0.9) 0%, rgba(168, 0, 0, 0.9) 100%)';">
                <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(148, 0, 0, 0.85) 0%, rgba(168, 0, 0, 0.85) 100%); z-index: 1;"></div>
              </div>
              <!-- Content -->
              <div class="container position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                  <div class="col-lg-6 hero-content">
                    <h1 class="hero-title text-white">OfisiLink</h1>
                    <h2 class="hero-subtitle text-white">Next-Generation Office Management System</h2>
                    <p class="hero-description text-white">
                      Comprehensive office management platform. Automate workflows, manage files, 
                      streamline HR, and make data-driven decisions.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                      <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-light btn-lg px-5 py-3">
                        <i class="bx bx-play-circle me-2"></i>Try Demo
                      </a>
                      <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-log-in me-2"></i>Access Live System
                      </a>
                      <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-info-circle me-2"></i>Learn More
                      </a>
                    </div>
                    <div class="mt-5 d-flex gap-4">
                      <div>
                        <h3 class="text-white mb-0">100%</h3>
                        <p class="text-white-50 mb-0">Digital</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">24/7</h3>
                        <p class="text-white-50 mb-0">Support</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">99.9%</h3>
                        <p class="text-white-50 mb-0">Uptime</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 text-center hero-image">
                    <!-- Sample image from Unsplash - Office/Technology theme -->
                    <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&h=800&fit=crop" 
                         alt="OfisiLink Dashboard" 
                         class="img-fluid" 
                         style="max-height: 600px; width: 100%; object-fit: cover; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);"
                         onerror="this.src='https://via.placeholder.com/1200x800/940000/ffffff?text=OfisiLink'">
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Slide 2 - File Management -->
          <div class="carousel-item">
            <div class="hero-slide position-relative" style="min-height: 100vh; display: flex; align-items: center; overflow: hidden;">
              <!-- Full-width background image -->
              <div class="hero-bg-image position-absolute w-100 h-100" style="top: 0; left: 0; z-index: 0;">
                <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1920&h=1080&fit=crop" 
                     alt="File Management" 
                     class="w-100 h-100"
                     style="object-fit: cover; filter: brightness(0.4);"
                     onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, rgba(30, 60, 114, 0.9) 0%, rgba(42, 82, 152, 0.9) 100%)';">
                <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(30, 60, 114, 0.85) 0%, rgba(42, 82, 152, 0.85) 100%); z-index: 1;"></div>
              </div>
              <!-- Content -->
              <div class="container position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                  <div class="col-lg-6 hero-content">
                    <h1 class="hero-title text-white">Advanced File Management</h1>
                    <h2 class="hero-subtitle text-white">Digital & Physical File Tracking</h2>
                    <p class="hero-description text-white">
                      Complete file management with access control, version tracking, and audit trails.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                      <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-light btn-lg px-5 py-3">
                        <i class="bx bx-play-circle me-2"></i>Try Demo
                      </a>
                      <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-log-in me-2"></i>Live System
                      </a>
                      <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-info-circle me-2"></i>View Features
                      </a>
                    </div>
                    <div class="mt-5 d-flex gap-4">
                      <div>
                        <h3 class="text-white mb-0">50K+</h3>
                        <p class="text-white-50 mb-0">Files Managed</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">100%</h3>
                        <p class="text-white-50 mb-0">Secure</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">24/7</h3>
                        <p class="text-white-50 mb-0">Access</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 text-center hero-image">
                    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&h=800&fit=crop" 
                         alt="File Management" 
                         class="img-fluid" 
                         style="max-height: 600px; width: 100%; object-fit: cover; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);"
                         onerror="this.src='https://via.placeholder.com/1200x800/1e3c72/ffffff?text=File+Management'">
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Slide 3 - HR Management -->
          <div class="carousel-item">
            <div class="hero-slide position-relative" style="min-height: 100vh; display: flex; align-items: center; overflow: hidden;">
              <!-- Full-width background image -->
              <div class="hero-bg-image position-absolute w-100 h-100" style="top: 0; left: 0; z-index: 0;">
                <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1920&h=1080&fit=crop" 
                     alt="HR Management" 
                     class="w-100 h-100"
                     style="object-fit: cover; filter: brightness(0.4);"
                     onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%)';">
                <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%); z-index: 1;"></div>
              </div>
              <!-- Content -->
              <div class="container position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                  <div class="col-lg-6 hero-content">
                    <h1 class="hero-title text-white">Complete HR Management</h1>
                    <h2 class="hero-subtitle text-white">Employee Lifecycle Management</h2>
                    <p class="hero-description text-white">
                      Manage employees from recruitment to retirement. Leave, permissions, assessments, and payroll.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                      <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-light btn-lg px-5 py-3">
                        <i class="bx bx-play-circle me-2"></i>Try Demo
                      </a>
                      <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-log-in me-2"></i>Live System
                      </a>
                      <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-info-circle me-2"></i>View Features
                      </a>
                    </div>
                    <div class="mt-5 d-flex gap-4">
                      <div>
                        <h3 class="text-white mb-0">10K+</h3>
                        <p class="text-white-50 mb-0">Active Users</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">Auto</h3>
                        <p class="text-white-50 mb-0">Workflows</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">100%</h3>
                        <p class="text-white-50 mb-0">Digital HR</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 text-center hero-image">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&h=800&fit=crop" 
                         alt="HR Management" 
                         class="img-fluid" 
                         style="max-height: 600px; width: 100%; object-fit: cover; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);"
                         onerror="this.src='https://via.placeholder.com/1200x800/667eea/ffffff?text=HR+Management'">
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Slide 4 - Task Management -->
          <div class="carousel-item">
            <div class="hero-slide position-relative" style="min-height: 100vh; display: flex; align-items: center; overflow: hidden;">
              <!-- Full-width background image -->
              <div class="hero-bg-image position-absolute w-100 h-100" style="top: 0; left: 0; z-index: 0;">
                <img src="https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=1920&h=1080&fit=crop" 
                     alt="Task Management" 
                     class="w-100 h-100"
                     style="object-fit: cover; filter: brightness(0.4);"
                     onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, rgba(240, 147, 251, 0.9) 0%, rgba(245, 87, 108, 0.9) 100%)';">
                <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(240, 147, 251, 0.85) 0%, rgba(245, 87, 108, 0.85) 100%); z-index: 1;"></div>
              </div>
              <!-- Content -->
              <div class="container position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                  <div class="col-lg-6 hero-content">
                    <h1 class="hero-title text-white">Task & Project Management</h1>
                    <h2 class="hero-subtitle text-white">Keep Teams Aligned</h2>
                    <p class="hero-description text-white">
                      Assign tasks, track progress, monitor deadlines, and generate reports.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                      <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-light btn-lg px-5 py-3">
                        <i class="bx bx-play-circle me-2"></i>Try Demo
                      </a>
                      <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-log-in me-2"></i>Live System
                      </a>
                      <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-info-circle me-2"></i>View Features
                      </a>
                    </div>
                    <div class="mt-5 d-flex gap-4">
                      <div>
                        <h3 class="text-white mb-0">250K+</h3>
                        <p class="text-white-50 mb-0">Tasks Completed</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">Real-Time</h3>
                        <p class="text-white-50 mb-0">Tracking</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">Auto</h3>
                        <p class="text-white-50 mb-0">Reports</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 text-center hero-image">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&h=800&fit=crop" 
                         alt="Task Management" 
                         class="img-fluid" 
                         style="max-height: 600px; width: 100%; object-fit: cover; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);"
                         onerror="this.src='https://via.placeholder.com/1200x800/f093fb/ffffff?text=Task+Management'">
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Slide 5 - Analytics & Reporting -->
          <div class="carousel-item">
            <div class="hero-slide position-relative" style="min-height: 100vh; display: flex; align-items: center; overflow: hidden;">
              <!-- Full-width background image -->
              <div class="hero-bg-image position-absolute w-100 h-100" style="top: 0; left: 0; z-index: 0;">
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1920&h=1080&fit=crop" 
                     alt="Analytics" 
                     class="w-100 h-100"
                     style="object-fit: cover; filter: brightness(0.4);"
                     onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, rgba(79, 172, 254, 0.9) 0%, rgba(0, 242, 254, 0.9) 100%)';">
                <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(79, 172, 254, 0.85) 0%, rgba(0, 242, 254, 0.85) 100%); z-index: 1;"></div>
              </div>
              <!-- Content -->
              <div class="container position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                  <div class="col-lg-6 hero-content">
                    <h1 class="hero-title text-white">Real-Time Analytics</h1>
                    <h2 class="hero-subtitle text-white">Data-Driven Decisions</h2>
                    <p class="hero-description text-white">
                      Comprehensive dashboards, custom reports, and real-time insights. Export to PDF or Excel.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                      <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-light btn-lg px-5 py-3">
                        <i class="bx bx-play-circle me-2"></i>Try Demo
                      </a>
                      <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-log-in me-2"></i>Live System
                      </a>
                      <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-info-circle me-2"></i>View Features
                      </a>
                    </div>
                    <div class="mt-5 d-flex gap-4">
                      <div>
                        <h3 class="text-white mb-0">Real-Time</h3>
                        <p class="text-white-50 mb-0">Dashboards</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">Custom</h3>
                        <p class="text-white-50 mb-0">Reports</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">PDF/Excel</h3>
                        <p class="text-white-50 mb-0">Export</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 text-center hero-image">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&h=800&fit=crop" 
                         alt="Analytics & Reporting" 
                         class="img-fluid" 
                         style="max-height: 600px; width: 100%; object-fit: cover; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);"
                         onerror="this.src='https://via.placeholder.com/1200x800/4facfe/ffffff?text=Analytics'">
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Slide 6 - Security & Compliance -->
          <div class="carousel-item">
            <div class="hero-slide position-relative" style="min-height: 100vh; display: flex; align-items: center; overflow: hidden;">
              <!-- Full-width background image -->
              <div class="hero-bg-image position-absolute w-100 h-100" style="top: 0; left: 0; z-index: 0;">
                <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=1920&h=1080&fit=crop" 
                     alt="Security" 
                     class="w-100 h-100"
                     style="object-fit: cover; filter: brightness(0.4);"
                     onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, rgba(67, 233, 123, 0.9) 0%, rgba(56, 249, 215, 0.9) 100%)';">
                <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(67, 233, 123, 0.85) 0%, rgba(56, 249, 215, 0.85) 100%); z-index: 1;"></div>
              </div>
              <!-- Content -->
              <div class="container position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                  <div class="col-lg-6 hero-content">
                    <h1 class="hero-title text-white">Enterprise Security</h1>
                    <h2 class="hero-subtitle text-white">Industry-Leading Protection</h2>
                    <p class="hero-description text-white">
                      AES-256 encryption, role-based access, audit trails, and 99.9% uptime guarantee.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                      <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-light btn-lg px-5 py-3">
                        <i class="bx bx-play-circle me-2"></i>Try Demo
                      </a>
                      <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-log-in me-2"></i>Live System
                      </a>
                      <a href="#security" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="bx bx-shield-check me-2"></i>Security Info
                      </a>
                    </div>
                    <div class="mt-5 d-flex gap-4">
                      <div>
                        <h3 class="text-white mb-0">AES-256</h3>
                        <p class="text-white-50 mb-0">Encryption</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">99.9%</h3>
                        <p class="text-white-50 mb-0">Uptime</p>
                      </div>
                      <div>
                        <h3 class="text-white mb-0">24/7</h3>
                        <p class="text-white-50 mb-0">Monitoring</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 text-center hero-image">
                    <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=1200&h=800&fit=crop" 
                         alt="Security & Compliance" 
                         class="img-fluid" 
                         style="max-height: 600px; width: 100%; object-fit: cover; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);"
                         onerror="this.src='https://via.placeholder.com/1200x800/43e97b/ffffff?text=Security'">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
    </section>

    <!-- Statistics Section -->
    <section id="stats" class="stats-section">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5">
            <h2 class="display-4 fw-bold text-white mb-3">Trusted by Organizations</h2>
            <p class="lead text-white-50">Join thousands of organizations transforming their operations</p>
          </div>
        </div>
        <div class="row g-3 justify-content-center">
          <div class="col-auto">
            <div class="stat-card">
              <i class="bx bx-building fs-1 mb-3"></i>
              <span class="stat-number" data-target="500">0</span>
              <p class="stat-label">Organizations</p>
            </div>
          </div>
          <div class="col-auto">
            <div class="stat-card">
              <i class="bx bx-user fs-1 mb-3"></i>
              <span class="stat-number" data-target="10000">0</span>
              <p class="stat-label">Active Users</p>
            </div>
          </div>
          <div class="col-auto">
            <div class="stat-card">
              <i class="bx bx-file fs-1 mb-3"></i>
              <span class="stat-number" data-target="50000">0</span>
              <p class="stat-label">Files Managed</p>
            </div>
          </div>
          <div class="col-auto">
            <div class="stat-card">
              <i class="bx bx-check-circle fs-1 mb-3"></i>
              <span class="stat-number" data-target="98">0</span>
              <p class="stat-label">% Satisfaction</p>
            </div>
          </div>
          <div class="col-auto">
            <div class="stat-card">
              <i class="bx bx-time-five fs-1 mb-3"></i>
              <span class="stat-number" data-target="70">0</span>
              <p class="stat-label">% Time Saved</p>
            </div>
          </div>
          <div class="col-auto">
            <div class="stat-card">
              <i class="bx bx-trending-down fs-1 mb-3"></i>
              <span class="stat-number" data-target="60">0</span>
              <p class="stat-label">% Cost Reduction</p>
            </div>
          </div>
          <div class="col-auto">
            <div class="stat-card">
              <i class="bx bx-task fs-1 mb-3"></i>
              <span class="stat-number" data-target="250000">0</span>
              <p class="stat-label">Tasks Completed</p>
            </div>
          </div>
          <div class="col-auto">
            <div class="stat-card">
              <i class="bx bx-shield-check fs-1 mb-3"></i>
              <span class="stat-number" data-target="99">0</span>
              <p class="stat-label">.9% Uptime</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Video Demo Section -->
    <section id="video-demo" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">See OfisiLink in Action</h2>
            <p class="lead text-muted mb-4">Watch how OfisiLink transforms office operations with intuitive workflows, powerful automation, and comprehensive reporting.</p>
            <div class="mx-auto mb-4" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
            
            <!-- Feature List -->
            <div class="row justify-content-center mt-4 mb-4">
              <div class="col-lg-8">
                <div class="row g-3">
                  <div class="col-md-6 text-start">
                    <div class="d-flex align-items-center">
                      <i class="bx bx-check-circle text-primary fs-4 me-3"></i>
                      <span>Complete system walkthrough</span>
                    </div>
                  </div>
                  <div class="col-md-6 text-start">
                    <div class="d-flex align-items-center">
                      <i class="bx bx-check-circle text-primary fs-4 me-3"></i>
                      <span>Real-world use cases</span>
                    </div>
                  </div>
                  <div class="col-md-6 text-start">
                    <div class="d-flex align-items-center">
                      <i class="bx bx-check-circle text-primary fs-4 me-3"></i>
                      <span>Feature demonstrations</span>
                    </div>
                  </div>
                  <div class="col-md-6 text-start">
                    <div class="d-flex align-items-center">
                      <i class="bx bx-check-circle text-primary fs-4 me-3"></i>
                      <span>Best practices and tips</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row justify-content-center">
          <div class="col-lg-10 col-xl-8 fade-in">
            <div class="video-demo-container position-relative" style="border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
              <div class="ratio ratio-16x9">
                <!-- OfisiLink Video Demo from YouTube - Autoplay with Muted -->
                <iframe 
                  src="https://www.youtube.com/embed/G2GX5cjAIo0?rel=0&modestbranding=1&showinfo=0&autoplay=1&mute=1&loop=1&playlist=G2GX5cjAIo0&controls=1" 
                  title="OfisiLink Video Demo" 
                  frameborder="0" 
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                  allowfullscreen
                  style="width: 100%; height: 100%; border-radius: 20px;">
                </iframe>
              </div>
              <div class="text-center mt-4">
                <div class="alert alert-info d-inline-block mb-3" style="border-left: 4px solid #940000;">
                  <i class="bx bx-info-circle me-2"></i>
                  <strong>Video Demo Coming Soon</strong> - We're preparing comprehensive video demonstrations showcasing all OfisiLink features and capabilities.
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                  <a href="https://www.youtube.com/@ofisilink" target="_blank" class="btn btn-primary">
                    <i class="bx bxl-youtube me-2"></i>Visit Our YouTube Channel
                  </a>
                  <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-outline-primary">
                    <i class="bx bx-play-circle me-2"></i>Try Live Demo
                  </a>
                  <a href="#features" class="btn btn-outline-primary">
                    <i class="bx bx-info-circle me-2"></i>Explore Features
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Image Slider Section -->
    <section id="slider" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Discover OfisiLink</h2>
            <p class="lead text-muted">Explore the power of comprehensive office management</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div id="ofisiSlider" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
              <div class="carousel-indicators">
                <button type="button" data-bs-target="#ofisiSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#ofisiSlider" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#ofisiSlider" data-bs-slide-to="2" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#ofisiSlider" data-bs-slide-to="3" aria-label="Slide 4"></button>
                <button type="button" data-bs-target="#ofisiSlider" data-bs-slide-to="4" aria-label="Slide 5"></button>
                <button type="button" data-bs-target="#ofisiSlider" data-bs-slide-to="5" aria-label="Slide 6"></button>
              </div>
              <div class="carousel-inner" style="border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
                <!-- Slide 1 -->
                <div class="carousel-item active">
                  <div class="slider-content-wrapper position-relative" style="min-height: 500px; display: flex; align-items: center; overflow: hidden;">
                    <!-- Background Image from Online Sample -->
                    <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1920&h=1080&fit=crop" 
                         alt="Financial Management" 
                         class="position-absolute w-100 h-100"
                         style="object-fit: cover; filter: brightness(0.3); z-index: 0;"
                         onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #940000 0%, #a80000 100%)';">
                    <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(148, 0, 0, 0.85) 0%, rgba(168, 0, 0, 0.85) 100%); z-index: 1;"></div>
                    <div class="container position-relative" style="z-index: 2;">
                      <div class="row align-items-center">
                        <div class="col-lg-6 text-white p-5">
                          <h2 class="display-5 fw-bold mb-4">Complete Financial Management</h2>
                          <p class="lead mb-4">Manage all your financial operations from petty cash to complex accounting entries. Automated workflows ensure approvals are fast and transparent.</p>
                          <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Petty Cash & Imprest Management</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Budget Planning & Forecasting</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Financial Reports & Analytics</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Accounts Payable & Receivable</li>
                          </ul>
                          <a href="#features" class="btn btn-light btn-lg">
                            <i class="bx bx-info-circle me-2"></i>Learn More
                          </a>
                        </div>
                        <div class="col-lg-6 text-center p-5">
                          <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&h=800&fit=crop" 
                               alt="Financial Dashboard" 
                               class="img-fluid rounded shadow-lg"
                               style="max-height: 400px; border: 3px solid rgba(255,255,255,0.3);"
                               onerror="this.innerHTML='<i class=\'bx bx-dollar-circle text-white\' style=\'font-size: 200px; opacity: 0.3;\'></i>'">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Slide 2 -->
                <div class="carousel-item">
                  <div class="slider-content-wrapper position-relative" style="min-height: 500px; display: flex; align-items: center; overflow: hidden;">
                    <!-- Background Image from Online Sample -->
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1920&h=1080&fit=crop" 
                         alt="File Management" 
                         class="position-absolute w-100 h-100"
                         style="object-fit: cover; filter: brightness(0.3); z-index: 0;"
                         onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)';">
                    <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(30, 60, 114, 0.85) 0%, rgba(42, 82, 152, 0.85) 100%); z-index: 1;"></div>
                    <div class="container position-relative" style="z-index: 2;">
                      <div class="row align-items-center">
                        <div class="col-lg-6 text-white p-5">
                          <h2 class="display-5 fw-bold mb-4">Advanced File Management</h2>
                          <p class="lead mb-4">Digital and physical file management with complete access control, version tracking, and audit trails. Never lose a document again.</p>
                          <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Digital & Physical File Tracking</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Access Request Workflows</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Version Control & History</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Complete Audit Trails</li>
                          </ul>
                          <a href="#features" class="btn btn-light btn-lg">
                            <i class="bx bx-info-circle me-2"></i>Learn More
                          </a>
                        </div>
                        <div class="col-lg-6 text-center p-5">
                          <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&h=800&fit=crop" 
                               alt="File Management System" 
                               class="img-fluid rounded shadow-lg"
                               style="max-height: 400px; border: 3px solid rgba(255,255,255,0.3);"
                               onerror="this.innerHTML='<i class=\'bx bx-folder text-white\' style=\'font-size: 200px; opacity: 0.3;\'></i>'">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Slide 3 -->
                <div class="carousel-item">
                  <div class="slider-content-wrapper position-relative" style="min-height: 500px; display: flex; align-items: center; overflow: hidden;">
                    <!-- Background Image from Online Sample -->
                    <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1920&h=1080&fit=crop" 
                         alt="HR Management" 
                         class="position-absolute w-100 h-100"
                         style="object-fit: cover; filter: brightness(0.3); z-index: 0;"
                         onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #667eea 0%, #764ba2 100%)';">
                    <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%); z-index: 1;"></div>
                    <div class="container position-relative" style="z-index: 2;">
                      <div class="row align-items-center">
                        <div class="col-lg-6 text-white p-5">
                          <h2 class="display-5 fw-bold mb-4">Comprehensive HR Management</h2>
                          <p class="lead mb-4">Streamline all HR processes from employee onboarding to payroll. Manage leave, permissions, assessments, and more from one platform.</p>
                          <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Employee Management & Profiles</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Leave & Permission Requests</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Performance Assessments</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Payroll Processing</li>
                          </ul>
                          <a href="#features" class="btn btn-light btn-lg">
                            <i class="bx bx-info-circle me-2"></i>Learn More
                          </a>
                        </div>
                        <div class="col-lg-6 text-center p-5">
                          <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&h=800&fit=crop" 
                               alt="HR Dashboard" 
                               class="img-fluid rounded shadow-lg"
                               style="max-height: 400px; border: 3px solid rgba(255,255,255,0.3);"
                               onerror="this.innerHTML='<i class=\'bx bx-briefcase text-white\' style=\'font-size: 200px; opacity: 0.3;\'></i>'">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Slide 4 -->
                <div class="carousel-item">
                  <div class="slider-content-wrapper position-relative" style="min-height: 500px; display: flex; align-items: center; overflow: hidden;">
                    <!-- Background Image from Online Sample -->
                    <img src="https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=1920&h=1080&fit=crop" 
                         alt="Task Management" 
                         class="position-absolute w-100 h-100"
                         style="object-fit: cover; filter: brightness(0.3); z-index: 0;"
                         onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';">
                    <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(240, 147, 251, 0.85) 0%, rgba(245, 87, 108, 0.85) 100%); z-index: 1;"></div>
                    <div class="container position-relative" style="z-index: 2;">
                      <div class="row align-items-center">
                        <div class="col-lg-6 text-white p-5">
                          <h2 class="display-5 fw-bold mb-4">Task & Project Management</h2>
                          <p class="lead mb-4">Assign tasks, track progress, and monitor completion. Keep your team aligned and projects on schedule with automated workflows.</p>
                          <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Task Assignment & Tracking</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Progress Monitoring</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Deadline Management</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Completion Reports</li>
                          </ul>
                          <a href="#features" class="btn btn-light btn-lg">
                            <i class="bx bx-info-circle me-2"></i>Learn More
                          </a>
                        </div>
                        <div class="col-lg-6 text-center p-5">
                          <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&h=800&fit=crop" 
                               alt="Task Dashboard" 
                               class="img-fluid rounded shadow-lg"
                               style="max-height: 400px; border: 3px solid rgba(255,255,255,0.3);"
                               onerror="this.innerHTML='<i class=\'bx bx-clipboard text-white\' style=\'font-size: 200px; opacity: 0.3;\'></i>'">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Slide 5 -->
                <div class="carousel-item">
                  <div class="slider-content-wrapper position-relative" style="min-height: 500px; display: flex; align-items: center; overflow: hidden;">
                    <!-- Background Image from Online Sample -->
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1920&h=1080&fit=crop" 
                         alt="Analytics" 
                         class="position-absolute w-100 h-100"
                         style="object-fit: cover; filter: brightness(0.3); z-index: 0;"
                         onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)';">
                    <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(79, 172, 254, 0.85) 0%, rgba(0, 242, 254, 0.85) 100%); z-index: 1;"></div>
                    <div class="container position-relative" style="z-index: 2;">
                      <div class="row align-items-center">
                        <div class="col-lg-6 text-white p-5">
                          <h2 class="display-5 fw-bold mb-4">Real-Time Analytics & Reporting</h2>
                          <p class="lead mb-4">Make data-driven decisions with comprehensive dashboards, custom reports, and real-time insights across all modules.</p>
                          <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Role-Based Dashboards</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Financial & HR Analytics</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Custom Report Builder</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>PDF & Excel Export</li>
                          </ul>
                          <a href="#features" class="btn btn-light btn-lg">
                            <i class="bx bx-info-circle me-2"></i>Learn More
                          </a>
                        </div>
                        <div class="col-lg-6 text-center p-5">
                          <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&h=800&fit=crop" 
                               alt="Analytics Dashboard" 
                               class="img-fluid rounded shadow-lg"
                               style="max-height: 400px; border: 3px solid rgba(255,255,255,0.3);"
                               onerror="this.innerHTML='<i class=\'bx bx-bar-chart text-white\' style=\'font-size: 200px; opacity: 0.3;\'></i>'">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Slide 6 -->
                <div class="carousel-item">
                  <div class="slider-content-wrapper position-relative" style="min-height: 500px; display: flex; align-items: center; overflow: hidden;">
                    <!-- Background Image from Online Sample -->
                    <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=1920&h=1080&fit=crop" 
                         alt="Security" 
                         class="position-absolute w-100 h-100"
                         style="object-fit: cover; filter: brightness(0.3); z-index: 0;"
                         onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)';">
                    <div class="position-absolute w-100 h-100" style="top: 0; left: 0; background: linear-gradient(135deg, rgba(67, 233, 123, 0.85) 0%, rgba(56, 249, 215, 0.85) 100%); z-index: 1;"></div>
                    <div class="container position-relative" style="z-index: 2;">
                      <div class="row align-items-center">
                        <div class="col-lg-6 text-white p-5">
                          <h2 class="display-5 fw-bold mb-4">Enterprise Security & Compliance</h2>
                          <p class="lead mb-4">Your data is protected with enterprise-grade security, complete audit trails, and compliance-ready features.</p>
                          <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>AES-256 Encryption</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Role-Based Access Control</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>Complete Audit Trails</li>
                            <li class="mb-2"><i class="bx bx-check-circle me-2 fs-5"></i>99.9% Uptime Guarantee</li>
                          </ul>
                          <a href="#security" class="btn btn-light btn-lg">
                            <i class="bx bx-info-circle me-2"></i>Learn More
                          </a>
                        </div>
                        <div class="col-lg-6 text-center p-5">
                          <img src="https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1200&h=800&fit=crop" 
                               alt="Security Settings" 
                               class="img-fluid rounded shadow-lg"
                               style="max-height: 400px; border: 3px solid rgba(255,255,255,0.3);"
                               onerror="this.innerHTML='<i class=\'bx bx-shield-check text-white\' style=\'font-size: 200px; opacity: 0.3;\'></i>'">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <button class="carousel-control-prev" type="button" data-bs-target="#ofisiSlider" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#ofisiSlider" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Advanced Features Section -->
    <section id="features" class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Comprehensive Office Management Modules</h2>
            <p class="lead text-muted">Powerful, integrated modules designed to transform your office operations</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card feature-card h-100">
              <div class="card-body text-center p-4">
                <div class="feature-icon">
                  <i class="bx bx-dollar-circle"></i>
                </div>
                <h4 class="card-title fw-bold mb-3">Accounting & Finance</h4>
                <p class="card-text text-muted mb-3">
                  Complete financial management with automated workflows, multi-level approvals, and real-time reporting.
                </p>
                <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#financeDetails" aria-expanded="false">
                  <i class="bx bx-chevron-down me-1"></i>View Details
                </button>
                <div class="collapse" id="financeDetails">
                  <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Petty Cash Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Imprest Request & Retirement</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Chart of Accounts</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Journal Entries & Ledger</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Accounts Payable & Receivable</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Budget Planning & Forecasting</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Financial Reports</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Cash Flow & Reconciliation</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Tax & PAYE Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Fixed Assets & Depreciation</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card feature-card h-100">
              <div class="card-body text-center p-4">
                <div class="feature-icon">
                  <i class="bx bx-folder"></i>
                </div>
                <h4 class="card-title fw-bold mb-3">File Management System</h4>
                <p class="card-text text-muted mb-3">
                  Digital and physical file tracking with access control, workflow approvals, and complete audit trails.
                </p>
                <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#fileDetails" aria-expanded="false">
                  <i class="bx bx-chevron-down me-1"></i>View Details
                </button>
                <div class="collapse" id="fileDetails">
                  <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Digital File System</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Physical Rack Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Access Request Workflow</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>User-Specific Assignments</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Advanced Search & Filtering</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Version Control & History</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>File Movement Tracking</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Complete Audit Trail</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Bulk Operations</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Confidentiality Levels</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card feature-card h-100">
              <div class="card-body text-center p-4">
                <div class="feature-icon">
                  <i class="bx bx-clipboard"></i>
                </div>
                <h4 class="card-title fw-bold mb-3">Task & Project Management</h4>
                <p class="card-text text-muted mb-3">
                  Assign tasks, track progress, manage deadlines, and generate automated reports. Keep teams aligned.
                </p>
                <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#taskDetails" aria-expanded="false">
                  <i class="bx bx-chevron-down me-1"></i>View Details
                </button>
                <div class="collapse" id="taskDetails">
                  <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Task Creation & Assignment</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Multi-Level Hierarchy</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Deadline Tracking & Reminders</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Progress Updates & Status</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Task Analytics & Reports</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Completion Certificates</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Department Filtering</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Task History & Audit</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Bulk Operations</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Real-Time Notifications</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card feature-card h-100">
              <div class="card-body text-center p-4">
                <div class="feature-icon">
                  <i class="bx bx-briefcase"></i>
                </div>
                <h4 class="card-title fw-bold mb-3">Human Resource Management</h4>
                <p class="card-text text-muted mb-3">
                  Complete employee lifecycle management from recruitment to retirement with automated workflows.
                </p>
                <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#hrDetails" aria-expanded="false">
                  <i class="bx bx-chevron-down me-1"></i>View Details
                </button>
                <div class="collapse" id="hrDetails">
                  <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Employee Registration & Profiles</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Leave Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Permission Requests</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Sick Sheet Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Performance Assessments</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Payroll Processing</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Recruitment Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Department & Position Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Attendance Tracking</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Document Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Bulk SMS & Email</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card feature-card h-100">
              <div class="card-body text-center p-4">
                <div class="feature-icon">
                  <i class="bx bx-flag"></i>
                </div>
                <h4 class="card-title fw-bold mb-3">Incident Management</h4>
                <p class="card-text text-muted mb-3">
                  Report, track, and resolve incidents with email integration, evidence management, and analytics.
                </p>
                <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#incidentDetails" aria-expanded="false">
                  <i class="bx bx-chevron-down me-1"></i>View Details
                </button>
                <div class="collapse" id="incidentDetails">
                  <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Incident Reporting & Classification</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Email Integration</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Evidence Attachments</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Assignment & Escalation</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Status Tracking</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Comment Threads</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Timeline & History</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Analytics & Reporting</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Bulk Operations</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Priority & SLA Tracking</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card feature-card h-100">
              <div class="card-body text-center p-4">
                <div class="feature-icon">
                  <i class="bx bx-bar-chart"></i>
                </div>
                <h4 class="card-title fw-bold mb-3">Reporting & Analytics</h4>
                <p class="card-text text-muted mb-3">
                  Real-time dashboards, custom reports, data visualization, and export capabilities for data-driven decisions.
                </p>
                <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#analyticsDetails" aria-expanded="false">
                  <i class="bx bx-chevron-down me-1"></i>View Details
                </button>
                <div class="collapse" id="analyticsDetails">
                  <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Role-Based Dashboards</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Financial Reports</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>HR Analytics</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Task & Project Analytics</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Incident Statistics</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Custom Report Builder</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Data Visualization</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>PDF & Excel Export</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Scheduled Reports</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Real-Time Alerts</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card feature-card h-100">
              <div class="card-body text-center p-4">
                <div class="feature-icon">
                  <i class="bx bx-cog"></i>
                </div>
                <h4 class="card-title fw-bold mb-3">System Administration</h4>
                <p class="card-text text-muted mb-3">
                  Complete control over users, roles, permissions, settings, and system health management.
                </p>
                <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#adminDetails" aria-expanded="false">
                  <i class="bx bx-chevron-down me-1"></i>View Details
                </button>
                <div class="collapse" id="adminDetails">
                  <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>User & Role Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Permission & Access Control</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Department & Organization</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>System Settings</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Activity Logging & Audit</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Backup & Restore</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>System Health Monitoring</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Communication Settings</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Financial Year Management</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Bulk Operations</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card feature-card h-100">
              <div class="card-body text-center p-4">
                <div class="feature-icon">
                  <i class="bx bx-box"></i>
                </div>
                <h4 class="card-title fw-bold mb-3">Asset Management</h4>
                <p class="card-text text-muted mb-3">
                  Track assets from procurement to disposal with assignments, maintenance, and issue management.
                </p>
                <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#assetDetails" aria-expanded="false">
                  <i class="bx bx-chevron-down me-1"></i>View Details
                </button>
                <div class="collapse" id="assetDetails">
                  <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Asset Registration</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Assignment & Return Tracking</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Maintenance Scheduling</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Issue Reporting</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Depreciation Calculation</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Barcode Generation</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Asset Analytics</li>
                    <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Bulk Operations</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Workflow & Process Section -->
    <section id="workflows" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Streamlined Workflows</h2>
            <p class="lead text-muted">Automated approval processes that save time and ensure accountability</p>
            <div class="mx-auto mb-3" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
            <p class="text-muted">
              <i class="bx bx-info-circle me-1"></i>Click on any workflow card to view detailed process steps
            </p>
          </div>
        </div>
        
        <!-- Financial Workflows -->
        <div class="row mb-5">
          <div class="col-12">
            <h3 class="fw-bold text-primary mb-4 text-center">Financial Management Workflows</h3>
          </div>
        </div>
        
        <div class="row g-4 mb-5">
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#pettyCashWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-money me-2"></i>Petty Cash Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="pettyCashWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>Staff Request</strong>
                      <p class="text-muted small mb-0">Staff submits petty cash request with amount and purpose</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>Accountant Verification</strong>
                      <p class="text-muted small mb-0">Accountant verifies request and checks cash availability</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>HOD Approval</strong>
                      <p class="text-muted small mb-0">Head of Department reviews and approves</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>CEO Approval</strong>
                      <p class="text-muted small mb-0">CEO/Director gives final approval</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">5</div>
                    <div class="flex-grow-1">
                      <strong>Payment Processing</strong>
                      <p class="text-muted small mb-0">Accountant processes payment and marks as paid</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">6</div>
                    <div class="flex-grow-1">
                      <strong>Retirement Submission</strong>
                      <p class="text-muted small mb-0">Staff submits retirement with receipts, Accountant approves</p>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#imprestWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-credit-card me-2"></i>Imprest Request Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="imprestWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>Accountant Creates Request</strong>
                      <p class="text-muted small mb-0">Accountant creates imprest request with amount and purpose</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>HOD Approval</strong>
                      <p class="text-muted small mb-0">HOD reviews and approves the imprest request</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>CEO Final Approval</strong>
                      <p class="text-muted small mb-0">CEO/Director gives final approval</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>Staff Assignment</strong>
                      <p class="text-muted small mb-0">Accountant assigns staff members to receive imprest</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">5</div>
                    <div class="flex-grow-1">
                      <strong>Payment Processing</strong>
                      <p class="text-muted small mb-0">Accountant processes payment to assigned staff</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">6</div>
                    <div class="flex-grow-1">
                      <strong>Receipt Verification</strong>
                      <p class="text-muted small mb-0">Staff submits receipts, Accountant verifies and completes</p>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- HR Workflows -->
        <div class="row mb-5 mt-5">
          <div class="col-12">
            <h3 class="fw-bold text-primary mb-4 text-center">Human Resources Workflows</h3>
          </div>
        </div>
        
        <div class="row g-4 mb-5">
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#leaveWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-calendar me-2"></i>Leave Request Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="leaveWorkflow">
                  <div class="workflow-steps mt-4">
                    <div class="d-flex align-items-center mb-3">
                      <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                      <div class="flex-grow-1">
                        <strong>Employee Request</strong>
                        <p class="text-muted small mb-0">Employee submits leave request with dates and type</p>
                      </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                      <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                      <div class="flex-grow-1">
                        <strong>HR Initial Review</strong>
                        <p class="text-muted small mb-0">HR verifies leave balance, eligibility, and active leave status</p>
                      </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                      <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                      <div class="flex-grow-1">
                        <strong>HOD Recommendation</strong>
                        <p class="text-muted small mb-0">HOD reviews and provides recommendation</p>
                      </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                      <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                      <div class="flex-grow-1">
                        <strong>CEO Approval</strong>
                        <p class="text-muted small mb-0">CEO/Director gives final approval</p>
                      </div>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">5</div>
                      <div class="flex-grow-1">
                        <strong>Certificate Generation</strong>
                        <p class="text-muted small mb-0">Leave certificate, fare certificate, and approval letter generated</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#permissionWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-user-check me-2"></i>Permission Request Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="permissionWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>Staff Request</strong>
                      <p class="text-muted small mb-0">Staff submits permission request to be outside office</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>HR Initial Review</strong>
                      <p class="text-muted small mb-0">HR reviews request and forwards to HOD</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>HOD Approval</strong>
                      <p class="text-muted small mb-0">HOD reviews and approves or rejects</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>HR Final Approval</strong>
                      <p class="text-muted small mb-0">HR gives final approval after HOD recommendation</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">5</div>
                    <div class="flex-grow-1">
                      <strong>Staff Confirms Return</strong>
                      <p class="text-muted small mb-0">Staff confirms return to office</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">6</div>
                    <div class="flex-grow-1">
                      <strong>HR Verification</strong>
                      <p class="text-muted small mb-0">HR verifies return and completes the process</p>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#sickSheetWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-plus-medical me-2"></i>Sick Sheet Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="sickSheetWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>Staff Submission</strong>
                      <p class="text-muted small mb-0">Staff submits sick sheet with medical document attachment</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>HR Review</strong>
                      <p class="text-muted small mb-0">HR reviews medical document and verifies authenticity</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>HOD Approval</strong>
                      <p class="text-muted small mb-0">HOD approves or rejects the sick sheet</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>Staff Confirms Return</strong>
                      <p class="text-muted small mb-0">Staff confirms return to work after recovery</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">5</div>
                    <div class="flex-grow-1">
                      <strong>HR Final Verification</strong>
                      <p class="text-muted small mb-0">HR verifies return and completes the process</p>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#assessmentWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-clipboard me-2"></i>Performance Assessment Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="assessmentWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>Staff Creates Assessment</strong>
                      <p class="text-muted small mb-0">Staff creates main responsibility with activities and contribution percentages</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>HOD Approval</strong>
                      <p class="text-muted small mb-0">HOD reviews and approves the assessment plan</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>Progress Reports</strong>
                      <p class="text-muted small mb-0">Staff submits progress reports based on frequency (daily/weekly/monthly)</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>HOD Approves Reports</strong>
                      <p class="text-muted small mb-0">HOD reviews and approves progress reports</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">5</div>
                    <div class="flex-grow-1">
                      <strong>Performance Calculation</strong>
                      <p class="text-muted small mb-0">System calculates annual performance, generates reports and PDFs</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Operational Workflows -->
        <div class="row mb-5 mt-5">
          <div class="col-12">
            <h3 class="fw-bold text-primary mb-4 text-center">Operational Workflows</h3>
          </div>
        </div>
        
        <div class="row g-4 mb-5">
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#taskWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-task me-2"></i>Task Assignment Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="taskWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>HOD Creates Task</strong>
                      <p class="text-muted small mb-0">HOD creates task and assigns to staff member</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>Staff Acknowledges</strong>
                      <p class="text-muted small mb-0">Staff receives notification and acknowledges task</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>Progress Updates</strong>
                      <p class="text-muted small mb-0">Staff provides progress updates throughout task execution</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>Task Completion</strong>
                      <p class="text-muted small mb-0">Staff marks task as completed with completion details</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">5</div>
                    <div class="flex-grow-1">
                      <strong>HOD Verification</strong>
                      <p class="text-muted small mb-0">HOD verifies completion and generates completion certificate</p>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#fileAccessWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-folder me-2"></i>File Access Request Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="fileAccessWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>User Requests Access</strong>
                      <p class="text-muted small mb-0">User requests access to confidential or private file</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>Manager Review</strong>
                      <p class="text-muted small mb-0">File manager (HOD/HR/Admin) reviews access request</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>Access Granted</strong>
                      <p class="text-muted small mb-0">Manager approves and assigns file access to user</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>File Access & Tracking</strong>
                      <p class="text-muted small mb-0">User accesses file, all activities logged in audit trail</p>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#incidentWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-flag me-2"></i>Incident Management Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="incidentWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>Incident Reporting</strong>
                      <p class="text-muted small mb-0">User reports incident with details and evidence attachments</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>Assignment</strong>
                      <p class="text-muted small mb-0">Manager assigns incident to responsible person/team</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>Investigation & Resolution</strong>
                      <p class="text-muted small mb-0">Assigned person investigates, adds comments, updates status</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>Closure</strong>
                      <p class="text-muted small mb-0">Manager verifies resolution and closes incident, complete timeline recorded</p>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#payrollWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-money-withdraw me-2"></i>Payroll Processing Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="payrollWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>HR Initiates Payroll</strong>
                      <p class="text-muted small mb-0">HR Officer initiates payroll processing for the period</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>Calculation & Review</strong>
                      <p class="text-muted small mb-0">System calculates salaries, deductions, and allowances</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>Accountant Review</strong>
                      <p class="text-muted small mb-0">Accountant reviews payroll calculations and adjustments</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>CEO Approval</strong>
                      <p class="text-muted small mb-0">CEO/Director approves payroll for payment</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">5</div>
                    <div class="flex-grow-1">
                      <strong>Payment & Payslips</strong>
                      <p class="text-muted small mb-0">Payments processed, payslips generated and distributed</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Accounting Workflows -->
        <div class="row mb-5 mt-5">
          <div class="col-12">
            <h3 class="fw-bold text-primary mb-4 text-center">Accounting Workflows</h3>
          </div>
        </div>
        
        <div class="row g-4 justify-content-center">
          <div class="col-lg-6 col-xl-5 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#journalWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-book me-2"></i>Journal Entry Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="journalWorkflow">
                  <div class="workflow-steps mt-4">
                    <div class="d-flex align-items-center mb-3">
                      <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                      <div class="flex-grow-1">
                        <strong>Accountant Creates Entry</strong>
                        <p class="text-muted small mb-0">Accountant creates journal entry with debit/credit transactions</p>
                      </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                      <div class="workflow-step-number bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                      <div class="flex-grow-1">
                        <strong>Balance Verification</strong>
                        <p class="text-muted small mb-0">System verifies debits equal credits</p>
                      </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                      <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                      <div class="flex-grow-1">
                        <strong>Review & Approval</strong>
                        <p class="text-muted small mb-0">Supervisor or CEO reviews and approves entry</p>
                      </div>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                      <div class="flex-grow-1">
                        <strong>Posting</strong>
                        <p class="text-muted small mb-0">Entry posted to general ledger, accounts updated</p>
                      </div>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-xl-5 fade-in">
            <div class="card border-0 shadow-sm h-100 workflow-card" style="border-radius: 15px; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#budgetWorkflow" aria-expanded="false">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-primary mb-0"><i class="bx bx-pie-chart me-2"></i>Budget Approval Workflow</h5>
                  <i class="bx bx-chevron-down text-primary workflow-arrow" style="font-size: 24px; transition: transform 0.3s ease;"></i>
                </div>
                <p class="text-muted small mb-0">Click to view detailed workflow steps</p>
                <div class="collapse" id="budgetWorkflow">
                  <div class="workflow-steps mt-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                    <div class="flex-grow-1">
                      <strong>Budget Creation</strong>
                      <p class="text-muted small mb-0">Accountant or HOD creates budget with line items and amounts</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                    <div class="flex-grow-1">
                      <strong>HOD Review</strong>
                      <p class="text-muted small mb-0">HOD reviews department budget and provides input</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-3">
                    <div class="workflow-step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                    <div class="flex-grow-1">
                      <strong>CEO Approval</strong>
                      <p class="text-muted small mb-0">CEO/Director reviews and approves overall budget</p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="workflow-step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                    <div class="flex-grow-1">
                      <strong>Budget Activation</strong>
                      <p class="text-muted small mb-0">Budget activated, actuals tracked against budget throughout period</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Call to Action in Workflows Section -->
        <div class="row mt-5">
          <div class="col-12 text-center">
            <div class="bg-primary text-white p-5 rounded-4 shadow-lg">
              <h3 class="fw-bold mb-3">Ready to Streamline Your Workflows?</h3>
              <p class="lead mb-4">Experience automated approval processes that save time and ensure accountability</p>
              <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-light btn-lg px-5">
                  <i class="bx bx-play-circle me-2"></i>Try Demo
                </a>
                <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-outline-light btn-lg px-5">
                  <i class="bx bx-log-in me-2"></i>Access Live System
                </a>
                <a href="#pricing" class="btn btn-outline-light btn-lg px-5">
                  <i class="bx bx-dollar me-2"></i>View Pricing
                </a>
                <a href="#features" class="btn btn-outline-light btn-lg px-5">
                  <i class="bx bx-info-circle me-2"></i>Explore Features
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Use Cases Section -->
    <section id="use-cases" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Perfect For Every Organization</h2>
            <p class="lead text-muted">OfisiLink adapts to your industry and organizational needs</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
              <div class="card-body p-4">
                <div class="mb-3">
                  <i class="bx bx-building-house text-primary" style="font-size: 48px;"></i>
                </div>
                <h5 class="fw-bold mb-3">Government Organizations</h5>
                <p class="text-muted">Streamline public sector operations with transparent financial management, 
                document tracking, and compliance reporting. Perfect for ministries, agencies, and local governments.</p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Budget compliance tracking</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Public document management</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Audit trail compliance</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
              <div class="card-body p-4">
                <div class="mb-3">
                  <i class="bx bx-briefcase-alt-2 text-primary" style="font-size: 48px;"></i>
                </div>
                <h5 class="fw-bold mb-3">Private Companies</h5>
                <p class="text-muted">Enhance productivity and efficiency in corporate environments. Manage 
                finances, HR, and operations from a single platform with enterprise-grade security.</p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Financial control & reporting</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Employee self-service</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Performance management</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
              <div class="card-body p-4">
                <div class="mb-3">
                  <i class="bx bx-heart text-primary" style="font-size: 48px;"></i>
                </div>
                <h5 class="fw-bold mb-3">NGOs & Non-Profits</h5>
                <p class="text-muted">Manage donor funds, track projects, and maintain transparency with 
                comprehensive financial and operational reporting. Perfect for accountability and compliance.</p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Donor fund tracking</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Project management</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Transparency reporting</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
              <div class="card-body p-4">
                <div class="mb-3">
                  <i class="bx bx-graduation text-primary" style="font-size: 48px;"></i>
                </div>
                <h5 class="fw-bold mb-3">Educational Institutions</h5>
                <p class="text-muted">Manage school operations, staff, finances, and administrative tasks. 
                Track student-related documents and maintain institutional records efficiently.</p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Staff & faculty management</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Budget & fee management</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Document archiving</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
              <div class="card-body p-4">
                <div class="mb-3">
                  <i class="bx bx-hospital text-primary" style="font-size: 48px;"></i>
                </div>
                <h5 class="fw-bold mb-3">Healthcare Facilities</h5>
                <p class="text-muted">Manage hospital administration, staff schedules, medical records, 
                and financial operations. Ensure compliance with healthcare regulations.</p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Staff scheduling</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Medical document management</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Financial tracking</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
              <div class="card-body p-4">
                <div class="mb-3">
                  <i class="bx bx-church text-primary" style="font-size: 48px;"></i>
                </div>
                <h5 class="fw-bold mb-3">Religious Organizations</h5>
                <p class="text-muted">Manage church operations, member records, donations, and administrative 
                tasks. Maintain transparency in financial management and operations.</p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Donation tracking</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Member management</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Event planning</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Comparison Table Section -->
    <section id="comparison" class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Before & After OfisiLink</h2>
            <p class="lead text-muted">See the transformation in your office operations</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="table-responsive">
              <table class="table table-bordered table-hover shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <thead class="bg-primary text-white">
                  <tr>
                    <th style="width: 40%;">Aspect</th>
                    <th class="text-center" style="width: 30%;">Before OfisiLink</th>
                    <th class="text-center" style="width: 30%;">With OfisiLink</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>Document Management</strong></td>
                    <td class="text-muted">Paper files, manual tracking, lost documents</td>
                    <td class="text-success"><i class="bx bx-check-circle me-2"></i>Digital & physical tracking, instant search</td>
                  </tr>
                  <tr>
                    <td><strong>Financial Approvals</strong></td>
                    <td class="text-muted">Physical signatures, delays, no audit trail</td>
                    <td class="text-success"><i class="bx bx-check-circle me-2"></i>Digital workflows, instant notifications, complete audit</td>
                  </tr>
                  <tr>
                    <td><strong>Leave Management</strong></td>
                    <td class="text-muted">Paper forms, manual calculations, errors</td>
                    <td class="text-success"><i class="bx bx-check-circle me-2"></i>Online requests, automatic balance tracking</td>
                  </tr>
                  <tr>
                    <td><strong>Task Tracking</strong></td>
                    <td class="text-muted">Email chains, missed deadlines, no visibility</td>
                    <td class="text-success"><i class="bx bx-check-circle me-2"></i>Centralized tracking, real-time updates</td>
                  </tr>
                  <tr>
                    <td><strong>Reporting</strong></td>
                    <td class="text-muted">Manual Excel sheets, time-consuming, errors</td>
                    <td class="text-success"><i class="bx bx-check-circle me-2"></i>Automated reports, real-time dashboards</td>
                  </tr>
                  <tr>
                    <td><strong>Compliance</strong></td>
                    <td class="text-muted">Incomplete records, audit challenges</td>
                    <td class="text-success"><i class="bx bx-check-circle me-2"></i>Complete audit trails, compliance ready</td>
                  </tr>
                  <tr>
                    <td><strong>Time Savings</strong></td>
                    <td class="text-muted">Hours spent on manual processes</td>
                    <td class="text-success"><i class="bx bx-check-circle me-2"></i>70% reduction in administrative time</td>
                  </tr>
                  <tr>
                    <td><strong>Cost Efficiency</strong></td>
                    <td class="text-muted">High paper, printing, storage costs</td>
                    <td class="text-success"><i class="bx bx-check-circle me-2"></i>Reduced operational costs by 60%</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Technology Stack Section -->
    <section id="technology" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Built on Modern Technology</h2>
            <p class="lead text-muted">Enterprise-grade technology stack for reliability and performance</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="text-center p-4">
              <div class="mb-3">
                <i class="bx bxl-php text-primary" style="font-size: 64px;"></i>
              </div>
              <h6 class="fw-bold">Laravel Framework</h6>
              <p class="text-muted small mb-0">Modern PHP framework for robust backend</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="text-center p-4">
              <div class="mb-3">
                <i class="bx bxl-mysql text-primary" style="font-size: 64px;"></i>
              </div>
              <h6 class="fw-bold">MySQL Database</h6>
              <p class="text-muted small mb-0">Reliable relational database system</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="text-center p-4">
              <div class="mb-3">
                <i class="bx bxl-javascript text-primary" style="font-size: 64px;"></i>
              </div>
              <h6 class="fw-bold">JavaScript & AJAX</h6>
              <p class="text-muted small mb-0">Dynamic and interactive user experience</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="text-center p-4">
              <div class="mb-3">
                <i class="bx bxl-bootstrap text-primary" style="font-size: 64px;"></i>
              </div>
              <h6 class="fw-bold">Bootstrap 5</h6>
              <p class="text-muted small mb-0">Responsive and modern UI framework</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="text-center p-4">
              <div class="mb-3">
                <i class="bx bx-cloud text-primary" style="font-size: 64px;"></i>
              </div>
              <h6 class="fw-bold">Cloud Hosting</h6>
              <p class="text-muted small mb-0">Scalable cloud infrastructure</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="text-center p-4">
              <div class="mb-3">
                <i class="bx bx-shield text-primary" style="font-size: 64px;"></i>
              </div>
              <h6 class="fw-bold">SSL Encryption</h6>
              <p class="text-muted small mb-0">Secure data transmission</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="text-center p-4">
              <div class="mb-3">
                <i class="bx bx-code-alt text-primary" style="font-size: 64px;"></i>
              </div>
              <h6 class="fw-bold">REST API</h6>
              <p class="text-muted small mb-0">Integration-ready API architecture</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="text-center p-4">
              <div class="mb-3">
                <i class="bx bx-mobile text-primary" style="font-size: 64px;"></i>
              </div>
              <h6 class="fw-bold">Mobile Responsive</h6>
              <p class="text-muted small mb-0">Works on all devices and screens</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Advanced Benefits Section -->
    <section id="benefits" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Key Benefits of OfisiLink</h2>
            <p class="lead text-muted">Transform your office operations with intelligent automation</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-3 col-md-6 fade-in">
            <div class="text-center p-4">
              <div class="benefit-icon">
                <i class="bx bx-cog"></i>
              </div>
              <h5 class="mt-3 fw-bold">Automation & Efficiency</h5>
              <p class="text-muted">Eliminate manual paperwork through intelligent automated workflows, 
              digital approvals, and instant notifications. Save time and reduce errors.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 fade-in">
            <div class="text-center p-4">
              <div class="benefit-icon">
                <i class="bx bx-shield-check"></i>
              </div>
              <h5 class="mt-3 fw-bold">Transparency & Accountability</h5>
              <p class="text-muted">Every task, approval, and financial transaction is tracked with 
              complete audit trails ensuring full institutional accountability.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 fade-in">
            <div class="text-center p-4">
              <div class="benefit-icon">
                <i class="bx bx-trending-up"></i>
              </div>
              <h5 class="mt-3 fw-bold">Data-Driven Decisions</h5>
              <p class="text-muted">Access real-time dashboards and comprehensive reports that empower 
              managers to make informed strategic decisions quickly.</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 fade-in">
            <div class="text-center p-4">
              <div class="benefit-icon">
                <i class="bx bx-user-check"></i>
              </div>
              <h5 class="mt-3 fw-bold">Employee Empowerment</h5>
              <p class="text-muted">Enable staff to manage leave, tasks, and appraisals digitally — 
              improving engagement, productivity, and job satisfaction.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">What Our Clients Say</h2>
            <p class="lead text-muted">Trusted by organizations across industries</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="testimonial-card h-100">
              <div class="mb-3">
                <i class="bx bxs-quote-left text-primary fs-1"></i>
              </div>
              <p class="testimonial-quote">
                "OfisiLink has revolutionized our office operations. The automation features have 
                saved us countless hours, and the transparency in financial tracking is unmatched."
              </p>
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bx bx-user fs-4"></i>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <p class="testimonial-author mb-0">John Doe</p>
                  <small class="text-muted">CEO, Tech Solutions Ltd</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="testimonial-card h-100">
              <div class="mb-3">
                <i class="bx bxs-quote-left text-primary fs-1"></i>
              </div>
              <p class="testimonial-quote">
                "The file management system is exceptional. We can now track every document, 
                and the search functionality makes finding files incredibly easy."
              </p>
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bx bx-user fs-4"></i>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <p class="testimonial-author mb-0">Jane Smith</p>
                  <small class="text-muted">Operations Manager, Global Corp</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="testimonial-card h-100">
              <div class="mb-3">
                <i class="bx bxs-quote-left text-primary fs-1"></i>
              </div>
              <p class="testimonial-quote">
                "The HR module has streamlined our entire employee management process. 
                Leave requests, payroll, and performance reviews are now all in one place."
              </p>
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bx bx-user fs-4"></i>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <p class="testimonial-author mb-0">Michael Johnson</p>
                  <small class="text-muted">HR Director, Enterprise Inc</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Flexible Pricing Plans</h2>
            <p class="lead text-muted">Choose the perfect plan for your organization</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4 justify-content-center">
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 15px;">
              <div class="card-body p-5 text-center">
                <h5 class="text-muted mb-3">Starter</h5>
                <h2 class="fw-bold text-primary mb-4">TZS 500K<span class="fs-6 text-muted">/month</span></h2>
                <ul class="list-unstyled text-start mb-4">
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Up to 25 Users</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Basic Modules</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Email Support</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>5GB Storage</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Standard Reports</li>
                </ul>
                <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                  <i class="bx bx-play-circle me-2"></i>Try Demo
                </a>
                <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-primary w-100">
                  <i class="bx bx-log-in me-2"></i>Access Live
                </a>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card h-100 shadow-lg border-primary border-2" style="border-radius: 15px; transform: scale(1.05);">
              <div class="card-body p-5 text-center position-relative">
                <span class="badge bg-primary position-absolute top-0 start-50 translate-middle px-3 py-2">Most Popular</span>
                <h5 class="text-primary mb-3 mt-3">Professional</h5>
                <h2 class="fw-bold text-primary mb-4">TZS 1.5M<span class="fs-6 text-muted">/month</span></h2>
                <ul class="list-unstyled text-start mb-4">
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Up to 100 Users</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>All Modules</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Priority Support</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>50GB Storage</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Advanced Reports</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>API Access</li>
                </ul>
                <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                  <i class="bx bx-play-circle me-2"></i>Try Demo
                </a>
                <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-primary w-100">
                  <i class="bx bx-log-in me-2"></i>Access Live
                </a>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 15px;">
              <div class="card-body p-5 text-center">
                <h5 class="text-muted mb-3">Enterprise</h5>
                <h2 class="fw-bold text-primary mb-4">Custom<span class="fs-6 text-muted">/month</span></h2>
                <ul class="list-unstyled text-start mb-4">
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Unlimited Users</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>All Modules + Custom</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>24/7 Dedicated Support</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Unlimited Storage</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>Custom Reports</li>
                  <li class="mb-3"><i class="bx bx-check text-primary me-2"></i>On-Premise Option</li>
                </ul>
                <a href="mailto:emca@emca.tech" class="btn btn-outline-primary w-100">Contact Sales</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Integrations Section -->
    <section id="integrations" class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Seamless Integrations</h2>
            <p class="lead text-muted">Connect with your favorite tools and services</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bxl-google bx-lg text-primary"></i>
              </div>
              <h6>Google Workspace</h6>
              <p class="text-muted small mb-0">Sync with Gmail, Drive, and Calendar</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bxl-microsoft bx-lg text-primary"></i>
              </div>
              <h6>Microsoft 365</h6>
              <p class="text-muted small mb-0">Integrate with Office and Outlook</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-credit-card bx-lg text-primary"></i>
              </div>
              <h6>Payment Gateways</h6>
              <p class="text-muted small mb-0">Connect with mobile money and banks</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-cloud-upload bx-lg text-primary"></i>
              </div>
              <h6>Cloud Storage</h6>
              <p class="text-muted small mb-0">Sync with Dropbox, OneDrive, AWS</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-message-rounded bx-lg text-primary"></i>
              </div>
              <h6>SMS Gateway</h6>
              <p class="text-muted small mb-0">Send notifications via SMS</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-envelope bx-lg text-primary"></i>
              </div>
              <h6>Email Services</h6>
              <p class="text-muted small mb-0">Connect with SMTP providers</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-code-alt bx-lg text-primary"></i>
              </div>
              <h6>REST API</h6>
              <p class="text-muted small mb-0">Build custom integrations</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-printer bx-lg text-primary"></i>
              </div>
              <h6>Biometric Devices</h6>
              <p class="text-muted small mb-0">Connect attendance systems</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Video Demo Section -->
    <section id="demo" class="py-5 bg-light">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 fade-in">
            <h2 class="display-5 fw-bold text-primary mb-4">See OfisiLink in Action</h2>
            <p class="lead text-muted mb-4">
              Watch how OfisiLink transforms office operations with intuitive workflows, 
              powerful automation, and comprehensive reporting.
            </p>
            <ul class="list-unstyled">
              <li class="mb-3"><i class="bx bx-check-circle text-primary me-2 fs-5"></i>Complete system walkthrough</li>
              <li class="mb-3"><i class="bx bx-check-circle text-primary me-2 fs-5"></i>Real-world use cases</li>
              <li class="mb-3"><i class="bx bx-check-circle text-primary me-2 fs-5"></i>Feature demonstrations</li>
              <li class="mb-3"><i class="bx bx-check-circle text-primary me-2 fs-5"></i>Best practices and tips</li>
            </ul>
            <div class="mt-4">
              <a href="https://demo.ofisilink.com/login" target="_blank" class="btn btn-primary btn-lg me-3">
                <i class="bx bx-play-circle me-2"></i>Try Live Demo
              </a>
              <a href="https://live.ofisilink.com/login" target="_blank" class="btn btn-outline-primary btn-lg">
                <i class="bx bx-log-in me-2"></i>Access Live System
              </a>
            </div>
          </div>
          <div class="col-lg-6 fade-in">
            <div class="position-relative" style="border-radius: 15px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
              <div class="ratio ratio-16x9">
                <!-- OfisiLink Video Demo from YouTube - Autoplay with Muted -->
                <iframe 
                  src="https://www.youtube.com/embed/G2GX5cjAIo0?rel=0&modestbranding=1&showinfo=0&autoplay=1&mute=1&loop=1&playlist=G2GX5cjAIo0&controls=1" 
                  title="OfisiLink Video Demo" 
                  frameborder="0" 
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                  allowfullscreen
                  style="width: 100%; height: 100%; border-radius: 15px;">
                </iframe>
              </div>
              <div class="text-center mt-3">
                <div class="alert alert-info d-inline-block mb-0" style="border-left: 4px solid #940000; padding: 10px 15px;">
                  <i class="bx bx-info-circle me-2"></i>
                  <strong>Video Demo Coming Soon</strong> - More comprehensive demonstrations coming soon!
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Security & Compliance Section -->
    <section id="security" class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Enterprise-Grade Security</h2>
            <p class="lead text-muted">Your data is protected with industry-leading security measures</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 p-4" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-shield-check text-primary" style="font-size: 48px;"></i>
              </div>
              <h5 class="fw-bold mb-3">Data Encryption</h5>
              <p class="text-muted">All data is encrypted in transit and at rest using AES-256 encryption standards.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 p-4" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-lock-alt text-primary" style="font-size: 48px;"></i>
              </div>
              <h5 class="fw-bold mb-3">Access Control</h5>
              <p class="text-muted">Role-based access control with multi-factor authentication and permission management.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 p-4" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-time-five text-primary" style="font-size: 48px;"></i>
              </div>
              <h5 class="fw-bold mb-3">Automated Backups</h5>
              <p class="text-muted">Daily automated backups with point-in-time recovery and disaster recovery plans.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 p-4" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-check-shield text-primary" style="font-size: 48px;"></i>
              </div>
              <h5 class="fw-bold mb-3">Compliance</h5>
              <p class="text-muted">Compliant with data protection regulations and industry standards.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 p-4" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-network-chart text-primary" style="font-size: 48px;"></i>
              </div>
              <h5 class="fw-bold mb-3">99.9% Uptime</h5>
              <p class="text-muted">Redundant infrastructure with monitoring and automatic failover capabilities.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 p-4" style="border-radius: 15px;">
              <div class="mb-3">
                <i class="bx bx-history text-primary" style="font-size: 48px;"></i>
              </div>
              <h5 class="fw-bold mb-3">Audit Trails</h5>
              <p class="text-muted">Complete audit logs for all system activities ensuring full transparency.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Mobile App Section -->
    <section id="mobile" class="py-5 bg-light">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 fade-in order-lg-2">
            <h2 class="display-5 fw-bold text-primary mb-4">Work on the Go</h2>
            <p class="lead text-muted mb-4">
              Access OfisiLink from anywhere with our mobile-responsive design. 
              Manage tasks, approve requests, and view reports from your smartphone or tablet.
            </p>
            <div class="row g-3 mb-4">
              <div class="col-6">
                <div class="d-flex align-items-center">
                  <i class="bx bx-check-circle text-primary me-2 fs-5"></i>
                  <span>Mobile-Optimized UI</span>
                </div>
              </div>
              <div class="col-6">
                <div class="d-flex align-items-center">
                  <i class="bx bx-check-circle text-primary me-2 fs-5"></i>
                  <span>Offline Capabilities</span>
                </div>
              </div>
              <div class="col-6">
                <div class="d-flex align-items-center">
                  <i class="bx bx-check-circle text-primary me-2 fs-5"></i>
                  <span>Push Notifications</span>
                </div>
              </div>
              <div class="col-6">
                <div class="d-flex align-items-center">
                  <i class="bx bx-check-circle text-primary me-2 fs-5"></i>
                  <span>Biometric Login</span>
                </div>
              </div>
            </div>
            <div class="d-flex gap-3">
              <a href="#" class="btn btn-dark btn-lg">
                <i class="bx bxl-apple me-2"></i>App Store
              </a>
              <a href="#" class="btn btn-dark btn-lg">
                <i class="bx bxl-android me-2"></i>Google Play
              </a>
            </div>
          </div>
          <div class="col-lg-6 fade-in order-lg-1">
            <div class="text-center">
              <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" 
                   alt="Mobile App" class="img-fluid" style="max-height: 500px; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.2));">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ROI & Benefits Section -->
    <section id="roi" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Return on Investment</h2>
            <p class="lead text-muted">See the measurable impact on your organization</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 15px;">
              <div class="card-body p-5">
                <div class="mb-4">
                  <i class="bx bx-time text-primary" style="font-size: 64px;"></i>
                </div>
                <h3 class="fw-bold text-primary mb-3">70% Time Savings</h3>
                <p class="text-muted">Reduce administrative time by automating workflows, eliminating manual data entry, 
                and streamlining approval processes. Your staff can focus on strategic work instead of paperwork.</p>
                <div class="mt-4">
                  <h5 class="text-success fw-bold">+15 Hours/Week</h5>
                  <p class="text-muted small mb-0">Saved per employee</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 15px;">
              <div class="card-body p-5">
                <div class="mb-4">
                  <i class="bx bx-money text-primary" style="font-size: 64px;"></i>
                </div>
                <h3 class="fw-bold text-primary mb-3">60% Cost Reduction</h3>
                <p class="text-muted">Eliminate paper, printing, storage, and manual processing costs. Reduce errors 
                and rework. Optimize resource allocation with data-driven insights.</p>
                <div class="mt-4">
                  <h5 class="text-success fw-bold">TZS 2M+/Year</h5>
                  <p class="text-muted small mb-0">Average savings per organization</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 15px;">
              <div class="card-body p-5">
                <div class="mb-4">
                  <i class="bx bx-trending-up text-primary" style="font-size: 64px;"></i>
                </div>
                <h3 class="fw-bold text-primary mb-3">95% Accuracy</h3>
                <p class="text-muted">Automated calculations, validation rules, and workflow controls eliminate human 
                errors. Ensure compliance and accuracy in all operations.</p>
                <div class="mt-4">
                  <h5 class="text-success fw-bold">99.9% Uptime</h5>
                  <p class="text-muted small mb-0">System reliability</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Implementation Timeline Section -->
    <section id="implementation" class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Quick Implementation</h2>
            <p class="lead text-muted">Get up and running in days, not months</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-8 mx-auto">
            <div class="timeline">
              <div class="timeline-item fade-in mb-4">
                <div class="d-flex">
                  <div class="timeline-marker bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-4" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">1</div>
                  <div class="flex-grow-1">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                      <div class="card-body p-4">
                        <h5 class="fw-bold mb-2">Day 1-2: Setup & Configuration</h5>
                        <p class="text-muted mb-0">Initial system setup, organization configuration, user account creation, 
                        and basic module configuration. Our team handles the technical setup.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="timeline-item fade-in mb-4">
                <div class="d-flex">
                  <div class="timeline-marker bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-4" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">2</div>
                  <div class="flex-grow-1">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                      <div class="card-body p-4">
                        <h5 class="fw-bold mb-2">Day 3-4: Data Migration</h5>
                        <p class="text-muted mb-0">Import existing employee data, financial records, and documents. 
                        Our team ensures data integrity and accuracy during migration.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="timeline-item fade-in mb-4">
                <div class="d-flex">
                  <div class="timeline-marker bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-4" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">3</div>
                  <div class="flex-grow-1">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                      <div class="card-body p-4">
                        <h5 class="fw-bold mb-2">Day 5-6: Training & Onboarding</h5>
                        <p class="text-muted mb-0">Comprehensive training sessions for administrators and end-users. 
                        Role-specific training, best practices, and Q&A sessions.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="timeline-item fade-in">
                <div class="d-flex">
                  <div class="timeline-marker bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-4" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">4</div>
                  <div class="flex-grow-1">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                      <div class="card-body p-4">
                        <h5 class="fw-bold mb-2">Day 7: Go Live!</h5>
                        <p class="text-muted mb-0">System goes live with full support. Ongoing monitoring, 
                        support, and optimization to ensure smooth operations.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Support & Training Section -->
    <section id="support" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Comprehensive Support & Training</h2>
            <p class="lead text-muted">We're with you every step of the way</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 15px;">
              <div class="card-body p-4">
                <div class="mb-3">
                  <i class="bx bx-book-open text-primary" style="font-size: 48px;"></i>
                </div>
                <h5 class="fw-bold mb-3">Comprehensive Training</h5>
                <p class="text-muted">Role-based training sessions, video tutorials, documentation, 
                and hands-on workshops. We ensure your team is confident using OfisiLink.</p>
                <ul class="list-unstyled text-start mt-3">
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Initial onboarding training</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Role-specific workshops</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Video tutorials & guides</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Advanced feature training</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 15px;">
              <div class="card-body p-4">
                <div class="mb-3">
                  <i class="bx bx-support text-primary" style="font-size: 48px;"></i>
                </div>
                <h5 class="fw-bold mb-3">24/7 Support</h5>
                <p class="text-muted">Multiple support channels available whenever you need help. 
                Fast response times and dedicated support for enterprise clients.</p>
                <ul class="list-unstyled text-start mt-3">
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Email support</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Phone support</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Live chat (coming soon)</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Ticket system</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 fade-in">
            <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 15px;">
              <div class="card-body p-4">
                <div class="mb-3">
                  <i class="bx bx-cog text-primary" style="font-size: 48px;"></i>
                </div>
                <h5 class="fw-bold mb-3">Ongoing Maintenance</h5>
                <p class="text-muted">Regular updates, security patches, performance optimization, 
                and feature enhancements. Your system stays current and secure.</p>
                <ul class="list-unstyled text-start mt-3">
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Regular system updates</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Security patches</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Performance monitoring</li>
                  <li class="mb-2"><i class="bx bx-check text-primary me-2"></i>Feature enhancements</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5 fade-in">
            <h2 class="display-4 fw-bold text-primary mb-3">Frequently Asked Questions</h2>
            <p class="lead text-muted">Everything you need to know about OfisiLink</p>
            <div class="mx-auto" style="width: 100px; height: 4px; background: linear-gradient(90deg, #940000, #a80000); border-radius: 2px;"></div>
          </div>
        </div>
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="accordion" id="faqAccordion">
              <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 10px;">
                <h2 class="accordion-header">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                    What is OfisiLink and what does it do?
                  </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    OfisiLink is a comprehensive office management system that automates and streamlines 
                    various office operations including accounting, file management, HR processes, task 
                    management, and incident tracking. It helps organizations improve efficiency, 
                    transparency, and decision-making through digital transformation.
                  </div>
                </div>
              </div>
              <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 10px;">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                    How do I get started with OfisiLink?
                  </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    Getting started is easy! Visit our demo system to explore features, or contact us 
                    for a personalized demonstration. Try our live demo at 
                    <a href="https://demo.ofisilink.com/login" target="_blank">demo.ofisilink.com</a> 
                    to explore the system before signing up.
                  </div>
                </div>
              </div>
              <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 10px;">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                    Is my data secure?
                  </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    Absolutely! We use industry-standard encryption (AES-256) for data at rest and in transit. 
                    We have regular security audits, automated backups, and comply with data protection 
                    regulations. Your data is stored securely and only accessible to authorized personnel.
                  </div>
                </div>
              </div>
              <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 10px;">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                    Can I customize OfisiLink for my organization?
                  </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    Yes! OfisiLink offers extensive customization options. You can configure workflows, 
                    create custom fields, set up role-based permissions, and even integrate with other 
                    systems via our REST API. Enterprise plans include dedicated support for custom 
                    development.
                  </div>
                </div>
              </div>
              <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 10px;">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                    What kind of support do you provide?
                  </button>
                </h2>
                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    We provide comprehensive support including email support, phone support, live chat, 
                    and dedicated account managers for enterprise clients. We also offer training sessions, 
                    documentation, video tutorials, and a knowledge base to help you get the most out of OfisiLink.
                  </div>
                </div>
              </div>
              <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 10px;">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                    Can I access OfisiLink on mobile devices?
                  </button>
                </h2>
                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    Yes! OfisiLink is fully responsive and works seamlessly on smartphones and tablets. 
                    You can access all features, approve requests, view reports, and manage tasks from 
                    any device with an internet connection. Native mobile apps are also in development.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Advanced CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="cta-content fade-in">
              <h2 class="display-4 fw-bold text-primary mb-4">Ready to Transform Your Office?</h2>
              <p class="lead mb-5 text-muted">
                Join thousands of organizations already using OfisiLink to streamline their operations, 
                increase efficiency, and make data-driven decisions. Experience the future of office management 
                with OfisiLink by EmCa Technologies LTD.
              </p>
              <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ route('login') }}" class="btn btn-primary cta-button">
                  <i class="bx bx-play-circle me-2"></i>Try Demo Now
                </a>
                <a href="#features" class="btn btn-outline-primary btn-lg px-5 py-3">
                  <i class="bx bx-info-circle me-2"></i>Learn More
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Advanced Footer -->
    <footer class="bg-dark text-white">
      <div class="container py-5">
        <div class="row g-4">
          <div class="col-lg-4 col-md-6">
            <img src="{{ asset('assets/img/office_link_logo.png') }}" alt="OfisiLink Logo" class="footer-logo" />
            <h5 class="mb-3">EmCa Technologies LTD</h5>
            <p class="text-white-50 mb-3">
              Leading provider of innovative ICT solutions for modern businesses across Tanzania and East Africa.
            </p>
            <p class="text-white mb-0">
              <strong>Our Slogan:</strong><br>
              <em class="text-white-50">"Affordable ICT Services with Exceptional Value"</em>
            </p>
          </div>
          <div class="col-lg-2 col-md-6">
            <h6 class="mb-3">Quick Links</h6>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#home" class="text-white-50 text-decoration-none">Home</a></li>
              <li class="mb-2"><a href="#features" class="text-white-50 text-decoration-none">Features</a></li>
              <li class="mb-2"><a href="#workflows" class="text-white-50 text-decoration-none">Workflows</a></li>
              <li class="mb-2"><a href="#use-cases" class="text-white-50 text-decoration-none">Use Cases</a></li>
              <li class="mb-2"><a href="#benefits" class="text-white-50 text-decoration-none">Benefits</a></li>
              <li class="mb-2"><a href="#pricing" class="text-white-50 text-decoration-none">Pricing</a></li>
              <li class="mb-2"><a href="#testimonials" class="text-white-50 text-decoration-none">Testimonials</a></li>
              <li class="mb-2"><a href="#faq" class="text-white-50 text-decoration-none">FAQ</a></li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-6">
            <h6 class="mb-3">Our Solutions</h6>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">SACCOSLink</a></li>
              <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">OfisiLink</a></li>
              <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">PangishaLink</a></li>
              <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">MauzoLink</a></li>
              <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">WauminiLink</a></li>
              <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">MkulimaLink</a></li>
              <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">ShuleLink</a></li>
              <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">NiajiriLink</a></li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-6">
            <h6 class="mb-3">Contact Information</h6>
            <ul class="list-unstyled text-white-50">
              <li class="mb-2">
                <i class="bx bx-map me-2"></i>
                Ben Bella Street, Moshi Municipality<br>
                <span class="ms-4">Opposite High Court of Tanzania</span><br>
                <span class="ms-4">P.O. Box 20, Moshi – Kilimanjaro</span><br>
                <span class="ms-4">Postcode: 25101</span>
              </li>
              <li class="mb-2"><i class="bx bx-phone me-2"></i><a href="tel:+255749719998" class="text-white-50 text-decoration-none">+255 749 719 998</a></li>
              <li class="mb-2"><i class="bx bx-envelope me-2"></i><a href="mailto:emca@emca.tech" class="text-white-50 text-decoration-none">emca@emca.tech</a></li>
              <li class="mb-2"><i class="bx bx-globe me-2"></i><a href="https://www.emca.tech" target="_blank" class="text-white-50 text-decoration-none">www.emca.tech</a></li>
              <li class="mb-2"><i class="bx bx-link me-2"></i><a href="https://live.ofisilink.com/login" target="_blank" class="text-white-50 text-decoration-none">Live System</a></li>
              <li class="mb-2"><i class="bx bx-play-circle me-2"></i><a href="https://demo.ofisilink.com/login" target="_blank" class="text-white-50 text-decoration-none">Demo System</a></li>
            </ul>
          </div>
        </div>
        <hr class="my-4 bg-white-50" />
        <div class="row mb-3">
          <div class="col-12">
            <div class="bg-dark p-3 rounded" style="border-left: 4px solid #940000;">
              <h6 class="text-white mb-2"><i class="bx bx-info-circle me-2"></i>Domain Structure</h6>
              <div class="row text-white-50 small">
                <div class="col-md-4">
                  <strong class="text-white">Main Domain:</strong><br>
                  <a href="https://ofisilink.com" class="text-white-50 text-decoration-none">ofisilink.com</a><br>
                  <span class="text-muted">Landing Page</span>
                </div>
                <div class="col-md-4">
                  <strong class="text-white">Live System:</strong><br>
                  <a href="https://live.ofisilink.com/login" target="_blank" class="text-white-50 text-decoration-none">live.ofisilink.com</a><br>
                  <span class="text-muted">Production Environment</span>
                </div>
                <div class="col-md-4">
                  <strong class="text-white">Demo System:</strong><br>
                  <a href="https://demo.ofisilink.com/login" target="_blank" class="text-white-50 text-decoration-none">demo.ofisilink.com</a><br>
                  <span class="text-muted">Demo Environment</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <hr class="my-4 bg-white-50" />
        <div class="row">
          <div class="col-md-6">
           
            <p class="mb-0 text-white-50">© {{ date('Y') }} EmCa Technologies LTD. All Rights Reserved.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <p class="mb-0 text-white-50">Proudly Tanzanian - Powered by EmCa Techonologies</p>
          </div>
        </div>
      </div>
    </footer>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Advanced Landing Page Scripts -->
    <script>
      // Scroll Progress Bar
      window.addEventListener('scroll', function() {
        const scrollProgress = document.getElementById('scrollProgress');
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        scrollProgress.style.width = scrollPercent + '%';
      });

      // Sticky Navbar
      window.addEventListener('scroll', function() {
        const navbar = document.getElementById('mainNav');
        if (window.scrollY > 50) {
          navbar.classList.add('scrolled');
        } else {
          navbar.classList.remove('scrolled');
        }
      });

      // Smooth Scrolling
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            const offsetTop = target.offsetTop - 80;
            window.scrollTo({
              top: offsetTop,
              behavior: 'smooth'
            });
          }
        });
      });

      // Fade In Animation on Scroll
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
          }
        });
      }, observerOptions);

      document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
      });

      // Animated Counter
      function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;

        const updateCounter = () => {
          current += increment;
          if (current < target) {
            element.textContent = Math.floor(current);
            requestAnimationFrame(updateCounter);
          } else {
            element.textContent = target;
          }
        };

        updateCounter();
      }

      // Counter Observer
      const counterObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
          if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
            entry.target.classList.add('counted');
            animateCounter(entry.target);
          }
        });
      }, { threshold: 0.5 });

      document.querySelectorAll('.stat-number').forEach(counter => {
        counterObserver.observe(counter);
      });

      // Parallax Effect for Hero Section
      window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero-section');
        if (hero) {
          hero.style.transform = `translateY(${scrolled * 0.5}px)`;
          hero.style.opacity = 1 - (scrolled / 500);
        }
      });

      // Active Navigation Link Highlighting
      window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link[href^="#"]');

        let current = '';
        sections.forEach(section => {
          const sectionTop = section.offsetTop - 100;
          const sectionHeight = section.clientHeight;
          if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
            current = section.getAttribute('id');
          }
        });

        navLinks.forEach(link => {
          link.classList.remove('active');
          if (link.getAttribute('href') === '#' + current) {
            link.classList.add('active');
          }
        });
      });
      
      // Workflow Card Arrow Animation
      document.querySelectorAll('.workflow-card').forEach(card => {
        card.addEventListener('show.bs.collapse', function() {
          const arrow = this.querySelector('.workflow-arrow');
          if (arrow) arrow.style.transform = 'rotate(180deg)';
        });
        card.addEventListener('hide.bs.collapse', function() {
          const arrow = this.querySelector('.workflow-arrow');
          if (arrow) arrow.style.transform = 'rotate(0deg)';
        });
      });
    </script>
  </body>
</html>