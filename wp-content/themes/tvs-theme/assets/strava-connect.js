// Strava OAuth callback handler for /strava/connected
(function() {
  const p = new URLSearchParams(location.search);
  const code = p.get('code');
  const status = document.getElementById('strava-status');
  if (!code) {
    if (status) status.innerHTML = '<p>Mangler Strava-kode. Prøv igjen.</p>';
    return;
  }
  if (typeof TVS_SETTINGS === 'undefined') {
    if (status) status.innerHTML = '<p>Konfigurasjon mangler. Prøv igjen.</p>';
    return;
  }
  status.innerHTML = '<p>Kobler til Strava ...</p>';
  fetch(`${TVS_SETTINGS.restRoot}tvs/v1/strava/connect`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': TVS_SETTINGS.nonce
    },
    body: JSON.stringify({ code })
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
      status.innerHTML = '<p>Strava tilkoblet! Sender deg videre ...</p>';
      location.href = '/min-profil/?strava=ok';
    })
    .catch((err) => {
      console.error('Strava connect error:', err);
      const msg = err && err.message ? err.message : 'Ukjent feil';
      status.innerHTML = `<p>Strava-tilkobling feilet: ${msg}. <button onclick="location.reload()">Prøv igjen</button></p>`;
    });
})();
