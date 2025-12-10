// TVS Auth UI: handle custom registration form and Strava assisted registration
(function() {
  const settings = window.TVS_SETTINGS || {};
  const rest = settings.restRoot || '/wp-json/';
  const nonce = settings.nonce || '';
  const inviteOnly = !!settings.inviteOnly;
  const recaptchaSiteKey = settings.recaptchaSiteKey || '';

  // Lazy-load reCAPTCHA v3 and get tokens for actions
  let recaptchaReadyPromise = null;
  function ensureRecaptcha(){
    if (!recaptchaSiteKey) return null;
    if (window.grecaptcha && window.grecaptcha.execute) return Promise.resolve();
    if (!recaptchaReadyPromise) {
      recaptchaReadyPromise = new Promise((resolve) => {
        const s = document.createElement('script');
        s.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(recaptchaSiteKey);
        s.async = true; s.defer = true;
        s.onload = () => {
          try { window.grecaptcha && window.grecaptcha.ready(resolve); } catch(e) { resolve(); }
        };
        document.head.appendChild(s);
      });
    }
    return recaptchaReadyPromise;
  }
  async function getRecaptchaToken(action){
    if (!recaptchaSiteKey) return '';
    try {
      await ensureRecaptcha();
      if (!window.grecaptcha || !window.grecaptcha.execute) return '';
      const t = await window.grecaptcha.execute(recaptchaSiteKey, { action: action || 'submit' });
      return t || '';
    } catch { return ''; }
  }

  // Eager-load reCAPTCHA on auth pages so grecaptcha is available as soon as the page renders
  if (recaptchaSiteKey) {
    try { ensureRecaptcha(); } catch(e) {}
  }

  function qs(id){ return document.getElementById(id); }
  function setStatus(el, msg, color){ if (!el) return; el.textContent = msg || ''; if (color) el.style.color = color; }
  function showAlert(el, text, level){
    if (!el) return;
    el.innerHTML = '';
    const div = document.createElement('div');
    div.className = 'tvs-alert ' + (level ? ('tvs-alert--' + level) : '');
    div.textContent = text || '';
    el.appendChild(div);
  }
  function clearAlert(el){ if (el) el.innerHTML = ''; }
  function setFieldError(input, message){
    if (!input || !input.id) return;
    const err = document.getElementById('err_' + input.id);
    if (err) err.textContent = message || '';
  }

  // Global Strava message handler so login and register both react to popup results
  let pendingCode = null;
  window.addEventListener('message', async (ev) => {
    if (!ev || !ev.data || typeof ev.data !== 'object') return;
    if (ev.data.type === 'tvs:strava-connected') {
      location.href = '/user-profile/?strava=ok';
      return;
    }
    if (ev.data.type === 'tvs:strava-code' && ev.data.code) {
      pendingCode = ev.data.code;
      const stravaSection = qs('strava-email-capture');
      if (stravaSection) {
        // Register page: check if this Strava account is already linked
        const statusEl = qs('strava-panel-status') || qs('strava-register-status');
        clearAlert(statusEl); showAlert(statusEl, 'Checking your Strava account…', '');
        try {
          const r = await fetch(rest + 'tvs/v1/auth/strava', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: pendingCode, check_only: true })
          });
          const body = await r.json().catch(()=>null);
          if (r.ok && body && body.linked) {
            const s = qs('strava-panel-status');
            if (s) {
              s.innerHTML = '';
              const wrap = document.createElement('div');
              wrap.className = 'tvs-alert tvs-alert--warning';
              const txt = document.createElement('span');
              txt.textContent = 'This Strava account is already linked to an existing user. Please sign in.';
              const a = document.createElement('a');
              a.href = '/login/';
              a.textContent = 'Login';
              a.className = 'tvs-btn tvs-btn--outline';
              wrap.appendChild(txt);
              wrap.appendChild(a);
              s.appendChild(wrap);
            }
            stravaSection.style.display = 'none';
            return;
          }
          stravaSection.style.display = '';
          clearAlert(statusEl);
        } catch (e) {
          stravaSection.style.display = '';
        }
      } else {
        // Login page: try silent login using athlete_id linkage
        const statusEl = qs('tvs-login-status');
        clearAlert(statusEl); showAlert(statusEl, 'Signing you in with Strava…', '');
        try {
          const r = await fetch(rest + 'tvs/v1/auth/strava', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: pendingCode })
          });
          const body = await r.json().catch(()=>null);
          if (!r.ok) {
            const needEmail = r.status === 400 && (body?.code === 'email_required' || /email required/i.test(body?.message||''));
            if (needEmail) {
              try { sessionStorage.setItem('tvs_strava_code', String(pendingCode)); } catch(e) {}
              location.href = '/register/?via=strava';
              return;
            }
            throw new Error(body && (body.message || body.code) || ('HTTP ' + r.status));
          }
          location.href = '/user-profile/?strava=ok';
        } catch (e) {
          console.error('Silent Strava login failed:', e);
          showAlert(statusEl, 'Strava sign-in failed. Please try again or register.', 'error');
        }
      }
    }
  });

  // Submit custom register form -> /tvs/v1/auth/register
  const form = qs('tvs-register-form');
  if (form) {
    // Live availability checks for username/email
    const u = qs('reg_username');
  const em = qs('reg_email');
  const inv = qs('reg_invite_code');
  const p1 = qs('reg_password');
    const p2 = qs('reg_password2');
    const sHint = document.getElementById('hint_reg_password_strength');
  const mHint = document.getElementById('hint_reg_password_match');
    const f = qs('reg_first_name');
    const l = qs('reg_last_name');
    const statusEl = qs('tvs-register-status');

    // Invite-only gating: hide rest of form and Strava panel until invite validated
    const inviteGroup = qs('tvs-invite-field-group');
    const stravaPanel = qs('tvs-strava-panel');
    let inviteValidated = false;
    let confirmedInvite = '';
    // When an invite is tied to an email, we need to show the email field even while locked
    const emailGroup = em ? (em.closest ? em.closest('.tvs-stack') : em.parentElement) : null;
    let requireEmailForInvite = false;

    function setInviteLock(locked){
      if (!inviteOnly) return;
      // Show invite group only when locked
      if (inviteGroup) inviteGroup.style.display = '';
      // Toggle other form sections
      Array.from(form.children).forEach(ch => {
        if (ch === inviteGroup) return;
        ch.style.display = locked ? 'none' : '';
      });
      // If email is required for the invite, reveal the email field group while locked
      if (locked && requireEmailForInvite && emailGroup) {
        emailGroup.style.display = '';
      }
      // Toggle Strava panel
      if (stravaPanel) stravaPanel.style.display = locked ? 'none' : '';
    }

    async function validateInviteNow(){
      const code = (inv?.value || '').trim();
      const emailVal = (em?.value || '').trim();
      if (!inviteOnly) return true;
      if (!code) {
        inviteValidated = false;
        confirmedInvite = '';
        markInvalid(inv, true, 'Enter invitation code.');
        setInviteLock(true);
        const badge = qs('tvs-invite-validated'); if (badge) badge.style.display = 'none';
        return false;
      }
      try {
        const recaptcha = await getRecaptchaToken('invite_validate');
        const r = await fetch(rest + 'tvs/v1/invites/validate', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
          body: JSON.stringify({ code, email: emailVal || undefined, recaptcha_token: recaptcha || undefined })
        });
        const body = await r.json().catch(()=>null);
        if (!r.ok) {
          inviteValidated = false; confirmedInvite = '';
          if (body?.code === 'invite_used') {
            markInvalid(inv, true, 'This code is already used');
          } else if (body?.code === 'invite_email_required') {
            markInvalid(inv, true, 'This invite is tied to an email. Enter your email first.');
            requireEmailForInvite = true;
          } else if (body?.code === 'invite_email_mismatch') {
            markInvalid(inv, true, 'This invite is tied to a different email.');
            requireEmailForInvite = true;
          } else {
            markInvalid(inv, true, 'Invalid code');
            requireEmailForInvite = false;
          }
          setInviteLock(true);
          if (requireEmailForInvite && em) { try { em.focus(); } catch(e) {} }
          const badge = qs('tvs-invite-validated'); if (badge) badge.style.display = 'none';
          return false;
        }
        // Valid
        inviteValidated = true;
        confirmedInvite = code;
        markInvalid(inv, false, '');
        requireEmailForInvite = false;
        setInviteLock(false);
        const badge = qs('tvs-invite-validated'); if (badge) badge.style.display = '';
        return true;
      } catch {
        // Treat as invalid on network error
        inviteValidated = false; confirmedInvite = '';
        markInvalid(inv, true, 'Could not validate code.');
        requireEmailForInvite = false;
        setInviteLock(true);
        const badge = qs('tvs-invite-validated'); if (badge) badge.style.display = 'none';
        return false;
      }
    }

    if (inviteOnly) {
      if (inviteGroup) inviteGroup.style.display = '';
      setInviteLock(true);
      inv?.addEventListener('blur', validateInviteNow);
      // If user edits after validation, re-lock until re-validated
      inv?.addEventListener('input', () => {
        if (!inviteOnly) return;
        const val = (inv.value || '').trim();
        if (val !== confirmedInvite) {
          inviteValidated = false;
          // Reset email requirement flag until we re-validate
          requireEmailForInvite = false;
          setInviteLock(true);
        }
      });
    } else {
      if (inviteGroup) inviteGroup.style.display = 'none';
    }

    const regTerms = qs('reg_accept_terms');
    const regSubmit = form.querySelector('button[type="submit"]');
    function updateRegButton(){ if (regSubmit) regSubmit.disabled = !(regTerms && regTerms.checked); }
    regTerms?.addEventListener('change', updateRegButton);
    updateRegButton();

    async function checkAvailability(type, value){
      try {
        const url = new URL((rest || '/wp-json/') + 'tvs/v1/auth/check');
  if (type === 'username') url.searchParams.set('username', value);
  if (type === 'email') url.searchParams.set('email', value);
        const r = await fetch(url.toString(), { headers: { 'X-WP-Nonce': nonce } });
        const body = await r.json().catch(()=>({}));
        return body;
      } catch { return {}; }
    }

    function markInvalid(input, invalid, message){
      if (!input) return;
      input.classList.toggle('tvs-input-error', !!invalid);
      setFieldError(input, invalid ? (message || '') : '');
    }

    u?.addEventListener('blur', async () => {
      const val = (u.value||'').trim();
      if (!val) return;
      const res = await checkAvailability('username', val);
      const exists = !!res?.username?.exists;
      markInvalid(u, exists, exists ? 'That username is taken.' : '');
    });
    em?.addEventListener('blur', async () => {
      const val = (em.value||'').trim();
      if (!val) return;
      const res = await checkAvailability('email', val);
      const exists = !!res?.email?.exists;
      markInvalid(em, exists, exists ? 'An account with this email already exists.' : '');
      // If invite validation depends on email, re-validate after email blur
      if (inviteOnly && inv && (inv.value||'').trim()) {
        await validateInviteNow();
      }
    });

    function validPassword(pass){
      if (!pass || pass.length < 10) return false;
      if (!/[a-z]/.test(pass)) return false;
      if (!/[A-Z]/.test(pass)) return false;
      if (!/[^a-zA-Z0-9]/.test(pass)) return false;
      return true;
    }

    function passwordStrength(pass){
      let score = 0;
      if (!pass) return { score, label: '', cls: '', details: '' };
      const len = pass.length;
      const hasLower = /[a-z]/.test(pass);
      const hasUpper = /[A-Z]/.test(pass);
      const hasSpec  = /[^a-zA-Z0-9]/.test(pass);
      const hasDigit = /\d/.test(pass);
      if (len >= 10) score++;
      if (hasLower) score++;
      if (hasUpper) score++;
      if (hasSpec || hasDigit) score++;
      const missing = [];
      if (len < 10) missing.push((10 - len) + ' more character' + ((10-len)===1?'':'s'));
      if (!hasLower) missing.push('lowercase');
      if (!hasUpper) missing.push('uppercase');
      if (!hasSpec) missing.push('special');
      const details = missing.length ? ('Add ' + missing.join(', ')) : 'All requirements met';
      if (score <= 1) return { score, label: 'Weak', cls: 'is-weak', details };
      if (score === 2 || score === 3) return { score, label: 'Medium', cls: 'is-medium', details };
      return { score, label: 'Strong', cls: 'is-strong', details };
    }

    function updateStrength(){
      if (!sHint) return;
      const res = passwordStrength(p1?.value || '');
      sHint.classList.remove('is-weak','is-medium','is-strong');
      if (res?.cls) sHint.classList.add(res.cls);
      sHint.textContent = res?.label ? ('Strength: ' + res.label + (res?.details ? ' — ' + res.details : '')) : '';
    }

    function updateMatch(){
      if (!mHint) return;
      const a = p1?.value || '';
      const b = p2?.value || '';
      mHint.classList.remove('is-ok','is-bad');
      if (!b) { mHint.textContent = ''; return; }
      if (a && b && a === b) { mHint.textContent = 'Passwords match'; mHint.classList.add('is-ok'); }
      else { mHint.textContent = 'Passwords do not match'; mHint.classList.add('is-bad'); }
    }

    p1?.addEventListener('input', () => { updateStrength(); updateMatch(); });
    p2?.addEventListener('input', () => { updateMatch(); });
    // Initialize hints (empty)
    updateStrength(); updateMatch();

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (inviteOnly) {
        const ok = await validateInviteNow();
        if (!ok) return;
      }
      clearAlert(statusEl); showAlert(statusEl, 'Creating account…', '');
      const data = {
        username: (u?.value || '').trim(),
        email: (em?.value || '').trim(),
        password: p1?.value || '',
        first_name: (f?.value || '').trim(),
        last_name: (l?.value || '').trim(),
        accept_terms: !!qs('reg_accept_terms')?.checked,
        newsletter: !!qs('reg_newsletter')?.checked,
      };
      // Client-side validations
  let hasError = false;
  // clear previous errors
  [u, em, f, l, p1, p2, inv].forEach(inp => { if (inp) { inp.classList.remove('tvs-input-error'); setFieldError(inp, ''); } });
  if (!data.username) { markInvalid(u, true, 'Username is required.'); hasError = true; }
  if (!data.email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(data.email)) { markInvalid(em, true, 'Enter a valid email.'); hasError = true; }
  if (!data.first_name) { markInvalid(f, true, 'First name is required.'); hasError = true; }
  if (!data.last_name) { markInvalid(l, true, 'Last name is required.'); hasError = true; }
  if (!validPassword(data.password)) { markInvalid(p1, true, 'Password must be at least 10 chars incl. lowercase, UPPERCASE and special.'); hasError = true; }
  if (!p2?.value || p2.value !== data.password) { markInvalid(p2, true, 'Passwords do not match.'); hasError = true; }
  if (!data.accept_terms) { showAlert(statusEl, 'You must agree to the Terms and Privacy Policy.', 'warning'); hasError = true; }
  if (hasError) { (u && u.classList.contains('tvs-input-error')) ? u.focus() : (em && em.classList.contains('tvs-input-error')) ? em.focus() : (p1 && p1.classList.contains('tvs-input-error')) ? p1.focus() : (p2 && p2.classList.contains('tvs-input-error')) ? p2.focus() : (f && f.classList.contains('tvs-input-error')) ? f.focus() : (l && l.classList.contains('tvs-input-error')) ? l.focus() : null; return; }

      try {
        const recaptcha = await getRecaptchaToken('register');
        const r = await fetch(rest + 'tvs/v1/auth/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
          body: JSON.stringify({
            ...data,
            invite_code: (inv?.value || '').trim() || undefined,
            recaptcha_token: recaptcha || undefined,
          })
        });
        const body = await r.json().catch(()=>null);
        if (!r.ok) {
          // Try to surface server-side validation per field
          const code = body?.code || '';
          if (code === 'username_exists') markInvalid(u, true, 'That username is taken.');
          if (code === 'email_exists') markInvalid(em, true, 'An account with this email already exists.');
          if (code === 'weak_password') markInvalid(p1, true, 'Password rule not met.');
          if (code === 'name_required') { markInvalid(f, true, 'First name is required.'); markInvalid(l, true, 'Last name is required.'); }
          if (code === 'invite_required' || code === 'invite_invalid') {
            markInvalid(inv, true, code === 'invite_required' ? 'Invitation code is required.' : 'Invalid or already used invitation code.');
          }
          showAlert(statusEl, body && (body.message || code) || 'Could not create account', 'error');
          return;
        }
        showAlert(statusEl, 'Account created! Signing you in…', 'success');
        // Check for redirect parameter (from protected pages)
        const urlParams = new URLSearchParams(window.location.search);
        const redirect = urlParams.get('redirect') || '/user-profile/';
        setTimeout(()=>{ location.href = redirect; }, 400);
      } catch (err) {
        console.error('Register failed', err);
        showAlert(statusEl, 'Could not create account: ' + (err?.message || 'Unknown error'), 'error');
      }
    });
  }

  // Strava assisted registration (email capture)
  const stravaSection = qs('strava-email-capture');
  const completeBtn = qs('strava_complete_register');
  if (stravaSection && completeBtn) {
    const stravaTerms = qs('strava_accept_terms');
    function updateStravaButton(){ completeBtn.disabled = !(stravaTerms && stravaTerms.checked); }
    stravaTerms?.addEventListener('change', updateStravaButton);
    updateStravaButton();
    // Check if we came from login with a stored code
    try {
      const stored = sessionStorage.getItem('tvs_strava_code');
      if (stored) {
        pendingCode = stored;
        // Before showing capture, verify if this code maps to an already-linked account
        (async () => {
          try {
            const r = await fetch(rest + 'tvs/v1/auth/strava', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ code: pendingCode, check_only: true })
            });
            const body = await r.json().catch(()=>null);
            if (r.ok && body && body.linked) {
              const s = qs('strava-panel-status') || qs('strava-register-status');
              if (s) {
                s.innerHTML = '';
                const wrap = document.createElement('div');
                wrap.className = 'tvs-alert tvs-alert--warning';
                const txt = document.createElement('span');
                txt.textContent = 'This Strava account is already linked to an existing user. Please sign in.';
                const a = document.createElement('a');
                a.href = '/login/';
                a.textContent = 'Login';
                a.className = 'tvs-btn tvs-btn--outline';
                wrap.appendChild(txt);
                wrap.appendChild(a);
                s.appendChild(wrap);
              }
              stravaSection.style.display = 'none';
            } else {
              stravaSection.style.display = '';
              updateStravaButton();
            }
          } catch(e) {
            stravaSection.style.display = '';
          }
        })();
        sessionStorage.removeItem('tvs_strava_code');
      }
    } catch(e) {}

    completeBtn.addEventListener('click', async () => {
      const email = (qs('strava_email')?.value || '').trim();
      const invite = (qs('strava_invite_code')?.value || '').trim();
      const accept = !!qs('strava_accept_terms')?.checked;
      const news = !!qs('strava_newsletter')?.checked;
      const statusEl = qs('strava-register-status');
      if (!pendingCode) {
        showAlert(statusEl, 'No Strava code. Click “Connect with Strava” first.', 'warning');
        return;
      }
      if (!accept) {
        showAlert(statusEl, 'You must agree to the Terms and Privacy Policy.', 'warning');
        return;
      }
      if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
        showAlert(statusEl, 'Enter a valid email address.', 'warning');
        return;
      }
      try {
        clearAlert(statusEl); showAlert(statusEl, 'Completing registration…', '');
        const recaptcha = await getRecaptchaToken('strava_register');
        const r = await fetch(rest + 'tvs/v1/auth/strava', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ code: pendingCode, email, accept_terms: true, newsletter: news, invite_code: invite || undefined, recaptcha_token: recaptcha || undefined })
        });
        const body = await r.json().catch(()=>null);
        if (!r.ok) {
          if (body?.code === 'invite_required') { showAlert(statusEl, 'Invitation code is required.', 'warning'); return; }
          if (body?.code === 'invite_invalid') { showAlert(statusEl, 'Invalid or already used invitation code.', 'error'); return; }
          throw new Error(body && (body.message || body.code) || ('HTTP ' + r.status));
        }
        showAlert(statusEl, 'All set! Signing you in…', 'success');
        // Check for redirect parameter (from protected pages)
        const urlParams = new URLSearchParams(window.location.search);
        const redirect = urlParams.get('redirect') || '/user-profile/';
        const redirectUrl = redirect.includes('?') ? redirect + '&strava=ok' : redirect + '?strava=ok';
        setTimeout(()=>{ location.href = redirectUrl; }, 400);
      } catch (err) {
        console.error('Strava register failed', err);
        showAlert(statusEl, 'Could not complete: ' + (err?.message || 'Unknown error'), 'error');
      }
    });
  }

  // Custom login form -> /tvs/v1/auth/login
  const loginForm = qs('tvs-login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const statusEl = qs('tvs-login-status');
      clearAlert(statusEl); showAlert(statusEl, 'Signing in…', '');
      const username = (qs('login_username')?.value || '').trim();
      const password = qs('login_password')?.value || '';
      if (!username || !password) {
        showAlert(statusEl, 'Enter username and password.', 'warning');
        return;
      }
      try {
        const r = await fetch(rest + 'tvs/v1/auth/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password })
        });
        const body = await r.json().catch(()=>null);
        if (!r.ok) throw new Error(body && (body.message || body.code) || ('HTTP ' + r.status));
        showAlert(statusEl, 'Signed in! Redirecting…', 'success');
        // Check for redirect parameter (from protected pages)
        const urlParams = new URLSearchParams(window.location.search);
        const redirect = urlParams.get('redirect') || '/user-profile/';
        setTimeout(()=>{ location.href = redirect; }, 300);
      } catch (err) {
        console.error('Login failed', err);
        showAlert(statusEl, 'Incorrect username or password.', 'error');
      }
    });
  }
})();
