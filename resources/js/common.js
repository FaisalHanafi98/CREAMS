/**
 * Common JavaScript functions for CREAMS - Community-based REhAbilitation Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all interactive elements
    initializeSidebar();
    initializeDropdowns();
    initializeNotifications();
    initializeTooltips();
    initializeSearch();
    initializeRecentItems();
    fixAvatarImages();
});

/**
 * Initialize sidebar toggle functionality
 */
function initializeSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (!sidebarToggle) return;

    // Check if sidebar was collapsed previously
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
    
    // Toggle sidebar on click
    sidebarToggle.addEventListener('click', function() {
        document.body.classList.toggle('sidebar-collapsed');
        
        // Store sidebar state in localStorage
        localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
    });
    
    // Handle submenu toggles
    const submenuToggles = document.querySelectorAll('[data-toggle="submenu"]');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('data-target');
            const target = document.querySelector(targetId);
            const arrow = this.querySelector('.sidebar-arrow');
            
            if (!target || !arrow) return;
            
            // Close other submenus
            document.querySelectorAll('.sidebar-submenu').forEach(menu => {
                if (menu !== target) menu.classList.remove('show');
            });
            
            document.querySelectorAll('.sidebar-arrow').forEach(a => {
                if (a !== arrow) a.classList.remove('rotate');
            });
            
            // Toggle this submenu
            target.classList.toggle('show');
            arrow.classList.toggle('rotate');
            
            // Store open submenus in localStorage
            storeOpenSubmenus();
        });
    });
    
    // Restore open submenus
    restoreOpenSubmenus();
    
    // Auto-expand submenu with active item
    const activeLink = document.querySelector('.submenu-link.active');
    if (activeLink) {
        const submenu = activeLink.closest('.sidebar-submenu');
        if (submenu) {
            submenu.classList.add('show');
            const arrow = submenu.previousElementSibling.querySelector('.sidebar-arrow');
            if (arrow) arrow.classList.add('rotate');
        }
    }
    
    // Toggle between mobile and desktop view
    function handleResize() {
        if (window.innerWidth <= 767) {
            document.body.classList.add('sidebar-collapsed');
        }
    }
    
    // Call on load and on resize
    handleResize();
    window.addEventListener('resize', handleResize);
}

/**
 * Initialize dropdown menus
 */
function initializeDropdowns() {
    // User profile dropdown
    const userProfileToggle = document.getElementById('userProfileToggle');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userProfileToggle && userDropdown) {
        userProfileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
            
            // Close notification menu if open
            const notificationMenu = document.getElementById('notificationMenu');
            if (notificationMenu) notificationMenu.classList.remove('show');
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (userDropdown) userDropdown.classList.remove('show');
        
        const notificationMenu = document.getElementById('notificationMenu');
        if (notificationMenu) notificationMenu.classList.remove('show');
        
        const mobileSearch = document.querySelector('.mobile-search');
        if (mobileSearch) mobileSearch.classList.remove('show');
    });
    
    // Prevent dropdown close when clicking inside
    document.querySelectorAll('#userDropdown, #notificationMenu, .mobile-search').forEach(el => {
        if (el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
}

/**
 * Initialize notifications system
 */
function initializeNotifications() {
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationMenu = document.getElementById('notificationMenu');
    
    if (notificationToggle && notificationMenu) {
        notificationToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationMenu.classList.toggle('show');
            
            // Close user dropdown if open
            const userDropdown = document.getElementById('userDropdown');
            if (userDropdown) userDropdown.classList.remove('show');
            
            // If notification menu is showing, load notifications
            if (notificationMenu.classList.contains('show')) {
                loadNotifications();
            }
        });
    }
    
    // Mobile search toggle
    const searchMobileToggle = document.querySelector('.search-mobile-toggle');
    const mobileSearch = document.querySelector('.mobile-search');
    
    if (searchMobileToggle && mobileSearch) {
        searchMobileToggle.addEventListener('click', function() {
            mobileSearch.classList.toggle('show');
        });
    }
}

/**
 * Load notifications from the server
 */
