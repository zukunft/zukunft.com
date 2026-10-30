# Differences of pv_lifetime_balance_js.html#low to pv_lifetime_balance_sample.html#low

Change the generated page pv_lifetime_balance_js.html#low so that it shows what the hand made sample pv_lifetime_balance_sample.html#low shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (1)

- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (60 g/kWh)

## Different (15)

- field: Orientation, hint: the sample shows "Yield factor 0.59 relative to south 30° [7]" and the generated page "Yield factor relative to south 30°"
- field: Replacement mix, value: the sample shows "ENTSO-E production mix 2022 — ~395 g/kWh (midpoint of 350–440) [3]" and the generated page "ENTSO-E production mix 2022 — 395 g/kWh [4]"
- row: Electricity / PV production, sub text: the sample shows "5.0 kWp × 800 kWh/kWp × factor 0.59" and the generated page "5.00 kWp × 800 kWh/kWp × factor 0.59"
- row: Electricity / Self-consumption rate, cells: the sample shows " | 72 %" and the generated page " | 72.32 %"
- row: Electricity / Autarky, cells: the sample shows " | 72 %" and the generated page " | 72.27 %"
- row: Production emissions / PV system, sub text: the sample shows "62.0 g/kWh at this site" and the generated page "222 g/kWh at this site"
- row: Production emissions / PV system, cells: the sample shows "2.9 t | 146 kg" and the generated page "10.48 t | 524.1 kg"
- row: Production emissions / Battery, sub text: the sample shows "2 × 5 kWh × 60 kg/kWh" and the generated page "2 × 5 kWh × 60.0 kg/kWh"
- row: Production emissions / Battery, cells: the sample shows "0.6 t | 30 kg" and the generated page "0.60 t | 30.0 kg"
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted), sub text: the sample shows "payback 15.9 a" and the generated page "payback 50.0 a"
- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted), cells: the sample shows "0.9 t | 45 kg" and the generated page "-6.65 t | -332.4 kg"
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395 g/kWh), sub text: the sample shows "payback 4.26 a" and the generated page "payback 13.4 a"
- row: CO₂-eq saved, net of production emissions / vs. replacement mix (395 g/kWh), cells: the sample shows "13.1 t | 653 kg" and the generated page "5.50 t | 274.9 kg"
- row: CO₂-eq saved, net of production emissions / Effect of the battery, cells: the sample shows "-0.9 t | -46 kg" and the generated page "-0.91 t | -45.7 kg"
- row: Month / Nov, cells: the sample shows "83 | 190 | 38 | 36 | 0 | 116 | 164" and the generated page "83 | 190 | 38 | 36 | -0 | 116 | 164"

## Only in the generated page (1)

- row: CO₂-eq saved, net of production emissions / vs. Swiss consumer mix, annual (60.0 g/kWh)

## Different order (0)
