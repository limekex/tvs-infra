// Build dynamic "Connect with Strava" button from TVS_SETTINGS
(function() {
  const btnContainer = document.getElementById('strava-connect-btn');
  if (!btnContainer || typeof TVS_SETTINGS === 'undefined') return;

  const clientId = TVS_SETTINGS.stravaClientId;
  const redirectUri = `${TVS_SETTINGS.siteUrl}/connect-strava/?mode=popup`;

  if (!clientId) {
    btnContainer.innerHTML = '<p style="color:#c00;">Missing Strava Client ID. Configure it in WordPress Admin under TVS → Strava.</p>';
    return;
  }

  function openCenteredPopup(url, title, w, h) {
    const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
    const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;
    const width = window.innerWidth || document.documentElement.clientWidth || screen.width;
    const height = window.innerHeight || document.documentElement.clientHeight || screen.height;
    const left = (width - w) / 2 + dualScreenLeft;
    const top = (height - h) / 2 + dualScreenTop;
    const features = `scrollbars=yes, width=${w}, height=${h}, top=${top}, left=${left}`;
    return window.open(url, title, features);
  }

  function renderButton(label, url) {
    btnContainer.innerHTML = '';
    const a = document.createElement('a');
    a.href = url;
    a.className = 'tvs-strava-img-btn';
    a.setAttribute('aria-label', label);
    const imgUrl = (TVS_SETTINGS && TVS_SETTINGS.stravaButtonImage) ? String(TVS_SETTINGS.stravaButtonImage) : '';
    if (imgUrl) {
      const img = document.createElement('img');
      img.src = imgUrl;
      img.alt = label;
      img.decoding = 'async';
      img.loading = 'lazy';
      img.style.maxWidth = '100%';
      img.style.height = 'auto';
      a.appendChild(img);
    } else {
      // Fallback text if image URL is missing
      a.textContent = label;
      try { console.warn('TVS: Missing TVS_SETTINGS.stravaButtonImage; rendering text fallback.'); } catch(e) {}
    }
    a.addEventListener('click', (e) => {
      e.preventDefault();
      openCenteredPopup(url, 'StravaAuth', 560, 740);
    });
    btnContainer.appendChild(a);
  }

  // Check connection status to adjust button text
  fetch(`${TVS_SETTINGS.restRoot}tvs/v1/strava/status`, {
    headers: { 'X-WP-Nonce': TVS_SETTINGS.nonce }
  })
    .then(r => r.json())
    .then(data => {
      const isConnected = data && data.connected;
      const buttonText = isConnected ? 'Reconnect to Strava' : 'Connect with Strava';
      const scopes = [
        'read', 'read_all', 'profile:read_all', 'activity:read_all', 'activity:write'
      ].join(',');
      const authorizeUrl = `https://www.strava.com/oauth/authorize?client_id=${encodeURIComponent(clientId)}&response_type=code&redirect_uri=${encodeURIComponent(redirectUri)}&approval_prompt=force&scope=${encodeURIComponent(scopes)}`;
      renderButton(buttonText, authorizeUrl);
    })
    .catch(() => {
      const scopes = [
        'read', 'read_all', 'profile:read_all', 'activity:read_all', 'activity:write'
      ].join(',');
      const authorizeUrl = `https://www.strava.com/oauth/authorize?client_id=${encodeURIComponent(clientId)}&response_type=code&redirect_uri=${encodeURIComponent(redirectUri)}&approval_prompt=force&scope=${encodeURIComponent(scopes)}`;
      renderButton('Connect with Strava', authorizeUrl);
    });
})();
