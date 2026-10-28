/* Storefront behaviour — vanilla JS, no build step.
   Sticky header, dropdown menu, locale switcher, search type-ahead, mobile
   drawer, tabs, collapses, Splide carousels, dialogs, newsletter forms and
   pop-in, store filter. */
(function () {
  'use strict';

  const $  = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
  const isDesktop = () => window.matchMedia('(min-width: 992px)').matches;
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const store = {
    get(key) { try { return window.localStorage.getItem(key); } catch (e) { return null; } },
    set(key, value) { try { window.localStorage.setItem(key, value); } catch (e) { /* private mode */ } },
  };

  /* ---------------- sticky header: hide on scroll down, show on scroll up ---------------- */
  function initHeader() {
    const header = $('.js-header');
    if (!header) return;

    const setHeight = () => document.documentElement.style.setProperty('--header-h', header.offsetHeight + 'px');
    setHeight();
    window.addEventListener('resize', setHeight);

    let last = window.scrollY;
    let ticking = false;

    window.addEventListener('scroll', () => {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(() => {
        const y = window.scrollY;
        const busy = header.querySelector('[aria-expanded="true"]');
        if (y > header.offsetHeight && y > last && !busy) header.classList.add('is-hidden');
        else if (y < last || y <= header.offsetHeight) header.classList.remove('is-hidden');
        last = y;
        ticking = false;
      });
    }, { passive: true });
  }

  /* ---------------- desktop dropdown menu ---------------- */
  function initDropdowns() {
    $$('.js-dropdown').forEach((item) => {
      const link = $('.site-nav__link', item);
      const open = (state) => {
        item.classList.toggle('is-open', state);
        link.setAttribute('aria-expanded', String(state));
      };

      item.addEventListener('mouseenter', () => isDesktop() && open(true));
      item.addEventListener('mouseleave', () => isDesktop() && open(false));
      item.addEventListener('focusin', () => open(true));
      item.addEventListener('focusout', (e) => { if (!item.contains(e.relatedTarget)) open(false); });

      // Touch screens have no hover: the first tap opens the menu, the second follows the link.
      link.addEventListener('pointerdown', (e) => { link.dataset.pointer = e.pointerType; });
      link.addEventListener('click', (e) => {
        if (link.dataset.pointer === 'touch' && !item.classList.contains('is-open')) {
          e.preventDefault();
          open(true);
        }
      });

      item.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { open(false); link.focus(); }
      });
    });
  }

  /* ---------------- locale switcher ---------------- */
  function initLocale() {
    $$('.js-locale').forEach((wrap) => {
      const toggle = $('.locale__toggle', wrap);
      const menu = $('.locale__menu', wrap);
      if (!toggle || !menu) return;

      const set = (open) => { menu.hidden = !open; toggle.setAttribute('aria-expanded', String(open)); };
      toggle.addEventListener('click', () => set(menu.hidden));
      document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) set(false); });
      wrap.addEventListener('keydown', (e) => { if (e.key === 'Escape') { set(false); toggle.focus(); } });
    });
  }

  /* ---------------- search panel + type-ahead ---------------- */
  function initSearch() {
    const panel = $('.js-search-panel');
    if (!panel) return;

    const toggles = $$('.js-search-toggle');
    const field = $('.js-search-field', panel);
    const box = $('.js-suggestions', panel);

    const set = (open) => {
      panel.hidden = !open;
      toggles.forEach((t) => { t.classList.toggle('is-active', open); t.setAttribute('aria-expanded', String(open)); });
      if (open) field && field.focus();
    };

    toggles.forEach((t) => t.addEventListener('click', () => set(panel.hidden)));
    $$('.js-search-close', panel).forEach((b) => b.addEventListener('click', () => {
      set(false);
      const visible = toggles.find((t) => t.offsetParent !== null);
      if (visible) visible.focus();
    }));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) set(false); });
    document.addEventListener('click', (e) => {
      if (!panel.hidden && !panel.contains(e.target) && !toggles.some((t) => t.contains(e.target))) set(false);
    });

    if (!field || !box) return;

    const escape = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const group = (label, items, withImage) => items.length
      ? '<div class="search-suggestions__group"><p class="search-suggestions__label">' + label + '</p><ul>' +
        items.map((it) => '<li><a href="' + escape(it.url) + '">' +
          (withImage && it.image ? '<img src="' + escape(it.image) + '" alt="">' : '') +
          '<span>' + escape(it.label) + '</span></a></li>').join('') + '</ul></div>'
      : '';

    let timer = null;
    let controller = null;

    field.addEventListener('input', () => {
      clearTimeout(timer);
      const q = field.value.trim();
      if (q.length < 2) { box.hidden = true; field.setAttribute('aria-expanded', 'false'); return; }

      timer = setTimeout(() => {
        if (controller) controller.abort();
        controller = new AbortController();

        fetch(field.dataset.suggestUrl + '?q=' + encodeURIComponent(q), {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          signal: controller.signal,
        })
          .then((r) => r.json())
          .then((data) => {
            const html = group(box.dataset.products || 'Products', data.products || [], true) +
                         group(box.dataset.articles || 'Articles', data.articles || [], false);
            const form = field.form;
            box.innerHTML = (html || '<p class="search-suggestions__label">No suggestions</p>') +
              '<a class="search-suggestions__all" href="' + escape(form.action + '?q=' + encodeURIComponent(q)) + '">See all results</a>';
            box.hidden = false;
            field.setAttribute('aria-expanded', 'true');
          })
          .catch(() => {});
      }, 200);
    });

    // Arrow keys move between suggestions.
    panel.addEventListener('keydown', (e) => {
      if (box.hidden || (e.key !== 'ArrowDown' && e.key !== 'ArrowUp')) return;
      const links = $$('a', box);
      if (!links.length) return;
      e.preventDefault();
      const i = links.indexOf(document.activeElement);
      const next = e.key === 'ArrowDown' ? Math.min(i + 1, links.length - 1) : i - 1;
      (next < 0 ? field : links[next]).focus();
    });
  }

  /* ---------------- mobile drawer ---------------- */
  function initDrawer() {
    const drawer = $('.js-drawer');
    const backdrop = $('.js-drawer-backdrop');
    const openers = $$('.js-drawer-open');
    if (!drawer) return;

    const back = $('.js-drawer-back', drawer);
    const panels = $$('.drawer__panel', drawer);

    const showPanel = (name) => {
      panels.forEach((p) => { p.hidden = p.dataset.panel !== name; });
      back.hidden = name === 'root';
      const first = $('[data-panel="' + name + '"] a, [data-panel="' + name + '"] button, [data-panel="' + name + '"] summary', drawer);
      if (first) first.focus();
    };

    const set = (open) => {
      drawer.hidden = !open;
      if (backdrop) backdrop.hidden = !open;
      document.body.classList.toggle('is-locked', open);
      openers.forEach((b) => b.setAttribute('aria-expanded', String(open)));
      if (open) { showPanel('root'); $('.js-drawer-close', drawer).focus(); }
      else if (openers[0]) openers[0].focus();
    };

    openers.forEach((b) => b.addEventListener('click', () => set(true)));
    $$('.js-drawer-close', drawer).forEach((b) => b.addEventListener('click', () => set(false)));
    if (backdrop) backdrop.addEventListener('click', () => set(false));
    $$('.js-drawer-panel', drawer).forEach((b) => b.addEventListener('click', () => showPanel(b.dataset.target)));
    if (back) back.addEventListener('click', () => showPanel('root'));

    drawer.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') set(false);
      if (e.key === 'Tab') trapFocus(drawer, e);
    });
    window.addEventListener('resize', () => { if (isDesktop() && !drawer.hidden) set(false); });
  }

  function trapFocus(container, e) {
    const focusable = $$('a[href], button:not([disabled]), input, select, textarea, summary, [tabindex]:not([tabindex="-1"])', container)
      .filter((el) => el.offsetParent !== null);
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }

  /* ---------------- tabs ---------------- */
  function initTabs() {
    $$('.js-tabs').forEach((wrap) => {
      const tabs = $$('[role="tab"]', wrap);

      const select = (tab, focus) => {
        tabs.forEach((t) => {
          const on = t === tab;
          t.setAttribute('aria-selected', String(on));
          t.tabIndex = on ? 0 : -1;
          const panel = document.getElementById(t.getAttribute('aria-controls'));
          if (panel) panel.hidden = !on;
        });
        if (focus) tab.focus();
        // Carousels measured while hidden need a refresh once visible.
        const panel = document.getElementById(tab.getAttribute('aria-controls'));
        if (panel) $$('.js-carousel', panel).forEach((el) => el.splide && el.splide.refresh());
      };

      tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => select(tab, false));
        tab.addEventListener('keydown', (e) => {
          const keys = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 };
          if (!(e.key in keys)) return;
          e.preventDefault();
          select(tabs[(keys[e.key] + tabs.length) % tabs.length], true);
        });
      });
    });
  }

  /* ---------------- collapse toggles (accordions, references, transcripts) ---------------- */
  function initCollapses() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.js-collapse');
      if (!btn) return;
      const target = document.getElementById(btn.getAttribute('aria-controls'));
      if (!target) return;
      const open = btn.getAttribute('aria-expanded') !== 'true';
      btn.setAttribute('aria-expanded', String(open));
      target.hidden = !open;
    });
  }

  /* ---------------- carousels (Splide) ---------------- */
  function initCarousels() {
    if (typeof window.Splide === 'undefined') return;

    const i18n = {
      prev: 'Previous slide', next: 'Next slide', first: 'Go to first slide', last: 'Go to last slide',
      slideX: 'Go to slide %s', pageX: 'Go to page %s', play: 'Start autoplay', pause: 'Pause autoplay',
      carousel: 'carousel', select: 'Select a slide to show', slide: 'slide', slideLabel: '%s of %s',
    };

    $$('.js-carousel').forEach((el) => {
      let options = {};
      try { options = JSON.parse(el.dataset.carousel || '{}'); } catch (e) { options = {}; }

      const splide = new window.Splide(el, Object.assign({
        mediaQuery: 'min',
        perMove: 1,
        speed: reducedMotion ? 0 : 500,
        i18n,
        reducedMotion: { speed: 0, rewindSpeed: 0, autoplay: 'pause' },
      }, options, {
        // autoplay only while the carousel is on screen
        autoplay: options.autoplay ? 'pause' : false,
      }));

      splide.mount();
      el.splide = splide;

      if (options.autoplay && !reducedMotion && 'IntersectionObserver' in window) {
        const autoplay = splide.Components.Autoplay;
        new IntersectionObserver((entries) => {
          entries.forEach((entry) => {
            if (!autoplay) return;
            if (entry.isIntersecting && !el.dataset.userPaused) autoplay.play();
            else autoplay.pause();
          });
        }, { threshold: 0.4 }).observe(el);

        // remember an explicit pause from the toggle button
        const toggle = $('.splide__toggle', el);
        if (toggle) toggle.addEventListener('click', () => {
          if (autoplay.isPaused()) el.dataset.userPaused = '1'; else delete el.dataset.userPaused;
        });
      }
    });
  }

  /* ---------------- dialogs ---------------- */
  let lastOpener = null;

  function openModal(modal, opener) {
    lastOpener = opener || document.activeElement;
    modal.hidden = false;
    document.body.classList.add('is-locked');

    const frame = $('.modal__frame', modal);
    if (frame && !frame.firstChild) {
      const iframe = document.createElement('iframe');
      iframe.src = frame.dataset.src;
      iframe.allow = 'autoplay; fullscreen; picture-in-picture';
      iframe.allowFullscreen = true;
      iframe.title = modal.getAttribute('aria-label') || 'Video';
      frame.appendChild(iframe);
    }
    const video = $('video', modal);
    if (video) video.play().catch(() => {});

    const close = $('.js-modal-close', modal);
    if (close) close.focus();
  }

  function closeModal(modal) {
    modal.hidden = true;
    document.body.classList.remove('is-locked');

    const frame = $('.modal__frame', modal);
    if (frame) frame.innerHTML = '';
    const video = $('video', modal);
    if (video) video.pause();

    if (modal.classList.contains('js-newsletter-popup')) {
      store.set('nl_snooze_until', String(Date.now() + Number(modal.dataset.snoozeDays || 7) * 864e5));
    }
    if (lastOpener && document.contains(lastOpener)) lastOpener.focus();
  }

  function initModals() {
    document.addEventListener('click', (e) => {
      const opener = e.target.closest('.js-modal-open');
      if (opener) {
        const modal = document.getElementById(opener.getAttribute('aria-controls'));
        if (modal) openModal(modal, opener);
        return;
      }
      const closer = e.target.closest('.js-modal-close');
      if (closer) { closeModal(closer.closest('.modal')); return; }
      // click on the backdrop itself
      if (e.target.classList && e.target.classList.contains('modal')) closeModal(e.target);
    });

    document.addEventListener('keydown', (e) => {
      const open = $$('.modal').find((m) => !m.hidden);
      if (!open) return;
      if (e.key === 'Escape') closeModal(open);
      if (e.key === 'Tab') trapFocus(open, e);
    });
  }

  /* ---------------- newsletter forms (block + pop-in) ---------------- */
  function initNewsletter() {
    $$('.js-newsletter-form').forEach((form) => {
      const scope = form.parentElement;
      const success = $('.js-newsletter-success', scope);
      const formError = $('.js-form-error', form);

      const setError = (input, message) => {
        const error = document.getElementById(input.id + '-error');
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        if (error) error.textContent = message || '';
      };

      const validate = () => {
        let firstInvalid = null;
        $$('input[required]', form).forEach((input) => {
          let message = '';
          if (input.type === 'checkbox' ? !input.checked : !input.value.trim()) message = 'This field is required.';
          else if (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(input.value.trim())) message = 'Please enter a valid e-mail address.';
          setError(input, message);
          if (message && !firstInvalid) firstInvalid = input;
        });
        if (firstInvalid) firstInvalid.focus();
        return !firstInvalid;
      };

      form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (formError) formError.hidden = true;
        if (!validate()) return;

        const button = $('button[type="submit"]', form);
        if (button) button.disabled = true;

        fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
          .then((r) => r.json().then((data) => ({ status: r.status, data })))
          .then(({ status, data }) => {
            if (status === 200 && data.ok) {
              form.hidden = true;
              if (success) {
                success.hidden = false;
                success.setAttribute('tabindex', '-1');
                success.focus();
              }
              store.set('nl_subscribed_until', String(Date.now() + 365 * 864e5));
              return;
            }
            if (status === 422 && data.errors) {
              const names = { first_name: 'first_name', last_name: 'last_name', email: 'email', consent: 'consent' };
              Object.keys(data.errors).forEach((name) => {
                const input = form.querySelector('[name="' + (names[name] || name) + '"]');
                if (input) setError(input, data.errors[name][0]);
              });
              return;
            }
            throw new Error('failed');
          })
          .catch(() => { if (formError) { formError.textContent = form.dataset.error; formError.hidden = false; } })
          .finally(() => { if (button) button.disabled = false; });
      });
    });
  }

  function initNewsletterPopup() {
    const popup = $('.js-newsletter-popup');
    if (!popup) return;

    const views = Number(store.get('nl_views') || 0) + 1;
    store.set('nl_views', String(views));

    const snoozed = Number(store.get('nl_snooze_until') || 0) > Date.now();
    const subscribed = Number(store.get('nl_subscribed_until') || 0) > Date.now();
    if (snoozed || subscribed || views < Number(popup.dataset.afterPages || 2)) return;

    window.setTimeout(() => {
      if ($$('.modal').some((m) => !m.hidden)) return;
      openModal(popup);
    }, 1500);
  }

  /* ---------------- misc ---------------- */
  function initAutosubmit() {
    $$('.js-autosubmit select').forEach((select) => select.addEventListener('change', () => select.form.submit()));
  }

  function initStoreFilter() {
    $$('.js-store-filter').forEach((wrap) => {
      const select = $('.js-store-country', wrap);
      const empty = $('.js-store-empty', wrap);
      if (!select) return;

      select.addEventListener('change', () => {
        const country = select.value;
        let visible = 0;
        $$('.store-card', wrap).forEach((card) => {
          const show = !country || card.dataset.country === country;
          card.hidden = !show;
          if (show) visible++;
        });
        $$('.stores__grid', wrap).forEach((grid) => {
          const any = $$('.store-card', grid).some((c) => !c.hidden);
          grid.hidden = !any;
          const title = grid.previousElementSibling;
          if (title && title.classList.contains('stores__title')) title.hidden = !any;
        });
        if (empty) empty.hidden = visible > 0;
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initDropdowns();
    initLocale();
    initSearch();
    initDrawer();
    initTabs();
    initCollapses();
    initCarousels();
    initModals();
    initNewsletter();
    initNewsletterPopup();
    initAutosubmit();
    initStoreFilter();
  });
})();
