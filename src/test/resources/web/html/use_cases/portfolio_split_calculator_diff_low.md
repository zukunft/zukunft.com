# Differences of portfolio_split_calculator_js.html#low to portfolio_split_calculator_sample.html#low

Change the generated page portfolio_split_calculator_js.html#low so that it shows what the hand made sample portfolio_split_calculator_sample.html#low shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (0)

## Different (48)

- field: Target weight parent, value: the sample shows "60 %" and the generated page "30 %"
- field: Exchange rate, tag: the sample shows "measured" and the generated page ""
- field: Exchange rate, unit: the sample shows "CHF per DKK" and the generated page ""
- field: Exchange rate, hint: the sample shows "CHF per unit of currency, bank report [2]" and the generated page "CHF per unit of currency, bank report"
- field: Exchange rate, value: the sample shows "0.1304" and the generated page ""
- row: Target / Target weight, cells: the sample shows "60.0 % | 20.0 % | 20.0 % | 100.0 %" and the generated page "30.0 % | 20.0 % | 20.0 % | 100.0 %"
- row: Target / Target value, cells: the sample shows "11'766'540 | 3'922'180 | 3'922'180 | 19'610'900" and the generated page "5'883'270 | 3'922'180 | 3'922'180 | 19'610'900"
- row: Realised / Stocks, cells: the sample shows "5'861'100 | 1'953'700 | 1'953'700 | 9'768'500" and the generated page "2'920'474 | 3'424'013 | 3'424'013 | 9'768'500"
- row: Realised / Bonds, cells: the sample shows "3'602'700 | 1'200'900 | 1'200'900 | 6'004'500" and the generated page "1'811'400 | 2'096'550 | 2'096'550 | 6'004'500"
- row: Realised / Cash, cells: the sample shows "2'302'740 | 767'580 | 767'580 | 3'837'900" and the generated page "1'151'396 | 1'343'252 | 1'343'252 | 3'837'900"
- row: Realised / Portfolio value, cells: the sample shows "11'766'540 | 3'922'180 | 3'922'180 | 19'610'900" and the generated page "5'883'270 | 6'863'815 | 6'863'815 | 19'610'900"
- row: Realised / Realised weight, cells: the sample shows "60.000 % | 20.000 % | 20.000 % | " and the generated page "30.000 % | 35.000 % | 35.000 % | "
- row: Realised / Deviation from target, cells: the sample shows "0.00 | 0.00 | 0.00 | 0.00" and the generated page "0.00 | +2'941'635.00 | +2'941'635.00 | 0.00"
- row: # / 3, cells: the sample shows "GIVN Givaudan SA | 100 shares | 3'700.00 | 20.000 | 20 | 20 | 60 | 0.00" and the generated page "ConfedBond30 Swiss Confederation 1.25% 2030 | 1'500'000 CHF | 5'025.00 | 300'000.000 | 300'000 | 300'000 | 900'000 | 0.00"
- row: # / 4, cells: the sample shows "PGHN Partners Group Holding AG | 250 shares | 1'200.00 | 50.000 | 50 | 50 | 150 | 0.00" and the generated page "NestleBond27 Nestlé Holdings 0.875% 2027 | 1'500'000 CHF | 4'990.00 | 300'000.000 | 300'000 | 300'000 | 900'000 | 0.00"
- row: # / 5, cells: the sample shows "GEBN Geberit AG | 500 shares | 590.00 | 100.000 | 100 | 100 | 300 | 0.00" and the generated page "RocheBond28 Roche Holdings 1.0% 2028 | 1'000'000 CHF | 4'950.00 | 200'000.000 | 200'000 | 200'000 | 600'000 | 0.00"
- row: # / 6, cells: the sample shows "LONN Lonza Group AG | 600 shares | 530.00 | 120.000 | 120 | 120 | 360 | 0.00" and the generated page "GIVN Givaudan SA | 100 shares | 3'700.00 | 20.000 | 20 | 20 | 60 | 0.00"
- row: # / 7, cells: the sample shows "ZURN Zurich Insurance Group AG | 600 shares | 530.00 | 120.000 | 120 | 120 | 360 | 0.00" and the generated page "PGHN Partners Group Holding AG | 250 shares | 1'200.00 | 50.000 | 50 | 50 | 150 | 0.00"
- row: # / 8, cells: the sample shows "SCMN Swisscom AG | 600 shares | 530.00 | 120.000 | 120 | 120 | 360 | 0.00" and the generated page "BundDE30 Bund 0% 2030 | 1'000'000 EUR | 940.80 | 200'000.000 | 200'000 | 200'000 | 600'000 | 0.00"
- row: # / 9, cells: the sample shows "VACN VAT Group AG | 800 shares | 380.00 | 160.000 | 160 | 160 | 480 | 0.00" and the generated page "TreasuryUS27 US Treasury 2.5% 2027 | 1'200'000 USD | 891.00 | 240'000.000 | 240'000 | 240'000 | 720'000 | 0.00"
- row: # / 10, cells: the sample shows "ROG Roche Holding AG | 2'000 shares | 240.00 | 400.000 | 400 | 400 | 1'200 | 0.00" and the generated page "GEBN Geberit AG | 500 shares | 590.00 | 100.000 | 100 | 100 | 300 | 0.00"
- row: # / 11, cells: the sample shows "SOON Sonova Holding AG | 1'200 shares | 240.00 | 240.000 | 240 | 240 | 720 | 0.00" and the generated page "LONN Lonza Group AG | 600 shares | 530.00 | 120.000 | 120 | 120 | 360 | 0.00"
- row: # / 12, cells: the sample shows "SCHP Schindler Holding AG (PS) | 1'200 shares | 230.00 | 240.000 | 240 | 240 | 720 | 0.00" and the generated page "ZURN Zurich Insurance Group AG | 600 shares | 530.00 | 120.000 | 120 | 120 | 360 | 0.00"
- row: # / 13, cells: the sample shows "SIKA Sika AG | 1'300 shares | 220.00 | 260.000 | 260 | 260 | 780 | 0.00" and the generated page "SCMN Swisscom AG | 600 shares | 530.00 | 120.000 | 120 | 120 | 360 | 0.00"
- row: # / 14, cells: the sample shows "KNIN Kuehne + Nagel International AG | 1'300 shares | 220.00 | 260.000 | 260 | 260 | 780 | 0.00" and the generated page "VACN VAT Group AG | 800 shares | 380.00 | 160.000 | 160 | 160 | 480 | 0.00"
- row: # / 15, cells: the sample shows "UHR The Swatch Group AG | 1'500 shares | 180.00 | 300.000 | 300 | 300 | 900 | 0.00" and the generated page "ROG Roche Holding AG | 2'000 shares | 240.00 | 400.000 | 400 | 400 | 1'200 | 0.00"
- row: # / 16, cells: the sample shows "CFR Compagnie Financière Richemont SA | 2'000 shares | 130.00 | 400.000 | 400 | 400 | 1'200 | 0.00" and the generated page "SOON Sonova Holding AG | 1'200 shares | 240.00 | 240.000 | 240 | 240 | 720 | 0.00"
- row: # / 17, cells: the sample shows "SREN Swiss Re AG | 2'000 shares | 130.00 | 400.000 | 400 | 400 | 1'200 | 0.00" and the generated page "SCHP Schindler Holding AG (PS) | 1'200 shares | 230.00 | 240.000 | 240 | 240 | 720 | 0.00"
- row: # / 18, cells: the sample shows "STMN Straumann Holding AG | 2'500 shares | 110.00 | 500.000 | 500 | 500 | 1'500 | 0.00" and the generated page "SIKA Sika AG | 1'300 shares | 220.00 | 260.000 | 260 | 260 | 780 | 0.00"
- row: # / 19, cells: the sample shows "LOGN Logitech International SA | 3'000 shares | 90.00 | 600.000 | 600 | 600 | 1'800 | 0.00" and the generated page "KNIN Kuehne + Nagel International AG | 1'300 shares | 220.00 | 260.000 | 260 | 260 | 780 | 0.00"
- row: # / 20, cells: the sample shows "NOVN Novartis AG | 3'000 shares | 90.00 | 600.000 | 600 | 600 | 1'800 | 0.00" and the generated page "UHR The Swatch Group AG | 1'500 shares | 180.00 | 300.000 | 300 | 300 | 900 | 0.00"
- row: # / 21, cells: the sample shows "SGSN SGS SA | 3'000 shares | 87.00 | 600.000 | 600 | 600 | 1'800 | 0.00" and the generated page "CFR Compagnie Financière Richemont SA | 2'000 shares | 130.00 | 400.000 | 400 | 400 | 1'200 | 0.00"
- row: # / 22, cells: the sample shows "HOLN Holcim AG | 3'500 shares | 80.00 | 700.000 | 700 | 700 | 2'100 | 0.00" and the generated page "SREN Swiss Re AG | 2'000 shares | 130.00 | 400.000 | 400 | 400 | 1'200 | 0.00"
- row: # / 23, cells: the sample shows "NESN Nestlé S.A. | 3'500 shares | 80.00 | 700.000 | 700 | 700 | 2'100 | 0.00" and the generated page "STMN Straumann Holding AG | 2'500 shares | 110.00 | 500.000 | 500 | 500 | 1'500 | 0.00"
- row: # / 24, cells: the sample shows "ALC Alcon AG | 3'500 shares | 75.00 | 700.000 | 700 | 700 | 2'100 | 0.00" and the generated page "LOGN Logitech International SA | 3'000 shares | 90.00 | 600.000 | 600 | 600 | 1'800 | 0.00"
- row: # / 25, cells: the sample shows "BAER Julius Bär Group AG | 4'500 shares | 60.00 | 900.000 | 900 | 900 | 2'700 | 0.00" and the generated page "NOVN Novartis AG | 3'000 shares | 90.00 | 600.000 | 600 | 600 | 1'800 | 0.00"
- row: # / 26, cells: the sample shows "BANB Bachem Holding AG | 4'500 shares | 60.00 | 900.000 | 900 | 900 | 2'700 | 0.00" and the generated page "SGSN SGS SA | 3'000 shares | 87.00 | 600.000 | 600 | 600 | 1'800 | 0.00"
- row: # / 27, cells: the sample shows "ABBN ABB Ltd | 5'500 shares | 50.00 | 1'100.000 | 1'100 | 1'100 | 3'300 | 0.00" and the generated page "HOLN Holcim AG | 3'500 shares | 80.00 | 700.000 | 700 | 700 | 2'100 | 0.00"
- row: # / 28, cells: the sample shows "SDZ Sandoz Group AG | 7'000 shares | 38.00 | 1'400.000 | 1'400 | 1'400 | 4'200 | 0.00" and the generated page "NESN Nestlé S.A. | 3'500 shares | 80.00 | 700.000 | 700 | 700 | 2'100 | 0.00"
- row: # / 29, cells: the sample shows "ADEN Adecco Group AG | 9'000 shares | 30.00 | 1'800.000 | 1'800 | 1'800 | 5'400 | 0.00" and the generated page "ALC Alcon AG | 3'500 shares | 75.00 | 700.000 | 700 | 700 | 2'100 | 0.00"
- row: # / 30, cells: the sample shows "UBSG UBS Group AG | 10'000 shares | 28.00 | 2'000.000 | 2'000 | 2'000 | 6'000 | 0.00" and the generated page "BAER Julius Bär Group AG | 4'500 shares | 60.00 | 900.000 | 900 | 900 | 2'700 | 0.00"
- row: # / 31, cells: the sample shows "ConfedBond30 Swiss Confederation 1.25% 2030 | 1'500'000 CHF | 5'025.00 | 300'000.000 | 300'000 | 300'000 | 900'000 | 0.00" and the generated page "BANB Bachem Holding AG | 4'500 shares | 60.00 | 900.000 | 900 | 900 | 2'700 | 0.00"
- row: # / 32, cells: the sample shows "NestleBond27 Nestlé Holdings 0.875% 2027 | 1'500'000 CHF | 4'990.00 | 300'000.000 | 300'000 | 300'000 | 900'000 | 0.00" and the generated page "ABBN ABB Ltd | 5'500 shares | 50.00 | 1'100.000 | 1'100 | 1'100 | 3'300 | 0.00"
- row: # / 33, cells: the sample shows "RocheBond28 Roche Holdings 1.0% 2028 | 1'000'000 CHF | 4'950.00 | 200'000.000 | 200'000 | 200'000 | 600'000 | 0.00" and the generated page "SDZ Sandoz Group AG | 7'000 shares | 38.00 | 1'400.000 | 1'400 | 1'400 | 4'200 | 0.00"
- row: # / 34, cells: the sample shows "BundDE30 Bund 0% 2030 | 1'000'000 EUR | 940.80 | 200'000.000 | 200'000 | 200'000 | 600'000 | 0.00" and the generated page "ADEN Adecco Group AG | 9'000 shares | 30.00 | 1'800.000 | 1'800 | 1'800 | 5'400 | 0.00"
- row: # / 35, cells: the sample shows "TreasuryUS27 US Treasury 2.5% 2027 | 1'200'000 USD | 891.00 | 240'000.000 | 240'000 | 240'000 | 720'000 | 0.00" and the generated page "UBSG UBS Group AG | 10'000 shares | 28.00 | 2'000.000 | 2'000 | 2'000 | 6'000 | 0.00"
- row: Position / ConfedBond30, cells: the sample shows "CHF | 1'500'000 | 100.5 % | 5'000" and the generated page "CHF | 1'500'000 | 100 % | 5'000"
- row: Position / NestleBond27, cells: the sample shows "CHF | 1'500'000 | 99.8 % | 5'000" and the generated page "CHF | 1'500'000 | 100 % | 5'000"

## Only in the generated page (0)

## Different order (0)
