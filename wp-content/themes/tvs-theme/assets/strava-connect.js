// Strava OAuth callback handler for /strava/connected
(function() {
  const p = new URLSearchParams(location.search);
  let code = p.get('code');
  const mode = p.get('mode') || '';
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
            if (mode === 'popup' && window.opener) {
              try { window.opener.postMessage({ type: 'tvs:strava-connected' }, window.location.origin); } catch(e) {}
              window.close();
              return;
            }
            if (status) status.innerHTML = `<p style="color:green;">✓ You are already connected to Strava.</p><p><a href="/user-profile/">Go to Profile</a></p>`;
          } else if (data && data.revoked) {
            // Token was revoked on Strava
            if (status) status.innerHTML = `<p style="color:orange;">⚠️ Your Strava connection has expired or was revoked. Click "Connect with Strava" below to reconnect.</p>`;
          } else {
            if (status) status.innerHTML = `<p>No Strava code found. Click "Connect with Strava" below to connect.</p>`;
          }
        })
        .catch(() => {
          if (status) status.innerHTML = `<p>No Strava code found. Click "Connect with Strava" below to connect.</p>`;
        });
    } else {
      if (status) status.innerHTML = `<p>No Strava code found. Click "Connect with Strava" below to connect.</p>`;
    }
    return;
  }
  if (typeof TVS_SETTINGS === 'undefined') {
    if (status) status.innerHTML = '<p>Missing configuration. Please try again.</p>';
    return;
  }
  
  // Prevent duplicate submissions - set flag immediately
  const storageKey = 'tvs_strava_connecting';
  if (sessionStorage.getItem(storageKey) === code) {
    console.log('Strava connect already in progress for this code, skipping duplicate');
    return;
  }
  sessionStorage.setItem(storageKey, code);
  
  status.innerHTML = '<p>Connecting to Strava…</p>';
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
        // If not authenticated, inform opener with the code so it can complete registration
        if (r.status === 401 && window.opener) {
          try { window.opener.postMessage({ type: 'tvs:strava-code', code, scope }, window.location.origin); } catch(e) {}
          window.close();
          return Promise.reject(new Error('Auth required'));
        }
        const msg = data && (data.message || data.code) ? `${data.message} (${data.code})` : `HTTP ${r.status}`;
        throw new Error(msg);
      }
      return data;
    })
    .then(() => {
      sessionStorage.removeItem(storageKey);
      if (mode === 'popup' && window.opener) {
        try { window.opener.postMessage({ type: 'tvs:strava-connected' }, window.location.origin); } catch(e) {}
        window.close();
        return;
      }
  status.innerHTML = '<p>Strava connected! Redirecting…</p>';
      // Clean URL before redirect to prevent re-use
      if (window.history && window.history.replaceState) {
        window.history.replaceState({}, document.title, location.pathname);
      }
      setTimeout(() => {
        location.href = '/user-profile/?strava=ok';
      }, 500);
    })
    .catch((err) => {
      sessionStorage.removeItem(storageKey);
      console.error('Strava connect error:', err);
  const msg = err && err.message ? err.message : 'Unknown error';
  status.innerHTML = `<p>Strava connection failed: ${msg}. <button onclick="location.reload()">Try again</button></p>`;
    });
})();
