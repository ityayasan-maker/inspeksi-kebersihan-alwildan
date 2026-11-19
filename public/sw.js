const CACHE_NAME = 'inspeksi-cache-v3';
const PRECACHE_ASSETS = [
  './manifest.json',
  './icons/icon-192x192.png',
  './icons/icon-512x512.png',
  './icons/icon-maskable-192x192.png',
  './icons/icon-maskable-512x512.png',
  './css/app.css',
  './css/sidebar.css',
  './css/upload.css',
  './js/app.js',
  './js/upload.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
  'https://cdn.jsdelivr.net/npm/sweetalert2@11'
];

// Install: precache essential shell assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(PRECACHE_ASSETS).catch(err => {
        console.warn('[SW] Pre-caching warning:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

// Activate: clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch: Network-first for dynamic requests/POST, cache-first for static assets
self.addEventListener('fetch', event => {
  const req = event.request;

  // Don't intercept non-GET requests (e.g. POST form submission)
  if (req.method !== 'GET') {
    return;
  }

  const url = new URL(req.url);

  // Cache-first for images, fonts, styles, scripts
  if (
    url.pathname.match(/\.(css|js|png|jpg|jpeg|svg|webp|woff2?|ttf|ico)$/i) ||
    url.hostname.includes('jsdelivr.net') ||
    url.hostname.includes('cdn.')
  ) {
    event.respondWith(
      caches.match(req).then(cached => {
        if (cached) return cached;
        return fetch(req).then(networkResp => {
          if (networkResp && networkResp.status === 200) {
            const respClone = networkResp.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(req, respClone));
          }
          return networkResp;
        }).catch(() => caches.match(req));
      })
    );
    return;
  }

  // Network-first for navigation and HTML pages (always fresh to avoid stale CSRF)
  event.respondWith(
    fetch(req).catch(() => caches.match(req))
  );
});
