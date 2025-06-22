/**
 * Common JavaScript functions for CREAMS - Community-based REhAbilitation Management System
 * 
 * This file contains all the common functionality used across different pages
 * in the CREAMS application, ensuring consistent behavior and user experience.
 * 
 * Enhanced with route helpers to improve navigation and prevent 404 errors.
 */

// Role-based route map for permission checking
const routeMap = {
    'admin': {
        'users': true,
        'user.view': true,
        'user.edit': true,
        'centres': true,
        'centre.view': true,
        'assets': true,
        'asset.view': true,
        'activities': true,
        'activity.view': true,
        'reports': true,
        'settings': true,
        'rehabilitation': true
    },
    'supervisor': {
        'users': true,
        'user.view': true,
        'teacher.view': true,
        'centres': true,
        'centre.view': true,
        'assets': true,
        'asset.view': true,
        'activities': true,
        'activity.view': true,
        'reports': true,
        'settings': true,
        'rehabilitation': true
    },
    'teacher': {
        'users': true,
        'user.view': false,
        'centres': true,
        'centre.view': true,
        'assets': true,
        'asset.view': true,
        'activities': true,
        'activity.view': true,
        'classes': true,
        'class.view': true,
        'schedule': true,
        'reports': true,
        'settings': true,
        'rehabilitation': true
    },
    'ajk': {
        'users': true,
        'user.view': false,
        'centres': true,
        'centre.view': true,
        'assets': true,
        'asset.view': true,
        'activities': true,
        'activity.view': true,
        'events': true,
        'event.view': true,
        'volunteers': true,
        'reports': true,
        'settings': true,
        'rehabilitation': true
    }
};

// Utility function to check if a route exists
function routeExists(routeName, role = null) {
    // Get current role if not provided
    const currentRole = role || document.body.getAttribute('data-user-role') || 
                       sessionStorage.getItem('userRole') || 'admin';
    
    // Check if route exists for this role
    return routeMap[currentRole] && routeMap[currentRole][routeName] === true;
}

// Store the current user role in session storage
document.addEventListener('DOMContentLoaded', function() {
    const role = document.body.getAttribute('data-user-role') || 
               document.querySelector('meta[name="user-role"]')?.getAttribute('content');
               
    if (role) {
        sessionStorage.setItem('userRole', role);
    }

    // Initialize all interactive elements
    initializeSidebar();
    initializeDropdowns();
    initializeNotifications();
    initializeTooltips();
    initializeSearch();
    initializeRecentItems();
    fixAvatarImages();
    initializeAnimations();
    initializeRoleBasedNavigation();
});

/**
 * Initialize animations for UI elements
 */
