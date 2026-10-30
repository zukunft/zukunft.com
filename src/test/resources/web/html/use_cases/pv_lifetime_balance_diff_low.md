# Differences of pv_lifetime_balance_js.html#low to pv_lifetime_balance_sample.html#low

Change the generated page pv_lifetime_balance_js.html#low so that it shows what the hand made sample pv_lifetime_balance_sample.html#low shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (1)

- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (60 g/kWh)

## Different (30)

- field: Orientation, hint: the sample shows "Yield factor 0.59 relative to south 30° [7]" and the generated page "Yield factor relative to south 30°"
- field: PV life-cycle emissions, hint: the sample shows "Swiss 3 kWp mono-Si pitched roof, 2023 update [1]; converted to a fixed amount per kWp" and the generated page "Swiss 3 kWp mono-Si pitched roof, 2023 update; converted to a fixed amount per kWp [1]"
- field: Replacement mix, value: the sample shows "ENTSO-E production mix 2022 — ~395 g/kWh (midpoint of 350–440) [3]" and the generated page "Production mix"
- field: Battery round-trip efficiency, hint: the sample shows "" and the generated page "the usual system efficiency of a lithium-ion home storage incl. the inverter losses"
- field: Battery production emissions, hint: the sample shows "Literature spans ~37–212 kg CO₂-eq per kWh capacity [5][6]" and the generated page "Literature spans ~37–212 kg CO₂-eq per kWh capacity [3][4]"
- field: Battery lifetime, hint: the sample shows "" and the generated page "a lithium-ion home storage with one cycle a day lasts 10 to 20 years"
- row: Electricity / PV production, sub text: the sample shows "5.0 kWp × 800 kWh/kWp × factor 0.59" and the generated page ""
- row: Electricity / Self-consumption rate, cells: the sample shows " | 72 %" and the generated page " | 72.32 %"
- row: Electricity / Autarky, cells: the sample shows " | 72 %" and the generated page " | 72.27 %"
- row: Production emissions / PV system, sub text: the sample shows "586 kg CO₂-eq/kWp → 62.0 g/kWh at this site" and the generated page ""
- row: Production emissions / PV system, cells: the sample shows "2.9 t | 146 kg" and the generated page "10.48 t | 524.1 kg"
- row: Production emissions / Battery, sub text: the sample shows "2 × 5 kWh × 60 kg/kWh" and the generated page ""
- row: Production emissions / Battery, cells: the sample shows "0.6 t | 30 kg" and the generated page "0.60 t | 30.0 kg"
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted), sub text: the sample shows "payback 15.9 a" and the generated page "payback 50.0 a"
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted), cells: the sample shows "0.9 t | 45 kg" and the generated page "-6.65 t | -332.4 kg"
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395 g/kWh), sub text: the sample shows "payback 4.3 a" and the generated page "payback 13.4 a"
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395 g/kWh), cells: the sample shows "13.1 t | 653 kg" and the generated page "5.50 t | 274.9 kg"
- row: CO₂-eq saved, net of production emissions / Effect of the battery, cells: the sample shows "-0.9 t | -46 kg" and the generated page "-0.91 t | -45.7 kg"
- row: Month / Jan, cells: the sample shows "59 | 200 | 40 | 15 | 0 | 145 | 151" and the generated page "59 | 200 | 40 | 15 | 0 | 145 | "
- row: Month / Feb, cells: the sample shows "106 | 180 | 36 | 56 | 0 | 88 | 147" and the generated page "106 | 180 | 36 | 56 | 0 | 88 | "
- row: Month / Mar, cells: the sample shows "189 | 180 | 36 | 122 | 0 | 22 | 141" and the generated page "189 | 180 | 36 | 122 | 0 | 22 | "
- row: Month / Apr, cells: the sample shows "260 | 160 | 32 | 120 | 78 | 8 | 100" and the generated page "260 | 160 | 32 | 120 | 78 | 8 | "
- row: Month / May, cells: the sample shows "295 | 150 | 30 | 120 | 115 | 0 | 69" and the generated page "295 | 150 | 30 | 120 | 115 | 0 | "
- row: Month / Jun, cells: the sample shows "307 | 140 | 28 | 112 | 139 | 0 | 80" and the generated page "307 | 140 | 28 | 112 | 139 | 0 | "
- row: Month / Jul, cells: the sample shows "319 | 140 | 28 | 112 | 151 | 0 | 88" and the generated page "319 | 140 | 28 | 112 | 151 | 0 | "
- row: Month / Aug, cells: the sample shows "283 | 140 | 28 | 112 | 115 | 0 | 89" and the generated page "283 | 140 | 28 | 112 | 115 | 0 | "
- row: Month / Sep, cells: the sample shows "236 | 150 | 30 | 120 | 56 | 0 | 130" and the generated page "236 | 150 | 30 | 120 | 56 | 0 | "
- row: Month / Oct, cells: the sample shows "165 | 170 | 34 | 105 | 0 | 31 | 135" and the generated page "165 | 170 | 34 | 105 | 0 | 31 | "
- row: Month / Nov, cells: the sample shows "83 | 190 | 38 | 36 | 0 | 116 | 164" and the generated page "83 | 190 | 38 | 36 | -0 | 116 | "
- row: Month / Dec, cells: the sample shows "59 | 200 | 40 | 15 | 0 | 145 | 153" and the generated page "59 | 200 | 40 | 15 | 0 | 145 | "

## Only in the generated page (4)

- field: CO2-eq electricity Europe life cycle production mix
- field: CO2-eq electricity Europe life cycle residual mix
- field: east-west yield factor
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (60.0 g/kWh)

## Different order (0)
