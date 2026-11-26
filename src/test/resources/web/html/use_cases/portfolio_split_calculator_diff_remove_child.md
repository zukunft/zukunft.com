# Differences of portfolio_split_calculator_js.html#remove_child to portfolio_split_calculator_sample.html#remove_child

Change the generated page portfolio_split_calculator_js.html#remove_child so that it shows what the hand made sample portfolio_split_calculator_sample.html#remove_child shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (1)

- warning: Weights differ, so every child is rounded on its own deviation.

## Different (55)

- field: Target weight second child, value: the sample shows "30" and the generated page "35"
- field: Target weight parent, value: the sample shows "35 %" and the generated page "30 %"
- row: Target / Target weight, cells: the sample shows "35.0 % | 35.0 % | 30.0 % | 100.0 %" and the generated page "30.0 % | 35.0 % | 35.0 % | 100.0 %"
- row: Target / Target value, cells: the sample shows "6'863'815 | 6'863'815 | 5'883'270 | 19'610'900" and the generated page "5'883'270 | 6'863'815 | 6'863'815 | 19'610'900"
- row: Realised / Stocks, cells: the sample shows "3'413'937 | 3'424'013 | 2'930'550 | 9'768'500" and the generated page "2'920'474 | 3'424'013 | 3'424'013 | 9'768'500"
- row: Realised / Bonds, cells: the sample shows "2'106'600 | 2'096'550 | 1'801'350 | 6'004'500" and the generated page "1'811'400 | 2'096'550 | 2'096'550 | 6'004'500"
- row: Realised / Cash, cells: the sample shows "1'343'278 | 1'343'252 | 1'151'370 | 3'837'900" and the generated page "1'151'396 | 1'343'252 | 1'343'252 | 3'837'900"
- row: Realised / Portfolio value, cells: the sample shows "6'863'815 | 6'863'815 | 5'883'270 | 19'610'900" and the generated page "5'883'270 | 6'863'815 | 6'863'815 | 19'610'900"
- row: Realised / Realised weight, cells: the sample shows "35.000 % | 35.000 % | 30.000 % | " and the generated page "30.000 % | 35.000 % | 35.000 % | "
- row: Value / LISN share, parent portfolio, cells: the sample shows "22 | 19 | differs" and the generated page "22 | 22 | match"
- row: Value / LISN share, second child portfolio, cells: the sample shows "9 | 12 | differs" and the generated page "9 | 9 | match"
- row: Value / LISP share, parent portfolio, cells: the sample shows "2 | 3 | differs" and the generated page "2 | 2 | match"
- row: Value / LISP share, second child portfolio, cells: the sample shows "4 | 3 | differs" and the generated page "4 | 4 | match"
- row: # / 1, cells: the sample shows "LISP Lindt & Sprüngli AG (PS) | 10 shares | 117'000.00 | 3.500 | 4 | 3 | 3 | +58'500.00" and the generated page "LISP Lindt & Sprüngli AG (PS) | 10 shares | 117'000.00 | 3.500 | 4 | 4 | 2 | +58'500.00"
- row: # / 2, cells: the sample shows "LISN Lindt & Sprüngli AG (registered) | 40 shares | 11'000.00 | 8.682 | 9 | 12 | 19 | +3'500.00" and the generated page "LISN Lindt & Sprüngli AG (registered) | 40 shares | 11'000.00 | 8.682 | 9 | 9 | 22 | +3'500.00"
- row: # / 3, cells: the sample shows "ConfedBond30 Swiss Confederation 1.25% 2030 | 1'500'000 CHF | 5'025.00 | 521'517.413 | 520'000 | 450'000 | 530'000 | -1'525.00" and the generated page "ConfedBond30 Swiss Confederation 1.25% 2030 | 1'500'000 CHF | 5'025.00 | 521'517.413 | 520'000 | 520'000 | 460'000 | -1'525.00"
- row: # / 4, cells: the sample shows "NestleBond27 Nestlé Holdings 0.875% 2027 | 1'500'000 CHF | 4'990.00 | 526'528.056 | 525'000 | 450'000 | 525'000 | -1'525.00" and the generated page "NestleBond27 Nestlé Holdings 0.875% 2027 | 1'500'000 CHF | 4'990.00 | 526'528.056 | 525'000 | 525'000 | 450'000 | -1'525.00"
- row: # / 5, cells: the sample shows "RocheBond28 Roche Holdings 1.0% 2028 | 1'000'000 CHF | 4'950.00 | 351'540.404 | 350'000 | 300'000 | 350'000 | -1'525.00" and the generated page "RocheBond28 Roche Holdings 1.0% 2028 | 1'000'000 CHF | 4'950.00 | 351'540.404 | 350'000 | 350'000 | 300'000 | -1'525.00"
- row: # / 6, cells: the sample shows "GIVN Givaudan SA | 100 shares | 3'700.00 | 35.412 | 35 | 30 | 35 | -1'525.00" and the generated page "GIVN Givaudan SA | 100 shares | 3'700.00 | 35.412 | 35 | 35 | 30 | -1'525.00"
- row: # / 7, cells: the sample shows "PGHN Partners Group Holding AG | 250 shares | 1'200.00 | 88.771 | 89 | 75 | 86 | +275.00" and the generated page "PGHN Partners Group Holding AG | 250 shares | 1'200.00 | 88.771 | 89 | 89 | 72 | +275.00"
- row: # / 8, cells: the sample shows "BundDE30 Bund 0% 2030 | 1'000'000 EUR | 940.80 | 349'707.696 | 350'000 | 300'000 | 350'000 | +275.00" and the generated page "BundDE30 Bund 0% 2030 | 1'000'000 EUR | 940.80 | 349'707.696 | 350'000 | 350'000 | 300'000 | +275.00"
- row: # / 9, cells: the sample shows "TreasuryUS27 US Treasury 2.5% 2027 | 1'200'000 USD | 891.00 | 419'691.358 | 420'000 | 360'000 | 420'000 | +275.00" and the generated page "TreasuryUS27 US Treasury 2.5% 2027 | 1'200'000 USD | 891.00 | 419'691.358 | 420'000 | 420'000 | 360'000 | +275.00"
- row: # / 10, cells: the sample shows "GEBN Geberit AG | 500 shares | 590.00 | 174.534 | 175 | 150 | 175 | +275.00" and the generated page "GEBN Geberit AG | 500 shares | 590.00 | 174.534 | 175 | 175 | 150 | +275.00"
- row: # / 11, cells: the sample shows "LONN Lonza Group AG | 600 shares | 530.00 | 209.481 | 209 | 180 | 211 | -255.00" and the generated page "LONN Lonza Group AG | 600 shares | 530.00 | 209.481 | 209 | 209 | 182 | -255.00"
- row: # / 12, cells: the sample shows "ZURN Zurich Insurance Group AG | 600 shares | 530.00 | 210.481 | 210 | 180 | 210 | -255.00" and the generated page "ZURN Zurich Insurance Group AG | 600 shares | 530.00 | 210.481 | 210 | 210 | 180 | -255.00"
- row: # / 13, cells: the sample shows "SCMN Swisscom AG | 600 shares | 530.00 | 210.481 | 210 | 180 | 210 | -255.00" and the generated page "SCMN Swisscom AG | 600 shares | 530.00 | 210.481 | 210 | 210 | 180 | -255.00"
- row: # / 14, cells: the sample shows "VACN VAT Group AG | 800 shares | 380.00 | 280.671 | 281 | 240 | 279 | +125.00" and the generated page "VACN VAT Group AG | 800 shares | 380.00 | 280.671 | 281 | 281 | 238 | +125.00"
- row: # / 15, cells: the sample shows "ROG Roche Holding AG | 2'000 shares | 240.00 | 699.479 | 699 | 600 | 701 | -115.00" and the generated page "ROG Roche Holding AG | 2'000 shares | 240.00 | 699.479 | 699 | 699 | 602 | -115.00"
- row: # / 16, cells: the sample shows "SOON Sonova Holding AG | 1'200 shares | 240.00 | 420.479 | 420 | 360 | 420 | -115.00" and the generated page "SOON Sonova Holding AG | 1'200 shares | 240.00 | 420.479 | 420 | 420 | 360 | -115.00"
- row: # / 17, cells: the sample shows "SCHP Schindler Holding AG (PS) | 1'200 shares | 230.00 | 420.500 | 420 | 360 | 420 | -115.00" and the generated page "SCHP Schindler Holding AG (PS) | 1'200 shares | 230.00 | 420.500 | 420 | 420 | 360 | -115.00"
- row: # / 18, cells: the sample shows "SIKA Sika AG | 1'300 shares | 220.00 | 455.523 | 456 | 390 | 454 | +105.00" and the generated page "SIKA Sika AG | 1'300 shares | 220.00 | 455.523 | 456 | 456 | 388 | +105.00"
- row: # / 19, cells: the sample shows "KNIN Kuehne + Nagel International AG | 1'300 shares | 220.00 | 454.523 | 455 | 390 | 455 | +105.00" and the generated page "KNIN Kuehne + Nagel International AG | 1'300 shares | 220.00 | 454.523 | 455 | 455 | 390 | +105.00"
- row: # / 20, cells: the sample shows "UHR The Swatch Group AG | 1'500 shares | 180.00 | 524.417 | 524 | 450 | 526 | -75.00" and the generated page "UHR The Swatch Group AG | 1'500 shares | 180.00 | 524.417 | 524 | 524 | 452 | -75.00"
- row: # / 21, cells: the sample shows "CFR Compagnie Financière Richemont SA | 2'000 shares | 130.00 | 700.577 | 701 | 600 | 699 | +55.00" and the generated page "CFR Compagnie Financière Richemont SA | 2'000 shares | 130.00 | 700.577 | 701 | 701 | 598 | +55.00"
- row: # / 22, cells: the sample shows "SREN Swiss Re AG | 2'000 shares | 130.00 | 699.577 | 700 | 600 | 700 | +55.00" and the generated page "SREN Swiss Re AG | 2'000 shares | 130.00 | 699.577 | 700 | 700 | 600 | +55.00"
- row: # / 23, cells: the sample shows "STMN Straumann Holding AG | 2'500 shares | 110.00 | 874.500 | 874 | 750 | 876 | -55.00" and the generated page "STMN Straumann Holding AG | 2'500 shares | 110.00 | 874.500 | 874 | 874 | 752 | -55.00"
- row: # / 24, cells: the sample shows "LOGN Logitech International SA | 3'000 shares | 90.00 | 1'050.611 | 1'051 | 900 | 1'049 | +35.00" and the generated page "LOGN Logitech International SA | 3'000 shares | 90.00 | 1'050.611 | 1'051 | 1'051 | 898 | +35.00"
- row: # / 25, cells: the sample shows "NOVN Novartis AG | 3'000 shares | 90.00 | 1'049.611 | 1'050 | 900 | 1'050 | +35.00" and the generated page "NOVN Novartis AG | 3'000 shares | 90.00 | 1'049.611 | 1'050 | 1'050 | 900 | +35.00"
- row: # / 26, cells: the sample shows "SGSN SGS SA | 3'000 shares | 87.00 | 1'049.598 | 1'050 | 900 | 1'050 | +35.00" and the generated page "SGSN SGS SA | 3'000 shares | 87.00 | 1'049.598 | 1'050 | 1'050 | 900 | +35.00"
- row: # / 27, cells: the sample shows "HOLN Holcim AG | 3'500 shares | 80.00 | 1'224.563 | 1'225 | 1'050 | 1'225 | +35.00" and the generated page "HOLN Holcim AG | 3'500 shares | 80.00 | 1'224.563 | 1'225 | 1'225 | 1'050 | +35.00"
- row: # / 28, cells: the sample shows "NESN Nestlé S.A. | 3'500 shares | 80.00 | 1'224.563 | 1'225 | 1'050 | 1'225 | +35.00" and the generated page "NESN Nestlé S.A. | 3'500 shares | 80.00 | 1'224.563 | 1'225 | 1'225 | 1'050 | +35.00"
- row: # / 29, cells: the sample shows "ALC Alcon AG | 3'500 shares | 75.00 | 1'224.533 | 1'225 | 1'050 | 1'225 | +35.00" and the generated page "ALC Alcon AG | 3'500 shares | 75.00 | 1'224.533 | 1'225 | 1'225 | 1'050 | +35.00"
- row: # / 30, cells: the sample shows "BAER Julius Bär Group AG | 4'500 shares | 60.00 | 1'574.417 | 1'574 | 1'350 | 1'576 | -25.00" and the generated page "BAER Julius Bär Group AG | 4'500 shares | 60.00 | 1'574.417 | 1'574 | 1'574 | 1'352 | -25.00"
- row: # / 31, cells: the sample shows "BANB Bachem Holding AG | 4'500 shares | 60.00 | 1'575.417 | 1'575 | 1'350 | 1'575 | -25.00" and the generated page "BANB Bachem Holding AG | 4'500 shares | 60.00 | 1'575.417 | 1'575 | 1'575 | 1'350 | -25.00"
- row: # / 32, cells: the sample shows "ABBN ABB Ltd | 5'500 shares | 50.00 | 1'925.500 | 1'925 | 1'650 | 1'925 | -25.00" and the generated page "ABBN ABB Ltd | 5'500 shares | 50.00 | 1'925.500 | 1'925 | 1'925 | 1'650 | -25.00"
- row: # / 33, cells: the sample shows "SDZ Sandoz Group AG | 7'000 shares | 38.00 | 2'450.658 | 2'451 | 2'100 | 2'449 | +13.00" and the generated page "SDZ Sandoz Group AG | 7'000 shares | 38.00 | 2'450.658 | 2'451 | 2'451 | 2'098 | +13.00"
- row: # / 34, cells: the sample shows "ADEN Adecco Group AG | 9'000 shares | 30.00 | 3'149.567 | 3'150 | 2'700 | 3'150 | +13.00" and the generated page "ADEN Adecco Group AG | 9'000 shares | 30.00 | 3'149.567 | 3'150 | 3'150 | 2'700 | +13.00"
- row: # / 35, cells: the sample shows "UBSG UBS Group AG | 10'000 shares | 28.00 | 3'499.536 | 3'500 | 3'000 | 3'500 | +13.00" and the generated page "UBSG UBS Group AG | 10'000 shares | 28.00 | 3'499.536 | 3'500 | 3'500 | 3'000 | +13.00"
- row: # / 36, cells: the sample shows "cash GBP Cash GBP | 200'000.00 GBP | 0.0113 | 69'988.496 | 69'988.50 | 60'000.00 | 70'011.50 | +0.01" and the generated page "cash GBP Cash GBP | 200'000.00 GBP | 0.0113 | 69'988.496 | 69'988.50 | 69'988.50 | 60'023.00 | +0.01"
- row: # / 37, cells: the sample shows "cash CHF Cash CHF | 1'000'000.00 CHF | 0.0100 | 349'999.995 | 349'999.99 | 300'000.00 | 350'000.01 | -0.01" and the generated page "cash CHF Cash CHF | 1'000'000.00 CHF | 0.0100 | 349'999.995 | 349'999.99 | 349'999.99 | 300'000.02 | -0.01"
- row: # / 38, cells: the sample shows "cash EUR Cash EUR | 1'800'000.00 EUR | 0.0098 | 630'000.005 | 630'000.01 | 540'000.00 | 629'999.99 | 0.00" and the generated page "cash EUR Cash EUR | 1'800'000.00 EUR | 0.0098 | 630'000.005 | 630'000.01 | 630'000.01 | 539'999.98 | 0.00"
- row: # / 39, cells: the sample shows "cash USD Cash USD | 500'000.00 USD | 0.0090 | 174'999.995 | 174'999.99 | 150'000.00 | 175'000.01 | 0.00" and the generated page "cash USD Cash USD | 500'000.00 USD | 0.0090 | 174'999.995 | 174'999.99 | 174'999.99 | 150'000.02 | 0.00"
- row: # / 40, cells: the sample shows "cash DKK Cash DKK | 1'000'000.00 DKK | 0.0013 | 350'000.032 | 350'000.03 | 300'000.00 | 349'999.97 | 0.00" and the generated page "cash DKK Cash DKK | 1'000'000.00 DKK | 0.0013 | 350'000.032 | 350'000.03 | 350'000.03 | 299'999.94 | 0.00"
- row: # / 41, cells: the sample shows "cash SEK Cash SEK | 1'000'000.00 SEK | 0.0009 | 350'000.003 | 350'000.00 | 300'000.00 | 350'000.00 | 0.00" and the generated page "cash SEK Cash SEK | 1'000'000.00 SEK | 0.0009 | 350'000.003 | 350'000.00 | 350'000.00 | 300'000.00 | 0.00"
- row: # / 42, cells: the sample shows "cash JPY Cash JPY | 30'000'000.00 JPY | 0.0001 | 10'500'000.048 | 10'500'000.05 | 9'000'000.00 | 10'499'999.95 | 0.00" and the generated page "cash JPY Cash JPY | 30'000'000.00 JPY | 0.0001 | 10'500'000.048 | 10'500'000.05 | 10'500'000.05 | 8'999'999.90 | 0.00"

## Only in the generated page (0)

## Different order (0)