function initializeAnimations() {
    // Animate stat values counting up
    const statValues = document.querySelectorAll('.stat-value');
    if (window.jQuery) {
        statValues.forEach(function(el) {
            const value = parseInt(el.textContent, 10);
            window.jQuery({ countNum: 0 }).animate({
                countNum: value
            }, {
                duration: 1000,
                easing: 'swing',
                step: function() {
                    el.textContent = Math.floor(this.countNum);
                },
                complete: function() {
                    el.textContent = value;
                }
            });
        });
    } else {
        // Fallback animation without jQuery
        statValues.forEach(function(el) {
            const finalValue = parseInt(el.textContent, 10);
            let currentValue = 0;
            const increment = Math.max(1, Math.floor(finalValue / 30)); // Aim for 30 steps
            const duration = 1000; // 1 second total
            const stepTime = duration / (finalValue / increment);
            
            const counter = setInterval(function() {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    currentValue = finalValue;
                    clearInterval(counter);
                }
                el.textContent = currentValue;
            }, stepTime);
        });
    }
    
    // Animate category cards appearance
    const categoryCards = document.querySelectorAll('.category-card, .rehab-category');
    categoryCards.forEach(function(card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(function() {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });
    
    // Animate dashboard sections
    const dashboardSections = document.querySelectorAll('.dashboard-section, .card-body > .row');
    dashboardSections.forEach(function(section, index) {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        
        setTimeout(function() {
            section.style.transition = 'all 0.5s ease';
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, 200 + (100 * index));
    });
}

// Handle page-specific initializations
document.addEventListener('DOMContentLoaded', function() {
    // Rehabilitation category page specific
    const rehabCategoryLinks = document.querySelectorAll('.category-footer a, .activity-link');
    rehabCategoryLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const linkText = this.textContent.trim();
            const linkUrl = this.getAttribute('href');
            let type = 'Activity';
            
            if (this.closest('.category-footer')) {
                const categoryTitle = this.closest('.category-card').querySelector('.category-title').textContent.trim();
                type = 'Rehabilitation Category: ' + categoryTitle;
            }
            
            // Track the item
            window.trackDetailedItem(linkText, linkUrl, type);
        });
    });
    
    // Trainee management page specific
    const traineeCards = document.querySelectorAll('.trainee-card a, .trainee-row');
    traineeCards.forEach(function(link) {
        link.addEventListener('click', function(e) {
            // Don't track clicks on action buttons within the row/card
            if (e.target.closest('.btn')) return;
            
            let traineeName = '';
            let traineeUrl = '';
            
            if (this.closest('.card')) {
                // Card view
                const traineeCard = this.closest('.card');
                traineeName = traineeCard.querySelector('.card-title').textContent.trim();
                traineeUrl = this.getAttribute('href');
            } else if (this.classList.contains('trainee-row')) {
                // Table row
                traineeName = this.querySelector('td:first-child').textContent.trim();
                traineeUrl = this.getAttribute('data-url') || '';
                
                // If no data-url is specified, prevent default and return
                if (!traineeUrl) {
                    return;
                }
                
                // Prevent default behavior to handle navigation manually
                e.preventDefault();
            }
            
            // Track the trainee
            window.trackDetailedItem(traineeName, traineeUrl, 'Trainee');
            
            // Navigate if needed
            if (this.classList.contains('trainee-row') && traineeUrl) {
                window.location.href = traineeUrl;
            }
        });
    });
    
    // Make sure all links in tables use the correct role-based URLs
    document.querySelectorAll('table a, .data-table a').forEach(link => {
        const href = link.getAttribute('href');
        
        // Skip non-href links
        if (!href) return;
        
        // Don't modify external links or special URLs
        if (href.startsWith('http') || href.startsWith('#') || 
            href.startsWith('javascript:') || href.startsWith('tel:') || 
            href.startsWith('mailto:')) {
            return;
        }
        
        // Fix problematic URLs
        const role = getCachedUserRole();
        const urlPatterns = [
            { pattern: /^\/users$/, replacement: `/${role}/users` },
            { pattern: /^\/users\/(\d+)$/, replacement: `/${role}/user/view/$1` },
            { pattern: /^\/centres$/, replacement: `/${role}/centres` },
            { pattern: /^\/assets$/, replacement: `/${role}/assets` }
        ];
        
        for (const pattern of urlPatterns) {
            if (pattern.pattern.test(href)) {
                const newHref = href.replace(pattern.pattern, pattern.replacement);
                link.setAttribute('href', newHref);
                break;
            }
        }
    });
});

/**
 * Route Helper Functions
 * These functions help ensure navigation works correctly with role-based routes
 */

/**
 * Check if a route exists for the current user's role
 * @param {string} routeName - The base route name without role prefix
 * @param {string|null} role - User role (optional, defaults to current role)
 * @returns {boolean} - True if route exists, false otherwise
 */
window.routeExists = function(routeName, role = null) {
    // Get current role from session if not provided
    const currentRole = role || getCachedUserRole();
    
    // Use route map if available (populated by server)
    if (typeof routeMap !== 'undefined' && routeMap[currentRole] && routeMap[currentRole][routeName] !== undefined) {
        return routeMap[currentRole][routeName];
    }
    
    // Default to true when we can't determine - the server will handle redirects
    return true;
};

/**
 * Generate a safe URL based on route pattern and role
 * @param {string} routeName - The base route name without role prefix
 * @param {object} params - Route parameters (e.g., {id: 123})
 * @param {string|null} role - User role (optional, defaults to current role)
 * @returns {string} - Complete URL
 */
