// Strava OAuth callback handler for /strava/connected
(function() {
  const p = new URLSearchParams(location.search);
  let code = p.get('code');
  const scope = p.get('scope') || '';
  const status = document.getElementById('strava-status');
  // Fallback: sometimes providers append params in hash fragment
  if (!code && location.hash && location.hash.length > 1) {
    const h = new URLSearchParams(location.hash.slice(1));
    code = h.get('code') || null;
  }
  
  // If no code, check if user is already connected via REST status
  if (!code) {
    if (typeof TVS_SETTINGS !== 'undefined' && TVS_SETTINGS.restRoot) {
      fetch(`${TVS_SETTINGS.restRoot}tvs/v1/strava/status`, {
        headers: { 'X-WP-Nonce': TVS_SETTINGS.nonce }
      })
        .then(r => r.json())
        .then(data => {
          if (data && data.connected) {
            if (status) status.innerHTML = `<p style="color:green;">✓ Du er allerede koblet til Strava som <strong>${data.athlete_name || 'atlet'}</strong>.</p><p><a href="/min-profil/">Gå til Min Profil</a></p>`;
          } else if (data && data.revoked) {
            // Token was revoked on Strava
            if (status) status.innerHTML = `<p style="color:orange;">⚠️ Din Strava-tilkobling har utløpt eller blitt trukket tilbake. Klikk "Connect with Strava" nedenfor for å koble til på nytt.</p>`;
          } else {
            if (status) status.innerHTML = `<p>Ingen Strava-kode funnet. Klikk "Connect with Strava" nedenfor for å koble til.</p>`;
          }
        })
        .catch(() => {
          if (status) status.innerHTML = `<p>Ingen Strava-kode funnet. Klikk "Connect with Strava" nedenfor for å koble til.</p>`;
        });
    } else {
      if (status) status.innerHTML = `<p>Ingen Strava-kode funnet. Klikk "Connect with Strava" nedenfor for å koble til.</p>`;
    }
    return;
  }
  if (typeof TVS_SETTINGS === 'undefined') {
    if (status) status.innerHTML = '<p>Konfigurasjon mangler. Prøv igjen.</p>';
    return;
  }
  
  // Prevent duplicate submissions - set flag immediately
  const storageKey = 'tvs_strava_connecting';
  if (sessionStorage.getItem(storageKey) === code) {
    console.log('Strava connect already in progress for this code, skipping duplicate');
    return;
  }
  sessionStorage.setItem(storageKey, code);
  
  status.innerHTML = '<p>Kobler til Strava ...</p>';
  fetch(`${TVS_SETTINGS.restRoot}tvs/v1/strava/connect`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': TVS_SETTINGS.nonce
    },
    body: JSON.stringify({ code, scope })
  })
    .then(async r => {
      const data = await r.json().catch(() => null);
      if (!r.ok) {
        const msg = data && (data.message || data.code) ? `${data.message} (${data.code})` : `HTTP ${r.status}`;
        throw new Error(msg);
      }
      return data;
    })
    .then(() => {
      sessionStorage.removeItem(storageKey);
      status.innerHTML = '<p>Strava tilkoblet! Sender deg videre ...</p>';
      // Clean URL before redirect to prevent re-use
      if (window.history && window.history.replaceState) {
        window.history.replaceState({}, document.title, location.pathname);
      }
      setTimeout(() => {
        location.href = '/min-profil/?strava=ok';
      }, 500);
    })
    .catch((err) => {
      sessionStorage.removeItem(storageKey);
      console.error('Strava connect error:', err);
      const msg = err && err.message ? err.message : 'Ukjent feil';
      status.innerHTML = `<p>Strava-tilkobling feilet: ${msg}. <button onclick="location.reload()">Prøv igjen</button></p>`;
    });
})();
