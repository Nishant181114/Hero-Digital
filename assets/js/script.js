// Hero Digital E-commerce Platform JavaScript

// Global variables
let cartItems = [];
let isLoggedIn = false;

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    checkAuthStatus();
    loadFeaturedProducts();
    setupEventListeners();
    updateCartCount();
}

// Authentication
async function checkAuthStatus() {
    try {
        const response = await fetch('api/auth.php?action=check-auth');
        const data = await response.json();
        
        if (data.success && data.logged_in) {
            isLoggedIn = true;
            updateAuthUI(data.user);
        } else {
            isLoggedIn = false;
            updateAuthUI(null);
        }
    } catch (error) {
        console.error('Auth check failed:', error);
    }
}

function updateAuthUI(user) {
    const authButtons = document.getElementById('auth-buttons');
    const userInfo = document.getElementById('user-info');
    
    if (user && isLoggedIn) {
        authButtons.innerHTML = `
            <span>Welcome, ${user.name}</span>
            <button class="btn btn-secondary" onclick="logout()">Logout</button>
        `;
        
        if (userInfo) {
            userInfo.innerHTML = `
                <div class="user-info">
                    <span>Welcome, ${user.name}</span>
                    <a href="profile.html" class="btn btn-primary">My Profile</a>
                </div>
            `;
        }
    } else {
        authButtons.innerHTML = `
            <button class="btn btn-primary" onclick="showLoginModal()">Login</button>
            <button class="btn btn-secondary" onclick="showRegisterModal()">Register</button>
        `;
        
        if (userInfo) {
            userInfo.innerHTML = '';
        }
    }
}

async function login(email, password) {
    try {
        const response = await fetch('api/auth.php?action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal('login-modal');
            checkAuthStatus();
            showAlert('Login successful!', 'success');
            return true;
        } else {
            showAlert(data.message || 'Login failed', 'error');
            return false;
        }
    } catch (error) {
        console.error('Login error:', error);
        showAlert('Login failed. Please try again.', 'error');
        return false;
    }
}

async function register(userData) {
    try {
        const response = await fetch('api/auth.php?action=register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal('register-modal');
            showAlert('Registration successful! Please login.', 'success');
            showLoginModal();
            return true;
        } else {
            showAlert(data.message || 'Registration failed', 'error');
            return false;
        }
    } catch (error) {
        console.error('Registration error:', error);
        showAlert('Registration failed. Please try again.', 'error');
        return false;
    }
}

async function logout() {
    try {
        const response = await fetch('api/auth.php?action=logout', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            isLoggedIn = false;
            updateAuthUI(null);
            showAlert('Logged out successfully', 'success');
            
            // Redirect to home if not already there
            if (window.location.pathname !== '/' && window.location.pathname !== '/index.html') {
                window.location.href = '/';
            }
        }
    } catch (error) {
        console.error('Logout error:', error);
    }
}

// Product Management
async function loadFeaturedProducts() {
    try {
        const response = await fetch('api/products.php?action=featured&limit=6');
        const data = await response.json();
        
        if (data.success) {
            displayProducts(data.products, 'featured-products');
        }
    } catch (error) {
        console.error('Error loading featured products:', error);
    }
}

async function loadAllProducts(categoryId = null, page = 1) {
    try {
        const limit = 12;
        const offset = (page - 1) * limit;
        let url = `api/products.php?action=list&limit=${limit}&offset=${offset}`;
        
        if (categoryId) {
            url += `&category_id=${categoryId}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            displayProducts(data.products, 'all-products');
            setupPagination(data.total, limit, page);
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

async function searchProducts(query) {
    try {
        const response = await fetch(`api/products.php?action=search&q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success) {
            displayProducts(data.products, 'search-results');
        } else {
            document.getElementById('search-results').innerHTML = '<p>No products found.</p>';
        }
    } catch (error) {
        console.error('Error searching products:', error);
    }
}

function displayProducts(products, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = '';
    
    if (products.length === 0) {
        container.innerHTML = '<p>No products found.</p>';
        return;
    }
    
    products.forEach(product => {
        const productCard = createProductCard(product);
        container.appendChild(productCard);
    });
}

function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    
    const imageUrl = product.image_url || '/assets/images/placeholder.jpg';
    const price = product.sale_price ? 
        `<span class="original-price">${Utils.formatPrice(product.price)}</span>
         <span class="sale-price">${Utils.formatPrice(product.sale_price)}</span>` :
        Utils.formatPrice(product.price);
    
    card.innerHTML = `
        <div class="product-image">
            <img src="${imageUrl}" alt="${product.name}" onerror="this.src='/assets/images/placeholder.jpg'">
        </div>
        <div class="product-info">
            <h3 class="product-title">${product.name}</h3>
            <p class="product-description">${product.short_description || product.description}</p>
            <div class="product-price">${price}</div>
            <button class="btn btn-primary" onclick="addToCart(${product.id})">
                Add to Cart
            </button>
        </div>
    `;
    
    return card;
}