window.generateRoleUrl = function(routeName, params = {}, role = null) {
    // Get current role from session if not provided
    const currentRole = role || getCachedUserRole();
    
    // Start building the URL
    let url = '/' + currentRole;
    
    // Add the route name
    if (routeName.startsWith('/')) {
        url += routeName;
    } else {
        url += '/' + routeName;
    }
    
    // Add params to the URL
    if (Object.keys(params).length > 0) {
        // For simple numeric ID
        if (params.id) {
            // Replace {id} in URL pattern or append /id
            if (url.includes('{id}')) {
                url = url.replace('{id}', params.id);
            } else if (!url.endsWith('/')) {
                url += '/' + params.id;
            } else {
                url += params.id;
            }
        }
        
        // Add other params as query string
        const queryParams = [];
        for (const key in params) {
            if (key !== 'id') {
                queryParams.push(`${key}=${encodeURIComponent(params[key])}`);
            }
        }
        
        if (queryParams.length > 0) {
            url += '?' + queryParams.join('&');
        }
    }
    
    return url;
};

/**
 * Get user role from various sources
 * @returns {string} - User role, defaults to 'admin' if not found
 */
function getCachedUserRole() {
    // Try from data attribute on body tag
    const bodyRole = document.body.getAttribute('data-user-role');
    if (bodyRole) return bodyRole;
    
    // Try from session storage
    const sessionRole = sessionStorage.getItem('userRole');
    if (sessionRole) return sessionRole;
    
    // Try from meta tag
    const metaRole = document.querySelector('meta[name="user-role"]');
    if (metaRole && metaRole.getAttribute('content')) {
        return metaRole.getAttribute('content');
    }
    
    // Default to admin if we can't determine
    return 'admin';
}

/**
 * Initialize role-based navigation
 * Ensures all navigation links use the correct routes based on user role
 */
