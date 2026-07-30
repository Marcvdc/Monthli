# Marktonderzoek Monthli — juli 2026

Onderzoek uitgevoerd 30-07-2026. Alle harde claims zijn adversarieel gecontroleerd tegen de
primaire bron; claims die de controle niet haalden zijn uit dit document verwijderd.
Bronnen met peildatum staan per cijfer vermeld.

## Conclusie

**Oordeel: degraderen tot bevroren portfolio-/referentieproject.**

Er is een markt voor portfolio-tracking in Nederland, maar niet voor het product dat hier
gebouwd wordt. De aangekondigde differentiator — DEGIRO-CSV-import met NL/EN-kolomnamen,
privacy-first, geen broker-credentials — is per juli 2026 gratis verkrijgbaar bij TrackinV en
voor €29 eenmalig bij FolioInsights, dat de privacyclaim zelfs strikt verslaat door alle
verwerking client-side te doen.

## 1. De verkeerde benchmark

getquin is niet de partij waar een Nederlandse DEGIRO-gebruiker tussen kiest. getquin behandelt
Nederland als bijvangst: de app heeft exact twee interfacetalen (EN, DE), de marketingsite heeft
alleen `/en` en `/de`, de 6.894 programmatische SEO-pagina's dekken en/de/fr/it/es zonder
Nederlandse hreflang, en de iOS-beoordelingen staan op 341 voor Nederland tegen 8.003 voor
Duitsland.

De werkelijke concurrentieset is Nederlands:

| Speler | Prijs | Relevantie |
|---|---|---|
| Portfolio Dividend Tracker | €75/jaar (€6,25/mnd), geen gratis tier | NL-marktleider. Bijna 10.000 beleggers NL+BE na vijf jaar, kernteam ~10, bootstrapped, winstgevend, **zelfde PHP/Laravel-stack**. Acht brokers + Google Sheets |
| TrackinV | Gratis tier zonder holdings-limiet; premium vanaf €4/mnd | Importeert DEGIRO `Account.csv`, herkent NL **én** EN kolomnamen, rekent TWR, benchmarkt. Dit is letterlijk Monthli's aangekondigde differentiator, gratis |
| FolioInsights | €29 eenmalig lifetime | DEGIRO `account.csv` volledig client-side in de browser (IndexedDB), **inclusief box 3-schatter**. Data verlaat het apparaat nooit. Gebouwd door één solo-dev |
| Capitalyse | €2,49/mnd premium | Freemium DEGIRO-specifieke tracker: dividend-insights, fees, jaarrapporten, CSV/JSON-export |
| Ghostfolio | USD 48/jaar eenmalig | Categorieleider in exact Monthli's positionering: AGPL-3.0, PostgreSQL+Redis+Docker, self-hostbaar, "privacy and data ownership". 9.025 sterren, 2,9 mln Docker-pulls |
| Portfolio Performance | Gratis | Sinds 2012. IRR **én** True TWR, ECB-FX, Yahoo, CoinGecko, volwaardige DEGIRO-extractor met meertalige testfixtures |

Ghostfolio is het scherpste datapunt: vijf jaar voorsprong in precies deze positionering, en de
eigen API meldt **2.081 actieve cloudgebruikers** over 30 dagen. Dat is het plafond van
"privacy-first self-hostable tracker" als commercieel model.

Donaties zijn geen uitweg: Actual Budget heeft 27.831 sterren bij een Open Collective-jaarbudget
van $43.128; Firefly III 24.171 sterren bij €148,60/maand van 43 patrons.

## 2. Sales-elementen: de vergelijking valt de verkeerde kant op

De vraag was welke sales-elementen Monthli heeft die getquin niet heeft. Het eerlijke antwoord is:
geen. Wat er is, zijn *featureverschillen* — en een featureverschil is geen reden om te wisselen.

