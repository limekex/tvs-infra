(function(){
  function setHeaderHeightVar(){
    try {
      var header = document.querySelector('.tvs-header');
      if(!header) return;
      var h = header.offsetHeight;
      if(h && h > 0){
        document.documentElement.style.setProperty('--tvs-header-height', h + 'px');
      }
    } catch(e) {}
  }

  var rafId;
  function onResize(){
    if(rafId) cancelAnimationFrame(rafId);
    rafId = requestAnimationFrame(setHeaderHeightVar);
  }

  function ensureBurgerToggles(){
    var container = document.querySelector('.tvs-header .wp-block-navigation__responsive-container');
    var openBtn = document.querySelector('.tvs-header .wp-block-navigation__responsive-container-open');
    if(!container || !openBtn) return;

    // Make sure the button is clickable and above content
    openBtn.style.zIndex = 'var(--tvs-z-sticky)';
    openBtn.style.cursor = 'pointer';

    // Toggle open/close on the same burger button
    openBtn.addEventListener('click', function(e){
      try {
        // prevent core from forcing modal behavior; we'll toggle class ourselves
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
      } catch(_) {}

      var isOpen = container.classList.contains('is-menu-open');
      if(isOpen){
        container.classList.remove('is-menu-open');
        document.body.classList.remove('has-modal-open');
      } else {
        container.classList.add('is-menu-open');
        document.body.classList.add('has-modal-open');
      }
    }, true);

    // Close when clicking any link (good UX for dropdowns)
    container.addEventListener('click', function(e){
      var link = e.target.closest('a');
      if(link){
        container.classList.remove('is-menu-open');
        document.body.classList.remove('has-modal-open');
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape'){
        container.classList.remove('is-menu-open');
        document.body.classList.remove('has-modal-open');
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    setHeaderHeightVar();
    ensureBurgerToggles();
  });
  window.addEventListener('resize', onResize, { passive: true });
})();