function initializeRoleBasedNavigation() {
    // Store the current user role in session storage for convenience
    const role = getCachedUserRole();
    sessionStorage.setItem('userRole', role);
    
    // Fix navigation links by ensuring they use proper role-based URLs
    fixNavigationLinks();
    
    // Add global error handler for navigation links
    document.addEventListener('click', function(e) {
        // Check if the clicked element is a navigation link
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            const link = e.target.tagName === 'A' ? e.target : e.target.closest('a');
            const href = link.getAttribute('href');
            
            // Skip external links, anchor links, and javascript links
            if (!href || href.startsWith('http') || href.startsWith('#') || 
                href.startsWith('javascript:') || href.startsWith('tel:') || 
                href.startsWith('mailto:')) {
                return;
            }
            
            // Check if this might be a problematic link (contains /users without role)
            if (href === '/users' || href.startsWith('/users/')) {
                e.preventDefault();
                
                // Redirect to the role-specific users route
                const redirectUrl = generateRoleUrl('users');
                window.location.href = redirectUrl;
                return;
            }
        }
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
 * Applies default image when avatars fail to load
 */
function fixAvatarImages() {
    const avatarImages = document.querySelectorAll('.avatar-img, .profile-img, .rounded-circle[src*="profile"], .user-avatar img, .avatar-container img');
    
    avatarImages.forEach(function(img) {
        // Check if src is empty, null, or undefined
        if (!img.getAttribute('src') || img.getAttribute('src') === '') {
            applyDefaultImage(img);
        }
        
        // Add error handler for loading failures
        img.addEventListener('error', function() {
            applyDefaultImage(this);
        });
    });
    
    function applyDefaultImage(imgElement) {
        // Check if the default image exists
        const defaultImagePath = '/images/default-avatar.png';
        const fallbackImagePath = '/assets/images/default-avatar.png';
        
        // Try the primary path first
        imgElement.src = defaultImagePath;
        // Also set as background in case img element is styled with size but content doesn't load
        imgElement.style.backgroundImage = `url('${defaultImagePath}')`;
        imgElement.style.backgroundSize = "cover";
        imgElement.style.backgroundPosition = "center";
        
        // Add a second error handler in case the first default image also fails
        imgElement.addEventListener('error', function() {
            this.src = fallbackImagePath;
            this.style.backgroundImage = `url('${fallbackImagePath}')`;
            
            // If all else fails, use a data URI for a generic avatar
            this.addEventListener('error', function() {
                const genericAvatarDataURI = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OTYgNTEyIj48cGF0aCBmaWxsPSIjY2NjY2NjIiBkPSJNMjQ4IDhDMTExIDggMCAxMTkgMCAyNTZzMTExIDI0OCAyNDggMjQ4IDI0OC0xMTEgMjQ4LTI0OFMzODUgOCAyNDggOHptMCA5NmM0OC42IDAgODggMzkuNCA4OCA4OHMtMzkuNCA4OC04OCA4OC04OC0zOS40LTg4LTg4IDM5LjQtODggODgtODh6bTAgMzQ0Yy01OC43IDAtMTExLjMtMjYuNi0xNDYuNS02OC4yIDE4LjgtMzUuNCAtMi41LTE2LjQgMzUuNC04Mi44IDUuMi05IDI4LjItMTMuNSAzOS41LTEzLjUgMTQ1LjIgMCAwIDI1LjggMzkuNSAyNS44IDI2LjkgMCAzOC42IDEyIDM5LjUgMjkuNSAyOC41IDY3LjEgOS4xIDEwNy4zIDMzLjQgNjUuOSAxNDYuNS02OC4yIDk2LjEtMTEzLjYgNDYuNC0xMTMuNiAxNDYuNS02OC4yek0yNDggNDA4Yy00NC4xIDAtODAtMzUuOS04MC04MGg2MGMwIDExIDkgMjAgMjAgMjBzMjAtOSAyMC0yMGg2MGMwIDQ0LjEtMzUuOSA4MC04MCA4MHoiPjwvcGF0aD48L3N2Zz4=';
                this.src = genericAvatarDataURI;
                this.style.backgroundImage = `url('${genericAvatarDataURI}')`;
            });
        });
    }
}

/**
 * Fix navigation links in the page to ensure they use correct role-based URLs
 */
function fixNavigationLinks() {
    const role = getCachedUserRole();
    
    // Common problematic routes that need fixing
    const routePatterns = [
        { pattern: /^\/users$/, replacement: `/${role}/users` },
        { pattern: /^\/users\/(\d+)$/, replacement: `/${role}/user/view/$1` },
        { pattern: /^\/centres$/, replacement: `/${role}/centres` },
        { pattern: /^\/assets$/, replacement: `/${role}/assets` }
    ];
    
    // Find all links in the page
    const links = document.querySelectorAll('a[href]');
    
    links.forEach(link => {
        const href = link.getAttribute('href');
        
        // Skip links that are not internal page navigation
        if (!href || href.startsWith('http') || href.startsWith('#') || 
            href.startsWith('javascript:') || href.startsWith('tel:') || 
            href.startsWith('mailto:')) {
            return;
        }
        
        // Check if this link matches any of our problematic patterns
        for (const route of routePatterns) {
            if (route.pattern.test(href)) {
                // Update the href to use the role-specific URL
                const newHref = href.replace(route.pattern, route.replacement);
                link.setAttribute('href', newHref);
                
                // Also add a data attribute to mark that this link was fixed
                link.setAttribute('data-fixed-route', 'true');
                break;
            }
        }
    });
}

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
    const submenuLinks = document.querySelectorAll('.sidebar-link');
    submenuLinks.forEach(link => {
        if (link.nextElementSibling && link.nextElementSibling.classList.contains('sidebar-submenu')) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.parentElement;
                
                // Toggle submenu-open class
                parent.classList.toggle('submenu-open');
                
                // Close other submenus
                const siblings = Array.from(parent.parentElement.children).filter(el => el !== parent);
                siblings.forEach(sibling => {
                    sibling.classList.remove('submenu-open');
                });
                
                // Store state of open submenus
                storeOpenSubmenus();
            });
        }
    });
    
    // Restore open submenus
    restoreOpenSubmenus();
    
    // Auto-expand submenu with active item
    const activeLink = document.querySelector('.sidebar-submenu-link.active');
    if (activeLink) {
        const submenu = activeLink.closest('.sidebar-submenu');
        if (submenu) {
            const parent = submenu.parentElement;
            if (parent) {
                parent.classList.add('submenu-open');
            }
        }
    }
    
    // Handle responsive sidebar
    function handleResize() {
        if (window.innerWidth <= 991) {
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
            if (notificationMenu) {
                notificationMenu.classList.remove('show');
            }
            
            // Close mobile search if open
            const mobileSearch = document.querySelector('.mobile-search');
            if (mobileSearch) {
                mobileSearch.classList.remove('show');
            }
        });
    }
    
    // Mobile search toggle
    const searchMobileToggle = document.querySelector('.search-mobile-toggle');
    const mobileSearch = document.querySelector('.mobile-search');
    
    if (searchMobileToggle && mobileSearch) {
        searchMobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            mobileSearch.classList.toggle('show');
            
            // Close other dropdowns
            if (userDropdown) userDropdown.classList.remove('show');
            
            const notificationMenu = document.getElementById('notificationMenu');
            if (notificationMenu) notificationMenu.classList.remove('show');
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (userDropdown) userDropdown.classList.remove('show');
        
        const notificationMenu = document.getElementById('notificationMenu');
        if (notificationMenu) notificationMenu.classList.remove('show');
        
        if (mobileSearch) mobileSearch.classList.remove('show');
    });
    
    // Prevent dropdown close when clicking inside
    const dropdownElements = [userDropdown, document.getElementById('notificationMenu'), mobileSearch];
    dropdownElements.forEach(el => {
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
            
            // Close mobile search if open
            const mobileSearch = document.querySelector('.mobile-search');
            if (mobileSearch) mobileSearch.classList.remove('show');
            
            // If notification menu is showing, load notifications
            if (notificationMenu.classList.contains('show')) {
                loadNotifications();
            }
        });
    }
}

