/* Admin behaviour: section editor (repeatable lists, media fields, Fields/JSON switch). Vanilla JS. */
(function () {
  'use strict';

  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
  const uploadsBase = document.querySelector('meta[name="uploads-base"]')?.content || '/uploads/';
  const assetBase = document.querySelector('meta[name="asset-base"]')?.content || '/';

  /* ---------- repeatable lists ---------- */
  function renumber(list) {
    $$(':scope > .js-repeater-item', list).forEach((item, i) => {
      const number = item.querySelector('.js-repeater-number');
      if (number) number.textContent = String(i + 1);
    });
  }

  function addItem(repeater) {
    const template = repeater.querySelector(':scope > .js-repeater-template');
    const list = repeater.querySelector(':scope > .js-repeater-items');
    const key = 'n' + Date.now().toString(36) + Math.floor(Math.random() * 1000);

    const html = template.innerHTML.replace(/__INDEX__/g, key);
    const holder = document.createElement('div');
    holder.innerHTML = html.trim();
    const item = holder.firstElementChild;

    // ids from the template repeat; make them unique and keep labels pointing at them
    $$('[id]', item).forEach((el) => {
      const old = el.id;
      el.id = old + '-' + key;
      $$('label[for="' + old + '"]', item).forEach((label) => label.setAttribute('for', el.id));
    });

    list.appendChild(item);
    renumber(list);
    const first = item.querySelector('input:not([type="hidden"]), textarea, select');
    if (first) first.focus();
  }

  document.addEventListener('click', (e) => {
    const add = e.target.closest('.js-repeater-add');
    if (add) { addItem(add.closest('.js-repeater')); return; }

    const item = e.target.closest('.js-repeater-item');
    if (!item) return;
    const list = item.parentElement;

    if (e.target.closest('.js-repeater-remove')) {
      if (confirm('Remove this item?')) { item.remove(); renumber(list); }
    } else if (e.target.closest('.js-repeater-up') && item.previousElementSibling) {
      list.insertBefore(item, item.previousElementSibling); renumber(list);
    } else if (e.target.closest('.js-repeater-down') && item.nextElementSibling) {
      list.insertBefore(item.nextElementSibling, item); renumber(list);
    }
  });

  /* ---------- media fields: preview, upload, clear ---------- */
  function mediaUrl(path) {
    if (!path) return '';
    if (/^(https?:)?\/\//.test(path) || path.startsWith('/') || path.startsWith('data:') || path.startsWith('blob:')) return path;
    return path.startsWith('img/') || path.startsWith('uploads/') ? assetBase + path : uploadsBase + path;
  }

  function preview(field, url, isVideoFile) {
    const box = field.querySelector('.js-media-preview');
    if (!box) return;
    box.innerHTML = '';
    if (!url) { box.innerHTML = '<span>None</span>'; return; }
    if (field.dataset.kind === 'video' && !isVideoFile && !/\.(mp4|webm|ogg)(\?|#|$)/i.test(url)) { box.innerHTML = '<span>Link</span>'; return; }
    const el = document.createElement(field.dataset.kind === 'video' ? 'video' : 'img');
    el.src = url;
    if (el.tagName === 'VIDEO') { el.muted = true; el.preload = 'metadata'; }
    else el.alt = '';
    box.appendChild(el);
  }

  document.addEventListener('input', (e) => {
    const input = e.target.closest('.js-media-path');
    if (input) preview(input.closest('.js-media-field'), mediaUrl(input.value.trim()));
  });

  document.addEventListener('change', (e) => {
    const file = e.target.closest('.js-media-file');
    if (!file || !file.files.length) return;
    const field = file.closest('.js-media-field');
    preview(field, URL.createObjectURL(file.files[0]), true);
    const path = field.querySelector('.js-media-path');
    if (path) path.placeholder = 'Will be replaced by the uploaded file: ' + file.files[0].name;
  });

  document.addEventListener('click', (e) => {
    const clear = e.target.closest('.js-media-clear');
    if (!clear) return;
    const field = clear.closest('.js-media-field');
    field.querySelector('.js-media-path').value = '';
    const file = field.querySelector('.js-media-file');
    if (file) file.value = '';
    preview(field, '');
  });

  /* ---------- small behaviours (no inline handlers: the CSP blocks them) ---------- */
  document.addEventListener('submit', (e) => {
    const form = e.target.closest('form[data-confirm]');
    if (form && !confirm(form.dataset.confirm)) e.preventDefault();
  });

  document.addEventListener('change', (e) => {
    const select = e.target.closest('[data-autosubmit]');
    if (select && select.form) select.form.submit();
  });

  document.addEventListener('click', (e) => {
    const input = e.target.closest('input[data-copy]');
    if (!input) return;
    input.select();
    if (navigator.clipboard) navigator.clipboard.writeText(input.value).catch(() => {});
  });

  /* ---------- Fields / JSON switch ---------- */
  $$('.js-section-form').forEach((form) => {
    const mode = form.querySelector('.js-editor-mode');
    const tabs = $$('.js-editor-tab', form);
    const panels = $$('.js-editor-panel', form);

    tabs.forEach((tab) => tab.addEventListener('click', () => {
      mode.value = tab.dataset.mode;
      tabs.forEach((t) => t.setAttribute('aria-selected', String(t === tab)));
      panels.forEach((p) => { p.hidden = p.dataset.mode !== tab.dataset.mode; });
    }));

    // New section: switching the type loads that type's fields.
    const type = form.querySelector('.js-section-type');
    if (type && type.dataset.createUrl) {
      type.addEventListener('change', () => {
        const url = new URL(type.dataset.createUrl, window.location.href);
        url.searchParams.set('type', type.value);
        url.searchParams.set('page_key', form.querySelector('#page_key').value || 'home');
        window.location.href = url.toString();
      });
    }
  });
})();
