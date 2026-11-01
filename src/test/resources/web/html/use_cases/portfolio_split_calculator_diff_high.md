# Differences of portfolio_split_calculator_js.html#high to portfolio_split_calculator_sample.html#high

Change the generated page portfolio_split_calculator_js.html#high so that it shows what the hand made sample portfolio_split_calculator_sample.html#high shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (1)

- button: Report

## Different (48)

- field: Target weight parent, value: the sample shows "10 %" and the generated page "30 %"
- field: Exchange rate, tag: the sample shows "measured" and the generated page ""
- field: Exchange rate, unit: the sample shows "CHF per DKK" and the generated page ""
- field: Exchange rate, hint: the sample shows "CHF per unit of currency, bank report [2]" and the generated page "CHF per unit of currency, bank report"
- field: Exchange rate, value: the sample shows "0.1304" and the generated page ""
- row: Target / Target weight, cells: the sample shows "10.0 % | 45.0 % | 45.0 % | 100.0 %" and the generated page "30.0 % | 45.0 % | 45.0 % | 100.0 %"
- row: Target / Target value, cells: the sample shows "1'961'090 | 8'824'905 | 8'824'905 | 19'610'900" and the generated page "5'883'270 | 8'824'905 | 8'824'905 | 19'610'900"
- row: Realised / Stocks, cells: the sample shows "976'876 | 4'395'812 | 4'395'812 | 9'768'500" and the generated page "2'920'474 | 3'424'013 | 3'424'013 | 9'768'500"
- row: Realised / Bonds, cells: the sample shows "600'450 | 2'702'025 | 2'702'025 | 6'004'500" and the generated page "1'811'400 | 2'096'550 | 2'096'550 | 6'004'500"
- row: Realised / Cash, cells: the sample shows "383'764 | 1'727'068 | 1'727'068 | 3'837'900" and the generated page "1'151'396 | 1'343'252 | 1'343'252 | 3'837'900"
- row: Realised / Portfolio value, cells: the sample shows "1'961'090 | 8'824'905 | 8'824'905 | 19'610'900" and the generated page "5'883'270 | 6'863'815 | 6'863'815 | 19'610'900"
- row: Realised / Realised weight, cells: the sample shows "10.000 % | 45.000 % | 45.000 % | " and the generated page "30.000 % | 35.000 % | 35.000 % | "
- row: Realised / Deviation from target, cells: the sample shows "0.00 | 0.00 | 0.00 | 0.00" and the generated page "0.00 | -1'961'090.00 | -1'961'090.00 | 0.00"
- row: # / 3, cells: the sample shows "GIVN Givaudan SA | 100 shares | 3'700.00 | 44.054 | 44 | 44 | 12 | -200.00" and the generated page "ConfedBond30 Swiss Confederation 1.25% 2030 | 1'500'000 CHF | 5'025.00 | 671'517.413 | 670'000 | 670'000 | 160'000 | -1'525.00"
- row: # / 4, cells: the sample shows "PGHN Partners Group Holding AG | 250 shares | 1'200.00 | 112.667 | 113 | 113 | 24 | +400.00" and the generated page "NestleBond27 Nestlé Holdings 0.875% 2027 | 1'500'000 CHF | 4'990.00 | 676'528.056 | 675'000 | 675'000 | 150'000 | -1'525.00"
- row: # / 5, cells: the sample shows "GEBN Geberit AG | 500 shares | 590.00 | 224.322 | 224 | 224 | 52 | -190.00" and the generated page "RocheBond28 Roche Holdings 1.0% 2028 | 1'000'000 CHF | 4'950.00 | 451'540.404 | 450'000 | 450'000 | 100'000 | -1'525.00"
- row: # / 6, cells: the sample shows "LONN Lonza Group AG | 600 shares | 530.00 | 270.358 | 270 | 270 | 60 | -190.00" and the generated page "GIVN Givaudan SA | 100 shares | 3'700.00 | 45.412 | 45 | 45 | 10 | -1'525.00"
- row: # / 7, cells: the sample shows "ZURN Zurich Insurance Group AG | 600 shares | 530.00 | 270.358 | 270 | 270 | 60 | -190.00" and the generated page "PGHN Partners Group Holding AG | 250 shares | 1'200.00 | 113.771 | 114 | 114 | 22 | +275.00"
- row: # / 8, cells: the sample shows "SCMN Swisscom AG | 600 shares | 530.00 | 270.358 | 270 | 270 | 60 | -190.00" and the generated page "BundDE30 Bund 0% 2030 | 1'000'000 EUR | 940.80 | 449'707.696 | 450'000 | 450'000 | 100'000 | +275.00"
- row: # / 9, cells: the sample shows "VACN VAT Group AG | 800 shares | 380.00 | 360.500 | 361 | 361 | 78 | +190.00" and the generated page "TreasuryUS27 US Treasury 2.5% 2027 | 1'200'000 USD | 891.00 | 539'691.358 | 540'000 | 540'000 | 120'000 | +275.00"
- row: # / 10, cells: the sample shows "ROG Roche Holding AG | 2'000 shares | 240.00 | 899.208 | 899 | 899 | 202 | -50.00" and the generated page "GEBN Geberit AG | 500 shares | 590.00 | 224.534 | 225 | 225 | 50 | +275.00"
- row: # / 11, cells: the sample shows "SOON Sonova Holding AG | 1'200 shares | 240.00 | 540.208 | 540 | 540 | 120 | -50.00" and the generated page "LONN Lonza Group AG | 600 shares | 530.00 | 269.481 | 269 | 269 | 62 | -255.00"
- row: # / 12, cells: the sample shows "SCHP Schindler Holding AG (PS) | 1'200 shares | 230.00 | 540.217 | 540 | 540 | 120 | -50.00" and the generated page "ZURN Zurich Insurance Group AG | 600 shares | 530.00 | 270.481 | 270 | 270 | 60 | -255.00"
- row: # / 13, cells: the sample shows "SIKA Sika AG | 1'300 shares | 220.00 | 585.227 | 585 | 585 | 130 | -50.00" and the generated page "SCMN Swisscom AG | 600 shares | 530.00 | 270.481 | 270 | 270 | 60 | -255.00"
- row: # / 14, cells: the sample shows "KNIN Kuehne + Nagel International AG | 1'300 shares | 220.00 | 585.227 | 585 | 585 | 130 | -50.00" and the generated page "VACN VAT Group AG | 800 shares | 380.00 | 360.671 | 361 | 361 | 78 | +125.00"
- row: # / 15, cells: the sample shows "UHR The Swatch Group AG | 1'500 shares | 180.00 | 675.278 | 675 | 675 | 150 | -50.00" and the generated page "ROG Roche Holding AG | 2'000 shares | 240.00 | 899.479 | 899 | 899 | 202 | -115.00"
- row: # / 16, cells: the sample shows "CFR Compagnie Financière Richemont SA | 2'000 shares | 130.00 | 900.385 | 900 | 900 | 200 | -50.00" and the generated page "SOON Sonova Holding AG | 1'200 shares | 240.00 | 540.479 | 540 | 540 | 120 | -115.00"
- row: # / 17, cells: the sample shows "SREN Swiss Re AG | 2'000 shares | 130.00 | 900.385 | 900 | 900 | 200 | -50.00" and the generated page "SCHP Schindler Holding AG (PS) | 1'200 shares | 230.00 | 540.500 | 541 | 541 | 118 | +115.00"
- row: # / 18, cells: the sample shows "STMN Straumann Holding AG | 2'500 shares | 110.00 | 1'125.455 | 1'125 | 1'125 | 250 | -50.00" and the generated page "SIKA Sika AG | 1'300 shares | 220.00 | 584.477 | 584 | 584 | 132 | -105.00"
- row: # / 19, cells: the sample shows "LOGN Logitech International SA | 3'000 shares | 90.00 | 1'350.556 | 1'351 | 1'351 | 298 | +40.00" and the generated page "KNIN Kuehne + Nagel International AG | 1'300 shares | 220.00 | 585.477 | 585 | 585 | 130 | -105.00"
- row: # / 20, cells: the sample shows "NOVN Novartis AG | 3'000 shares | 90.00 | 1'349.556 | 1'350 | 1'350 | 300 | +40.00" and the generated page "UHR The Swatch Group AG | 1'500 shares | 180.00 | 675.583 | 676 | 676 | 148 | +75.00"
- row: # / 21, cells: the sample shows "SGSN SGS SA | 3'000 shares | 87.00 | 1'349.540 | 1'350 | 1'350 | 300 | +40.00" and the generated page "CFR Compagnie Financière Richemont SA | 2'000 shares | 130.00 | 899.423 | 899 | 899 | 202 | -55.00"
- row: # / 22, cells: the sample shows "HOLN Holcim AG | 3'500 shares | 80.00 | 1'574.500 | 1'575 | 1'575 | 350 | +40.00" and the generated page "SREN Swiss Re AG | 2'000 shares | 130.00 | 900.423 | 900 | 900 | 200 | -55.00"
- row: # / 23, cells: the sample shows "NESN Nestlé S.A. | 3'500 shares | 80.00 | 1'574.500 | 1'575 | 1'575 | 350 | +40.00" and the generated page "STMN Straumann Holding AG | 2'500 shares | 110.00 | 1'125.500 | 1'126 | 1'126 | 248 | +55.00"
- row: # / 24, cells: the sample shows "ALC Alcon AG | 3'500 shares | 75.00 | 1'574.467 | 1'574 | 1'574 | 352 | -35.00" and the generated page "LOGN Logitech International SA | 3'000 shares | 90.00 | 1'349.389 | 1'349 | 1'349 | 302 | -35.00"
- row: # / 25, cells: the sample shows "BAER Julius Bär Group AG | 4'500 shares | 60.00 | 2'025.583 | 2'026 | 2'026 | 448 | +25.00" and the generated page "NOVN Novartis AG | 3'000 shares | 90.00 | 1'350.389 | 1'350 | 1'350 | 300 | -35.00"
- row: # / 26, cells: the sample shows "BANB Bachem Holding AG | 4'500 shares | 60.00 | 2'024.583 | 2'025 | 2'025 | 450 | +25.00" and the generated page "SGSN SGS SA | 3'000 shares | 87.00 | 1'350.402 | 1'350 | 1'350 | 300 | -35.00"
- row: # / 27, cells: the sample shows "ABBN ABB Ltd | 5'500 shares | 50.00 | 2'474.500 | 2'475 | 2'475 | 550 | +25.00" and the generated page "HOLN Holcim AG | 3'500 shares | 80.00 | 1'575.438 | 1'575 | 1'575 | 350 | -35.00"
- row: # / 28, cells: the sample shows "SDZ Sandoz Group AG | 7'000 shares | 38.00 | 3'149.342 | 3'149 | 3'149 | 702 | -13.00" and the generated page "NESN Nestlé S.A. | 3'500 shares | 80.00 | 1'575.438 | 1'575 | 1'575 | 350 | -35.00"
- row: # / 29, cells: the sample shows "ADEN Adecco Group AG | 9'000 shares | 30.00 | 4'050.433 | 4'050 | 4'050 | 900 | -13.00" and the generated page "ALC Alcon AG | 3'500 shares | 75.00 | 1'575.467 | 1'575 | 1'575 | 350 | -35.00"
- row: # / 30, cells: the sample shows "UBSG UBS Group AG | 10'000 shares | 28.00 | 4'500.464 | 4'500 | 4'500 | 1'000 | -13.00" and the generated page "BAER Julius Bär Group AG | 4'500 shares | 60.00 | 2'025.583 | 2'026 | 2'026 | 448 | +25.00"
- row: # / 31, cells: the sample shows "ConfedBond30 Swiss Confederation 1.25% 2030 | 1'500'000 CHF | 5'025.00 | 675'012.935 | 675'000 | 675'000 | 150'000 | -13.00" and the generated page "BANB Bachem Holding AG | 4'500 shares | 60.00 | 2'024.583 | 2'025 | 2'025 | 450 | +25.00"
- row: # / 32, cells: the sample shows "NestleBond27 Nestlé Holdings 0.875% 2027 | 1'500'000 CHF | 4'990.00 | 675'013.026 | 675'000 | 675'000 | 150'000 | -13.00" and the generated page "ABBN ABB Ltd | 5'500 shares | 50.00 | 2'474.500 | 2'475 | 2'475 | 550 | +25.00"
- row: # / 33, cells: the sample shows "RocheBond28 Roche Holdings 1.0% 2028 | 1'000'000 CHF | 4'950.00 | 450'013.131 | 450'000 | 450'000 | 100'000 | -13.00" and the generated page "SDZ Sandoz Group AG | 7'000 shares | 38.00 | 3'149.342 | 3'149 | 3'149 | 702 | -13.00"
- row: # / 34, cells: the sample shows "BundDE30 Bund 0% 2030 | 1'000'000 EUR | 940.80 | 450'013.818 | 450'000 | 450'000 | 100'000 | -13.00" and the generated page "ADEN Adecco Group AG | 9'000 shares | 30.00 | 4'050.433 | 4'050 | 4'050 | 900 | -13.00"
- row: # / 35, cells: the sample shows "TreasuryUS27 US Treasury 2.5% 2027 | 1'200'000 USD | 891.00 | 540'014.590 | 540'000 | 540'000 | 120'000 | -13.00" and the generated page "UBSG UBS Group AG | 10'000 shares | 28.00 | 4'500.464 | 4'500 | 4'500 | 1'000 | -13.00"
- row: Position / ConfedBond30, cells: the sample shows "CHF | 1'500'000 | 100.5 % | 5'000" and the generated page "CHF | 1'500'000 | 100 % | 5'000"
- row: Position / NestleBond27, cells: the sample shows "CHF | 1'500'000 | 99.8 % | 5'000" and the generated page "CHF | 1'500'000 | 100 % | 5'000"

## Only in the generated page (0)

## Different order (0)
