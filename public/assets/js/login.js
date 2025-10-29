/**
 * Login Page Script
 * Handles user authentication
 */

// Override API base URL for mock server in sandbox
if (window.location.hostname !== 'localhost') {
    API_CONFIG.baseURL = window.location.origin + '/api/mock-server.php';
}

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('signInForm');

    // Check if already logged in
    if (Auth.isAuthenticated()) {
        window.location.href = '/public/index.html';
        return;
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('emailSignIn').value;
            const password = document.getElementById('loginPassword').value;
            const submitBtn = loginForm.querySelector('button[type="submit"]');

            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';

            try {
                const response = await api.login(email, password);

                if (response.success) {
                    // Show success message
                    showMessage('Login successful! Redirecting...', 'success');

                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = '/public/index.html';
                    }, 1000);
                } else {
                    showMessage(response.error || 'Login failed', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In';
                }
            } catch (error) {
                console.error('Login error:', error);
                showMessage(error.message || 'Login failed. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            }
        });
    }

    // Password toggle
    const togglePassword = document.querySelector('.toggle-password-type');
    if (togglePassword) {
        togglePassword.addEventListener('click', () => {
            const passwordInput = document.getElementById('loginPassword');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('uil-eye');
            togglePassword.classList.toggle('uil-eye-slash');
        });
    }
});

/**
 * Show message to user
 */
function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMsg = document.querySelector('.alert-message');
    if (existingMsg) {
        existingMsg.remove();
    }

    // Create message element
    const msgDiv = document.createElement('div');
    msgDiv.className = `alert-message alert-${type}`;
    msgDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#4caf50' : '#f44336'};
        color: white;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    msgDiv.textContent = message;

    document.body.appendChild(msgDiv);

    // Remove after 3 seconds
    setTimeout(() => {
        msgDiv.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => msgDiv.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
