(function(){
  function onClick(e){
    const btn = e.target.closest('.tvs-bookmark-btn');
    if(!btn) return;
    e.preventDefault();
    e.stopPropagation();
    const routeId = btn.getAttribute('data-route');
    if(!routeId){ return; }
    if(!window.TVS_SETTINGS || !window.TVS_SETTINGS.restRoot){ window.TVS_SETTINGS = window.TVS_SETTINGS || {}; TVS_SETTINGS.restRoot = '/wp-json/'; }
    const rest = TVS_SETTINGS.restRoot.replace(/\/$/, '');
    const nonce = TVS_SETTINGS.nonce || '';

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
