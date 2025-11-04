<!--
TVS Virtual Sports – PR Template
Fyll ut relevant – slett seksjoner som ikke er relevante.
-->

## Hva og hvorfor
Kort beskrivelse av endringen(e) og hvorfor de trengs (1–3 linjer).

**Lenker til saker**
Fixes #123
Relates to #456

**Type**
- [ ] feat (ny funksjon)
- [ ] fix (bugfix)
- [ ] chore (vedlikehold)
- [ ] perf (ytelse)
- [ ] docs (dokumentasjon)
- [ ] test (tester)
- [ ] refactor (omstrukturering uten funksjonsendring)

---

## Scope og påvirkning
**Berørte deler**
- [ ] Plugin (tvs-virtual-sports)
- [ ] Tema (tvs-theme)
- [ ] REST API
- [ ] Infrastruktur/Docker/Tunnel
- [ ] CI/Release

**Nye/endrede datafelt**
- Options: 
- User meta: 
- Post meta: 
- WP-cron/hook: 

**Nye/endrede REST-endepunkter**
- Metode + path: 
- Request body/query: 
- Response (shape): 
- Eksempel(JSON): 

**Miljø/secrets**
- [ ] STRAVA_CLIENT_ID / STRAVA_CLIENT_SECRET
- [ ] Andre (beskriv):
> Påpek hvis dev/stage/prod trenger nye verdier eller redirect-URLer.

---

## Hvordan teste (manuelt)
1. …
2. …
3. …

<details>
<summary>Demo-skript (copy-paste)</summary>

```bash
# Lokal WP opp
docker compose up -d
# (Valgfritt) seed testdata
docker compose run --rm cli wp eval-file scripts/seed.php
# Åpne rutevisning
open http://localhost:8080/?p=123
```
</details>

---

## Skjermbilder / GIF
_Beskriv endringen visuelt for reviewer._  
(legg inn før/etter, gjerne med DevOverlay i hjørnet for state/REST)

---

## Sjekkliste – forfatter
- [ ] Tittel følger `feat|fix|chore: …`
- [ ] Linter/PHPCS (om satt opp) er grønn
- [ ] PHPUnit (om satt opp) kjørt lokalt
- [ ] Ingen hemmeligheter sjekket inn
- [ ] WP-sikkerhet: capabilities, nonce/CSRF, sanitizing/escaping
- [ ] I18n: `__()/_x()` med `tvs-virtual-sports`
- [ ] A11y: ARIA/labels/fokus/tastatur
- [ ] Ytelse: caching/transients + invalidasjon vurdert/lagt til
- [ ] Dokumentasjon/kommentarer på nye hooks/filtre
- [ ] Changelog-forslag lagt under (Added/Changed/Fixed/Performance/Security)
- [ ] Rollback-plan beskrevet nedenfor

---

## Sikkerhet
- Angrepsflate (kort): 
- Hvem kan kalle endepunkt/feature (caps/roles): 
- Input validering/sanitering: 
- Nonce/CSRF for skrivende kall: 
- Sensitive felt i respons/logg: 

---

## A11y (frontend)
- [ ] Tastaturnavigasjon OK
- [ ] Fokusstyring ved modaler/overlays
- [ ] Skjermleser-tekster/labels
- [ ] Live regioner for status (laster/feil/suksess)
- [ ] Kontrast/fargebruk (ingen informasjon kun i farge)

---

## Ytelse
- [ ] Unngå N+1 spørringer / tunge loops
- [ ] Cache/transients på lister
- [ ] Ryddet event-lyttere / ingen memory leaks

---

## Endringslogg (forslag)
**Added:**  
- …

**Changed:**  
- …

**Fixed:**  
- …

**Performance:**  
- …

**Security:**  
- …

---

## Risiko og rollback
- Risikoer: 
- Feature flag / toggle: 
- Hvordan rulle tilbake raskt: 
- Migrasjon (om noen) kan reverseres: 

---

## Reviewer-sjekk (kan hukes av under review)
- [ ] WP-sikkerhet: caps/nonce/escaping ok
- [ ] REST: riktige statuskoder/feiltekster; ingen sensitive felt
- [ ] Arkitektur: ingen dobbeltregistrering av scripts (tema vs plugin)
- [ ] A11y: OK på tastatur og skjermleser
- [ ] Ytelse: cache/invalidasjon for lister, ingen åpenbare N+1
- [ ] I18n: all tekst oversettbar (`tvs-virtual-sports`)
- [ ] Dokumentasjon: PR beskriver teststeg, ev. migrasjoner og miljøendringer