// Cart Management
async function addToCart(productId, quantity = 1) {
    try {
        const response = await fetch('api/cart.php?action=add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId, quantity })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            updateCartCount();
            loadCartItems();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showAlert('Failed to add item to cart', 'error');
    }
}

async function loadCartItems() {
    try {
        const response = await fetch('api/cart.php?action=get');
        const data = await response.json();
        
        if (data.success) {
            cartItems = data.items;
            displayCartItems(data.items);
            updateCartTotal(data.total);
        }
    } catch (error) {
        console.error('Error loading cart:', error);
    }
}

function displayCartItems(items) {
    const cartItemsContainer = document.getElementById('cart-items');
    if (!cartItemsContainer) return;
    
    if (items.length === 0) {
        cartItemsContainer.innerHTML = '<p>Your cart is empty</p>';
        return;
    }
    
    cartItemsContainer.innerHTML = '';
    
    items.forEach(item => {
        const cartItem = createCartItem(item);
        cartItemsContainer.appendChild(cartItem);
    });
}

function createCartItem(item) {
    const div = document.createElement('div');
    div.className = 'cart-item';
    
    const imageUrl = item.image_url || '/assets/images/placeholder.jpg';
    
    div.innerHTML = `
        <img src="${imageUrl}" alt="${item.name}" class="cart-item-image">
        <div class="cart-item-info">
            <div class="cart-item-title">${item.name}</div>
            <div class="cart-item-price">${Utils.formatPrice(item.price)}</div>
            <div class="cart-item-quantity">
                <button class="quantity-btn" onclick="updateCartItemQuantity(${item.product_id}, ${item.quantity - 1})">-</button>
                <span>${item.quantity}</span>
                <button class="quantity-btn" onclick="updateCartItemQuantity(${item.product_id}, ${item.quantity + 1})">+</button>
                <button class="btn btn-danger" onclick="removeFromCart(${item.product_id})">Remove</button>
            </div>
        </div>
    `;
    
    return div;
}

async function updateCartItemQuantity(productId, quantity) {
    if (quantity < 1) return;
    
    try {
        const response = await fetch('api/cart.php?action=update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId, quantity })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadCartItems();
            updateCartCount();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        console.error('Error updating cart:', error);
    }
}

async function removeFromCart(productId) {
    try {
        const response = await fetch(`api/cart.php?action=remove&product_id=${productId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadCartItems();
            updateCartCount();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
    }
}

async function updateCartCount() {
    try {
        const response = await fetch('api/cart.php?action=count');
        const data = await response.json();
        
        if (data.success) {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.count;
                cartCountElement.style.display = data.count > 0 ? 'flex' : 'none';
            }
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

function updateCartTotal(total) {
    const cartTotalElement = document.getElementById('cart-total');
    if (cartTotalElement) {
        cartTotalElement.textContent = Utils.formatPrice(total);
    }
}

// UI Functions
function toggleCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    if (cartSidebar) {
        cartSidebar.classList.toggle('active');
        if (cartSidebar.classList.contains('active')) {
            loadCartItems();
        }
    }
}

function showLoginModal() {
    const modal = document.getElementById('login-modal');
    if (modal) {
        modal.classList.add('active');
    }
}

function showRegisterModal() {
    const modal = document.getElementById('register-modal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    alertContainer.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alert-container';
    container.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    `;
    document.body.appendChild(container);
    return container;
}

// Event Listeners
function setupEventListeners() {
    // Cart toggle
    const cartToggle = document.getElementById('cart-toggle');
    if (cartToggle) {
        cartToggle.addEventListener('click', toggleCart);
    }
    
    // Close cart when clicking outside
    document.addEventListener('click', function(event) {
        const cartSidebar = document.getElementById('cart-sidebar');
        const cartToggle = document.getElementById('cart-toggle');
        
        if (cartSidebar && !cartSidebar.contains(event.target) && !cartToggle.contains(event.target)) {
            cartSidebar.classList.remove('active');
        }
    });
    
    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    });
    
    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            
            if (email && password) {
                await login(email, password);
            }
        });
    }
    
    // Register form
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const userData = {
                username: document.getElementById('register-username').value,
                email: document.getElementById('register-email').value,
                password: document.getElementById('register-password').value,
                first_name: document.getElementById('register-firstname').value,
                last_name: document.getElementById('register-lastname').value,
                phone: document.getElementById('register-phone').value
            };
            
            if (userData.username && userData.email && userData.password) {
                await register(userData);
            }
        });
    }
    
    // Search form
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = document.getElementById('search-input').value;
            if (query) {
                searchProducts(query);
            }
        });
    }
}

// Utility Functions
const Utils = {
    formatPrice: function(price) {
        return '$' + parseFloat(price).toFixed(2);
    },
    
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    validateEmail: function(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },
    
    validatePassword: function(password) {
        return password.length >= 6;
    }
};

// Pagination
function setupPagination(totalItems, itemsPerPage, currentPage) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationContainer = document.getElementById('pagination');
    
    if (!paginationContainer || totalPages <= 1) return;
    
    paginationContainer.innerHTML = '';
    
    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Previous';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => loadAllProducts(null, currentPage - 1);
    paginationContainer.appendChild(prevBtn);
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = i === currentPage ? 'active' : '';
            pageBtn.onclick = () => loadAllProducts(null, i);
            paginationContainer.appendChild(pageBtn);
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            paginationContainer.appendChild(dots);
        }
    }
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => loadAllProducts(null, currentPage + 1);
    paginationContainer.appendChild(nextBtn);
}
