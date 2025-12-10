<?php
/**
 * Server-render for Connect Strava block
 */

if ( ! is_user_logged_in() ) {
    $login    = home_url( '/login' );
    $register = home_url( '/register' );
    echo '<div class="tvs-connect-strava tvs-connect-strava--loggedout">';
    echo '<p class="tvs-connect-strava__state">' . esc_html__( 'You need an account to connect Strava.', 'tvs-virtual-sports' ) . '</p>';
    echo '<div class="tvs-connect-strava__actions">';
    echo '<a class="tvs-btn tvs-btn--outline" href="' . esc_url( $login ) . '">' . esc_html__( 'Log in', 'tvs-virtual-sports' ) . '</a>';
        if ( get_option( 'users_can_register' ) ) {
            echo '<a class="tvs-btn tvs-btn--outline" href="' . esc_url( $register ) . '">' . esc_html__( 'Register', 'tvs-virtual-sports' ) . '</a>';
        }
    echo '</div>';
    echo '</div>';
    return;
}

$user_id   = get_current_user_id();
$tokens    = get_user_meta( $user_id, 'tvs_strava', true );
$connected = ! empty( $tokens ) && ! empty( $tokens['access'] );
$connect_url = home_url( '/connect-strava' );
$client_id   = get_option( 'tvs_strava_client_id', '' );
$site_url    = home_url( '/' );

$nonce = wp_create_nonce( 'wp_rest' );
?>
<div class="tvs-connect-strava" data-rest-root="<?php echo esc_attr( get_rest_url() ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-client-id="<?php echo esc_attr( $client_id ); ?>" data-site-url="<?php echo esc_attr( $site_url ); ?>">
    <?php if ( $connected ) : ?>
        <?php
            $athlete_name = '';
            if ( is_array( $tokens ) && ! empty( $tokens['athlete'] ) && is_array( $tokens['athlete'] ) ) {
                $a = $tokens['athlete'];
                $first = isset( $a['firstname'] ) ? trim( (string) $a['firstname'] ) : '';
                $last  = isset( $a['lastname'] ) ? trim( (string) $a['lastname'] ) : '';
                $usern = isset( $a['username'] ) ? trim( (string) $a['username'] ) : '';
                $athlete_name = trim( ($first . ' ' . $last) );
                if ( $athlete_name === '' ) $athlete_name = $usern;
            }
            if ( $athlete_name === '' ) {
                $u = wp_get_current_user();
                $athlete_name = $u ? $u->display_name : '';
            }
        ?>
    <h3 class="tvs-connect-strava__heading"><?php echo esc_html( sprintf( __( 'Connected to Strava as %s', 'tvs-virtual-sports' ), $athlete_name ) ); ?></h3>
        <div class="tvs-connect-strava__actions">
            <button type="button" class="tvs-strava-btn" data-tvs-disconnect><?php echo esc_html__( 'Disconnect Strava', 'tvs-virtual-sports' ); ?></button>
        </div>
    <?php else : ?>
    <p class="tvs-connect-strava__state"><?php echo esc_html__( 'Not connected to Strava', 'tvs-virtual-sports' ); ?></p>
        <div class="tvs-connect-strava__actions">
            <a class="tvs-strava-btn" href="#" data-tvs-connect aria-label="<?php echo esc_attr__( 'Connect with Strava', 'tvs-virtual-sports' ); ?>">
                <span class="tvs-strava-btn__icon" aria-hidden="true"></span>
                <span class="tvs-strava-btn__text"><?php echo esc_html__( 'Connect with Strava', 'tvs-virtual-sports' ); ?></span>
            </a>
        </div>
    <?php endif; ?>
</div>
<script>
(function(){
    const wrap = document.currentScript && document.currentScript.previousElementSibling;
    if (!wrap || !wrap.classList.contains('tvs-connect-strava')) return;
    const rest = wrap.getAttribute('data-rest-root');
    const nonce = wrap.getAttribute('data-nonce');
    const btnDisc = wrap.querySelector('[data-tvs-disconnect]');
    const btnConn = wrap.querySelector('[data-tvs-connect]');
    const clientId = wrap.getAttribute('data-client-id') || '';
    const siteUrl = (wrap.getAttribute('data-site-url') || '').replace(/\/$/, '');
    if (btnDisc){
        btnDisc.addEventListener('click', async ()=>{
            try{
                const r = await fetch(rest + 'tvs/v1/strava/disconnect', { method:'POST', headers: { 'X-WP-Nonce': nonce } });
                if (r.ok){ location.reload(); } else { alert('Disconnect failed ('+r.status+')'); }
            } catch(e){ alert('Disconnect failed'); }
        });
    }
    if (btnConn && clientId){
        const openPopup = (url)=>{
            const w = 640, h = 780;
            const y = window.top.outerHeight/2 + window.top.screenY - ( h / 2 );
            const x = window.top.outerWidth/2 + window.top.screenX - ( w / 2 );
            return window.open(url, 'strava_auth', `width=${w},height=${h},left=${x},top=${y}`);
        };
        const scopes = ['read','read_all','profile:read_all','activity:read_all','activity:write'].join(',');
        const redirectUri = encodeURIComponent(siteUrl + '/connect-strava/?mode=popup');
        const authorizeUrl = `https://www.strava.com/oauth/authorize?client_id=${encodeURIComponent(clientId)}&response_type=code&redirect_uri=${redirectUri}&approval_prompt=auto&scope=${encodeURIComponent(scopes)}`;
        btnConn.addEventListener('click', (e)=>{
            e.preventDefault();
            const win = openPopup(authorizeUrl);
            if (!win) { location.href = authorizeUrl; return; }
        });
        window.addEventListener('message', (ev)=>{
            if (ev && ev.data && ev.data.type === 'tvs:strava-connected') {
                // Refresh to update block state
                location.reload();
            }
        });
    }
})();
</script>
