# Differences of portfolio_split_calculator_js.html#standard to portfolio_split_calculator_sample.html#standard

Change the generated page portfolio_split_calculator_js.html#standard so that it shows what the hand made sample portfolio_split_calculator_sample.html#standard shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (1)

- button: Report

## Different (44)

- field: Exchange rate, tag: the sample shows "measured" and the generated page ""
- field: Exchange rate, unit: the sample shows "CHF per DKK" and the generated page ""
- field: Exchange rate, hint: the sample shows "CHF per unit of currency, bank report [2]" and the generated page "CHF per unit of currency, bank report"
- field: Exchange rate, value: the sample shows "0.1304" and the generated page ""
- row: Realised / Stocks, cells: the sample shows "2'930'576 | 3'418'962 | 3'418'962 | 9'768'500" and the generated page "2'920'474 | 3'424'013 | 3'424'013 | 9'768'500"
- row: Realised / Bonds, cells: the sample shows "1'801'350 | 2'101'575 | 2'101'575 | 6'004'500" and the generated page "1'811'400 | 2'096'550 | 2'096'550 | 6'004'500"
- row: Realised / Cash, cells: the sample shows "1'151'344 | 1'343'278 | 1'343'278 | 3'837'900" and the generated page "1'151'396 | 1'343'252 | 1'343'252 | 3'837'900"
- row: # / 3, cells: the sample shows "GIVN Givaudan SA | 100 shares | 3'700.00 | 34.054 | 34 | 34 | 32 | -200.00" and the generated page "ConfedBond30 Swiss Confederation 1.25% 2030 | 1'500'000 CHF | 5'025.00 | 521'517.413 | 520'000 | 520'000 | 460'000 | -1'525.00"
- row: # / 4, cells: the sample shows "PGHN Partners Group Holding AG | 250 shares | 1'200.00 | 87.667 | 88 | 88 | 74 | +400.00" and the generated page "NestleBond27 Nestlé Holdings 0.875% 2027 | 1'500'000 CHF | 4'990.00 | 526'528.056 | 525'000 | 525'000 | 450'000 | -1'525.00"
- row: # / 5, cells: the sample shows "GEBN Geberit AG | 500 shares | 590.00 | 174.322 | 174 | 174 | 152 | -190.00" and the generated page "RocheBond28 Roche Holdings 1.0% 2028 | 1'000'000 CHF | 4'950.00 | 351'540.404 | 350'000 | 350'000 | 300'000 | -1'525.00"
- row: # / 6, cells: the sample shows "LONN Lonza Group AG | 600 shares | 530.00 | 210.358 | 210 | 210 | 180 | -190.00" and the generated page "GIVN Givaudan SA | 100 shares | 3'700.00 | 35.412 | 35 | 35 | 30 | -1'525.00"
- row: # / 7, cells: the sample shows "ZURN Zurich Insurance Group AG | 600 shares | 530.00 | 210.358 | 210 | 210 | 180 | -190.00" and the generated page "PGHN Partners Group Holding AG | 250 shares | 1'200.00 | 88.771 | 89 | 89 | 72 | +275.00"
- row: # / 8, cells: the sample shows "SCMN Swisscom AG | 600 shares | 530.00 | 210.358 | 210 | 210 | 180 | -190.00" and the generated page "BundDE30 Bund 0% 2030 | 1'000'000 EUR | 940.80 | 349'707.696 | 350'000 | 350'000 | 300'000 | +275.00"
- row: # / 9, cells: the sample shows "VACN VAT Group AG | 800 shares | 380.00 | 280.500 | 280 | 280 | 240 | -190.00" and the generated page "TreasuryUS27 US Treasury 2.5% 2027 | 1'200'000 USD | 891.00 | 419'691.358 | 420'000 | 420'000 | 360'000 | +275.00"
- row: # / 10, cells: the sample shows "ROG Roche Holding AG | 2'000 shares | 240.00 | 700.792 | 701 | 701 | 598 | +50.00" and the generated page "GEBN Geberit AG | 500 shares | 590.00 | 174.534 | 175 | 175 | 150 | +275.00"
- row: # / 11, cells: the sample shows "SOON Sonova Holding AG | 1'200 shares | 240.00 | 419.792 | 420 | 420 | 360 | +50.00" and the generated page "LONN Lonza Group AG | 600 shares | 530.00 | 209.481 | 209 | 209 | 182 | -255.00"
- row: # / 12, cells: the sample shows "SCHP Schindler Holding AG (PS) | 1'200 shares | 230.00 | 419.783 | 420 | 420 | 360 | +50.00" and the generated page "ZURN Zurich Insurance Group AG | 600 shares | 530.00 | 210.481 | 210 | 210 | 180 | -255.00"
- row: # / 13, cells: the sample shows "SIKA Sika AG | 1'300 shares | 220.00 | 454.773 | 455 | 455 | 390 | +50.00" and the generated page "SCMN Swisscom AG | 600 shares | 530.00 | 210.481 | 210 | 210 | 180 | -255.00"
- row: # / 14, cells: the sample shows "KNIN Kuehne + Nagel International AG | 1'300 shares | 220.00 | 454.773 | 455 | 455 | 390 | +50.00" and the generated page "VACN VAT Group AG | 800 shares | 380.00 | 280.671 | 281 | 281 | 238 | +125.00"
- row: # / 15, cells: the sample shows "UHR The Swatch Group AG | 1'500 shares | 180.00 | 524.722 | 525 | 525 | 450 | +50.00" and the generated page "ROG Roche Holding AG | 2'000 shares | 240.00 | 699.479 | 699 | 699 | 602 | -115.00"
- row: # / 16, cells: the sample shows "CFR Compagnie Financière Richemont SA | 2'000 shares | 130.00 | 699.615 | 700 | 700 | 600 | +50.00" and the generated page "SOON Sonova Holding AG | 1'200 shares | 240.00 | 420.479 | 420 | 420 | 360 | -115.00"
- row: # / 17, cells: the sample shows "SREN Swiss Re AG | 2'000 shares | 130.00 | 699.615 | 700 | 700 | 600 | +50.00" and the generated page "SCHP Schindler Holding AG (PS) | 1'200 shares | 230.00 | 420.500 | 420 | 420 | 360 | -115.00"
- row: # / 18, cells: the sample shows "STMN Straumann Holding AG | 2'500 shares | 110.00 | 874.545 | 875 | 875 | 750 | +50.00" and the generated page "SIKA Sika AG | 1'300 shares | 220.00 | 455.523 | 456 | 456 | 388 | +105.00"
- row: # / 19, cells: the sample shows "LOGN Logitech International SA | 3'000 shares | 90.00 | 1'049.444 | 1'049 | 1'049 | 902 | -40.00" and the generated page "KNIN Kuehne + Nagel International AG | 1'300 shares | 220.00 | 454.523 | 455 | 455 | 390 | +105.00"
- row: # / 20, cells: the sample shows "NOVN Novartis AG | 3'000 shares | 90.00 | 1'050.444 | 1'050 | 1'050 | 900 | -40.00" and the generated page "UHR The Swatch Group AG | 1'500 shares | 180.00 | 524.417 | 524 | 524 | 452 | -75.00"
- row: # / 21, cells: the sample shows "SGSN SGS SA | 3'000 shares | 87.00 | 1'050.460 | 1'050 | 1'050 | 900 | -40.00" and the generated page "CFR Compagnie Financière Richemont SA | 2'000 shares | 130.00 | 700.577 | 701 | 701 | 598 | +55.00"
- row: # / 22, cells: the sample shows "HOLN Holcim AG | 3'500 shares | 80.00 | 1'225.500 | 1'226 | 1'226 | 1'048 | +40.00" and the generated page "SREN Swiss Re AG | 2'000 shares | 130.00 | 699.577 | 700 | 700 | 600 | +55.00"
- row: # / 23, cells: the sample shows "NESN Nestlé S.A. | 3'500 shares | 80.00 | 1'224.500 | 1'225 | 1'225 | 1'050 | +40.00" and the generated page "STMN Straumann Holding AG | 2'500 shares | 110.00 | 874.500 | 874 | 874 | 752 | -55.00"
- row: # / 24, cells: the sample shows "ALC Alcon AG | 3'500 shares | 75.00 | 1'224.467 | 1'224 | 1'224 | 1'052 | -35.00" and the generated page "LOGN Logitech International SA | 3'000 shares | 90.00 | 1'050.611 | 1'051 | 1'051 | 898 | +35.00"
- row: # / 25, cells: the sample shows "BAER Julius Bär Group AG | 4'500 shares | 60.00 | 1'575.583 | 1'576 | 1'576 | 1'348 | +25.00" and the generated page "NOVN Novartis AG | 3'000 shares | 90.00 | 1'049.611 | 1'050 | 1'050 | 900 | +35.00"
- row: # / 26, cells: the sample shows "BANB Bachem Holding AG | 4'500 shares | 60.00 | 1'574.583 | 1'575 | 1'575 | 1'350 | +25.00" and the generated page "SGSN SGS SA | 3'000 shares | 87.00 | 1'049.598 | 1'050 | 1'050 | 900 | +35.00"
- row: # / 27, cells: the sample shows "ABBN ABB Ltd | 5'500 shares | 50.00 | 1'924.500 | 1'925 | 1'925 | 1'650 | +25.00" and the generated page "HOLN Holcim AG | 3'500 shares | 80.00 | 1'224.563 | 1'225 | 1'225 | 1'050 | +35.00"
- row: # / 28, cells: the sample shows "SDZ Sandoz Group AG | 7'000 shares | 38.00 | 2'449.342 | 2'449 | 2'449 | 2'102 | -13.00" and the generated page "NESN Nestlé S.A. | 3'500 shares | 80.00 | 1'224.563 | 1'225 | 1'225 | 1'050 | +35.00"
- row: # / 29, cells: the sample shows "ADEN Adecco Group AG | 9'000 shares | 30.00 | 3'150.433 | 3'150 | 3'150 | 2'700 | -13.00" and the generated page "ALC Alcon AG | 3'500 shares | 75.00 | 1'224.533 | 1'225 | 1'225 | 1'050 | +35.00"
- row: # / 30, cells: the sample shows "UBSG UBS Group AG | 10'000 shares | 28.00 | 3'500.464 | 3'500 | 3'500 | 3'000 | -13.00" and the generated page "BAER Julius Bär Group AG | 4'500 shares | 60.00 | 1'574.417 | 1'574 | 1'574 | 1'352 | -25.00"
- row: # / 31, cells: the sample shows "ConfedBond30 Swiss Confederation 1.25% 2030 | 1'500'000 CHF | 5'025.00 | 525'012.935 | 525'000 | 525'000 | 450'000 | -13.00" and the generated page "BANB Bachem Holding AG | 4'500 shares | 60.00 | 1'575.417 | 1'575 | 1'575 | 1'350 | -25.00"
- row: # / 32, cells: the sample shows "NestleBond27 Nestlé Holdings 0.875% 2027 | 1'500'000 CHF | 4'990.00 | 525'013.026 | 525'000 | 525'000 | 450'000 | -13.00" and the generated page "ABBN ABB Ltd | 5'500 shares | 50.00 | 1'925.500 | 1'925 | 1'925 | 1'650 | -25.00"
- row: # / 33, cells: the sample shows "RocheBond28 Roche Holdings 1.0% 2028 | 1'000'000 CHF | 4'950.00 | 350'013.131 | 350'000 | 350'000 | 300'000 | -13.00" and the generated page "SDZ Sandoz Group AG | 7'000 shares | 38.00 | 2'450.658 | 2'451 | 2'451 | 2'098 | +13.00"
- row: # / 34, cells: the sample shows "BundDE30 Bund 0% 2030 | 1'000'000 EUR | 940.80 | 350'013.818 | 350'000 | 350'000 | 300'000 | -13.00" and the generated page "ADEN Adecco Group AG | 9'000 shares | 30.00 | 3'149.567 | 3'150 | 3'150 | 2'700 | +13.00"
- row: # / 35, cells: the sample shows "TreasuryUS27 US Treasury 2.5% 2027 | 1'200'000 USD | 891.00 | 420'014.590 | 420'000 | 420'000 | 360'000 | -13.00" and the generated page "UBSG UBS Group AG | 10'000 shares | 28.00 | 3'499.536 | 3'500 | 3'500 | 3'000 | +13.00"
- row: # / 36, cells: the sample shows "cash GBP Cash GBP | 200'000.00 GBP | 0.0113 | 70'011.504 | 70'011.50 | 70'011.50 | 59'977.00 | -0.01" and the generated page "cash GBP Cash GBP | 200'000.00 GBP | 0.0113 | 69'988.496 | 69'988.50 | 69'988.50 | 60'023.00 | +0.01"
- row: # / 37, cells: the sample shows "cash CHF Cash CHF | 1'000'000.00 CHF | 0.0100 | 350'000.005 | 350'000.00 | 350'000.00 | 300'000.00 | -0.01" and the generated page "cash CHF Cash CHF | 1'000'000.00 CHF | 0.0100 | 349'999.995 | 349'999.99 | 349'999.99 | 300'000.02 | -0.01"
- row: Position / ConfedBond30, cells: the sample shows "CHF | 1'500'000 | 100.5 % | 5'000" and the generated page "CHF | 1'500'000 | 100 % | 5'000"
- row: Position / NestleBond27, cells: the sample shows "CHF | 1'500'000 | 99.8 % | 5'000" and the generated page "CHF | 1'500'000 | 100 % | 5'000"

## Only in the generated page (0)

## Different order (0)
