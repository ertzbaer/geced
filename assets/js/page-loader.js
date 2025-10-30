/**
 * Dynamisches PHP-Template-System mit AJAX
 * Lädt nur den <main>-Inhalt ohne Header/Sidebar neu zu laden
 */

(function() {
    'use strict';
    
    const pageLoader = {
        // Konfiguration
        config: {
            mainContentId: 'dynamicContent',
            loaderId: 'pageLoader',
            apiEndpoint: 'ajax-handler.php'
        },
        
        // Initialisierung
        init: function() {
            this.bindEvents();
            this.handleInitialPage();
        },
        
        // Event-Listener binden
        bindEvents: function() {
            const self = this;
            
            // Alle Links mit der Klasse 'page-link' abfangen
            document.addEventListener('click', function(e) {
                const target = e.target.closest('.page-link');
                if (target && target.dataset.page) {
                    e.preventDefault();
                    const page = target.dataset.page;
                    self.loadPage(page);
                }
            });
            
            // Browser-Navigation (Zurück/Vorwärts)
            window.addEventListener('popstate', function(e) {
                if (e.state && e.state.page) {
                    self.loadPage(e.state.page, false);
                }
            });
        },
        
        // Initiale Seite aus URL laden
        handleInitialPage: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page') || 'dashboard';
            this.loadPage(page, true);
        },
        
        // Seite laden
        loadPage: function(page, updateHistory = true) {
            const self = this;
            const loader = document.getElementById(this.config.loaderId);
            const content = document.getElementById(this.config.mainContentId);
            
            // Loading-Indicator anzeigen
            if (loader) loader.classList.add('active');
            
            // AJAX-Request
            fetch(this.config.apiEndpoint + '?page=' + encodeURIComponent(page))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    // Inhalt aktualisieren
                    if (content) {
                        content.innerHTML = html;
                        
                        // Scroll nach oben
                        window.scrollTo(0, 0);
                        
                        // Browser-History aktualisieren
                        if (updateHistory) {
                            const newUrl = window.location.pathname + '?page=' + page;
                            history.pushState({page: page}, '', newUrl);
                        }
                        
                        // Aktive Menu-Items aktualisieren
                        self.updateActiveMenu(page);
                        
                        // Custom Event für andere Scripts
                        document.dispatchEvent(new CustomEvent('pageLoaded', {
                            detail: {page: page}
                        }));
                    }
                })
                .catch(error => {
                    console.error('Fehler beim Laden der Seite:', error);
                    if (content) {
                        content.innerHTML = '<div class="alert alert-danger">Fehler beim Laden der Seite. Bitte versuchen Sie es erneut.</div>';
                    }
                })
                .finally(() => {
                    // Loading-Indicator ausblenden
                    if (loader) loader.classList.remove('active');
                });
        },
        
        // Aktive Menu-Items hervorheben
        updateActiveMenu: function(page) {
            // Alle aktiven Links zurücksetzen
            document.querySelectorAll('.page-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Aktuellen Link markieren
            document.querySelectorAll('.page-link[data-page="' + page + '"]').forEach(link => {
                link.classList.add('active');
            });
        }
    };
    
    // Beim DOM-Ready initialisieren
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            pageLoader.init();
        });
    } else {
        pageLoader.init();
    }
    
})();