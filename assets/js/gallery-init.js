/**
 * BusinessBloom Galéria - GLightbox Inicializálás
 * @version 1.0.0
 */
(function(){
  'use strict';
  
  const GROUP_CONTAINERS = [
    '.carousel',
    '.embla__container',
    '.wp-block-gallery',
    '.blocks-gallery-grid',
    '.gallery',
    '.bbloom-gallery'
  ];

  const IMG_EXT = /\.(avif|webp|jpe?g|png|gif|bmp|svg)(\?.*)?$/i;

  const containerIds = new WeakMap();
  let containerAutoId = 0;

  function getContainerId(node){
    if (!node) return 'page-group';
    if (!containerIds.has(node)) {
      containerIds.set(node, `glbx-group-${++containerAutoId}`);
    }
    return containerIds.get(node);
  }

  function enhance(root=document){
    const links = Array.from(
      root.querySelectorAll('a[href]')
    ).filter(a => {
      const href = a.getAttribute('href') || '';
      const hasImgChild = a.querySelector('img, picture, figure img');
      return IMG_EXT.test(href) && hasImgChild;
    });

    links.forEach(a => {
      if (a.hasAttribute('data-glightbox')) return;

      let parent = null;
      for (const sel of GROUP_CONTAINERS) {
        const p = a.closest(sel);
        if (p) { parent = p; break; }
      }
      const groupId = getContainerId(parent);

      a.setAttribute('data-glightbox', '');
      a.setAttribute('data-gallery', groupId);
    });

    if (!window.__bbloomGLightbox) {
      window.__bbloomGLightbox = GLightbox({
        selector: 'a[data-glightbox]',
        loop: true,
        touchNavigation: true,
        openEffect: 'zoom',
        closeEffect: 'zoom',
        slideEffect: 'slide',
        descPosition: 'none',
        plyr: {
          config: {
            youtube: {
              noCookie: true,
              rel: 0,
              showinfo: 0,
              iv_load_policy: 3
            },
            autoplay: true,
            loop: { active: true },
            muted: true
          }
        },
        onSlideChanged: function({ prev, current }) {
          if (prev && prev.slideNode) {
            const prevIframe = prev.slideNode.querySelector('iframe');
            if (prevIframe && prevIframe.src.includes('youtube.com')) {
              prevIframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
            }
          }
          
          if (current && current.slideNode) {
            const currentIframe = current.slideNode.querySelector('iframe');
            if (currentIframe && currentIframe.src.includes('youtube.com')) {
              setTimeout(() => {
                currentIframe.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
              }, 300);
            }
          }
        },
        onClose: function() {
          const iframes = document.querySelectorAll('.glightbox-container iframe');
          iframes.forEach(iframe => {
            if (iframe.src.includes('youtube.com')) {
              iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
            }
          });
        }
      });
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    enhance();

    const mo = new MutationObserver(muts => {
      for (const m of muts) {
        if (m.addedNodes && m.addedNodes.length) enhance(m.target || document);
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  });
})();