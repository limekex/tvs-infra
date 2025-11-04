# PR-sjekkliste (forfatter + reviewer)

## Forfatter – før du oppretter PR
- [ ] **Tittel** følger mønsteret: `feat|fix|chore: kort beskrivelse` (gjerne Conventional Commits).
- [ ] **Scope avklart**: lenker til issues (`Fixes #123`, `Relates to #456`).
- [ ] **Endepunkter**: REST-endringer dokumentert (metode, path, req/resp + eksempler).
- [ ] **WP-sikkerhet**: capabilities sjekket, nonce/CSRF på POST/PUT/DELETE, sanitizing/escaping, ingen sensitive secrets i kode.
- [ ] **Dataendringer**: nye `option`/`user_meta`/`post_meta`-nøkler dokumentert; migrasjon (om nødvendig).
- [ ] **I18n**: bruker `__()/_x()` med text-domain `tvs-virtual-sports`; ingen hardkodet tekst i PHP/JS uten oversettelse.
- [ ] **Frontend a11y**: ARIA-roller, fokusrekkefølge, tastaturnavigasjon, synlig fokus, labels, `aria-live` for dynamisk status.
- [ ] **Ytelse**: cache/transients på listeendepunkt; invalidasjon bekreftet; ingen unødvendig DOM/JS work.
- [ ] **DevOverlay**: viser relevante felter uten å lekke secrets; toggles kun i dev (`?tvsdebug=1`/backtick).
- [ ] **Build/QA**: lokalt testet `phpunit` (om finnes), manuell klikkrunde (beskrevet i PR-en).
- [ ] **Screenshots/GIF** for UI-endringer + korte demo-trinn.
- [ ] **Changelog**: foreslåtte linjer (Added/Changed/Fixed/Performance/Security).
- [ ] **Rollback-plan**: hvordan disable feature/feature flag eller revert uten datatap.

## Reviewer – under review
- [ ] Koden følger WP-standard (esc_html/attr/url, nonce, caps), ingen direkte `$_POST/$_GET` uten sanitering.
- [ ] REST: korrekte statuskoder, tydelige feilmeldinger; ingen private data i responses.
- [ ] Testbarhet: enkle manuelle steg, gjerne feature flag for ny funksjon.
- [ ] A11y: kan brukes med tastatur; komponenter har ARIA/labels.
- [ ] Ytelse: ingen N+1 WP_Query, caching på lister der det gir mening.
- [ ] I18n: all visningstekst er oversettbar med riktig text-domain.
- [ ] Arkitektur: ingen dobbeltregistrering av scripts (tema vs plugin); klar eierskap til mount-punkt.
- [ ] Dokumentasjon: PR beskriver endringer, migrasjoner og ev. miljøvariabler; changelog-forslag ser greie ut.
- [ ] Risiko: identifisert; rollback/migrasjon beskrives for større endringer.
