/**
 * Authentication Module
 * Handles user authentication and authorization
 */

const Auth = {
    /**
     * Check if user is logged in
     */
    isAuthenticated() {
        const token = localStorage.getItem('accessToken');
        const user = this.getCurrentUser();
        return !!(token && user);
    },

    /**
     * Get current user from localStorage
     */
    getCurrentUser() {
        const userJson = localStorage.getItem('user');
        return userJson ? JSON.parse(userJson) : null;
    },

    /**
     * Get user role
     */
    getRole() {
        const user = this.getCurrentUser();
        return user ? user.role : null;
    },

    /**
     * Check if user has role
     */
    hasRole(roles) {
        const userRole = this.getRole();
        if (!userRole) return false;

        if (Array.isArray(roles)) {
            return roles.includes(userRole);
        }
        return userRole === roles;
    },

    /**
     * Check if user is superadmin
     */
    isSuperadmin() {
        return this.hasRole('superadmin');
    },

    /**
     * Check if user is admin or superadmin
     */
    isAdmin() {
        return this.hasRole(['superadmin', 'admin']);
    },

    /**
     * Check if user is agent
     */
    isAgent() {
        return this.hasRole('agent');
    },

    /**
     * Redirect to login if not authenticated
     */
    requireAuth() {
        if (!this.isAuthenticated()) {
            window.location.href = '/public/signin.html';
            return false;
        }
        return true;
    },

    /**
     * Require specific role
     */
    requireRole(roles) {
        if (!this.requireAuth()) return false;

        if (!this.hasRole(roles)) {
            this.showUnauthorized();
            return false;
        }
        return true;
    },

    /**
     * Show unauthorized message
     */
    showUnauthorized() {
        alert('Sie haben keine Berechtigung für diese Aktion.');
        window.location.href = '/public/index.html';
    },

    /**
     * Logout user
     */
    async logout() {
        try {
            await api.logout();
        } catch (error) {
            console.error('Logout error:', error);
            // Force logout even if API call fails
            localStorage.clear();
            window.location.href = '/public/signin.html';
        }
    },

    /**
     * Initialize auth check on page load
     */
    async init() {
        // Skip auth check on login/signup pages
        const currentPage = window.location.pathname;
        if (currentPage.includes('signin.html') ||
            currentPage.includes('signup.html') ||
            currentPage.includes('forgot-password.html')) {
            return;
        }

        // Check authentication
        if (!this.isAuthenticated()) {
            window.location.href = '/public/signin.html';
            return;
        }

        // Verify token with backend
        try {
            await api.checkAuth();
        } catch (error) {
            console.error('Auth check failed:', error);
            localStorage.clear();
            window.location.href = '/public/signin.html';
        }
    },

    /**
     * Update user navigation based on role
     */
    updateNavigation() {
        const user = this.getCurrentUser();
        if (!user) return;

        // Show/hide menu items based on role
        const userManagementLinks = document.querySelectorAll('[data-role="superadmin"]');
        userManagementLinks.forEach(link => {
            link.style.display = this.isSuperadmin() ? '' : 'none';
        });

        // Update user info in header
        const usernameElements = document.querySelectorAll('[data-user-name]');
        usernameElements.forEach(el => {
            el.textContent = user.username;
        });

        const userEmailElements = document.querySelectorAll('[data-user-email]');
        userEmailElements.forEach(el => {
            el.textContent = user.email;
        });

        const userRoleElements = document.querySelectorAll('[data-user-role]');
        userRoleElements.forEach(el => {
            el.textContent = this.getRoleDisplayName(user.role);
        });
    },

    /**
     * Get role display name
     */
    getRoleDisplayName(role) {
        const roleNames = {
            'superadmin': 'Superadmin',
            'admin': 'Administrator',
            'agent': 'Agent'
        };
        return roleNames[role] || role;
    },

    /**
     * Show/hide elements based on permissions
     */
    applyPermissions() {
        const user = this.getCurrentUser();
        if (!user) return;

        // Hide create/edit/delete buttons for agents
        if (this.isAgent()) {
            const writeActions = document.querySelectorAll('[data-permission="write"]');
            writeActions.forEach(el => {
                el.style.display = 'none';
            });
        }

        // Hide superadmin-only elements
        if (!this.isSuperadmin()) {
            const superadminElements = document.querySelectorAll('[data-permission="superadmin"]');
            superadminElements.forEach(el => {
                el.style.display = 'none';
            });
        }
    }
};

// Initialize auth on page load
document.addEventListener('DOMContentLoaded', async () => {
    await Auth.init();
    Auth.updateNavigation();
    Auth.applyPermissions();

    // Setup logout button
    const logoutButtons = document.querySelectorAll('[data-logout]');
    logoutButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            if (confirm('Möchten Sie sich wirklich abmelden?')) {
                await Auth.logout();
            }
        });
    });
});
