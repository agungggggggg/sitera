const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function setup(width = 390) {
  const events = new Map();
  const classes = new Set();
  const documentEvents = new Map();
  let focused = null;
  const firstLink = { focus() { focused = this; } };
  const lastLink = { focus() { focused = this; } };
  const toggle = { attrs: {}, addEventListener: (n, fn) => events.set(`toggle:${n}`, fn), setAttribute(n, v) { this.attrs[n] = v; }, focus() { focused = this; } };
  const nav = { inert: false, attrs: {}, addEventListener: (n, fn) => events.set(`nav:${n}`, fn), setAttribute(n, v) { this.attrs[n] = v; }, querySelector: () => firstLink, querySelectorAll: () => [firstLink, lastLink] };
  const backdrop = { hidden: true, addEventListener: (n, fn) => events.set(`backdrop:${n}`, fn) };
  const document = { body: { classList: { add: n => classes.add(n), remove: n => classes.delete(n), toggle(n, v) { v ? classes.add(n) : classes.delete(n); }, contains: n => classes.has(n) } }, get activeElement() { return focused; }, getElementById: id => ({ 'sidebar-toggle': toggle, 'main-nav': nav, 'sidebar-backdrop': backdrop })[id] || null, addEventListener: (n, fn) => documentEvents.set(n, fn) };
  const window = { innerWidth: width, addEventListener: (n, fn) => events.set(`window:${n}`, fn) };
  vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/sitera/sidebar.js'), 'utf8'), { document, window });
  return { events, classes, documentEvents, toggle, nav, backdrop, window, document, firstLink, lastLink };
}

test('Super Admin prototype references the tracked XLSX vendor asset', () => {
  const html = fs.readFileSync(path.join(__dirname, '../../public/sitera/superadmin-review.html'), 'utf8');
  const assetPath = path.join(__dirname, '../../public/sitera/vendor/xlsx.full.min.js');

  assert.match(html, /<script src="\/sitera\/vendor\/xlsx\.full\.min\.js"><\/script>/);
  assert.equal(fs.existsSync(assetPath), true);
});

test('drawer opens/closes accessibly, closes on Escape/backdrop/link, and resets on desktop resize', () => {
  const ui = setup();
  assert.equal(ui.nav.inert, true);
  assert.equal(ui.nav.attrs['aria-hidden'], 'true');
  ui.events.get('toggle:click')();
  assert.equal(ui.classes.has('sidebar-open'), true);
  assert.equal(ui.toggle.attrs['aria-expanded'], 'true');
  assert.equal(ui.nav.attrs['aria-hidden'], 'false');
  assert.equal(ui.backdrop.hidden, false);
  assert.equal(ui.document.activeElement, ui.firstLink);
  ui.lastLink.focus();
  let prevented = false;
  ui.events.get('nav:keydown')({ key: 'Tab', shiftKey: false, preventDefault() { prevented = true; } });
  assert.equal(prevented, true);
  assert.equal(ui.document.activeElement, ui.firstLink);
  ui.documentEvents.get('keydown')({ key: 'Escape' });
  assert.equal(ui.classes.has('sidebar-open'), false);
  assert.equal(ui.document.activeElement, ui.toggle);
  ui.events.get('toggle:click')();
  ui.events.get('backdrop:click')();
  assert.equal(ui.backdrop.hidden, true);
  ui.events.get('toggle:click')();
  ui.events.get('nav:click')({ target: { closest: () => ({}) } });
  assert.equal(ui.classes.has('sidebar-open'), false);
  ui.events.get('toggle:click')();
  ui.window.innerWidth = 1024;
  ui.events.get('window:resize')();
  assert.equal(ui.classes.has('sidebar-open'), false);
  assert.equal(ui.nav.inert, false);
});

test('role-specific shells expose shared drawer markup and script', () => {
  for (const file of ['layouts/sitera-pelapor.blade.php', 'layouts/sitera-verifier.blade.php', 'layouts/sitera-executive.blade.php', 'admin-only.blade.php', 'admin/dashboard.blade.php']) {
    const source = fs.readFileSync(path.join(__dirname, '../../resources/views', file), 'utf8');
    assert.match(source, /id="sidebar-toggle"/);
    assert.match(source, /id="main-nav"/);
    assert.match(source, /id="sidebar-backdrop"/);
    assert.match(source, /sitera\/sidebar\.js/);
    assert.match(source, /mobile-shell\.css/);
  }
});

test('legacy role layout exposes mobile drawer and shared shell stylesheet', () => {
  const source = fs.readFileSync(path.join(__dirname, '../../resources/views/layouts/sitera.blade.php'), 'utf8');
  assert.match(source, /id="sidebar-toggle"/);
  assert.match(source, /id="main-nav"/);
  assert.match(source, /id="sidebar-backdrop"/);
  assert.match(source, /sitera\/sidebar\.js/);
  assert.match(source, /mobile-shell\.css/);
});

