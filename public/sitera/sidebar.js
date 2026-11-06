(() => {
  "use strict";
  const toggle = document.getElementById("sidebar-toggle");
  const nav = document.getElementById("main-nav");
  const backdrop = document.getElementById("sidebar-backdrop");
  if (!toggle || !nav || !backdrop) return;

  const drawerBreakpoint = 820;
  const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

  function isDrawerMode() {
    return window.innerWidth <= drawerBreakpoint;
  }

  function setOpen(open, restoreFocus = false) {
    const drawerMode = isDrawerMode();
    const isOpen = drawerMode && open;
    document.body.classList.toggle("sidebar-open", isOpen);
    toggle.setAttribute("aria-expanded", String(isOpen));
    toggle.setAttribute("aria-label", isOpen ? "Tutup menu navigasi" : "Buka menu navigasi");
    nav.setAttribute("aria-hidden", String(drawerMode && !isOpen));
    nav.inert = drawerMode && !isOpen;
    backdrop.hidden = !isOpen;

    if (isOpen) {
      nav.querySelector(focusableSelector)?.focus();
    } else if (restoreFocus && drawerMode) {
      toggle.focus();
    }
  }

  toggle.addEventListener("click", () => {
    setOpen(!document.body.classList.contains("sidebar-open"));
  });
  backdrop.addEventListener("click", () => setOpen(false, true));
  nav.addEventListener("click", event => {
    if (event.target.closest(".nav-item, .nav a, .nav button")) setOpen(false);
  });
  nav.addEventListener("keydown", event => {
    if (event.key !== "Tab" || !isDrawerMode()) return;
    const focusable = [...nav.querySelectorAll(focusableSelector)];
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });
  document.addEventListener("keydown", event => {
    if (event.key === "Escape" && document.body.classList.contains("sidebar-open")) {
      setOpen(false, true);
    }
  });
  window.addEventListener("resize", () => {
    if (!isDrawerMode()) setOpen(false);
  });

  setOpen(false);
})();