| Verschil | T.o.v. wie | Koopargument? |
|---|---|---|
| Nederlandstalig product + NL-support | getquin | **Nee.** Gat bij getquin is hard en echt, maar PDT, TrackinV, FolioInsights en Capitalyse zijn allemaal al Nederlands. Je verslaat een partij die hier niet meedoet — dat is geen positie, dat is een leeg vak |
| Geen broker-credentials, geen accountkoppeling | getquin | **Misschien, ongetest.** Sterkste emotionele argument, first-party gedocumenteerd: meerdere App Store-reviewers melden buitenlandse logins op hun brokeraccount ná koppeling (Madrid, Israël, VS), incl. Trading212-lockout; een NL-reviewer schrijft letterlijk "doe dit niet". Maar Ghostfolio en Portfolio Performance vragen ook geen credentials, getquin biedt zelf handmatige invoer, en FolioInsights verslaat je strikt. In je eigen segment is dit de instapeis |
| DEGIRO-CSV met volledige historie in één keer | getquin | **Nee.** getquin heeft aantoonbaar geen CSV-route (eigen helpartikel biedt alleen Open Banking of handmatig) en NL-reviewers klagen daar expliciet over — "It took me 2 hours to fill everything in manually". Maar TrackinV doet exact dit, gratis |
| Permanent, exporteerbaar maandarchief (PDF) | getquin, Ghostfolio, Portfolio Performance | **Misschien, ongetest.** Enige werkelijk lege terrein in het dossier. getquins Rewind is mobile-only en hun helpcentrum zegt: "It is not possible to access the Rewind of previous months after the end of the month." Ghostfolio: nul functionele PDF-treffers in de broncode. Portfolio Performance: geen geplande rapportage. Natuurlijk abonnementsanker. Maar "ik wil mijn maandrapport bewaren" is nergens als klacht gedocumenteerd — dus hypothese, geen vraag |
| Geen advertenties, affiliates of belangenconflict | getquin | **Nee.** Inhoudelijk vernietigend argument: QUIN Technologies is nu §34f Finanzanlagenvermittler én §34d Versicherungsmakler, mag provisies van uitgevers ontvangen, haalde 19-05-2026 €12M op onder leiding van State Street IM, en integreert State Street-ETF's in het adviesplatform — terwijl de homepage zegt "no one can access your data – not even us" en het helpcentrum "Revenue from ads in the app" als inkomstenbron noemt. Maar particulieren wisselen hier empirisch niet voor: getquin claimt 500.000+ gebruikers mét die advertenties |
| Self-hostable, Apache-2.0, data-eigendom | alle closed SaaS-concurrenten | **Nee.** Ghostfolio is hier de leider en monetiseert ~2.000 mensen. Extra risico: Apache-2.0 is bij een OSS-consumentenstrategie een cadeau aan wie wél distributie heeft — Ghostfolio koos bewust AGPL, en omschakelen kan achteraf niet zonder alle contributors |
| Persoonlijke support door de bouwer | getquin | **Misschien, ongetest.** Gat is reëel ("non existing customer service, just an AI chatbot"). Harde caveat: de repo is het tegenbewijs — nul productcommits in acht maanden. Een supportbelofte naast betaald werk is de belofte die het eerst breekt |

### Wat er aan de andere kant staat

- **Een verkoopmachine, überhaupt.** getquin: 6.894 geïndexeerde URL's (988 aandelen- en 301
  ETF-pagina's × 5 talen), deelbare publieke portfolio-links die niet-gebruikers binnenhalen, een
  affiliate-/influencerprogramma met eigen vacature, en Facebook Pixel, Google Ads, TikTok,
  LinkedIn, Reddit, Bing, Apple Search Ads, financeAds en AppsFlyer. Monthli heeft één route in
  `routes/web.php` die de standaard Laravel-welcome-view rendert.
