// CREAMS Dashboard Service Worker
// Version 1.0.0 - Mobile-Optimized Dashboard

const CACHE_NAME = 'creams-dashboard-v1';
const CACHE_URLS = [
    '/',
    '/dashboard',
    '/dashboard/mobile',
    '/manifest.json',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css',
    'https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap'
];

// Install Service Worker
self.addEventListener('install', event => {
    console.log('CREAMS SW: Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('CREAMS SW: Caching core assets');
                return cache.addAll(CACHE_URLS);
            })
            .then(() => {
                console.log('CREAMS SW: Installation complete');
                return self.skipWaiting();
            })
    );
});

// Activate Service Worker
self.addEventListener('activate', event => {
    console.log('CREAMS SW: Activating...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('CREAMS SW: Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('CREAMS SW: Activation complete');
                return self.clients.claim();
            })
    );
});

// Fetch Strategy: Cache First for assets, Network First for API calls
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    
    // Handle API calls (Network First)
    if (url.pathname.includes('/dashboard/updates') || url.pathname.includes('/api/')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // Cache successful responses
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME)
                            .then(cache => {
                                cache.put(event.request, responseClone);
                            });
                    }
                    return response;
                })
                .catch(() => {
                    // Return cached version if network fails
                    return caches.match(event.request)
                        .then(cachedResponse => {
                            if (cachedResponse) {
                                return cachedResponse;
                            }
                            // Return offline fallback for API calls
                            return new Response(
                                JSON.stringify({
                                    success: false,
                                    error: 'Network unavailable',
                                    offline: true,
                                    timestamp: Date.now()
                                }),
                                {
                                    headers: { 'Content-Type': 'application/json' }
                                }
                            );
                        });
                })
        );
    }
    // Handle static assets (Cache First)
    else if (url.pathname.includes('.css') || url.pathname.includes('.js') || url.pathname.includes('.woff')) {
        event.respondWith(
            caches.match(event.request)
                .then(cachedResponse => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    return fetch(event.request)
                        .then(response => {
                            const responseClone = response.clone();
                            caches.open(CACHE_NAME)
                                .then(cache => {
                                    cache.put(event.request, responseClone);
                                });
                            return response;
                        });
                })
        );
    }
    // Handle dashboard pages (Network First with Cache Fallback)
    else if (url.pathname.includes('/dashboard')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME)
                        .then(cache => {
                            cache.put(event.request, responseClone);
                        });
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request)
                        .then(cachedResponse => {
                            return cachedResponse || caches.match('/dashboard');
                        });
                })
        );
    }
    // Default: Network First
    else {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return caches.match(event.request);
                })
        );
    }
});

// Background Sync for offline actions
self.addEventListener('sync', event => {
    console.log('CREAMS SW: Background sync triggered:', event.tag);
    
    if (event.tag === 'dashboard-sync') {
        event.waitUntil(syncDashboardData());
    }
});

// Push Notifications
self.addEventListener('push', event => {
    console.log('CREAMS SW: Push notification received');
    
    const options = {
        body: event.data ? event.data.text() : 'New CREAMS notification',
        icon: '/android-chrome-192x192.png',
        badge: '/android-chrome-96x96.png',
        vibrate: [200, 100, 200],
        data: {
            url: '/dashboard'
        },
        actions: [
            {
                action: 'view',
                title: 'View Dashboard',
                icon: '/icons/view-action.png'
            },
            {
                action: 'dismiss',
                title: 'Dismiss',
                icon: '/icons/dismiss-action.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('CREAMS Dashboard', options)
    );
});

// Notification Click Handler
self.addEventListener('notificationclick', event => {
    console.log('CREAMS SW: Notification clicked');
    
    event.notification.close();
    
    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow('/dashboard')
        );
    }
});

// Helper Functions
async function syncDashboardData() {
    try {
        console.log('CREAMS SW: Syncing dashboard data...');
        
        // Get stored offline actions
        const cache = await caches.open(CACHE_NAME);
        const offlineActions = await getOfflineActions();
        
        // Process offline actions when online
        for (const action of offlineActions) {
            try {
                await fetch(action.url, {
                    method: action.method,
                    headers: action.headers,
                    body: action.body
                });
                console.log('CREAMS SW: Synced action:', action.type);
            } catch (error) {
                console.error('CREAMS SW: Failed to sync action:', error);
            }
        }
        
        // Clear processed actions
        await clearOfflineActions();
        
        console.log('CREAMS SW: Dashboard sync complete');
        
    } catch (error) {
        console.error('CREAMS SW: Dashboard sync failed:', error);
    }
}

async function getOfflineActions() {
    // Implementation to retrieve offline actions from IndexedDB
    return [];
}

async function clearOfflineActions() {
    // Implementation to clear processed offline actions
    console.log('CREAMS SW: Offline actions cleared');
}

// Message Handler for communication with main thread
self.addEventListener('message', event => {
    console.log('CREAMS SW: Message received:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }
});

console.log('CREAMS Service Worker loaded successfully');