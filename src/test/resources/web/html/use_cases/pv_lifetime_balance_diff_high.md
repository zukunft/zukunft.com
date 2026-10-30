# Differences of pv_lifetime_balance_js.html#high to pv_lifetime_balance_sample.html#high

Change the generated page pv_lifetime_balance_js.html#high so that it shows what the hand made sample pv_lifetime_balance_sample.html#high shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (0)

## Different (30)

- field: Orientation, hint: the sample shows "Yield factor 1.00 relative to south 30° [7]" and the generated page "Yield factor relative to south 30°"
- field: PV life-cycle emissions, hint: the sample shows "Swiss 3 kWp mono-Si pitched roof, 2023 update [1]; converted to a fixed amount per kWp" and the generated page "Swiss 3 kWp mono-Si pitched roof, 2023 update; converted to a fixed amount per kWp [1]"
- field: Replacement mix, value: the sample shows "ENTSO-E production mix 2022 — ~395 g/kWh (midpoint of 350–440) [3]" and the generated page "Production mix"
- field: Battery round-trip efficiency, hint: the sample shows "" and the generated page "the usual system efficiency of a lithium-ion home storage incl. the inverter losses"
- field: Battery production emissions, hint: the sample shows "Literature spans ~37–212 kg CO₂-eq per kWh capacity [5][6]" and the generated page "Literature spans ~37–212 kg CO₂-eq per kWh capacity [3][4]"
- field: Battery lifetime, hint: the sample shows "" and the generated page "a lithium-ion home storage with one cycle a day lasts 10 to 20 years"
- row: Electricity / PV production, sub text: the sample shows "20.0 kWp × 1'200 kWh/kWp × factor 1.00" and the generated page ""
- row: Electricity / Self-consumption rate, cells: the sample shows " | 35 %" and the generated page " | 35.49 %"
- row: Electricity / Autarky, cells: the sample shows " | 93 %" and the generated page " | 92.92 %"
- row: Production emissions / PV system, sub text: the sample shows "1'464 kg CO₂-eq/kWp → 30.5 g/kWh at this site" and the generated page ""
- row: Production emissions / PV system, cells: the sample shows "29.3 t | 732 kg" and the generated page "10.5 t | 262 kg"
- row: Production emissions / Battery, sub text: the sample shows "2 × 20 kWh × 150 kg/kWh" and the generated page ""
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (200 g/kWh), sub text: the sample shows "payback 7.4 a" and the generated page "payback 3.46 a"
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (200 g/kWh), cells: the sample shows "155.5 t | 3'887 kg" and the generated page "174.3 t | 4'357 kg"
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted), sub text: the sample shows "payback 14.0 a" and the generated page "payback 6.52 a"
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted), cells: the sample shows "65.8 t | 1'646 kg" and the generated page "84.6 t | 2'116 kg"
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395 g/kWh), sub text: the sample shows "payback 3.7 a" and the generated page "payback 1.75 a"
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395 g/kWh), cells: the sample shows "341.5 t | 8'536 kg" and the generated page "360.3 t | 9'006 kg"
- row: Month / Jan, cells: the sample shows "600 | 900 | 540 | 57 | 0 | 303 | 151" and the generated page "600 | 900 | 540 | 57 | 0 | 303 | "
- row: Month / Feb, cells: the sample shows "1'080 | 810 | 486 | 324 | 253 | 0 | 147" and the generated page "1'080 | 810 | 486 | 324 | 253 | 0 | "
- row: Month / Mar, cells: the sample shows "1'920 | 810 | 486 | 324 | 1'093 | 0 | 141" and the generated page "1'920 | 810 | 486 | 324 | 1'093 | 0 | "
- row: Month / Apr, cells: the sample shows "2'640 | 720 | 432 | 288 | 1'905 | 0 | 100" and the generated page "2'640 | 720 | 432 | 288 | 1'905 | 0 | "
- row: Month / May, cells: the sample shows "3'000 | 675 | 405 | 270 | 2'311 | 0 | 69" and the generated page "3'000 | 675 | 405 | 270 | 2'311 | 0 | "
- row: Month / Jun, cells: the sample shows "3'120 | 630 | 378 | 252 | 2'477 | -0 | 80" and the generated page "3'120 | 630 | 378 | 252 | 2'477 | 0 | "
- row: Month / Jul, cells: the sample shows "3'240 | 630 | 378 | 252 | 2'597 | -0 | 88" and the generated page "3'240 | 630 | 378 | 252 | 2'597 | 0 | "
- row: Month / Aug, cells: the sample shows "2'880 | 630 | 378 | 252 | 2'237 | -0 | 89" and the generated page "2'880 | 630 | 378 | 252 | 2'237 | 0 | "
- row: Month / Sep, cells: the sample shows "2'400 | 675 | 405 | 270 | 1'711 | 0 | 130" and the generated page "2'400 | 675 | 405 | 270 | 1'711 | 0 | "
- row: Month / Oct, cells: the sample shows "1'680 | 765 | 459 | 306 | 899 | 0 | 135" and the generated page "1'680 | 765 | 459 | 306 | 899 | 0 | "
- row: Month / Nov, cells: the sample shows "840 | 855 | 513 | 311 | 0 | 31 | 164" and the generated page "840 | 855 | 513 | 311 | 0 | 31 | "
- row: Month / Dec, cells: the sample shows "600 | 900 | 540 | 57 | 0 | 303 | 153" and the generated page "600 | 900 | 540 | 57 | 0 | 303 | "

## Only in the generated page (3)

- field: CO2-eq electricity Europe life cycle production mix
- field: CO2-eq electricity Europe life cycle residual mix
- field: east-west yield factor

## Different order (0)
