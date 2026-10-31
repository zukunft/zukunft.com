# Differences of portfolio_split_calculator_js.html#standard to portfolio_split_calculator_sample.html#standard

Change the generated page portfolio_split_calculator_js.html#standard so that it shows what the hand made sample portfolio_split_calculator_sample.html#standard shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (5)

- heading: Inputs from
- row: Value / Portfolio value original
- table: # | Position | Original | Lot value CHF | Target first | First | Second | Parent | Cum. dev. first CHF
- button: 
- button:  (2)

## Different (93)

- field: Target weight first child, tag: the sample shows "split agreement" and the generated page ""
- field: Target weight first child, hint: the sample shows "" and the generated page "[1]"
- field: Target weight second child, tag: the sample shows "split agreement" and the generated page ""
- field: Target weight second child, hint: the sample shows "" and the generated page "[1]"
- field: Target weight parent, value: the sample shows "30.0 %" and the generated page "30 %"
- row: Realised / Stocks, cells: the sample shows "2'930'576 | 3'418'962 | 3'418'962 | 9'768'500" and the generated page "2'920'474 | 3'424'013 | 3'424'013 | 9'768'500"
- row: Realised / Bonds, cells: the sample shows "1'801'350 | 2'101'575 | 2'101'575 | 6'004'500" and the generated page "1'811'400 | 2'096'550 | 2'096'550 | 6'004'500"
- row: Realised / Cash, cells: the sample shows "1'151'344 | 1'343'278 | 1'343'278 | 3'837'900" and the generated page "1'151'396 | 1'343'252 | 1'343'252 | 3'837'900"
- row: Realised / Deviation from target, cells: the sample shows "-0.00 | 0.00 | 0.00 | 0.00" and the generated page "0.00 | 0.00 | 0.00 | 0.00"
- row: # / 1, cells: the sample shows "LISPLindt & Sprüngli AG (PS) | 10shares | 117'000.00 | 3.500 | 4 | 4 | 2 | +58'500.00" and the generated page "LISP | 10 | 117'000 | 3.500 | 4 | 4 | 2 | 58'500"
- row: # / 2, cells: the sample shows "LISNLindt & Sprüngli AG (registered) | 40shares | 11'000.00 | 8.682 | 9 | 9 | 22 | +3'500.00" and the generated page "LISN | 40 | 11'000 | 8.682 | 9 | 9 | 22 | 3'500"
- row: # / 3, cells: the sample shows "GIVNGivaudan SA | 100shares | 3'700.00 | 34.054 | 34 | 34 | 32 | -200.00" and the generated page "ConfedBond30 | 1'500'000 | 5'025 | 521'517.413 | 520'000 | 520'000 | 460'000 | -1'525"
- row: # / 4, cells: the sample shows "PGHNPartners Group Holding AG | 250shares | 1'200.00 | 87.667 | 88 | 88 | 74 | +400.00" and the generated page "NestleBond27 | 1'500'000 | 4'990 | 526'528.056 | 525'000 | 525'000 | 450'000 | -1'525"
- row: # / 5, cells: the sample shows "GEBNGeberit AG | 500shares | 590.00 | 174.322 | 174 | 174 | 152 | -190.00" and the generated page "RocheBond28 | 1'000'000 | 4'950 | 351'540.404 | 350'000 | 350'000 | 300'000 | -1'525"
- row: # / 6, cells: the sample shows "LONNLonza Group AG | 600shares | 530.00 | 210.358 | 210 | 210 | 180 | -190.00" and the generated page "GIVN | 100 | 3'700 | 35.412 | 35 | 35 | 30 | -1'525"
- row: # / 7, cells: the sample shows "ZURNZurich Insurance Group AG | 600shares | 530.00 | 210.358 | 210 | 210 | 180 | -190.00" and the generated page "PGHN | 250 | 1'200 | 88.771 | 89 | 89 | 72 | 275"
- row: # / 8, cells: the sample shows "SCMNSwisscom AG | 600shares | 530.00 | 210.358 | 210 | 210 | 180 | -190.00" and the generated page "BundDE30 | 1'000'000 | 941 | 349'707.696 | 350'000 | 350'000 | 300'000 | 275"
- row: # / 9, cells: the sample shows "VACNVAT Group AG | 800shares | 380.00 | 280.500 | 280 | 280 | 240 | -190.00" and the generated page "TreasuryUS27 | 1'200'000 | 891 | 419'691.358 | 420'000 | 420'000 | 360'000 | 275"
- row: # / 10, cells: the sample shows "ROGRoche Holding AG | 2'000shares | 240.00 | 700.792 | 701 | 701 | 598 | +50.00" and the generated page "GEBN | 500 | 590 | 174.534 | 175 | 175 | 150 | 275"
- row: # / 11, cells: the sample shows "SOONSonova Holding AG | 1'200shares | 240.00 | 419.792 | 420 | 420 | 360 | +50.00" and the generated page "LONN | 600 | 530 | 209.481 | 209 | 209 | 182 | -255"
- row: # / 12, cells: the sample shows "SCHPSchindler Holding AG (PS) | 1'200shares | 230.00 | 419.783 | 420 | 420 | 360 | +50.00" and the generated page "ZURN | 600 | 530 | 210.481 | 210 | 210 | 180 | -255"
- row: # / 13, cells: the sample shows "SIKASika AG | 1'300shares | 220.00 | 454.773 | 455 | 455 | 390 | +50.00" and the generated page "SCMN | 600 | 530 | 210.481 | 210 | 210 | 180 | -255"
- row: # / 14, cells: the sample shows "KNINKuehne + Nagel International AG | 1'300shares | 220.00 | 454.773 | 455 | 455 | 390 | +50.00" and the generated page "VACN | 800 | 380 | 280.671 | 281 | 281 | 238 | 125"
- row: # / 15, cells: the sample shows "UHRThe Swatch Group AG | 1'500shares | 180.00 | 524.722 | 525 | 525 | 450 | +50.00" and the generated page "ROG | 2'000 | 240 | 699.479 | 699 | 699 | 602 | -115"
- row: # / 16, cells: the sample shows "CFRCompagnie Financière Richemont SA | 2'000shares | 130.00 | 699.615 | 700 | 700 | 600 | +50.00" and the generated page "SOON | 1'200 | 240 | 420.479 | 420 | 420 | 360 | -115"
- row: # / 17, cells: the sample shows "SRENSwiss Re AG | 2'000shares | 130.00 | 699.615 | 700 | 700 | 600 | +50.00" and the generated page "SCHP | 1'200 | 230 | 420.500 | 420 | 420 | 360 | -115"
- row: # / 18, cells: the sample shows "STMNStraumann Holding AG | 2'500shares | 110.00 | 874.545 | 875 | 875 | 750 | +50.00" and the generated page "SIKA | 1'300 | 220 | 455.523 | 456 | 456 | 388 | 105"
- row: # / 19, cells: the sample shows "LOGNLogitech International SA | 3'000shares | 90.00 | 1'049.444 | 1'049 | 1'049 | 902 | -40.00" and the generated page "KNIN | 1'300 | 220 | 454.523 | 455 | 455 | 390 | 105"
- row: # / 20, cells: the sample shows "NOVNNovartis AG | 3'000shares | 90.00 | 1'050.444 | 1'050 | 1'050 | 900 | -40.00" and the generated page "UHR | 1'500 | 180 | 524.417 | 524 | 524 | 452 | -75"
- row: # / 21, cells: the sample shows "SGSNSGS SA | 3'000shares | 87.00 | 1'050.460 | 1'050 | 1'050 | 900 | -40.00" and the generated page "CFR | 2'000 | 130 | 700.577 | 701 | 701 | 598 | 55"
- row: # / 22, cells: the sample shows "HOLNHolcim AG | 3'500shares | 80.00 | 1'225.500 | 1'226 | 1'226 | 1'048 | +40.00" and the generated page "SREN | 2'000 | 130 | 699.577 | 700 | 700 | 600 | 55"
- row: # / 23, cells: the sample shows "NESNNestlé S.A. | 3'500shares | 80.00 | 1'224.500 | 1'225 | 1'225 | 1'050 | +40.00" and the generated page "STMN | 2'500 | 110 | 874.500 | 874 | 874 | 752 | -55"
- row: # / 24, cells: the sample shows "ALCAlcon AG | 3'500shares | 75.00 | 1'224.467 | 1'224 | 1'224 | 1'052 | -35.00" and the generated page "LOGN | 3'000 | 90 | 1'050.611 | 1'051 | 1'051 | 898 | 35"
- row: # / 25, cells: the sample shows "BAERJulius Bär Group AG | 4'500shares | 60.00 | 1'575.583 | 1'576 | 1'576 | 1'348 | +25.00" and the generated page "NOVN | 3'000 | 90 | 1'049.611 | 1'050 | 1'050 | 900 | 35"
- row: # / 26, cells: the sample shows "BANBBachem Holding AG | 4'500shares | 60.00 | 1'574.583 | 1'575 | 1'575 | 1'350 | +25.00" and the generated page "SGSN | 3'000 | 87 | 1'049.598 | 1'050 | 1'050 | 900 | 35"
- row: # / 27, cells: the sample shows "ABBNABB Ltd | 5'500shares | 50.00 | 1'924.500 | 1'925 | 1'925 | 1'650 | +25.00" and the generated page "HOLN | 3'500 | 80 | 1'224.562 | 1'225 | 1'225 | 1'050 | 35"
- row: # / 28, cells: the sample shows "SDZSandoz Group AG | 7'000shares | 38.00 | 2'449.342 | 2'449 | 2'449 | 2'102 | -13.00" and the generated page "NESN | 3'500 | 80 | 1'224.562 | 1'225 | 1'225 | 1'050 | 35"
- row: # / 29, cells: the sample shows "ADENAdecco Group AG | 9'000shares | 30.00 | 3'150.433 | 3'150 | 3'150 | 2'700 | -13.00" and the generated page "ALC | 3'500 | 75 | 1'224.533 | 1'225 | 1'225 | 1'050 | 35"
- row: # / 30, cells: the sample shows "UBSGUBS Group AG | 10'000shares | 28.00 | 3'500.464 | 3'500 | 3'500 | 3'000 | -13.00" and the generated page "BAER | 4'500 | 60 | 1'574.417 | 1'574 | 1'574 | 1'352 | -25"
- row: # / 31, cells: the sample shows "ConfedBond30Swiss Confederation 1.25% 2030 | 1'500'000CHF | 5'025.00 | 525'012.935 | 525'000 | 525'000 | 450'000 | -13.00" and the generated page "BANB | 4'500 | 60 | 1'575.417 | 1'575 | 1'575 | 1'350 | -25"
- row: # / 32, cells: the sample shows "NestleBond27Nestlé Holdings 0.875% 2027 | 1'500'000CHF | 4'990.00 | 525'013.026 | 525'000 | 525'000 | 450'000 | -13.00" and the generated page "ABBN | 5'500 | 50 | 1'925.500 | 1'925 | 1'925 | 1'650 | -25"
- row: # / 33, cells: the sample shows "RocheBond28Roche Holdings 1.0% 2028 | 1'000'000CHF | 4'950.00 | 350'013.131 | 350'000 | 350'000 | 300'000 | -13.00" and the generated page "SDZ | 7'000 | 38 | 2'450.658 | 2'451 | 2'451 | 2'098 | 13"
- row: # / 34, cells: the sample shows "BundDE30Bund 0% 2030 | 1'000'000EUR | 940.80 | 350'013.818 | 350'000 | 350'000 | 300'000 | -13.00" and the generated page "ADEN | 9'000 | 30 | 3'149.567 | 3'150 | 3'150 | 2'700 | 13"
- row: # / 35, cells: the sample shows "TreasuryUS27US Treasury 2.5% 2027 | 1'200'000USD | 891.00 | 420'014.590 | 420'000 | 420'000 | 360'000 | -13.00" and the generated page "UBSG | 10'000 | 28 | 3'499.536 | 3'500 | 3'500 | 3'000 | 13"
- row: # / 36, cells: the sample shows "cash CHFCash CHF | 1'000'000.00CHF | 0.0100 | 350'013.00 | 350'013.00 | 350'013.00 | 299'974.00 | 0.00" and the generated page "cash GBP | 200'000 | 0 | 69'988.496 | 69'989 | 69'989 | 60'023 | 0"
- row: # / 37, cells: the sample shows "cash EURCash EUR | 1'800'000.00EUR | 0.0098 | 630'000.00 | 630'000.00 | 630'000.00 | 540'000.00 | 0.00" and the generated page "cash CHF | 1'000'000 | 0 | 349'999.995 | 350'000 | 350'000 | 300'000 | 0"
- row: # / 38, cells: the sample shows "cash USDCash USD | 500'000.00USD | 0.0090 | 175'000.00 | 175'000.00 | 175'000.00 | 150'000.00 | 0.00" and the generated page "cash EUR | 1'800'000 | 0 | 630'000.005 | 630'000 | 630'000 | 540'000 | 0"
- row: # / 39, cells: the sample shows "cash GBPCash GBP | 200'000.00GBP | 0.0113 | 70'000.00 | 70'000.00 | 70'000.00 | 60'000.00 | 0.00" and the generated page "cash USD | 500'000 | 0 | 174'999.995 | 175'000 | 175'000 | 150'000 | 0"
- row: # / 40, cells: the sample shows "cash JPYCash JPY | 30'000'000.00JPY | 0.0001 | 10'500'000.00 | 10'500'000.00 | 10'500'000.00 | 9'000'000.00 | 0.00" and the generated page "cash DKK | 1'000'000 | 0 | 350'000.032 | 350'000 | 350'000 | 300'000 | 0"
- row: # / 41, cells: the sample shows "cash DKKCash DKK | 1'000'000.00DKK | 0.0013 | 350'000.00 | 350'000.00 | 350'000.00 | 300'000.00 | 0.00" and the generated page "cash SEK | 1'000'000 | 0 | 350'000.003 | 350'000 | 350'000 | 300'000 | 0"
- row: # / 42, cells: the sample shows "cash SEKCash SEK | 1'000'000.00SEK | 0.0009 | 350'000.00 | 350'000.00 | 350'000.00 | 300'000.00 | 0.00" and the generated page "cash JPY | 30'000'000 | 0 | 10'500'000.048 | 10'500'000 | 10'500'000 | 9'000'000 | 0"
- row: Position / LISP, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 10 | 117'000 | 1.00"
- row: Position / LISN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 40 | 11'000 | 1.00"
- row: Position / GIVN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 100 | 3'700 | 1.00"
- row: Position / PGHN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 250 | 1'200 | 1.00"
- row: Position / GEBN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 500 | 590 | 1.00"
- row: Position / LONN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 600 | 530 | 1.00"
- row: Position / ZURN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 600 | 530 | 1.00"
- row: Position / SCMN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 600 | 530 | 1.00"
- row: Position / VACN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 800 | 380 | 1.00"
- row: Position / ROG, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 2'000 | 240 | 1.00"
- row: Position / SOON, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 1'200 | 240 | 1.00"
- row: Position / SCHP, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 1'200 | 230 | 1.00"
- row: Position / SIKA, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 1'300 | 220 | 1.00"
- row: Position / KNIN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 1'300 | 220 | 1.00"
- row: Position / UHR, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 1'500 | 180 | 1.00"
- row: Position / CFR, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 2'000 | 130 | 1.00"
- row: Position / SREN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 2'000 | 130 | 1.00"
- row: Position / STMN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 2'500 | 110 | 1.00"
- row: Position / LOGN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 3'000 | 90 | 1.00"
- row: Position / NOVN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 3'000 | 90 | 1.00"
- row: Position / SGSN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 3'000 | 87 | 1.00"
- row: Position / HOLN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 3'500 | 80 | 1.00"
- row: Position / NESN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 3'500 | 80 | 1.00"
- row: Position / ALC, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 3'500 | 75 | 1.00"
- row: Position / BAER, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 4'500 | 60 | 1.00"
- row: Position / BANB, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 4'500 | 60 | 1.00"
- row: Position / ABBN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 5'500 | 50 | 1.00"
- row: Position / SDZ, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 7'000 | 38 | 1.00"
- row: Position / ADEN, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 9'000 | 30 | 1.00"
- row: Position / UBSG, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 10'000 | 28 | 1.00"
- row: Position / ConfedBond30, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 1'500'000 | 100 % | 5'000"
- row: Position / NestleBond27, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 1'500'000 | 100 % | 5'000"
- row: Position / RocheBond28, cells: the sample shows "CHF |  |  | " and the generated page "CHF | 1'000'000 | 99 % | 5'000"
- row: Position / BundDE30, cells: the sample shows "EUR |  |  | " and the generated page "EUR | 1'000'000 | 96 % | 1'000"
- row: Position / TreasuryUS27, cells: the sample shows "USD |  |  | " and the generated page "USD | 1'200'000 | 99 % | 1'000"
- row: Position / cash CHF, cells: the sample shows "CHF |  | — | 0.01" and the generated page "CHF | 1'000'000 |  | 0"
- row: Position / cash EUR, cells: the sample shows "EUR |  | — | 0.01" and the generated page "EUR | 1'800'000 |  | 0"
- row: Position / cash USD, cells: the sample shows "USD |  | — | 0.01" and the generated page "USD | 500'000 |  | 0"
- row: Position / cash GBP, cells: the sample shows "GBP |  | — | 0.01" and the generated page "GBP | 200'000 |  | 0.01"
- row: Position / cash JPY, cells: the sample shows "JPY |  | — | 0.01" and the generated page "JPY | 30'000'000 |  | 0.01"
- row: Position / cash DKK, cells: the sample shows "DKK |  | — | 0.01" and the generated page "DKK | 1'000'000 |  | 0.01"
- row: Position / cash SEK, cells: the sample shows "SEK |  | — | 0.01" and the generated page "SEK | 1'000'000 |  | 0.01"

