// Build dynamic "Connect with Strava" button from TVS_SETTINGS
(function() {
  const btnContainer = document.getElementById('strava-connect-btn');
  if (!btnContainer || typeof TVS_SETTINGS === 'undefined') return;
  
  const clientId = TVS_SETTINGS.stravaClientId;
  const redirectUri = `${TVS_SETTINGS.siteUrl}/connect-strava/`;
  
  if (!clientId) {
    btnContainer.innerHTML = '<p style="color:#c00;">Strava client ID mangler. Konfigurer dette i WordPress admin under TVS → Strava.</p>';
    return;
  }
  
  // Check connection status to adjust button text
  fetch(`${TVS_SETTINGS.restRoot}tvs/v1/strava/status`, {
    headers: { 'X-WP-Nonce': TVS_SETTINGS.nonce }
  })
    .then(r => r.json())
    .then(data => {
      const isConnected = data && data.connected;
      const buttonText = isConnected ? 'Reconnect to Strava' : 'Connect with Strava';
      
      // Request scopes: activity:write for upload, activity:read_all for updating private activities
      const authorizeUrl = `https://www.strava.com/oauth/authorize?client_id=${encodeURIComponent(clientId)}&response_type=code&redirect_uri=${encodeURIComponent(redirectUri)}&approval_prompt=auto&scope=read,activity:read_all,activity:write`;
      
      btnContainer.innerHTML = `<a href="${authorizeUrl}" class="button" style="background:#fc4c02;color:#fff;padding:1em 2em;border-radius:4px;text-decoration:none;font-weight:bold;display:inline-block;">${buttonText}</a>`;
    })
    .catch(() => {
      // Fallback if status check fails
      const authorizeUrl = `https://www.strava.com/oauth/authorize?client_id=${encodeURIComponent(clientId)}&response_type=code&redirect_uri=${encodeURIComponent(redirectUri)}&approval_prompt=auto&scope=read,activity:read_all,activity:write`;
      
      btnContainer.innerHTML = `<a href="${authorizeUrl}" class="button" style="background:#fc4c02;color:#fff;padding:1em 2em;border-radius:4px;text-decoration:none;font-weight:bold;display:inline-block;">Connect with Strava</a>`;
    });
})();
