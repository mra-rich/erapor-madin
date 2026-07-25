const CACHE = 'erapor-v3';
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
  '/css/style.css',
  '/assets/js/main.js',
  '/assets/js/offline-queue.js',
  '/assets/img/logo.png',
  OFFLINE_URL,
];

const CACHE_PAGES = [
  '/dashboard',
  '/data_santri',
  '/data_nilai',
  '/evaluasi_wali',
  '/penilaian_mapel',
];

const SYNC_TAG = 'erapor-form-sync';

// ── Install: cache static assets + key pages ──────────────────
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE)
      .then(c => Promise.allSettled(
        [...STATIC_ASSETS, ...CACHE_PAGES].map(u => c.add(u))
      ))
      .then(() => self.skipWaiting())
  );
});

// ── Activate: remove old caches ───────────────────────────────
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// ── Fetch ─────────────────────────────────────────────────────
self.addEventListener('fetch', e => {
  const { request } = e;
  const url = new URL(request.url);

  // POST ke endpoint form → queue jika offline
  if (request.method === 'POST') {
    const isFormEndpoint =
      url.pathname.includes('proses_nilai_massal') ||
      url.pathname.includes('proses_evaluasi_wali');
    if (isFormEndpoint) {
      e.respondWith(handleFormPost(request));
    }
    return; // POST lain: biarkan browser handle
  }

  if (request.method !== 'GET') return;

  // Cross-origin (CDN font/icon): network-first, cache fallback
  if (url.origin !== self.location.origin) {
    e.respondWith(
      fetch(request).then(res => {
        const clone = res.clone();
        caches.open(CACHE).then(c => c.put(request, clone));
        return res;
      }).catch(() => caches.match(request))
    );
    return;
  }

  // Same-origin GET: network-first, cache fallback, offline page
  e.respondWith(
    fetch(request).then(res => {
      if (res.ok) {
        const clone = res.clone();
        caches.open(CACHE).then(c => c.put(request, clone));
      }
      return res;
    }).catch(async () => {
      const cached = await caches.match(request);
      if (cached) return cached;
      if (request.mode === 'navigate') return caches.match(OFFLINE_URL);
      return new Response('Offline', { status: 503 });
    })
  );
});
// ── Handle POST form offline ───────────────────────────────────
async function handleFormPost(request) {
  try {
    return await fetch(request.clone());
  } catch {
    // Offline: simpan payload ke IndexedDB queue
    const formData = await request.clone().formData();
    const payload = {};
    for (const [k, v] of formData.entries()) payload[k] = v;

    await saveToQueue({ url: request.url, payload, timestamp: Date.now() });

    // Register background sync (Chrome/Android). iOS: manual sync via app.
    if (self.registration.sync) {
      try { await self.registration.sync.register(SYNC_TAG); } catch {}
    }

    // Beri tahu tab agar update badge "pending"
    const clients = await self.clients.matchAll();
    clients.forEach(c => c.postMessage({ type: 'QUEUED' }));

    return new Response(JSON.stringify({ status: 'queued', offline: true }), {
      headers: { 'Content-Type': 'application/json' },
    });
  }
}

// ── Background Sync ───────────────────────────────────────────
self.addEventListener('sync', e => {
  if (e.tag === SYNC_TAG) e.waitUntil(replayQueue());
});

// Manual sync trigger dari halaman (untuk iOS / tombol sync)
self.addEventListener('message', e => {
  if (e.data && e.data.type === 'REPLAY_QUEUE') {
    e.waitUntil(replayQueue());
  }
});

// ── IndexedDB helpers ─────────────────────────────────────────
function openDB() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open('erapor-offline', 1);
    req.onupgradeneeded = () => req.result.createObjectStore('queue', { keyPath: 'id', autoIncrement: true });
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}

async function saveToQueue(item) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('queue', 'readwrite');
    tx.objectStore('queue').add(item);
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error);
  });
}

async function replayQueue() {
  const db = await openDB();
  const items = await new Promise((resolve, reject) => {
    const tx = db.transaction('queue', 'readonly');
    const req = tx.objectStore('queue').getAll();
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });

  let synced = 0;
  for (const item of items) {
    try {
      const body = new URLSearchParams(item.payload).toString();
      const res = await fetch(item.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body,
      });
      if (res.ok || res.redirected) {
        await deleteFromQueue(db, item.id);
        synced++;
      }
    } catch { /* tetap di queue, coba lagi nanti */ }
  }

  const clients = await self.clients.matchAll();
  clients.forEach(c => c.postMessage({ type: 'SYNC_DONE', synced }));
}

async function deleteFromQueue(db, id) {
  return new Promise((resolve, reject) => {
    const tx = db.transaction('queue', 'readwrite');
    tx.objectStore('queue').delete(id);
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error);
  });
}

