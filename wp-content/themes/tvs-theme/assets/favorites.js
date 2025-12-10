(function(){
  function onClick(e){
    const btn = e.target.closest('.tvs-bookmark-btn');
    if(!btn) return;
    e.preventDefault();
    e.stopPropagation();
    const routeId = btn.getAttribute('data-route');
    if(!routeId){ return; }
    
    // Try TVS_FAVORITES_SETTINGS first, then fallback to TVS_SETTINGS
    const settings = window.TVS_FAVORITES_SETTINGS || window.TVS_SETTINGS || {};
    if (!settings.restRoot) {
      settings.restRoot = '/wp-json/';
    }
    const rest = settings.restRoot.replace(/\/$/, '');
    const nonce = settings.nonce || '';

    // Debug logging
    console.log('[TVS] Favorites debug:', {
      hasNonce: !!nonce,
      nonceLength: nonce.length,
      noncePreview: nonce ? nonce.substring(0, 4) + '...' : 'NONE',
      restRoot: rest,
      isLoggedIn: settings.user || null,
      settingsSource: window.TVS_FAVORITES_SETTINGS ? 'TVS_FAVORITES_SETTINGS' : (window.TVS_SETTINGS ? 'TVS_SETTINGS' : 'none')
    });

    // optimistic toggle
    const active = btn.classList.contains('is-active');
    const nextActive = !active;
    render(btn, nextActive);

    fetch(`${rest}/tvs/v1/favorites/${encodeURIComponent(routeId)}`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-WP-Nonce': nonce }
    }).then(async (r)=>{
      const data = await r.json().catch(()=>({}));
      if(!r.ok){
        const err = new Error((data && data.message) || `HTTP ${r.status}`);
        err.status = r.status;
        throw err;
      }
      const fav = !!data?.favorited;
      render(btn, fav);
      window.dispatchEvent(new CustomEvent('tvs:favorites-updated', { detail: { ids: data?.ids || [] } }));
    }).catch((err)=>{
      // revert on error
      render(btn, active);
      if(err && err.status === 401){
        window.tvsFlash ? window.tvsFlash('Please log in to save favorites','error') : alert('Please log in to save favorites');
      } else {
        console.error('[TVS] Favorite toggle failed:', err);
        window.tvsFlash ? window.tvsFlash('Failed to update favorite','error') : null;
      }
    });
  }

  function render(btn, active){
    btn.classList.toggle('is-active', !!active);
    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    btn.textContent = active ? '★' : '☆';
  }

  document.addEventListener('click', onClick);
})();
