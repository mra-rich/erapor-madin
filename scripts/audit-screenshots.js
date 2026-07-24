const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE = 'http://127.0.0.1:8000';
const OUT_DIR = path.join(__dirname, '..', 'public', 'assets', 'audit');

const PAGES = [
  { name: 'login',           url: '/index.php', auth: false },
  { name: 'dashboard',       url: '/dashboard.php', auth: true },
  { name: 'data-santri',     url: '/data_santri.php', auth: true },
  { name: 'data-guru',       url: '/data_guru.php', auth: true },
  { name: 'data-kelas',      url: '/data_kelas.php', auth: true },
  { name: 'data-mapel',      url: '/data_mata_pelajaran.php', auth: true },
  { name: 'data-nilai',      url: '/data_nilai.php', auth: true },
  { name: 'penilaian-mapel', url: '/penilaian_mapel.php', auth: true },
  { name: 'import-nilai',    url: '/import_nilai.php', auth: true },
  { name: 'kenaikan-kelas',  url: '/kenaikan_kelas.php', auth: true },
  { name: 'identitas',       url: '/identitas_madrasah.php', auth: true },
  { name: 'log-aktivitas',   url: '/log_aktivitas.php', auth: true },
];

const VIEWPORTS = [
  { name: 'desktop', width: 1366, height: 768 },
  { name: 'mobile',  width: 390,  height: 844 },
];

async function login(page) {
  await page.goto(BASE + '/index.php');
  await page.fill('input[name="username"]', 'admin');
  await page.fill('input[name="password"]', 'admin');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard.php', { timeout: 10000 });
}

(async () => {
  if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });
  const browser = await chromium.launch();

  for (const vp of VIEWPORTS) {
    const context = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
    const page = await context.newPage();
    await login(page);

    const loginContext = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
    const loginPage = await loginContext.newPage();

    for (const p of PAGES) {
      try {
        if (!p.auth) {
          await loginPage.goto(BASE + p.url);
          await loginPage.waitForLoadState('networkidle').catch(() => {});
          await loginPage.screenshot({ path: path.join(OUT_DIR, p.name + '-' + vp.name + '.png'), fullPage: true });
        } else {
          await page.goto(BASE + p.url);
          await page.waitForLoadState('networkidle').catch(() => {});
          await page.screenshot({ path: path.join(OUT_DIR, p.name + '-' + vp.name + '.png'), fullPage: true });
        }
        console.log('OK', p.name + '-' + vp.name);
      } catch(e) {
        console.error('ERR', p.name + '-' + vp.name + ':', e.message);
      }
    }
    await loginContext.close();
    await context.close();
  }

  await browser.close();
  console.log('Done!', OUT_DIR);
})();
