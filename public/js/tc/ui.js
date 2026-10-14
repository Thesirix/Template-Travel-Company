/* ==========================================================================
   Travel Company, interactions du back-office (sans dépendance)
   Toasts, barre de chargement, ripple, menu repliable, palette Ctrl+K,
   compteurs animés, apparitions au défilement.
   ========================================================================== */
(function () {
    'use strict';

    const TC = window.TC = window.TC || {};
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const store = {
        get: (k) => { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set: (k, v) => { try { localStorage.setItem(k, v); } catch (e) { /* stockage indisponible */ } },
    };

    /* ---------- Toasts ---------- */
    const ICONS = { success: 'fa-check', error: 'fa-xmark', warning: 'fa-exclamation', info: 'fa-info' };
    const TITLES = { success: 'C\'est fait', error: 'Oups…', warning: 'Attention', info: 'Info' };

    function toastHost() {
        let host = document.querySelector('.tc-toasts');
        if (!host) {
            host = document.createElement('div');
            host.className = 'tc-toasts';
            host.setAttribute('aria-live', 'polite');
            document.body.appendChild(host);
        }
        return host;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    TC.toast = function (opts) {
        if (typeof opts === 'string') opts = { message: opts };
        const type = ICONS[opts.type] ? opts.type : 'info';
        const duration = opts.duration ?? (type === 'error' ? 7000 : 4500);
        const el = document.createElement('div');
        el.className = `tc-toast tc-toast--${type}`;
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        const body = Array.isArray(opts.message)
            ? `<ul>${opts.message.map((m) => `<li>${escapeHtml(m)}</li>`).join('')}</ul>`
            : escapeHtml(opts.message || '');
        el.innerHTML = `
            <div class="tc-toast__icon"><i class="fa-solid ${ICONS[type]}"></i></div>
            <div><div class="tc-toast__title">${escapeHtml(opts.title || TITLES[type])}</div><div class="tc-toast__msg">${body}</div></div>
            <button type="button" class="tc-toast__close" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
            ${duration ? `<div class="tc-toast__bar" style="animation-duration:${duration}ms"></div>` : ''}`;
        const close = () => {
            if (el.classList.contains('is-leaving')) return;
            el.classList.add('is-leaving');
            setTimeout(() => el.remove(), reduceMotion ? 0 : 340);
        };
        el.querySelector('.tc-toast__close').addEventListener('click', close);
        if (duration) el.querySelector('.tc-toast__bar').addEventListener('animationend', close);
        const host = toastHost();
        host.appendChild(el);
        while (host.children.length > 5) host.firstElementChild.remove();
        return { close };
    };
    ['success', 'error', 'warning', 'info'].forEach((t) => {
        TC.toast[t] = (message, title, extra) => TC.toast(Object.assign({ type: t, message, title }, extra || {}));
    });

    // Les appels toastr.success(...) des pages affichent les toasts du thème
    window.toastr = {
        options: {},
        success: (m, t) => TC.toast.success(m, t),
        error: (m, t) => TC.toast.error(m, t),
        warning: (m, t) => TC.toast.warning(m, t),
        info: (m, t) => TC.toast.info(m, t),
        clear: () => document.querySelectorAll('.tc-toast').forEach((e) => e.remove()),
        remove: () => {},
    };

    /* ---------- Barre de chargement ---------- */
    const bar = document.createElement('div');
    bar.className = 'tc-progress';
    let barTimer = null;
    TC.progress = {
        start() {
            clearInterval(barTimer);
            bar.classList.add('is-running');
            let w = 8;
            bar.style.width = w + '%';
            barTimer = setInterval(() => { w += (90 - w) * 0.08; bar.style.width = w + '%'; }, 200);
        },
        done() {
            clearInterval(barTimer);
            bar.style.width = '100%';
            setTimeout(() => { bar.classList.remove('is-running'); bar.style.width = '0'; }, 350);
        },
    };

    /* ---------- Ripple sur les boutons ---------- */
    function ripple(e) {
        const btn = e.target.closest('.btn, .tc-ripple-host');
        if (!btn || reduceMotion || btn.disabled) return;
        const r = btn.getBoundingClientRect();
        const size = Math.max(r.width, r.height);
        const span = document.createElement('span');
        span.className = 'tc-ripple';
        span.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - r.left - size / 2}px;top:${e.clientY - r.top - size / 2}px`;
        btn.appendChild(span);
        setTimeout(() => span.remove(), 650);
    }

    /* ---------- Chiffres clés (affichés directement, séparateur de milliers français) ---------- */
    function animateCount(el) {
        const target = parseFloat(el.dataset.tcCount);
        if (isNaN(target)) return;
        const decimals = (el.dataset.tcCount.split('.')[1] || '').length;
        el.textContent = target.toLocaleString('fr-FR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + (el.dataset.tcSuffix || '');
    }

    /* ---------- Menu latéral ---------- */
    function initSidebar() {
        const html = document.documentElement;
        if (store.get('tc-sidebar') === 'collapsed' && window.innerWidth >= 992) html.classList.add('tc-collapsed');
        document.querySelectorAll('[data-tc-toggle="sidebar"]').forEach((btn) => btn.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                html.classList.toggle('tc-mobile-open');
            } else {
                html.classList.toggle('tc-collapsed');
                store.set('tc-sidebar', html.classList.contains('tc-collapsed') ? 'collapsed' : 'open');
            }
        }));
        document.querySelector('.tc-backdrop')?.addEventListener('click', () => html.classList.remove('tc-mobile-open'));

        // Lien actif : correspondance la plus précise avec l'URL courante, et ouverture de ses parents
        const here = location.pathname.replace(/\/+$/, '') + location.search;
        let best = null;
        let bestLen = -1;
        document.querySelectorAll('.tc-sidebar a[href]').forEach((a) => {
            const url = new URL(a.href, location.origin);
            if (url.origin !== location.origin || a.getAttribute('href') === '#') return;
            const path = url.pathname.replace(/\/+$/, '');
            const full = path + url.search;
            const len = here === full ? 10000 : (here.startsWith(path + '/') || here.split('?')[0] === path ? path.length : -1);
            if (len > bestLen) { best = a; bestLen = len; }
        });
        if (best) {
            best.classList.add('is-active');
            let node = best.parentElement;
            while (node && !node.classList.contains('tc-sidebar')) {
                if (node.classList.contains('collapse')) {
                    node.classList.add('show');
                    const toggler = document.querySelector(`[data-target="#${node.id}"]`);
                    if (toggler) { toggler.setAttribute('aria-expanded', 'true'); toggler.classList.remove('collapsed'); }
                }
                node = node.parentElement;
            }
            // Centre le lien actif dans le menu, sans faire défiler la page
            setTimeout(() => {
                const scroller = best.closest('.tc-sidebar__scroll');
                if (scroller) scroller.scrollTop = best.offsetTop - scroller.clientHeight / 2;
            }, 50);
        }
    }

    /* ---------- Palette de commandes (Ctrl + K) ---------- */
    function initPalette() {
        const links = [];
        document.querySelectorAll('.tc-sidebar a[href]').forEach((a) => {
            if (a.getAttribute('href') === '#' || a.getAttribute('href').startsWith('javascript')) return;
            const label = a.textContent.trim().replace(/\s+/g, ' ');
            const trail = [];
            let node = a.parentElement;
            while (node && !node.classList.contains('tc-sidebar')) {
                if (node.classList.contains('collapse')) {
                    const t = document.querySelector(`[data-target="#${node.id}"]`);
                    if (t) trail.unshift(t.textContent.trim().replace(/\s+/g, ' '));
                }
                node = node.parentElement;
            }
            const icon = (a.querySelector('i[class*="fa-"]') || a.closest('.collapse')?.previousElementSibling?.querySelector('i[class*="fa-"]'))?.className || 'fa-solid fa-arrow-right';
            links.push({ label, trail: trail.join(' › '), href: a.href, icon });
        });
        (window.TC_EXTRA_COMMANDS || []).forEach((c) => links.push(c));
        if (!links.length) return;

        const wrap = document.createElement('div');
        wrap.className = 'tc-palette';
        wrap.innerHTML = `<div class="tc-palette__box" role="dialog" aria-label="Recherche rapide">
            <div class="tc-palette__input"><i class="fa-solid fa-magnifying-glass"></i><input type="text" placeholder="Aller à une page… (vols, agents, planning)" autocomplete="off"><kbd class="tc-chip">Échap</kbd></div>
            <ul class="tc-palette__list"></ul></div>`;
        document.body.appendChild(wrap);
        const input = wrap.querySelector('input');
        const list = wrap.querySelector('ul');
        let items = [];
        let active = 0;

        const norm = (s) => s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
        const render = () => {
            const q = norm(input.value.trim());
            items = links
                .map((l) => ({ l, hay: norm(l.trail + ' ' + l.label) }))
                .filter(({ hay }) => !q || q.split(' ').every((w) => hay.includes(w)))
                .slice(0, 40)
                .map(({ l }) => l);
            active = 0;
            list.innerHTML = items.length
                ? items.map((l, i) => {
                    let label = escapeHtml(l.label);
                    if (q) q.split(' ').filter(Boolean).forEach((w) => {
                        const idx = norm(l.label).indexOf(w);
                        if (idx >= 0) label = escapeHtml(l.label.slice(0, idx)) + '<mark>' + escapeHtml(l.label.slice(idx, idx + w.length)) + '</mark>' + escapeHtml(l.label.slice(idx + w.length));
                    });
                    return `<li class="tc-palette__item${i === 0 ? ' is-active' : ''}" data-i="${i}"><i class="${l.icon}"></i><span>${label}</span><small>${escapeHtml(l.trail)}</small></li>`;
                }).join('')
                : '<li class="tc-palette__empty">Aucune page ne correspond</li>';
        };
        const setActive = (i) => {
            const nodes = list.querySelectorAll('.tc-palette__item');
            if (!nodes.length) return;
            active = (i + nodes.length) % nodes.length;
            nodes.forEach((n, k) => n.classList.toggle('is-active', k === active));
            nodes[active].scrollIntoView({ block: 'nearest' });
        };
        const open = () => { wrap.classList.add('is-open'); input.value = ''; render(); setTimeout(() => input.focus(), 20); };
        const close = () => wrap.classList.remove('is-open');
        const go = () => { if (items[active]) { close(); TC.progress.start(); location.href = items[active].href; } };

        TC.palette = { open, close };
        document.querySelectorAll('[data-tc-open="palette"]').forEach((b) => b.addEventListener('click', open));
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); wrap.classList.contains('is-open') ? close() : open(); }
            if (!wrap.classList.contains('is-open')) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowDown') { e.preventDefault(); setActive(active + 1); }
            if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
            if (e.key === 'Enter') { e.preventDefault(); go(); }
        });
        input.addEventListener('input', render);
        list.addEventListener('mousemove', (e) => { const li = e.target.closest('[data-i]'); if (li) setActive(+li.dataset.i); });
        list.addEventListener('click', (e) => { if (e.target.closest('[data-i]')) go(); });
        wrap.addEventListener('click', (e) => { if (e.target === wrap) close(); });
    }

    /* ---------- Horloge et salutation ---------- */
    function initClock() {
        const clock = document.querySelector('[data-tc-clock]');
        if (clock) {
            const tick = () => { clock.textContent = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }); };
            tick();
            setInterval(tick, 15000);
        }
        const greet = document.querySelector('[data-tc-greeting]');
        if (greet) {
            const h = new Date().getHours();
            greet.textContent = h < 5 ? 'Bonne nuit' : h < 12 ? 'Bonjour' : h < 18 ? 'Bon après-midi' : 'Bonsoir';
        }
    }

    /* ---------- Apparitions au défilement ---------- */
    function initReveal() {
        const targets = document.querySelectorAll('#content .card, .tc-stat, [data-tc-reveal]');
        if (!('IntersectionObserver' in window) || reduceMotion) return;
        const io = new IntersectionObserver((entries) => {
            entries.forEach((en) => {
                if (!en.isIntersecting) return;
                en.target.classList.add('is-in');
                en.target.querySelectorAll?.('[data-tc-count]').forEach(animateCount);
                if (en.target.matches('[data-tc-count]')) animateCount(en.target);
                io.unobserve(en.target);
            });
        }, { rootMargin: '0px 0px -40px 0px' });
        let i = 0;
        targets.forEach((el) => {
            if (el.closest('.modal') || el.getBoundingClientRect().top < window.innerHeight) return; // déjà visibles : animés par la page
            el.classList.add('tc-reveal');
            el.style.setProperty('--tc-delay', `${(i++ % 4) * 70}ms`);
            io.observe(el);
        });
        document.querySelectorAll('[data-tc-count]').forEach((el) => {
            if (el.getBoundingClientRect().top < window.innerHeight) animateCount(el);
            else io.observe(el);
        });
    }

    /* ---------- Démarrage ---------- */
    document.addEventListener('DOMContentLoaded', () => {
        document.body.appendChild(bar);
        initSidebar();
        initPalette();
        initClock();
        initReveal();
        // (effet « ripple » des boutons retiré : style sobre)

        // Barre de chargement : navigation et envoi de formulaires
        document.addEventListener('click', (e) => {
            const a = e.target.closest('a[href]');
            if (!a || e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank' || a.hasAttribute('download')) return;
            const href = a.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript') || a.dataset.toggle) return;
            const url = new URL(a.href, location.origin);
            if (url.origin === location.origin && url.pathname !== location.pathname) TC.progress.start();
        });
        document.addEventListener('submit', (e) => { if (!e.defaultPrevented && e.target.method?.toLowerCase() !== 'dialog') TC.progress.start(); });
        if (window.jQuery) {
            jQuery(document).ajaxStart(() => TC.progress.start()).ajaxStop(() => TC.progress.done());
        }

        // Messages flash du serveur (session et erreurs de validation)
        (window.TC_FLASH || []).forEach((f, i) => setTimeout(() => TC.toast(f), 250 + i * 180));
    });
    window.addEventListener('pageshow', () => TC.progress && TC.progress.done());
})();

/* Aperçu d'une image choisie dans un champ fichier : onchange="PreviewImage('id-du-champ', 'id-de-l-image')" */
window.PreviewImage = function (inputId, imageId) {
    const file = document.getElementById(inputId)?.files?.[0];
    const image = document.getElementById(imageId);
    if (!file || !image) return;
    image.style.display = 'block';
    const reader = new FileReader();
    reader.onload = (e) => { image.src = e.target.result; };
    reader.readAsDataURL(file);
};

/* Une modale placée dans un bloc animé (transform) se positionne par rapport à ce bloc et peut sortir de
   l'écran. À l'ouverture, on la déplace donc au niveau de la page, sauf si elle contient des champs d'un
   formulaire englobant (les déplacer les retirerait du formulaire). */
if (window.jQuery) {
    jQuery(document).on('show.bs.modal', '.modal', function () {
        if (this.parentElement !== document.body && !this.closest('form')) document.body.appendChild(this);
    });
}

/* Visionneuse intégrée : les documents internes (guides, procédures, contrats, e-billets, pièces jointes)
   s'ouvrent dans une fenêtre sur la page au lieu d'un nouvel onglet. Ctrl/Cmd + clic garde l'ouverture
   dans un onglet ; data-tc-viewer="off" désactive la visionneuse sur un lien. */
(function () {
    'use strict';
    let viewer = null;

    function build() {
        viewer = document.createElement('div');
        viewer.className = 'tc-viewer';
        viewer.innerHTML = `
            <div class="tc-viewer__box" role="dialog" aria-modal="true" aria-labelledby="tcViewerTitle">
                <div class="tc-viewer__head">
                    <strong id="tcViewerTitle"></strong>
                    <a class="btn btn-secondary btn-sm" data-tc-viewer-download download><i class="fa-solid fa-download mr-1"></i> Télécharger</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-tc-viewer-close aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <iframe title="Document"></iframe>
            </div>`;
        viewer.addEventListener('click', (e) => { if (e.target === viewer || e.target.closest('[data-tc-viewer-close]')) close(); });
        document.body.appendChild(viewer);
    }

    function open(url, title) {
        if (!viewer) build();
        viewer.querySelector('#tcViewerTitle').textContent = title || 'Document';
        viewer.querySelector('[data-tc-viewer-download]').href = url;
        viewer.querySelector('iframe').src = url;
        viewer.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        viewer.classList.remove('is-open');
        viewer.querySelector('iframe').src = 'about:blank';
        document.body.style.overflow = '';
    }

    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[target="_blank"], a[data-tc-viewer]');
        if (!link || link.dataset.tcViewer === 'off' || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
        const url = new URL(link.href, location.href);
        if (url.origin !== location.origin) return;
        e.preventDefault();
        open(url.href, link.textContent.trim() || link.title);
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && viewer?.classList.contains('is-open')) close(); });
})();

/* Titre de la barre du haut : sans titre défini par la page, on reprend son titre principal (h1) */
document.addEventListener('DOMContentLoaded', () => {
    const slot = document.querySelector('[data-tc-page-title]');
    if (!slot || slot.textContent.trim()) return;
    const heading = document.querySelector('.tc-page h1, #content h1, #content .h3');
    if (heading) slot.textContent = heading.textContent.replace(/\s+/g, ' ').trim();
});
