// Auto-logout on /logout/ page
(function() {
  console.log('Logout script loaded');
  console.log('TVS_SETTINGS:', typeof TVS_SETTINGS, TVS_SETTINGS);
  
  if (typeof TVS_SETTINGS === 'undefined') {
    console.error('TVS_SETTINGS not found, redirecting anyway');
    setTimeout(() => {
      window.location.href = '/';
    }, 500);
    return;
  }

  const rest = TVS_SETTINGS.restRoot;
  console.log('Calling logout endpoint:', rest + 'tvs/v1/auth/logout');

  // Call logout endpoint
  fetch(rest + 'tvs/v1/auth/logout', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' }
  })
    .then((response) => {
      console.log('Logout response:', response);
      return response.json();
    })
    .then((data) => {
      console.log('Logout success:', data);
      // Redirect to home after logout
      setTimeout(() => {
        window.location.href = '/';
      }, 500);
    })
    .catch((err) => {
      console.error('Logout failed', err);
      // Redirect anyway
      setTimeout(() => {
        window.location.href = '/';
      }, 500);
    });
})();
