// PTPN 1 WMS Service Worker — v2
// Strategi: Network-first untuk halaman, Stale-while-revalidate untuk assets statis

const CACHE_NAME = 'ptpn1-wms-v2';

// Asset statis yang di-cache saat install
const PRECACHE_ASSETS = [
    '/',
    '/manifest.json',
    '/images/logo-ptpn.png',
];

// Halaman fallback offline
const OFFLINE_PAGE = '/';

// ── Install: pre-cache aset penting ──────────────────────────────────────
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch(() => {
                // Lanjutkan meski sebagian gagal (misal logo belum ada)
            });
        })
    );
});

// ── Activate: hapus cache lama ────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

// ── Fetch: routing strategy ───────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Abaikan request non-GET dan request ke API OCR / storage
    if (request.method !== 'GET') return;
    if (url.pathname.startsWith('/api/')) return;
    if (url.pathname.startsWith('/storage/')) return;

    // Aset statis (CSS, JS, fonts, images) → Stale-while-revalidate
    if (isStaticAsset(url)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // Halaman HTML → Network-first dengan fallback ke cache
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirstWithFallback(request));
        return;
    }

    // Default: network-only (untuk POST/OCR/upload dll)
    event.respondWith(fetch(request).catch(() => caches.match(request)));
});

// ── Helper Functions ──────────────────────────────────────────────────────

function isStaticAsset(url) {
    return (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/images/') ||
        /\.(css|js|woff2?|ttf|otf|ico|png|jpg|jpeg|svg|webp)$/i.test(url.pathname)
    );
}

/**
 * Stale-while-revalidate: tampilkan dari cache, perbarui di background
 */
async function staleWhileRevalidate(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request)
        .then((response) => {
            if (response && response.status === 200) {
                cache.put(request, response.clone());
            }
            return response;
        })
        .catch(() => cached);

    return cached || fetchPromise;
}

/**
 * Network-first: coba jaringan, jika gagal gunakan cache atau halaman offline
 */
async function networkFirstWithFallback(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;

        // Fallback: kembalikan halaman utama yang ter-cache
        const fallback = await caches.match(OFFLINE_PAGE);
        return fallback || new Response(
            '<html><body style="font-family:sans-serif;text-align:center;padding:40px;">' +
            '<h2>⚠️ Tidak Ada Koneksi</h2>' +
            '<p>Periksa koneksi internet Anda dan coba lagi.</p>' +
            '<button onclick="location.reload()" style="padding:10px 20px;background:#34A853;color:white;border:none;border-radius:8px;cursor:pointer;font-size:14px;">Coba Lagi</button>' +
            '</body></html>',
            { headers: { 'Content-Type': 'text/html' } }
        );
    }
}
