/**
 * E-Rapor Offline Queue — client helper
 * - Menampilkan banner status koneksi (online/offline)
 * - Menampilkan badge jumlah data yang menunggu sinkronisasi
 * - Tombol "Sinkronkan Sekarang" (penting untuk iOS yang tak mendukung Background Sync)
 * - Auto-sync saat koneksi kembali
 */
(function () {
  'use strict';

  const DB_NAME = 'erapor-offline';
  const STORE = 'queue';

  // ── IndexedDB helpers ───────────────────────────────────────
  function openDB() {
    return new Promise((resolve, reject) => {
      const req = indexedDB.open(DB_NAME, 1);
      req.onupgradeneeded = () => {
        if (!req.result.objectStoreNames.contains(STORE)) {
          req.result.createObjectStore(STORE, { keyPath: 'id', autoIncrement: true });
        }
      };
      req.onsuccess = () => resolve(req.result);
      req.onerror = () => reject(req.error);
    });
  }

  async function countQueue() {
    try {
      const db = await openDB();
      return await new Promise((resolve) => {
        const tx = db.transaction(STORE, 'readonly');
        const req = tx.objectStore(STORE).count();
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => resolve(0);
      });
    } catch { return 0; }
  }

  // ── UI: banner + badge ──────────────────────────────────────
  function ensureUI() {
    if (document.getElementById('erapor-offline-bar')) return;
    const bar = document.createElement('div');
    bar.id = 'erapor-offline-bar';
    bar.style.cssText =
      'position:fixed;left:0;right:0;top:0;z-index:10001;display:none;' +
      'align-items:center;justify-content:center;gap:.5rem;padding:.5rem .75rem;' +
      'font-family:system-ui,sans-serif;font-size:.8rem;font-weight:700;' +
      'color:#fff;background:#f59e0b;box-shadow:0 2px 10px rgba(0,0,0,.15);' +
      'transition:transform .3s;transform:translateY(-100%);';
    bar.innerHTML =
      '<span id="erapor-offline-text">Anda sedang offline — data disimpan sementara</span>' +
      '<button id="erapor-sync-btn" style="display:none;margin-left:.5rem;background:rgba(255,255,255,.25);' +
      'border:none;color:#fff;font-weight:700;padding:.25rem .6rem;border-radius:.5rem;cursor:pointer;font-size:.75rem;">↻ Sinkronkan</button>';
    document.body.appendChild(bar);

    document.getElementById('erapor-sync-btn').addEventListener('click', triggerSync);
  }

  function showBar(text, color, showSync) {
    ensureUI();
    const bar = document.getElementById('erapor-offline-bar');
    document.getElementById('erapor-offline-text').textContent = text;
    document.getElementById('erapor-sync-btn').style.display = showSync ? 'inline-block' : 'none';
    bar.style.background = color;
    bar.style.display = 'flex';
    requestAnimationFrame(() => { bar.style.transform = 'translateY(0)'; });
  }

  function hideBar() {
    const bar = document.getElementById('erapor-offline-bar');
    if (!bar) return;
    bar.style.transform = 'translateY(-100%)';
    setTimeout(() => { bar.style.display = 'none'; }, 300);
  }

  // ── Sync trigger ────────────────────────────────────────────
  async function triggerSync() {
    if (!navigator.onLine) return;
    const reg = await navigator.serviceWorker.ready;
    // Background Sync (Android) ATAU pesan manual (iOS fallback)
    if (reg.sync) {
      try { await reg.sync.register('erapor-form-sync'); } catch { postReplay(reg); }
    } else {
      postReplay(reg);
    }
  }

  function postReplay(reg) {
    if (reg.active) reg.active.postMessage({ type: 'REPLAY_QUEUE' });
  }

  // ── Refresh status UI ───────────────────────────────────────
  async function refresh() {
    const pending = await countQueue();
    if (!navigator.onLine) {
      const extra = pending > 0 ? ` (${pending} data menunggu)` : '';
      showBar('Anda sedang offline — data disimpan sementara' + extra, '#f59e0b', false);
    } else if (pending > 0) {
      showBar(`${pending} data belum tersinkron`, '#3b82f6', true);
      triggerSync(); // auto-sync begitu online
    } else {
      hideBar();
    }
  }

  // ── Events ──────────────────────────────────────────────────
  window.addEventListener('online', refresh);
  window.addEventListener('offline', refresh);

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', (e) => {
      const d = e.data || {};
      if (d.type === 'QUEUED') {
        refresh();
      } else if (d.type === 'SYNC_DONE') {
        refresh();
        if (d.synced > 0 && typeof Swal !== 'undefined') {
          const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true });
          Toast.fire({ icon: 'success', title: `${d.synced} data offline berhasil dikirim ke server` });
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', refresh);
  document.body && document.body.addEventListener &&
    document.body.addEventListener('htmx:afterSettle', refresh);

  // Ekspos untuk pemakaian manual jika perlu
  window.eraporOffline = { refresh, triggerSync, countQueue };
})();