function loadNotifications() {
    const notificationMenu = document.getElementById('notificationMenu');
    if (!notificationMenu) return;
    
    // Mock notification data for now
    // This would normally be loaded from an AJAX call to the server
    const mockNotifications = [
        {
            id: 1,
            title: 'New Trainee Registered',
            content: 'A new trainee has been registered in the system.',
            icon: 'fas fa-user-plus',
            color: 'primary',
            time: '5 minutes ago',
            url: '#'
        },
        {
            id: 2,
            title: 'Activity Scheduled',
            content: 'A new activity has been scheduled for tomorrow.',
            icon: 'fas fa-calendar-alt',
            color: 'success',
            time: '1 hour ago',
            url: '#'
        },
        {
            id: 3,
            title: 'System Update',
            content: 'The system will be undergoing maintenance tonight.',
            icon: 'fas fa-cog',
            color: 'warning',
            time: '3 hours ago',
            url: '#'
        }
    ];
    
    // Build notification HTML
    let notificationsHtml = `
        <div class="p-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0">Notifications</h6>
                <a href="#" class="text-muted small" id="mark-all-read">
                    Mark all as read
                </a>
            </div>
        </div>
    `;
    
    if (mockNotifications.length > 0) {
        mockNotifications.forEach(notification => {
            notificationsHtml += `
                <a href="${notification.url}" class="d-flex p-3 border-bottom">
                    <div class="mr-3">
                        <div class="notification-icon ${notification.color}">
                            <i class="${notification.icon}"></i>
                        </div>
                    </div>
                    <div>
                        <div class="font-weight-bold">${notification.title}</div>
                        <div class="small text-muted">${notification.content}</div>
                        <div class="smallest text-muted mt-1">${notification.time}</div>
                    </div>
                </a>
            `;
        });
    } else {
        notificationsHtml += `
            <div class="p-3 text-center text-muted">
                <i class="fas fa-bell-slash fa-2x mb-3"></i>
                <p>No new notifications</p>
            </div>
        `;
    }
    
    notificationsHtml += `
        <div class="p-2 text-center border-top">
            <a href="#" class="btn btn-sm btn-light w-100">
                View All Notifications
            </a>
        </div>
    `;
    
    notificationMenu.innerHTML = notificationsHtml;
    
    // Set up mark all read button
    const markAllReadBtn = document.getElementById('mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationCount = document.querySelector('.notification-count');
            if (notificationCount) notificationCount.style.display = 'none';
            
            notificationMenu.classList.remove('show');
        });
    }
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        // This would typically use a tooltip library, but for simplicity we'll use title attribute
        if (tooltip.getAttribute('title')) {
            tooltip.setAttribute('data-original-title', tooltip.getAttribute('title'));
        }
    });
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const globalSearch = document.getElementById('globalSearch');
    if (!globalSearch) return;
    
    globalSearch.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            // This would normally submit a search form
            console.log('Searching for:', this.value);
        }
    });
}

/**
 * Function to store open submenus
 */
function storeOpenSubmenus() {
    const openSubmenus = [];
    document.querySelectorAll('.sidebar-submenu.show').forEach(submenu => {
        openSubmenus.push(submenu.id);
    });
    localStorage.setItem('open-submenus', JSON.stringify(openSubmenus));
}

/**
 * Function to restore open submenus
 */
function restoreOpenSubmenus() {
    try {
        const openSubmenus = JSON.parse(localStorage.getItem('open-submenus')) || [];
        openSubmenus.forEach(submenuId => {
            const submenu = document.getElementById(submenuId);
            if (submenu) {
                submenu.classList.add('show');
                const toggle = document.querySelector(`[data-target="#${submenuId}"]`);
                if (toggle) {
                    const arrow = toggle.querySelector('.sidebar-arrow');
                    if (arrow) arrow.classList.add('rotate');
                }
            }
        });
    } catch (e) {
        console.error('Error restoring open submenus:', e);
    }
}

/**
 * Function to track recently accessed items
 */
function initializeRecentItems() {
    // Track the current page visit
    trackRecentAccess();
    
    // Load recent items if the container exists
    const recentItemsContainer = document.querySelector('.recent-items');
    if (recentItemsContainer) {
        loadRecentItems();
    }
    
    // Set up clear history button
    const clearHistoryBtn = document.getElementById('clear-history');
    if (clearHistoryBtn) {
        clearHistoryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            localStorage.removeItem('recentItems');
            
            const container = document.querySelector('.recent-items');
            if (container) {
                container.innerHTML = `
                    <li class="recent-item text-center py-4">
                        <span class="text-muted"><i class="fas fa-sync fa-spin"></i> Clearing history...</span>
                    </li>
                `;
                
                setTimeout(() => {
                    loadRecentItems();
                }, 800);
            }
        });
    }
}

/**
 * Track the current page visit in recently accessed items
 */
function trackRecentAccess() {
    // Get current page information
    const pageTitle = document.title;
    const pageUrl = window.location.href;
    const pageType = 'Page'; // Default type
    
    // Create recent item object
    const recentItem = {
        name: pageTitle,
        url: pageUrl,
        type: pageType,
        timestamp: new Date().toISOString()
    };
    
    // Get existing recent items from localStorage
    let recentItems = [];
    try {
        recentItems = JSON.parse(localStorage.getItem('recentItems')) || [];
    } catch (e) {
        console.error('Error parsing recent items:', e);
    }
    
    // Check if this item already exists
    const existingItemIndex = recentItems.findIndex(item => item.url === pageUrl);
    
    if (existingItemIndex >= 0) {
        // Remove existing item
        recentItems.splice(existingItemIndex, 1);
    }
    
    // Add new item at the beginning
    recentItems.unshift(recentItem);
    
    // Limit to 10 items
    recentItems = recentItems.slice(0, 10);
    
    // Save back to localStorage
    localStorage.setItem('recentItems', JSON.stringify(recentItems));
}

/**
 * Function to load recently accessed items from localStorage
 */
