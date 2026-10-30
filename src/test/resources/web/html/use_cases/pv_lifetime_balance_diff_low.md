# Differences of pv_lifetime_balance_js.html#low to pv_lifetime_balance_sample.html#low

Change the generated page pv_lifetime_balance_js.html#low so that it shows what the hand made sample pv_lifetime_balance_sample.html#low shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (1)

- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (60 g/kWh)

## Different (38)

- field: Orientation, hint: the sample shows "Yield factor 0.59 relative to south 30° [7]" and the generated page "Yield factor relative to south 30°"
- field: PV life-cycle emissions, hint: the sample shows "Swiss 3 kWp mono-Si pitched roof, 2023 update [1]; converted to a fixed amount per kWp" and the generated page "Swiss 3 kWp mono-Si pitched roof, 2023 update; converted to a fixed amount per kWp [1]"
- field: Replacement mix, value: the sample shows "ENTSO-E production mix 2022 — ~395 g/kWh (midpoint of 350–440) [3]" and the generated page "Production mix"
- field: Battery round-trip efficiency, hint: the sample shows "" and the generated page "the usual system efficiency of a lithium-ion home storage incl. the inverter losses"
- field: Battery production emissions, tag: the sample shows "assumed 0.5" and the generated page "assumed"
- field: Battery production emissions, hint: the sample shows "Literature spans ~37–212 kg CO₂-eq per kWh capacity [5][6]" and the generated page "Literature spans ~37–212 kg CO₂-eq per kWh capacity [3][4]"
- field: Battery lifetime, tag: the sample shows "assumed 0.6" and the generated page "assumed"
- field: Battery lifetime, hint: the sample shows "" and the generated page "a lithium-ion home storage with one cycle a day lasts 10 to 20 years"
- row: Electricity / PV production, sub text: the sample shows "5.0 kWp × 800 kWh/kWp × factor 0.59" and the generated page ""
- row: Electricity / Used directly, cells: the sample shows "8'000 kWh | 400 kWh" and the generated page "31'500 kWh | 1'575 kWh"
- row: Electricity / Used via battery, cells: the sample shows "20'909 kWh | 1'045 kWh" and the generated page "39'534 kWh | 1'977 kWh"
- row: Electricity / Exported to grid, cells: the sample shows "13'064 kWh | 653 kWh" and the generated page "-33'717 kWh | -1'686 kWh"
- row: Electricity / Battery losses, cells: the sample shows "5'227 kWh | 261 kWh" and the generated page "9'883 kWh | 494 kWh"
- row: Electricity / Grid import still needed, cells: the sample shows "11'091 kWh | 555 kWh" and the generated page "-31'034 kWh | -1'552 kWh"
- row: Electricity / Self-consumption rate, cells: the sample shows " | 72 %" and the generated page " | 171.43 %"
- row: Electricity / Autarky, cells: the sample shows " | 72 %" and the generated page " | 177.58 %"
- row: Production emissions / PV system, sub text: the sample shows "586 kg CO₂-eq/kWp → 62.0 g/kWh at this site" and the generated page ""
- row: Production emissions / PV system, cells: the sample shows "2.9 t | 146 kg" and the generated page "10.48 t | 524.1 kg"
- row: Production emissions / Battery, sub text: the sample shows "2 × 5 kWh × 60 kg/kWh" and the generated page ""
- row: Production emissions / Battery, cells: the sample shows "0.6 t | 30 kg" and the generated page "0.60 t | 30.0 kg"
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted), sub text: the sample shows "payback 15.9 a" and the generated page ""
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted), cells: the sample shows "0.9 t | 45 kg" and the generated page "-7.13 t | -356.6 kg"
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395 g/kWh), sub text: the sample shows "payback 4.3 a" and the generated page ""
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395 g/kWh), cells: the sample shows "13.1 t | 653 kg" and the generated page "3.66 t | 182.9 kg"
- row: CO₂-eq saved, net of production emissions / Effect of the battery, cells: the sample shows "-0.9 t | -46 kg" and the generated page "-1.19 t | -59.7 kg"
- row: Month / Jan, cells: the sample shows "59 | 200 | 40 | 15 | 0 | 145 | 151" and the generated page "207 | 450.0 | 158 | 40 | 0 | 253 | "
- row: Month / Feb, cells: the sample shows "106 | 180 | 36 | 56 | 0 | 88 | 147" and the generated page "373 | 405.0 | 142 | 185 | 0 | 78 | "
- row: Month / Mar, cells: the sample shows "189 | 180 | 36 | 122 | 0 | 22 | 141" and the generated page "664 | 405.0 | 142 | 234 | 229 | 29 | "
- row: Month / Apr, cells: the sample shows "260 | 160 | 32 | 120 | 78 | 8 | 100" and the generated page "913 | 360.0 | 126 | 208 | 527 | 26 | "
- row: Month / May, cells: the sample shows "295 | 150 | 30 | 120 | 115 | 0 | 69" and the generated page "1'037 | 337.5 | 118 | 195 | 675 | 24 | "
- row: Month / Jun, cells: the sample shows "307 | 140 | 28 | 112 | 139 | 0 | 80" and the generated page "1'078 | 315.0 | 110 | 182 | 741 | 23 | "
- row: Month / Jul, cells: the sample shows "319 | 140 | 28 | 112 | 151 | 0 | 88" and the generated page "1'120 | 315.0 | 110 | 182 | 782 | 23 | "
- row: Month / Aug, cells: the sample shows "283 | 140 | 28 | 112 | 115 | 0 | 89" and the generated page "996 | 315.0 | 110 | 182 | 658 | 23 | "
- row: Month / Sep, cells: the sample shows "236 | 150 | 30 | 120 | 56 | 0 | 130" and the generated page "830 | 337.5 | 118 | 195 | 468 | 24 | "
- row: Month / Oct, cells: the sample shows "165 | 170 | 34 | 105 | 0 | 31 | 135" and the generated page "581 | 382.5 | 134 | 221 | 171 | 28 | "
- row: Month / Nov, cells: the sample shows "83 | 190 | 38 | 36 | 0 | 116 | 164" and the generated page "290 | 427.5 | 150 | 113 | 0 | 165 | "
- row: Month / Dec, cells: the sample shows "59 | 200 | 40 | 15 | 0 | 145 | 153" and the generated page "207 | 450.0 | 158 | 40 | 0 | 253 | "
- row: Month / Year, cells: the sample shows "2'360 | 2'000 | 400 | 1'045 | 653 | 555 | 106" and the generated page "2'360 | 2'000 | 1'575 | 1'977 | -1'686 | -1'552 | 105.8"

## Only in the generated page (4)

- field: CO2-eq electricity Europe life cycle production mix
- field: CO2-eq electricity Europe life cycle residual mix
- field: east-west yield factor
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (60.0 g/kWh)

## Different order (0)
