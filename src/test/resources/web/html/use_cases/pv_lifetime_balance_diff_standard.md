# Differences of pv_lifetime_balance_js.html#standard to pv_lifetime_balance_sample.html#standard

Change the generated page pv_lifetime_balance_js.html#standard so that it shows what the hand made sample pv_lifetime_balance_sample.html#standard shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (0)

## Different (24)

- field: Orientation, hint: the sample shows "Yield factor 0.85 relative to south 30° [7]" and the generated page "Yield factor relative to south 30°"
- field: PV life-cycle emissions, hint: the sample shows "Swiss 3 kWp mono-Si pitched roof, 2023 update [1]; converted to a fixed amount per kWp" and the generated page "Swiss 3 kWp mono-Si pitched roof, 2023 update; converted to a fixed amount per kWp [1]"
- field: Replacement mix, value: the sample shows "European residual mix — 523 g/kWh [4]" and the generated page "Residual mix"
- field: Battery round-trip efficiency, hint: the sample shows "" and the generated page "the usual system efficiency of a lithium-ion home storage incl. the inverter losses"
- field: Battery production emissions, hint: the sample shows "Literature spans ~37–212 kg CO₂-eq per kWh capacity [5][6]" and the generated page "Literature spans ~37–212 kg CO₂-eq per kWh capacity [3][4]"
- field: Battery lifetime, hint: the sample shows "" and the generated page "a lithium-ion home storage with one cycle a day lasts 10 to 20 years"
- row: Electricity / PV production, sub text: the sample shows "10.0 kWp × 976 kWh/kWp × factor 0.85" and the generated page ""
- row: Electricity / Self-consumption rate, cells: the sample shows " | 49 %" and the generated page " | 48.77 %"
- row: Electricity / Autarky, cells: the sample shows " | 84 %" and the generated page " | 84.42 %"
- row: Production emissions / PV system, sub text: the sample shows "1'048 kg CO₂-eq/kWp → 42.1 g/kWh at this site" and the generated page ""
- row: Production emissions / Battery, sub text: the sample shows "2 × 10 kWh × 100 kg/kWh" and the generated page ""
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (523 g/kWh), sub text: the sample shows "payback 3.0 a" and the generated page "payback 2.97 a"
- row: Month / Jan, cells: the sample shows "207 | 450 | 158 | 45 | 0 | 248 | 151" and the generated page "207 | 450 | 158 | 45 | 0 | 248 | "
- row: Month / Feb, cells: the sample shows "373 | 405 | 142 | 208 | 0 | 55 | 147" and the generated page "373 | 405 | 142 | 208 | 0 | 55 | "
- row: Month / Mar, cells: the sample shows "664 | 405 | 142 | 263 | 229 | 0 | 141" and the generated page "664 | 405 | 142 | 263 | 229 | 0 | "
- row: Month / Apr, cells: the sample shows "913 | 360 | 126 | 234 | 527 | 0 | 100" and the generated page "913 | 360 | 126 | 234 | 527 | 0 | "
- row: Month / May, cells: the sample shows "1'037 | 338 | 118 | 219 | 675 | 0 | 69" and the generated page "1'037 | 338 | 118 | 219 | 675 | 0 | "
- row: Month / Jun, cells: the sample shows "1'078 | 315 | 110 | 205 | 741 | 0 | 80" and the generated page "1'078 | 315 | 110 | 205 | 741 | 0 | "
- row: Month / Jul, cells: the sample shows "1'120 | 315 | 110 | 205 | 782 | 0 | 88" and the generated page "1'120 | 315 | 110 | 205 | 782 | 0 | "
- row: Month / Aug, cells: the sample shows "996 | 315 | 110 | 205 | 658 | 0 | 89" and the generated page "996 | 315 | 110 | 205 | 658 | 0 | "
- row: Month / Sep, cells: the sample shows "830 | 338 | 118 | 219 | 468 | 0 | 130" and the generated page "830 | 338 | 118 | 219 | 468 | 0 | "
- row: Month / Oct, cells: the sample shows "581 | 383 | 134 | 249 | 171 | 0 | 135" and the generated page "581 | 383 | 134 | 249 | 171 | 0 | "
- row: Month / Nov, cells: the sample shows "290 | 428 | 150 | 127 | 0 | 151 | 164" and the generated page "290 | 428 | 150 | 127 | 0 | 151 | "
- row: Month / Dec, cells: the sample shows "207 | 450 | 158 | 45 | 0 | 248 | 153" and the generated page "207 | 450 | 158 | 45 | 0 | 248 | "

## Only in the generated page (3)

- field: CO2-eq electricity Europe life cycle production mix
- field: CO2-eq electricity Europe life cycle residual mix
- field: east-west yield factor

## Different order (0)
