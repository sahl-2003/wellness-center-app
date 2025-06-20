function updateHeader(username, role) {
    // 1. Find the exact button positions
    const loginBtn = document.querySelector('a.btn-primary[href="login.html"]');
    const registerBtn = document.querySelector('a.btn[href="register.html"]:not(.btn-primary)');

    // 2. Replace login button with logout (same position)
    if (loginBtn && loginBtn.closest('li')) {
        loginBtn.closest('li').innerHTML = `
            <a href="logout.php" class="btn btn-primary">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        `;
    }

    // Replace the register button with the dashboard button
    if (registerBtn && registerBtn.closest('li')) {
        registerBtn.closest('li').innerHTML = `
        <a href="${role}_dashboard.php" class="btn">
            <i class="fas fa-user-circle"></i> Dashboard
        </a>
    `;
    }

    // 4. Update hero section on index page
    if (window.location.pathname.endsWith('index.html') || window.location.pathname === '/') {
        updateHeroWelcome(username);
    }
}

function updateHeroWelcome(username) {
    const heroContent = document.querySelector('.hero-content');
    if (!heroContent) return;

    const existingWelcome = heroContent.querySelector('.hero-welcome');
    if (existingWelcome) existingWelcome.remove();

    const welcomeDiv = document.createElement('div');
    welcomeDiv.className = 'hero-welcome';
    welcomeDiv.innerHTML = `<p class="welcome-text">Welcome back, <span class="username">${username}</span></p> `;

    const heroTitle = heroContent.querySelector('h1');
    if (heroTitle) {
        heroTitle.insertAdjacentElement('beforebegin', welcomeDiv);
    }
}
// Enhanced auth check with error handling
function checkAuthStatus() {
    fetch('auth_status.php')
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            console.log('Auth response:', data);
            if (data.loggedIn) {
                updateHeader(data.username, data.role);
            }
        })
        .catch(error => console.error('Auth check failed:', error));
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, checking auth...');
    checkAuthStatus();

    // Your existing mobile menu code
    document.querySelector('.mobile-menu-btn')?.addEventListener('click', function () {
        document.querySelector('.nav-links').classList.toggle('active');
    });
});