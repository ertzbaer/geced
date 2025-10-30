<?php
/**
 * Geex Dashboard - PHP Template System
 * Hauptdatei - Lädt Header, Sidebar und Footer statisch
 * Nur der <main>-Inhalt wird dynamisch über AJAX geladen
 */

// Optionales Laden der Datenbank
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
    } catch (Exception $e) {
        // Datenbank nicht verfügbar
    }
}

// Session starten (für Benutzer-Authentication)
session_start();

// Header einbinden
include 'includes/header.php';
?>

<main class="geex-main-content">
    <?php 
    // Sidebar einbinden (innerhalb von main!)
    include 'includes/sidebar.php'; 
    ?>
    
    <?php 
    // Customizer einbinden
    include 'includes/customizer.php'; 
    ?>
    
    <div class="geex-content">
        <div class="geex-content__header">
            <div class="geex-content__header__content">
                <h2 class="geex-content__header__title">Dashboard</h2>
                <p class="geex-content__header__subtitle">Welcome to Geex Modern Admin Dashboard</p>
            </div>
            
            <div class="geex-content__header__action">
                <div class="geex-content__header__customizer">
                    <button class="geex-btn geex-btn__toggle-sidebar">   
                        <i class="uil uil-align-center-alt"></i> 
                    </button>
                    <button class="geex-btn geex-btn__customizer"> 
                        <i class="uil uil-pen"></i> 
                        <span>Customizer</span>  
                    </button>
                </div> 
                <div class="geex-content__header__action__wrap">
                    <ul class="geex-content__header__quickaction">
                        <li class="geex-content__header__quickaction__item">
                            <a href="#" class="geex-content__header__quickaction__link">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 8L10.8906 13.2604C11.5624 13.7083 12.4376 13.7083 13.1094 13.2604L21 8M5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19Z" stroke="#A3A3C2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="geex-content__header__badge">84</span>
                            </a>
                            <div class="geex-content__header__popup geex-content__header__popup--message">
                                <h3 class="geex-content__header__popup__title">
                                    Messages<span class="content__header__popup__title__count">7</span>
                                </h3>
                                <div class="geex-content__header__popup__content">
                                    <ul class="geex-content__header__popup__items">
                                        <li class="geex-content__header__popup__item">
                                            <a class="geex-content__header__popup__link" href="#">
                                                <div class="geex-content__header__popup__item__img">
                                                    <img src="assets/img/avatar/user.svg" alt="Popup" />
                                                </div>
                                                <div class="geex-content__header__popup__item__content">
                                                    <h5 class="geex-content__header__popup__item__title">
                                                        Manoj Kumar
                                                        <span class="geex-content__header__popup__item__time">2 min ago</span>
                                                    </h5>
                                                    <p class="geex-content__header__popup__item__description">
                                                        Lorem ipsum dolor sit amet
                                                    </p>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="geex-content__header__quickaction__item">
                            <a href="#" class="geex-content__header__quickaction__link">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 17H20L18.5951 15.5951C18.2141 15.2141 18 14.6973 18 14.1585V11C18 8.38757 16.3304 6.16509 14 5.34142V5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5V5.34142C7.66962 6.16509 6 8.38757 6 11V14.1585C6 14.6973 5.78595 15.2141 5.40493 15.5951L4 17H9M15 17V18C15 19.6569 13.6569 21 12 21C10.3431 21 9 19.6569 9 18V17M15 17H9" stroke="#A3A3C2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="geex-content__header__badge">2</span>
                            </a>
                            <div class="geex-content__header__popup geex-content__header__popup--notification">
                                <h3 class="geex-content__header__popup__title">
                                    Notifications<span class="content__header__popup__title__count">5</span>
                                </h3>
                                <div class="geex-content__header__popup__content">
                                    <ul class="geex-content__header__popup__items">
                                        <li class="geex-content__header__popup__item">
                                            <a class="geex-content__header__popup__link" href="#">
                                                <div class="geex-content__header__popup__item__img">
                                                    <img src="assets/img/avatar/user.svg" alt="Popup Img" />
                                                </div>
                                                <div class="geex-content__header__popup__item__content">
                                                    <h5 class="geex-content__header__popup__item__title">
                                                        Manoj Kumar
                                                        <span class="geex-content__header__popup__item__time">2 min ago</span>
                                                    </h5>
                                                    <p class="geex-content__header__popup__item__description">
                                                        Added 2 new photos
                                                    </p>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="geex-content__header__quickaction__item">
                            <a href="#" class="geex-content__header__quickaction__link">
                                <img class="user-img" src="assets/img/avatar/user.svg" alt="User" />
                            </a>
                            <div class="geex-content__header__popup geex-content__header__popup--author">
                                <div class="geex-content__header__popup__header">
                                    <div class="geex-content__header__popup__header__img">
                                        <img src="assets/img/avatar/user.svg" alt="user" />
                                    </div>
                                    <div class="geex-content__header__popup__header__content">
                                        <h3 class="geex-content__header__popup__header__title">Manoj Kumar</h3>
                                        <span class="geex-content__header__popup__header__subtitle">Admin</span>
                                    </div>
                                </div>
                                <div class="geex-content__header__popup__content">
                                    <ul class="geex-content__header__popup__items">
                                        <li class="geex-content__header__popup__item">
                                            <a class="geex-content__header__popup__link" href="#">
                                                <i class="uil uil-user"></i>
                                                Profile
                                            </a>
                                        </li>
                                        <li class="geex-content__header__popup__item">
                                            <a class="geex-content__header__popup__link" href="#">
                                                <i class="uil uil-cog"></i>
                                                Settings
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="geex-content__header__popup__footer">
                                    <a href="#" class="geex-content__header__popup__footer__link">
                                        <i class="uil uil-arrow-up-left"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div> 
        </div>
        
        <!-- Dieser Bereich wird dynamisch über AJAX geladen -->
        <div id="dynamicContent">
            <!-- Initialer Inhalt wird durch JavaScript geladen -->
            <div style="text-align: center; padding: 50px;">
                <div class="spinner" style="margin: 0 auto;"></div>
                <p>Lade...</p>
            </div>
        </div>
    </div>
</main>

<?php
// Footer einbinden
include 'includes/footer.php';
?>