- **Distributie, en de gratis kanalen zijn dicht.** PDT is gebouwd op Jong Beleggen (ruim 12 mln
  plays, #1 in Apple Podcasts NL Business-Investing, 5,5k ratings à 4,8/5). IEX Media claimt 1,3
  mln unieke particuliere beleggers per maand maar zet "Adverteren" onder "3. Strikt niet
  toegestane activiteiten" met schorsing tot twee maanden. Mr FOB verwijst na tien jaar en 17.000
  nieuwsbriefabonnees naar géén enkele portfolio tracker.
- **Een product dat een vreemde kan gebruiken.** Geen registratie, onboarding, betaalmuur,
  multi-tenancy of marketingsite. Alle functionaliteit zit in een Filament-adminpanel. `ROADMAP.md`
  heeft "Basis auth & users implementation" nog onafgevinkt. Tussen nu en de eerste betalende
  gebruiker zit niet de laatste 20%, maar een compleet SaaS-omhulsel dat nog nul uur heeft gekost.
- **Brokerbreedte.** getquin koppelt via Plaid, Flanks, finAPI en SnapTrade met aparte
  helpartikelen voor twaalf brokers. PDT ondersteunt acht plus Google Sheets. Monthli ondersteunt
  één — en juist de duurzame fiscale wig zit in de optelsom over meerdere brokers.
- **Mobiel.** getquin heeft iOS en Android met 468.002 Play-installaties.
- **Elk bewijs van betalingsbereidheid.** Nul betalende gebruikers, nul pre-orders, nul
  prijstoezeggingen op naam. Daarom kan geen enkele differentiator hierboven het label "ja, mensen
  betalen hiervoor" krijgen.

## 3. Marktomvang en betalingsbereidheid

- 19% van de Nederlandse huishoudens belegde in 2024 (14,3% = 1,2 mln mét voldoende buffer, 5,0% =
  410.000 zonder), 22% inclusief derde pijler, op ~8,37 mln huishoudens.
  *(AFM Occasional Paper op CBS-microdata, april 2026; CBS-tabel 37975)*
- DEGIRO passeerde 1 miljoen klanten in Nederland (2025). flatexDEGIRO: 3,66 mln klantaccounts,
  €108,3 mrd assets under custody per 30-06-2026. *(flatexDEGIRO Q2 2026, 22-07-2026)*
- Uit die 1 miljoen haalt de marktleider PDT na vijf jaar bijna 10.000 gebruikers in NL+BE — met
  de #1-beleggingspodcast van de Benelux en een team van tien. **De betaalbereidheid in deze niche
  is smal.**
- SEO als kanaal is verslechterd: aanwezigheid van een AI Overview correleert met 58% lagere CTR op
  positie 1; positie-1-CTR zakte van 0,073 naar 0,016 (300.000 keywords, data dec 2025).
  BerekenHet.nl bezet het NL-rekentoolterrein al: 26 jaar, 314 tools, >25 mln berekeningen/jaar,
  mét een gratis box 3-tegenbewijscheck sinds 01-07-2026. *(ahrefs.com, berekenhet.nl)*
- Solo-oprichtersprofiel: over duizenden Stripe Atlas-startups halen solo **B2B**-oprichters bij
  maand 24 meer dan 4× de omzet van solo **B2C**. Nederlandstalig, DEGIRO-only, consumentgericht is
  letterlijk het medianeprofiel. *(stripe.com/blog/top-solo-founder-traits, 28-05-2026)*

## 4. De box 3-wig: echt, maar met houdbaarheidsdatum

Dit is het enige onderbouwde gat in de markt.

**Wetgevingsstand per 30-07-2026:** Tweede Kamer nam wetsvoorstel 36748 aan op 12-02-2026; Eerste
Kamer hield de stemming aan op 30-06-2026 tot een novelle op Prinsjesdag 2026; motie-Schalk om het
voorstel in te trekken werd verworpen op 07-07-2026. Inwerkingtreding bij koninklijk besluit,
streefdatum 01-01-2028. Tegenbewijsregeling geldt 2021 t/m 2027 voor iedereen.
*(eerstekamer.nl/wetsvoorstel/36748 + MvT)*

**De vraag is gedocumenteerd, in citaten die je als copy kunt gebruiken:**
- DEGIRO: *"DEGIRO verstrekt geen overzichten van je rendement per jaar"* en *"De Nederlandse
  belastingdienst neemt de financiële gegevens evenwel niet over in de vooringevulde aangifte (...)
  U zult uw aangifte inkomstenbelasting op dit punt dus zelf moeten aanvullen."*
- Belastingdienst: *"Met dit hulpmiddel kunt u niet uw werkelijk rendement berekenen."*

**En de omvang, uit de wetsstukken:** ~2,5 van de 3,9 mln box 3-aangifteplichtigen blijft vanaf
2028 volledig vooringevuld, maar voor 1,4 mln "kan de aangifte niet volledig vooraf worden
ingevuld", met expliciete vermelding van beleggingsrekeningen bij buitenlandse financiële
instellingen. CRS/FATCA wisselt géén stortingen en onttrekkingen uit en komt pas in oktober van
het volgende jaar binnen. ~900 fte tekort bij de Belastingdienst; renseigneringsvenster krimpt van
~veertien naar ~twee maanden. *(Kamerstuk 36748 nr. 3, MvT §8.3.1/8.3.2, 19-05-2025)*

**Vier redenen waarom dit géén vrijbrief is:**
1. De consumentwig is al deels bezet: FolioInsights heeft een box 3-schatter voor €29 lifetime,
   BerekenHet.nl een gratis tegenbewijscheck sinds 01-07-2026.
2. De staat dicht het gat: vanaf 2028 is het voor 2,5 van 3,9 mln vooringevuld.
3. Het is **geen** hergebruik van de bestaande IRR/TWR. Onder de tegenbewijsregeling (t/m 2027)
   zijn kosten níet aftrekbaar, onder de nieuwe wet wél — er moet dus een regime-schakelaar per
   belastingjaar in, plus bronbelasting per transactie en een scheiding tussen storting/onttrekking
   en interne herallocatie. Nieuw werk, geen bijproduct. Grep over `app`, `src`, `tests`,
   `database`, `config` en `routes` op `box3`, `werkelijk rendement`, `stripe`, `cashier` en
   `subscription` geeft **nul treffers**.
4. Er zijn effectief maar twee aangifteseizoenen over: voorjaar 2027 en 2028.

## 5. De rekening

Zzp-uurtarief software developer: **€94 excl. btw** gemiddeld (Knab Zzp Uurtarievenboekje 2026,
meer dan 20.000 zzp'ers, 01-07-2026).

| Scenario | Tijd | Kosten @€94 | Realistische uitkomst | Slaagkans |
|---|---|---|---|---|
| **A.** Doorgaan als consumenten-SaaS | 140-240 u bouw + 520-780 u distributie = **660-1.020 u** | €62.040-€95.880 | Tientallen tot laag-honderden gebruikers. Break-even vergt **827-1.278 abonnees à €75/jaar** in jaar 1 = 8-13% van wat PDT in vijf jaar opbouwde mét podcast en team van tien | Zeer laag |
| **B.** Box 3-jaarrapport voor consumenten (seizoensproduct, €39-49) | 120-180 u | €11.280-€16.920 | ~€13.500/jaar bij 300 verkopen à €45, supportlast in weken i.p.v. maanden | Laag tot matig |
| **C.** Box 3-dossiergenerator voor administratiekantoren (B2B, on-prem, €750/jaar) | 170-290 u | €15.980-€27.260 | €15.000 recurring bij 20 kantoren, bijna nul marginale kosten. **Enige scenario waarin de som per uur klopt.** Maar Nextens heeft al een OWR-module, het kanaal en het aansprakelijkheidsverhaal; jij hebt bus factor 1 | Matig, volledig ongevalideerd |
| **D.** Bevriezen als referentieproject | 2-4 u eenmalig | ~€300 | Nul productomzet, maar 660-1.020 uur/jaar vrij voor facturabel werk, plus een publiek verifieerbaar Laravel 12/Filament v4-referentiestuk in precies de stack die je verkoopt | Zeker |

Bij scenario C is Apache-2.0 en self-host voor het eerst een **inkoopeis** in plaats van een
geloofsartikel — een kantoor mag cliëntdata niet zomaar bij een derde parkeren. Dat is de enige
plek in het hele dossier waar de bestaande positionering een commercieel voordeel wordt.

## 6. Aanbeveling

Degradeer Monthli tot bevroren portfolio-/referentieproject en zet dat letterlijk in de README
("geen SaaS, niet actief onderhouden, Laravel/Filament-referentie"). Die beslissing is feitelijk
op 22-11-2025 al genomen; hij is alleen niet opgeschreven, en is daardoor acht maanden als stille
verplichting blijven hangen.

**Precies één uitzondering, hard begrensd:** vóór 31-08-2026 maximaal **12 uur** en **géén regel
nieuwe productcode** aan één test met twee sporen.

- **Spoor 1 (consument):** een Nederlandse landingspagina met een échte Stripe-checkout van €39
  voor *"Box 3 werkelijk-rendement jaarrapport belastingjaar 2026 — PDF met onderbouwing per post,
  levering maart 2027, geld terug als het niet klopt."* Gebruik de twee citaten uit §4 als copy.
- **Spoor 2 (B2B):** twintig mails naar administratiekantoren met één vraag — betaalt u €750/jaar
  voor een onderbouwd werkelijk-rendement-dossier voor cliënten met een buitenlandse broker- of
  cryptorekening?

### Beslismomenten

| Datum | Criterium |
|---|---|
| **31-08-2026** | Test live: landingspagina met werkende Stripe-checkout **en** twintig mails verstuurd, binnen 12 uur, nul regels productcode. Niet live = dat is het antwoord |
| **15-09-2026** | Lees de novelle bij wetsvoorstel 36748 en check of de AMvB die de uitzonderingen op de administratieplicht aanwijst is gepubliceerd — specifiek of **flatexDEGIRO Bank Dutch Branch** onder de uitgebreide nationale renseignering valt. Valt DEGIRO erbinnen, dan verdampt de wig voor je grootste brokerpopulatie en vervalt scenario B onmiddellijk, ongeacht de pre-orders |
| **30-09-2026** (harde meting, geen verlenging) | GO **alleen** bij ≥25 betaalde consumenten-pre-orders (€975 daadwerkelijk op de rekening) **óf** ≥3 kantoren die schriftelijk met naam en datum €750/jaar toezeggen. Klikken, wachtlijstinschrijvingen en enthousiaste mails tellen niet |
| **01-03-2027** | Bij GO: box 3-dossier plus betaalde leverketen werkend en gedemonstreerd op een echt DEGIRO-bestand, binnen max. 240 uur. Niet live = aangifteseizoen 2027 gemist, en er blijft maar één seizoen over. Stoppen |
| **31-05-2027** | ≥100 betaalde dossiers à €39-49 **óf** ≥8 kantoren op jaarcontract à €750. Daaronder stoppen, ongeacht groeicurve |
| **Elke 8 weken** | Nul commits in de repo = de beslissing is genomen. Schrijf het die week in de README in plaats van het te laten hangen |

## 7. Onzekerheden en beperkingen van dit onderzoek

- **De AMvB is niet gepubliceerd.** Of flatexDEGIRO Bank Dutch Branch onder de uitgebreide
  nationale renseignering gaat vallen, is de enige feitelijke vraag die de hele box 3-positionering
  kan maken of breken. Vandaag valt DEGIRO er aantoonbaar buiten voor de vooringevulde aangifte.
- **01-01-2028 is niet zeker** (aangehouden stemming, novelle, koninklijk besluit). Uitstel is voor
  deze positie goed nieuws maar maakt planning onmogelijk — bouw geen roadmap die op die datum hangt.
- **Hoeveel van de 1,4 mln niet-vooringevulde aangifteplichtigen echt effectenbeleggers bij
  buitenlandse brokers zijn, is nergens vastgesteld.** Dat getal bevat ook vastgoedbezitters en
  houders van niet-beursgenoteerde aandelen. De doelgroep kan een orde van grootte kleiner zijn.
- **Of administratiekantoren een losse dossiergenerator naast hun aangiftesoftware kopen, is pure
  hypothese.** Nextens' OWR-module is tegelijk bewijs van vraag én de incumbent.
- **De tractie van TrackinV, FolioInsights en Capitalyse is nergens gepubliceerd.** Ik weet wat ze
  doen en wat ze vragen, niet of ze 50 of 5.000 gebruikers hebben. Werkt de niche ook voor hén
  niet, dan is het marktsignaal slechter in plaats van beter.
- **De urenschatting van de sunk cost (120-250 u) is een inferentie uit codeomvang en
  commitspreiding, geen urenregistratie.** Veel branches heten `codex/*`, dus een deel is
  AI-gegenereerd en de werkelijke handmatige inzet kan lager liggen. Dat verlaagt de sunk cost, niet
  de forward cost — en sunk cost mag hoe dan ook niet meewegen.
- **De opportunity-costberekening veronderstelt dat die avond- en weekenduren daadwerkelijk
  gefactureerd zouden worden.** Is het alternatief geen betaald werk, dan is €94/uur geen reële
  kostenpost en zakt het break-evenpunt van 827-1.278 abonnees naar een handvol boven de
  hostingkosten. Dat is de enige frame waarin doorgaan verdedigbaar is — en dan is het expliciet
  een hobby, geen investering.
- **Betalingsbereidheid voor box 3 bij particulieren is nergens gemeten.** PDT noemt box 3 niet op
  de productpagina, Divly doet alleen crypto, Nextens richt zich op fiscalisten. Dat kan een leeg
  gat zijn — maar afwezigheid van concurrentie kan óók betekenen dat er geen consumentenmarkt is.
  Daarom moet de test met **geld** meten, niet met interesse.
- **Niet primair leesbaar, dus geen basis voor beslissingen:** Trustpilot (WAF-blokkade), de
  letterlijke zelfpromotieregels van r/beleggen en r/DutchFIRE (Reddit netwerk-geblokkeerd,
  ledenaantallen van één aggregator), en de Duitse getquin-reviewteksten (lege RSS-feed). getquins
  "+500k Users" en "+20 B € Tracked" zijn zelfgerapporteerde marketingcijfers zonder peildatum of
  externe validatie.
