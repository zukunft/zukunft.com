# Differences of pv_lifetime_balance_js.html#high to pv_lifetime_balance_sample.html#high

Change the generated page pv_lifetime_balance_js.html#high so that it shows what the hand made sample pv_lifetime_balance_sample.html#high shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (5)

- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (200 g/kWh)
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted)
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395 g/kWh)
- list item: Comparative LCA of Li-ion, Na-ion and solid-state cells, J. Industrial Ecology 29 (2025): 58–92 kg CO₂-eq/kWh cell, LFP down to ~37
- list item: HTW Berlin (Weniger et al.) 2019: Sind Solarstromspeicher Klimaschützer? (212 kg CO₂-eq/kWh)

## Different (33)

- field: Orientation, hint: the sample shows "Yield factor 1.00 relative to south 30° [7]" and the generated page "Yield factor relative to south 30°"
- field: PV life-cycle emissions, hint: the sample shows "Swiss 3 kWp mono-Si pitched roof, 2023 update [1]; converted to a fixed amount per kWp" and the generated page "Swiss 3 kWp mono-Si pitched roof, 2023 update; converted to a fixed amount per kWp [1]"
- field: Replacement mix, value: the sample shows "ENTSO-E production mix 2022 — ~395 g/kWh (midpoint of 350–440) [3]" and the generated page "Production mix"
- field: Battery round-trip efficiency, hint: the sample shows "" and the generated page "the usual system efficiency of a lithium-ion home storage incl. the inverter losses"
- field: Battery production emissions, tag: the sample shows "assumed 0.5" and the generated page "assumed"
- field: Battery production emissions, hint: the sample shows "Literature spans ~37–212 kg CO₂-eq per kWh capacity [5][6]" and the generated page "Literature spans ~37–212 kg CO₂-eq per kWh capacity"
- field: Battery lifetime, tag: the sample shows "assumed 0.6" and the generated page "assumed"
- field: Battery lifetime, hint: the sample shows "" and the generated page "a lithium-ion home storage with one cycle a day lasts 10 to 20 years"
- row: Electricity / PV production, sub text: the sample shows "20.0 kWp × 1'200 kWh/kWp × factor 1.00" and the generated page ""
- row: Electricity / Used directly, cells: the sample shows "216'000 kWh | 5'400 kWh" and the generated page "63'000 kWh | 1'575 kWh"
- row: Electricity / Used via battery, cells: the sample shows "118'506 kWh | 2'963 kWh" and the generated page "93'893 kWh | 2'347 kWh"
- row: Electricity / Exported to grid, cells: the sample shows "619'257 kWh | 15'481 kWh" and the generated page "798'166 kWh | 19'954 kWh"
- row: Electricity / Battery losses, cells: the sample shows "6'237 kWh | 156 kWh" and the generated page "4'942 kWh | 124 kWh"
- row: Electricity / Grid import still needed, cells: the sample shows "25'494 kWh | 637 kWh" and the generated page "203'107 kWh | 5'078 kWh"
- row: Electricity / Self-consumption rate, cells: the sample shows " | 35 %" and the generated page " | 16.86 %"
- row: Electricity / Autarky, cells: the sample shows " | 93 %" and the generated page " | 43.58 %"
- row: Production emissions / PV system, sub text: the sample shows "1'464 kg CO₂-eq/kWp → 30.5 g/kWh at this site" and the generated page ""
- row: Production emissions / PV system, cells: the sample shows "29.3 t | 732 kg" and the generated page "10.5 t | 262 kg"
- row: Production emissions / Battery, sub text: the sample shows "2 × 20 kWh × 150 kg/kWh" and the generated page ""
- row: CO₂-eq saved, net of production emissions / Effect of the battery, cells: the sample shows "-7.2 t | -181 kg" and the generated page "-7.0 t | -175 kg"
- row: Month / Jan, cells: the sample shows "600 | 900 | 540 | 57 | 0 | 303 | 151" and the generated page "207 | 450.0 | 158 | 47 | 0 | 245 | "
- row: Month / Feb, cells: the sample shows "1'080 | 810 | 486 | 324 | 253 | 0 | 147" and the generated page "373 | 405.0 | 142 | 220 | 0 | 43 | "
- row: Month / Mar, cells: the sample shows "1'920 | 810 | 486 | 324 | 1'093 | 0 | 141" and the generated page "664 | 405.0 | 142 | 278 | 229 | -15 | "
- row: Month / Apr, cells: the sample shows "2'640 | 720 | 432 | 288 | 1'905 | 0 | 100" and the generated page "913 | 360.0 | 126 | 247 | 527 | -13 | "
- row: Month / May, cells: the sample shows "3'000 | 675 | 405 | 270 | 2'311 | 0 | 69" and the generated page "1'037 | 337.5 | 118 | 232 | 675 | -12 | "
- row: Month / Jun, cells: the sample shows "3'120 | 630 | 378 | 252 | 2'477 | -0 | 80" and the generated page "1'078 | 315.0 | 110 | 216 | 741 | -11 | "
- row: Month / Jul, cells: the sample shows "3'240 | 630 | 378 | 252 | 2'597 | -0 | 88" and the generated page "1'120 | 315.0 | 110 | 216 | 782 | -11 | "
- row: Month / Aug, cells: the sample shows "2'880 | 630 | 378 | 252 | 2'237 | -0 | 89" and the generated page "996 | 315.0 | 110 | 216 | 658 | -11 | "
- row: Month / Sep, cells: the sample shows "2'400 | 675 | 405 | 270 | 1'711 | 0 | 130" and the generated page "830 | 337.5 | 118 | 232 | 468 | -12 | "
- row: Month / Oct, cells: the sample shows "1'680 | 765 | 459 | 306 | 899 | 0 | 135" and the generated page "581 | 382.5 | 134 | 262 | 171 | -14 | "
- row: Month / Nov, cells: the sample shows "840 | 855 | 513 | 311 | 0 | 31 | 164" and the generated page "290 | 427.5 | 150 | 134 | 0 | 144 | "
- row: Month / Dec, cells: the sample shows "600 | 900 | 540 | 57 | 0 | 303 | 153" and the generated page "207 | 450.0 | 158 | 47 | 0 | 245 | "
- row: Month / Year, cells: the sample shows "24'000 | 9'000 | 5'400 | 2'963 | 15'481 | 637 | 106" and the generated page "24'000 | 9'000 | 1'575 | 2'347 | 19'954 | 5'078 | 105.8"

## Only in the generated page (6)

- field: CO2-eq electricity Europe life cycle production mix
- field: CO2-eq electricity Europe life cycle residual mix
- field: east-west yield factor
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (200.0 g/kWh)
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (105.8 g/kWh)
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395.0 g/kWh)

## Different order (0)
