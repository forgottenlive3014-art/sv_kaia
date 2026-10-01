// ============================================
// KAIA_SV - Animaciones GSAP (FIX v2)
// ============================================
if (window.gsap && window.ScrollTrigger) {
  gsap.registerPlugin(ScrollTrigger);

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---------- HERO ----------
  if (!reduceMotion) {
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
    tl.from('#logo', { y: -30, opacity: 0, duration: 0.7 })
      .from('.hero-eyebrow', { y: 20, opacity: 0, duration: 0.5 }, '-=0.3')
      .from('.hero h1', { y: 40, opacity: 0, duration: 0.8 }, '-=0.3')
      .from('.hero .lead', { y: 30, opacity: 0, duration: 0.6 }, '-=0.4')
      .from('.hero-actions .btn', { y: 20, opacity: 0, duration: 0.5, stagger: 0.12 }, '-=0.4')
      .from('.hero-visual', { scale: 0.85, opacity: 0, duration: 1 }, '-=0.8')
      .from('.floating-sticker', { scale: 0, opacity: 0, stagger: 0.2, duration: 0.6, ease: 'back.out(2)' }, '-=0.5');
  }

  // ---------- ScrollTrigger genérico (FIX: no deja nada oculto) ----------
  const animarSeccion = (selector) => {
    const elementos = gsap.utils.toArray(selector);
    if (!elementos.length) return;

    elementos.forEach((el, i) => {
      gsap.fromTo(el,
        { y: 40, opacity: 0 },
        {
          y: 0, opacity: 1, duration: 0.6,
          delay: i * 0.08,
          ease: 'power2.out',
          clearProps: 'transform,opacity',
          scrollTrigger: {
            trigger: el,
            start: 'top 92%',
            toggleActions: 'play none none none',
            once: true,
          }
        }
      );
    });
  };

  window.addEventListener('load', () => {
    if (reduceMotion) {
      gsap.set('.cat-card, .prod-card, .promo-section, .section-title, .section-sub, .condicion-card',
        { clearProps: 'all' });
      return;
    }

    animarSeccion('.section-title');
    animarSeccion('.section-sub');
    animarSeccion('.condicion-card');
    animarSeccion('.cat-card');
    animarSeccion('.prod-card');
    animarSeccion('.promo-section');

    ScrollTrigger.refresh();
  });

  // ---------- Hover 3D ----------
  document.querySelectorAll('.prod-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
      gsap.to(card, { y: -8, duration: 0.3, ease: 'power2.out', overwrite: 'auto' });
    });
    card.addEventListener('mouseleave', () => {
      gsap.to(card, { y: 0, duration: 0.3, ease: 'power2.out', overwrite: 'auto' });
    });
  });
}