function loadRecentItems() {
    // Get recent items from localStorage
    let recentItems = [];
    try {
        recentItems = JSON.parse(localStorage.getItem('recentItems')) || [];
    } catch (e) {
        console.error('Error parsing recent items:', e);
    }
    
    // Get container
    const recentItemsContainer = document.querySelector('.recent-items');
    if (!recentItemsContainer) return;
    
    // Clear container
    recentItemsContainer.innerHTML = '';
    
    if (recentItems.length === 0) {
        recentItemsContainer.innerHTML = `
            <li class="recent-item text-center py-4">
                <span class="text-muted">No recent items</span>
            </li>
        `;
        return;
    }
    
    // Populate container with animation
    recentItems.forEach((item, index) => {
        // Determine icon based on URL pattern
        let icon = 'file';
        
        if (item.url.includes('trainee')) {
            icon = 'user-graduate';
        } else if (item.url.includes('centre')) {
            icon = 'building';
        } else if (item.url.includes('asset')) {
            icon = 'boxes';
        } else if (item.url.includes('staff') || item.url.includes('user')) {
            icon = 'users';
        } else if (item.url.includes('activit')) {
            icon = 'calendar-alt';
        } else if (item.url.includes('report')) {
            icon = 'chart-bar';
        } else if (item.url.includes('dashboard')) {
            icon = 'home';
        } else if (item.url.includes('rehabilitation')) {
            icon = 'heart';
        }
        
        // Format relative time
        const timestamp = new Date(item.timestamp);
        const now = new Date();
        const diffMs = now - timestamp;
        
        let timeDisplay = '';
        
        if (diffMs < 60000) { // Less than a minute
            timeDisplay = 'Just now';
        } else if (diffMs < 3600000) { // Less than an hour
            const mins = Math.floor(diffMs / 60000);
            timeDisplay = `${mins}m ago`;
        } else if (diffMs < 86400000) { // Less than a day
            const hours = Math.floor(diffMs / 3600000);
            timeDisplay = `${hours}h ago`;
        } else if (diffMs < 604800000) { // Less than a week
            const days = Math.floor(diffMs / 86400000);
            timeDisplay = `${days}d ago`;
        } else {
            timeDisplay = timestamp.toLocaleDateString();
        }
        
        const li = document.createElement('li');
        li.className = 'recent-item';
        li.style.opacity = '0';
        li.style.transform = 'translateY(20px)';
        li.style.transition = `all 0.3s ease ${index * 0.1}s`;
        
        li.innerHTML = `
            <a href="${item.url}" class="recent-link">
                <div class="recent-icon">
                    <i class="fas fa-${icon}"></i>
                </div>
                <div class="recent-content">
                    <div class="recent-name">${item.name}</div>
                    <div class="recent-meta">${item.type}</div>
                </div>
                <div class="recent-time">${timeDisplay}</div>
            </a>
        `;
        
        recentItemsContainer.appendChild(li);
        
        // Trigger animation
        setTimeout(() => {
            li.style.opacity = '1';
            li.style.transform = 'translateY(0)';
        }, 50);
    });
}

/**
 * Function to track detailed items (trainees, centres, assets)
 * This can be called from anywhere in the application
 */
window.trackDetailedItem = function(name, url, type) {
    // Create item object
    const detailedItem = {
        name: name,
        url: url,
        type: type,
        timestamp: new Date().toISOString()
    };
    
    // Get existing items from localStorage
    let recentItems = [];
    try {
        recentItems = JSON.parse(localStorage.getItem('recentItems')) || [];
    } catch (e) {
        console.error('Error parsing recent items:', e);
    }
    
    // Check if this item already exists
    const existingItemIndex = recentItems.findIndex(item => item.url === url);
    
    if (existingItemIndex >= 0) {
        // Remove existing item
        recentItems.splice(existingItemIndex, 1);
    }
    
    // Add new item at the beginning
    recentItems.unshift(detailedItem);
    
    // Limit to 10 items
    recentItems = recentItems.slice(0, 10);
    
    // Save back to localStorage
    localStorage.setItem('recentItems', JSON.stringify(recentItems));
};

/**
 * Fix for avatar images not loading properly
 */
function fixAvatarImages() {
    const avatarImages = document.querySelectorAll('.avatar-img, .profile-img, .rounded-circle[src*="profile"], .user-avatar img, .avatar-container img');
    
    avatarImages.forEach(function(img) {
        // Check if src is empty, null, or undefined
        if (!img.getAttribute('src') || img.getAttribute('src') === '') {
            console.log('Avatar with empty src found, applying default image');
            applyDefaultImage(img);
        }
        
        // Add error handler for loading failures
        img.addEventListener('error', function(e) {
            console.log('Avatar image failed to load, applying default image');
            applyDefaultImage(this);
        });
    });
    
    function applyDefaultImage(imgElement) {
        imgElement.src = '/images/default-avatar.png';
        // Also set as background in case img element is styled with size but content doesn't load
        imgElement.style.backgroundImage = "url('/images/default-avatar.png')";
        imgElement.style.backgroundSize = "cover";
        imgElement.style.backgroundPosition = "center";
    }
    
    // Run immediately and also after a short delay to catch dynamically loaded elements
    setTimeout(fixAvatarImages, 500);
    setTimeout(fixAvatarImages, 1500);
}