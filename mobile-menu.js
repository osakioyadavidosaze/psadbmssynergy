(() => {
    const header = document.querySelector('.site-header');
    const nav = header?.querySelector('nav');

    if (!header || !nav || nav.querySelector('.menu-toggle')) return;

    const toggle = document.createElement('button');
    toggle.className = 'menu-toggle';
    toggle.type = 'button';
    toggle.setAttribute('aria-label', 'Open menu');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.innerHTML = '<span></span><span></span><span></span>';

    const styles = document.createElement('style');
    styles.textContent = `
        .menu-toggle { display: none; border: 0; background: var(--ink); color: #fff; width: 44px; height: 40px; padding: 10px; cursor: pointer; }
        .menu-toggle span { display: block; height: 2px; margin: 4px 0; background: currentColor; transition: transform .2s, opacity .2s; }
        @media (max-width: 800px) {
            .site-header { position: relative; }
            .menu-toggle { display: block; }
            .site-header nav { display: flex; align-items: stretch; }
            .site-header nav > a, .site-header nav > span { display: none !important; }
            .site-header nav > .menu-toggle { display: block !important; }
            .site-header nav.menu-open { position: absolute; z-index: 10; top: 70px; right: 18px; min-width: 190px; padding: 10px; flex-direction: column; gap: 0; background: var(--paper); border: 1px solid var(--line); box-shadow: var(--shadow); }
            .site-header nav.menu-open > a, .site-header nav.menu-open > span { display: block !important; padding: 11px 10px; }
            .site-header nav.menu-open > .button { display: inline-flex !important; margin: 4px 0; }
            .site-header nav.menu-open .menu-toggle { position: absolute; top: -52px; right: 0; }
            .site-header nav.menu-open .menu-toggle span:first-child { transform: translateY(6px) rotate(45deg); }
            .site-header nav.menu-open .menu-toggle span:nth-child(2) { opacity: 0; }
            .site-header nav.menu-open .menu-toggle span:last-child { transform: translateY(-6px) rotate(-45deg); }
        }
    `;
    document.head.appendChild(styles);
    nav.appendChild(toggle);

    const closeMenu = () => {
        nav.classList.remove('menu-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open menu');
    };

    toggle.addEventListener('click', () => {
        const isOpen = nav.classList.toggle('menu-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
    });

    nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    document.addEventListener('click', (event) => {
        if (!header.contains(event.target)) closeMenu();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMenu();
    });
})();