/**
 * Load notifications from the server
 */
function loadNotifications() {
    const notificationMenu = document.getElementById('notificationMenu');
    if (!notificationMenu) return;
    
    // Add a loading state
    notificationMenu.innerHTML = `
        <div class="p-3 text-center">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mb-0 mt-2">Loading notifications...</p>
        </div>
    `;
    
    // In a real implementation, this would be an AJAX call to fetch notifications
    // For demonstration, using mock data and a small delay to simulate network request
    setTimeout(() => {
        // Get notification data - this would normally be a fetch request
        const mockNotifications = [
            {
                id: 1,
                title: 'New Trainee Registered',
                content: 'A new trainee has been registered in the system.',
                icon: 'fas fa-user-plus',
                color: 'primary',
                time: '5 minutes ago',
                url: '#notification-1'
            },
            {
                id: 2,
                title: 'Activity Scheduled',
                content: 'A new activity has been scheduled for tomorrow.',
                icon: 'fas fa-calendar-alt',
                color: 'success',
                time: '1 hour ago',
                url: '#notification-2'
            },
            {
                id: 3,
                title: 'System Update',
                content: 'The system will be undergoing maintenance tonight.',
                icon: 'fas fa-cog',
                color: 'warning',
                time: '3 hours ago',
                url: '#notification-3'
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
                    <a href="${notification.url}" class="d-flex p-3 border-bottom notification-item">
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
        
        // Get the correct notifications route based on user role
        const role = getCachedUserRole();
        const viewAllUrl = window.generateRoleUrl('notifications');
        
        notificationsHtml += `
            <div class="p-2 text-center border-top">
                <a href="${viewAllUrl}" class="btn btn-sm btn-light w-100">
                    View All Notifications
                </a>
            </div>
        `;
        
        notificationMenu.innerHTML = notificationsHtml;
        
        // Add animation to notification items
        const notificationItems = document.querySelectorAll('.notification-item');
        notificationItems.forEach((item, index) => {
            item.style.opacity = 0;
            item.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = 1;
                item.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Set up mark all read button
        const markAllReadBtn = document.getElementById('mark-all-read');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationCount = document.querySelector('.notification-count');
                if (notificationCount) {
                    notificationCount.style.display = 'none';
                    
                    // Add a subtle animation to show the action was registered
                    notificationCount.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                    notificationCount.style.transform = 'scale(0.5)';
                    notificationCount.style.opacity = '0';
                }
                
                notificationMenu.classList.remove('show');
                
                // In a real implementation, make an AJAX call to mark all as read
                // Example:
                // fetch('/notifications/mark-all-read', {
                //     method: 'POST',
                //     headers: {
                //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                //         'Content-Type': 'application/json'
                //     }
                // }).then(response => response.json())
                //   .then(data => console.log('All notifications marked as read'))
                //   .catch(error => console.error('Error marking notifications as read:', error));
            });
        }
    });
}