## Only in the generated page (3)

- field: Exchange rate
- table: # | Position | Original | Lot value CHF | Target first | First child | Second child | Parent | Cum. dev. first CHF
- button: Import

## Different order (1)

- row: the sample shows "row: Target / Target weight", "row: Target / Target value", "row: Realised / Stocks", "row: Realised / Bonds", "row: Realised / Cash", "row: Realised / Portfolio value", "row: Realised / Realised weight", "row: Realised / Deviation from target", "row: Value / LISP share, first child portfolio", "row: Value / LISP share, second child portfolio", "row: Value / LISP share, parent portfolio", "row: Value / LISN share, first child portfolio", "row: Value / LISN share, second child portfolio", "row: Value / LISN share, parent portfolio", "row: # / 1", "row: # / 2", "row: # / 3", "row: # / 4", "row: # / 5", "row: # / 6", "row: # / 7", "row: # / 8", "row: # / 9", "row: # / 10", "row: # / 11", "row: # / 12", "row: # / 13", "row: # / 14", "row: # / 15", "row: # / 16", "row: # / 17", "row: # / 18", "row: # / 19", "row: # / 20", "row: # / 21", "row: # / 22", "row: # / 23", "row: # / 24", "row: # / 25", "row: # / 26", "row: # / 27", "row: # / 28", "row: # / 29", "row: # / 30", "row: # / 31", "row: # / 32", "row: # / 33", "row: # / 34", "row: # / 35", "row: # / 36", "row: # / 37", "row: # / 38", "row: # / 39", "row: # / 40", "row: # / 41", "row: # / 42", "row: Position / LISP", "row: Position / LISN", "row: Position / GIVN", "row: Position / PGHN", "row: Position / GEBN", "row: Position / LONN", "row: Position / ZURN", "row: Position / SCMN", "row: Position / VACN", "row: Position / ROG", "row: Position / SOON", "row: Position / SCHP", "row: Position / SIKA", "row: Position / KNIN", "row: Position / UHR", "row: Position / CFR", "row: Position / SREN", "row: Position / STMN", "row: Position / LOGN", "row: Position / NOVN", "row: Position / SGSN", "row: Position / HOLN", "row: Position / NESN", "row: Position / ALC", "row: Position / BAER", "row: Position / BANB", "row: Position / ABBN", "row: Position / SDZ", "row: Position / ADEN", "row: Position / UBSG", "row: Position / ConfedBond30", "row: Position / NestleBond27", "row: Position / RocheBond28", "row: Position / BundDE30", "row: Position / TreasuryUS27", "row: Position / cash CHF", "row: Position / cash EUR", "row: Position / cash USD", "row: Position / cash GBP", "row: Position / cash JPY", "row: Position / cash DKK", "row: Position / cash SEK" and the generated page "row: Target / Target weight", "row: Target / Target value", "row: Realised / Stocks", "row: Realised / Bonds", "row: Realised / Cash", "row: Realised / Portfolio value", "row: Realised / Realised weight", "row: Realised / Deviation from target", "row: Value / LISN share, first child portfolio", "row: Value / LISN share, parent portfolio", "row: Value / LISN share, second child portfolio", "row: Value / LISP share, first child portfolio", "row: Value / LISP share, parent portfolio", "row: Value / LISP share, second child portfolio", "row: # / 1", "row: # / 2", "row: # / 3", "row: # / 4", "row: # / 5", "row: # / 6", "row: # / 7", "row: # / 8", "row: # / 9", "row: # / 10", "row: # / 11", "row: # / 12", "row: # / 13", "row: # / 14", "row: # / 15", "row: # / 16", "row: # / 17", "row: # / 18", "row: # / 19", "row: # / 20", "row: # / 21", "row: # / 22", "row: # / 23", "row: # / 24", "row: # / 25", "row: # / 26", "row: # / 27", "row: # / 28", "row: # / 29", "row: # / 30", "row: # / 31", "row: # / 32", "row: # / 33", "row: # / 34", "row: # / 35", "row: # / 36", "row: # / 37", "row: # / 38", "row: # / 39", "row: # / 40", "row: # / 41", "row: # / 42", "row: Position / ConfedBond30", "row: Position / NestleBond27", "row: Position / RocheBond28", "row: Position / BundDE30", "row: Position / TreasuryUS27", "row: Position / cash GBP", "row: Position / cash CHF", "row: Position / cash EUR", "row: Position / cash USD", "row: Position / cash DKK", "row: Position / cash SEK", "row: Position / cash JPY", "row: Position / GEBN", "row: Position / LONN", "row: Position / ZURN", "row: Position / SCMN", "row: Position / VACN", "row: Position / ROG", "row: Position / SOON", "row: Position / SCHP", "row: Position / SIKA", "row: Position / KNIN", "row: Position / UHR", "row: Position / CFR", "row: Position / SREN", "row: Position / STMN", "row: Position / LOGN", "row: Position / NOVN", "row: Position / SGSN", "row: Position / HOLN", "row: Position / NESN", "row: Position / ALC", "row: Position / BAER", "row: Position / BANB", "row: Position / ABBN", "row: Position / SDZ", "row: Position / ADEN", "row: Position / UBSG", "row: Position / GIVN", "row: Position / LISN", "row: Position / LISP", "row: Position / PGHN"