test('mobile shared shell keeps logout reachable with compact header and touch targets', () => {
  const css = fs.readFileSync(path.join(__dirname, '../../public/css/mobile-shell.css'), 'utf8');
  assert.match(css, /\.app-header \.header-right \.exit-link\{display:inline-flex/);
  assert.match(css, /\.mini-action\{display:inline-flex[^}]*min-height:44px/);
  assert.match(css, /@media\(max-width:420px\)/);
});

test('Verifikator filters and long review history fit narrow screens', () => {
  const view = fs.readFileSync(path.join(__dirname, '../../resources/views/verifikasi/index.blade.php'), 'utf8');
  const css = fs.readFileSync(path.join(__dirname, '../../public/sitera/verifikator-review.css'), 'utf8');
  assert.match(view, /class="toolbar verifier-toolbar"/);
  assert.match(css, /\.verifier-toolbar/);
  assert.match(css, /\.review-history-row\{align-items:flex-start;flex-wrap:wrap/);
  assert.match(css, /\.detail-back\{flex-wrap:wrap;gap/);
  assert.match(css, /\.cpcl-scope-control select\{height:44px;min-height:44px\}/);
});

test('Pimpinan filter reset, chart scope, and date fields remain mobile-sized', () => {
  const css = fs.readFileSync(path.join(__dirname, '../../public/sitera/pimpinan-review.css'), 'utf8');
  assert.match(css, /\.filter-panel-heading\{flex-wrap:wrap\}/);
  assert.match(css, /\.leader-filters \.date-filter \.filter-control\{[^}]*flex:0 0 44px/);
  assert.match(css, /\.cpcl-scope-control select\{height:44px;min-height:44px\}/);
  assert.match(css, /\.cpcl-chart-heading\{align-items:stretch\}/);
});

test('Super Admin mobile actions, audit filters, and table scroll targets are touch sized', () => {
  const view = fs.readFileSync(path.join(__dirname, '../../resources/views/admin-only.blade.php'), 'utf8');
  const css = fs.readFileSync(path.join(__dirname, '../../public/css/mobile-shell.css'), 'utf8');
  assert.match(view, /\.audit-select,\.audit-reset\{height:44px;min-height:44px\}/);
  assert.match(view, /\.row-actions button\{[^}]*min-height:44px/);
  assert.match(css, /\.table-wrap\[tabindex="0"\]:focus-visible/);
  assert.match(css, /overscroll-behavior-x:contain;-webkit-overflow-scrolling:touch/);
});

test('mobile account action controls never become a vertical stack', () => {
  const view = fs.readFileSync(path.join(__dirname, '../../resources/views/admin-only.blade.php'), 'utf8');
  assert.match(view, /\.row-actions\{[^}]*flex-wrap:nowrap/);
});

test('role table wrappers are keyboard-focusable for horizontal overflow', () => {
  for (const file of ['reports/index.blade.php', 'reports/all.blade.php', 'reports/cpcl-index.blade.php', 'reports/dashboard.blade.php', 'verifikasi/index.blade.php', 'verifikasi/history.blade.php', 'verifikasi/dashboard.blade.php', 'monitoring/index.blade.php', 'monitoring/dashboard.blade.php', 'admin/dashboard.blade.php', 'admin-only.blade.php', 'admin/partials/audit-log.blade.php']) {
    const source = fs.readFileSync(path.join(__dirname, '../../resources/views', file), 'utf8');
    assert.match(source, /class="[^"]*table-wrap[^"]*"[^>]*tabindex="0"/ , file);
  }
});

test('CPCL import preview actions stack on narrow screens', () => {
  const view = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/cpcl-import-preview.blade.php'), 'utf8');
  assert.match(view, /Batalkan impor/);
  assert.match(view, /Konfirmasi tambah data baru valid/);
  assert.match(view, /@media\(max-width:480px\)\{\.form-foot\{flex-direction:column/);
});

test('Pelapor step progress stays readable in the mobile package form', () => {
  const view = fs.readFileSync(path.join(__dirname, '../../resources/views/reports/package-form.blade.php'), 'utf8');
  assert.match(view, /@media\(max-width:560px\)\{[^}]*\.form-progress\{display:grid;grid-template-columns:repeat\(3,minmax\(0,1fr\)\)/);
  assert.match(view, /\.form-progress small\{font-size:9px/);
});

test('forgot-password help screen is a branded, fluid standalone mobile page', () => {
  const view = fs.readFileSync(path.join(__dirname, '../../resources/views/auth/forgot-password.blade.php'), 'utf8');
  assert.match(view, /name="viewport"/);
  assert.match(view, /sitera\/theme\.css/);
  assert.match(view, /class="password-help-card"/);
  assert.match(view, /@media\s*\(max-width:\s*480px\)/);
});

test('role stylesheets keep responsive breakpoints', () => {
  for (const file of ['pelapor-review.css', 'verifikator-review.css', 'pimpinan-review.css', 'superadmin-review.css']) {
    assert.match(fs.readFileSync(path.join(__dirname, `../../public/sitera/${file}`), 'utf8'), /@media\s*\(max-width/);
  }
});
