/**
 * Geex Template - Vereinfachtes Main.js für PHP Template
 * Enthält nur die wesentlichen Funktionen ohne unnötige Dependencies
 */

(function ($) {
    'use strict';

    // Sidebar Toggle
    $(document).on('click', '.geex-btn__toggle-sidebar', function () {
        $('.geex-sidebar').toggleClass('show');
    });

    $(document).on('click', '.geex-sidebar__close', function () {
        $('.geex-sidebar').removeClass('show');
    });

    // Customizer Toggle
    $(document).on('click', '.geex-btn__customizer', function () {
        $('.geex-customizer').addClass('show');
        $('.geex-customizer-overlay').addClass('show');
    });

    $(document).on('click', '.geex-btn__customizer-close, .geex-customizer-overlay', function () {
        $('.geex-customizer').removeClass('show');
        $('.geex-customizer-overlay').removeClass('show');
    });

    // Theme Customizer
    $('.geex-customizer__btn--light').on('click', function () {
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
        $('.geex-customizer__btn--light').addClass('active');
        $('.geex-customizer__btn--dark').removeClass('active');
    });

    $('.geex-customizer__btn--dark').on('click', function () {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        $('.geex-customizer__btn--dark').addClass('active');
        $('.geex-customizer__btn--light').removeClass('active');
    });

    // Layout Customizer
    $('.geex-customizer__btn--top').on('click', function () {
        document.documentElement.setAttribute('data-nav', 'top');
        localStorage.setItem('navbar', 'top');
        $('.geex-customizer__btn--top').addClass('active');
        $('.geex-customizer__btn--side').removeClass('active');
    });

    $('.geex-customizer__btn--side').on('click', function () {
        document.documentElement.setAttribute('data-nav', 'side');
        localStorage.setItem('navbar', 'side');
        $('.geex-customizer__btn--side').addClass('active');
        $('.geex-customizer__btn--top').removeClass('active');
    });

    // RTL/LTR
    $('.geex-customizer__btn--rtl').on('click', function () {
        document.documentElement.setAttribute('dir', 'rtl');
        localStorage.setItem('layout', 'rtl');
        $('.geex-customizer__btn--rtl').addClass('active');
        $('.geex-customizer__btn--ltr').removeClass('active');
    });

    $('.geex-customizer__btn--ltr').on('click', function () {
        document.documentElement.setAttribute('dir', 'ltr');
        localStorage.setItem('layout', 'ltr');
        $('.geex-customizer__btn--ltr').addClass('active');
        $('.geex-customizer__btn--rtl').removeClass('active');
    });

    // Dropdown Toggle
    $(document).on('click', '.has-children > a', function (e) {
        var $this = $(this);
        var $parent = $this.parent();
        var $submenu = $parent.find('> ul');

        if ($submenu.length) {
            e.preventDefault();
            $parent.toggleClass('active');
            $submenu.slideToggle(300);
        }
    });

    // Header Search Toggle
    $(document).on('click', '.geex-content__header__quickaction__link', function (e) {
        e.preventDefault();
        var $popup = $(this).siblings('.geex-content__header__popup');
        $popup.toggleClass('show');
    });

    // Close popup when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.geex-content__header__quickaction__item').length) {
            $('.geex-content__header__popup').removeClass('show');
        }
    });

    // Close sidebar on overlay click (mobile)
    $(document).on('click', '.geex-overlay', function () {
        $('.geex-sidebar').removeClass('show');
        $(this).removeClass('show');
    });

    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Smooth scroll for anchor links
    $(document).on('click', 'a[href^="#"]', function (e) {
        var target = $(this).attr('href');
        if (target !== '#' && $(target).length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $(target).offset().top - 100
            }, 500);
        }
    });

    // Active menu item based on current URL
    var currentUrl = window.location.href;
    $('.geex-sidebar__menu a, .geex-header__menu a').each(function () {
        if (this.href === currentUrl) {
            $(this).addClass('active');
            $(this).parents('.has-children').addClass('active');
        }
    });

})(jQuery);

// Console info
console.log('%c Geex PHP Template ', 'background: #AB54DB; color: #fff; padding: 5px 10px; border-radius: 3px;');
console.log('Dynamisches Content-Loading aktiv ✓');
