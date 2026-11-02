# Differences of npp_ch_vote.html to npp_ch_vote_sample.html

Change the generated page npp_ch_vote.html so that it shows what the hand made sample npp_ch_vote_sample.html shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (4)

- button: Optimistic (5'000 CHF/kW)
- button: BAK base case
- button: Recent projects (12'000 CHF/kW)
- button: Academies pessimistic

## Different (58)

- field: Construction cost (overnight), unit: the sample shows "CHF/kW" and the generated page "CHF per kW"
- field: Construction cost (overnight), hint: the sample shows "Olkiluoto 3: 6'875 EUR/kW nominal; Flamanville 3: about 10'000 CHF/kW; BAK base case 8'826 CHF/kW incl. 11 % Swiss surcharge [5]" and the generated page "Olkiluoto 3: 6'875 EUR/kW nominal; Flamanville 3: about 10'000 CHF/kW; BAK base case 8'826 CHF/kW incl. 11 % Swiss surcharge [2]"
- field: Cost of capital (WACC, real), hint: the sample shows "8 % market rate for nuclear, 5 % if the state bears part of the risk [3]" and the generated page "8 % market rate for nuclear, 5 % if the state bears part of the risk [1]"
- field: Construction time, unit: the sample shows "a" and the generated page ""
- field: Construction time, hint: the sample shows "Olkiluoto 3 took 18 years, Flamanville 3 about 17; BAK assumes 10 [4][5]" and the generated page "Olkiluoto 3 took 18 years, Flamanville 3 about 17; BAK assumes 10 [3] [2]"
- field: New nuclear capacity, unit: the sample shows "GW" and the generated page ""
- field: New nuclear capacity, hint: the sample shows "One EPR 1.63 GW, one AP1000 1.157 GW, two AP1000 2.314 GW [5]" and the generated page "One EPR 1.63 GW, one AP1000 1.157 GW, two AP1000 2.314 GW [2]"
- field: Cost of the renewable alternative, unit: the sample shows "CHF/MWh" and the generated page "CHF per MWh"
- field: Cost of the renewable alternative, hint: the sample shows "Levelised cost 2050 of the PV and wind that would replace the nuclear kilowatt-hours: rooftop PV 50-130, wind 60-100 CHF/MWh [5]; Fraunhofer: PV 41-50, wind 43-92 EUR/MWh in Germany [9]" and the generated page "Levelised cost 2050 of the PV and wind that would replace the nuclear kilowatt-hours: rooftop PV 50-130, wind 60-100 CHF/MWh [2] ; Fraunhofer: PV 41-50, wind 43-92 EUR/MWh in Germany [4]"
- field: System cost of intermittent supply, unit: the sample shows "CHF/MWh" and the generated page "CHF per MWh"
- field: System cost of intermittent supply, hint: the sample shows "Storage, backup and grid cost per renewable MWh beyond the plant itself; no Swiss figure exists [5]" and the generated page "Storage, backup and grid cost per renewable MWh beyond the plant itself; no Swiss figure exists [2]"
- field: Value of secured winter supply, unit: the sample shows "CHF/MWh" and the generated page "CHF per MWh"
- field: Value of secured winter supply, hint: the sample shows "Per MWh of avoided winter import: 0 counts only the cost; BAK's 520 million CHF per year for 4.3 TWh is about 120 CHF/MWh [5]" and the generated page "Per MWh of avoided winter import: 0 counts only the cost; BAK's 520 million CHF per year for 4.3 TWh is about 120 CHF/MWh [2]"
- field: Capacity factor, hint: the sample shows "Swiss plants 85-90 % historically, OECD median 88 %; BAK uses 85 % [5]" and the generated page "Swiss plants 85-90 % historically, OECD median 88 %; BAK uses 85 % [2]"
- field: Economic lifetime, unit: the sample shows "a" and the generated page ""
- field: Economic lifetime, hint: the sample shows "" and the generated page "[2]"
- field: Operating cost incl. fuel, decommissioning, waste, unit: the sample shows "CHF/MWh" and the generated page "CHF per MWh"
- field: Operating cost incl. fuel, decommissioning, waste, hint: the sample shows "BAK: 39.3 CHF/MWh for the EPR, 37 for the AP1000; existing plants produce at 44 to 49 CHF/MWh all-in [5][10]" and the generated page "BAK: 39.3 CHF/MWh for the EPR, 37 for the AP1000; existing plants produce at 44 to 49 CHF/MWh all-in [2] [5]"
- field: Interest-during-construction factor, unit: the sample shows "share" and the generated page "%"
- field: Interest-during-construction factor, hint: the sample shows "Share of the construction time over which the full capital is on average financed (S-curve); 0.4 reproduces BAK's 2'868 million CHF of interest [5]" and the generated page "Share of the construction time over which the full capital is on average financed (S-curve); 0.4 reproduces BAK's 2'868 million CHF of interest [2]"
- field: Interest-during-construction factor, value: the sample shows "0.4" and the generated page "40"
- field: Electricity demand 2050, unit: the sample shows "TWh" and the generated page ""
- field: Electricity demand 2050, hint: the sample shows "Energy Perspectives 2050+ ZERO basis: 76 TWh; VSE: up to 90 TWh [4][5]" and the generated page "Energy Perspectives 2050+ ZERO basis: 76 TWh; VSE: up to 90 TWh [3] [2]"
- field: Winter share of nuclear output, unit: the sample shows "share" and the generated page "%"
- field: Winter share of nuclear output, hint: the sample shows "Revisions fall into summer, so 55 % of the output is winter output (October to March) [5]" and the generated page "Revisions fall into summer, so 55 % of the output is winter output (October to March) [2]"
- field: Winter share of nuclear output, value: the sample shows "0.55" and the generated page "55"
- field: Winter share of the renewable alternative, unit: the sample shows "share" and the generated page "%"
- field: Winter share of the renewable alternative, value: the sample shows "0.3" and the generated page "30"
- field: Winter import price 2050, unit: the sample shows "CHF/MWh" and the generated page "CHF per MWh"
- field: Winter import price 2050, hint: the sample shows "Winter peaks of 130-135 CHF/MWh against an annual average of about 90 CHF/MWh in the model year 2050 without new plants [5]" and the generated page "Winter peaks of 130-135 CHF/MWh against an annual average of about 90 CHF/MWh in the model year 2050 without new plants [2]"
- field: Summer export price 2050, unit: the sample shows "CHF/MWh" and the generated page "CHF per MWh"
- field: Market price 2050 (real), unit: the sample shows "CHF/MWh" and the generated page "CHF per MWh"
- field: Market price 2050 (real), hint: the sample shows "Axpo market simulation in prices of 2024; only used for the subsidy need, not for the verdict [5]" and the generated page "Axpo market simulation in prices of 2024; only used for the subsidy need, not for the verdict [2]"
- field: Winter net import 2050 without new plants, unit: the sample shows "TWh" and the generated page ""
- field: Winter net import 2050 without new plants, hint: the sample shows "BAK balance model 7.5 TWh; the four ETH/PSI models 5.4 to 12.4 TWh [3][5]" and the generated page "BAK balance model 7.5 TWh; the four ETH/PSI models 5.4 to 12.4 TWh [1] [2]"
- field: Grid, metering and levies per kWh, unit: the sample shows "Rp/kWh" and the generated page "Rp per kWh"
- field: Grid, metering and levies per kWh, hint: the sample shows "ElCom median 2026 for an H4 household: 27.7 Rp total, of which energy 12.11 Rp [11]" and the generated page "ElCom median 2026 for an H4 household: 27.7 Rp total, of which energy 12.11 Rp [6]"
- field: Energy tariff 2050 without new plants, unit: the sample shows "Rp/kWh" and the generated page "Rp per kWh"
- field: Energy tariff 2050 without new plants, hint: the sample shows "Taken as today's median energy tariff in real terms; only shifts the absolute level, not the difference" and the generated page "Taken as today's median energy tariff in real terms; only shifts the absolute level, not the difference [6]"
- field: Decision threshold, unit: the sample shows "Rp/kWh" and the generated page "Rp per kWh"
- row: New nuclear plant / Annual production, cells: the sample shows "12.14 TWh | 0 TWh | 12.14 TWh" and the generated page "12.14 | 0.00 | +12.14"
- row: New nuclear plant / Investment incl. interest during construction, cells: the sample shows "17.3 billion CHF |  | " and the generated page "17.3 CHF |  | "
- row: New nuclear plant / Levelised cost of electricity, sub text: the sample shows "75.1 capital + 39.3 operation" and the generated page "76.2 capital + 39.3 operation"
- row: New nuclear plant / Levelised cost of electricity, cells: the sample shows "114.4 CHF/MWh | 100.0 CHF/MWh | +14.4 CHF/MWh" and the generated page "114.4 CHF per MWh | 100.0 CHF per MWh | +14.4 CHF per MWh"
- row: New nuclear plant / Subsidy need above the market price, cells: the sample shows "612 million CHF/a |  | " and the generated page "612 CHF |  | "
- row: Winter balance / Winter net import, cells: the sample shows "4.5 TWh | 7.5 TWh | -3.0 TWh" and the generated page "4.5 | 7.5 | -3.0"
- row: Winter balance / Net value of the shifted trade, cells: the sample shows "182 million CHF/a |  | " and the generated page "182 CHF |  | "
- row: Winter balance / Value of secured winter supply, cells: the sample shows "0 million CHF/a |  | " and the generated page "0 CHF |  | "
- row: Cost for the consumers / Annual cost of the compared kilowatt-hours, cells: the sample shows "1'389 million CHF/a | 1'396 million CHF/a | -7 million CHF/a" and the generated page "1'389 CHF | 1'396 CHF | -7 CHF"
- row: Cost for the consumers / Average electricity cost, cells: the sample shows "12.09 Rp/kWh | 12.10 Rp/kWh | -0.01 Rp/kWh" and the generated page "12.09 Rp per kWh | 12.10 Rp per kWh | -0.01 Rp per kWh"
- row: Cost for the consumers / Household price, cells: the sample shows "27.69 Rp/kWh | 27.70 Rp/kWh | -0.01 Rp/kWh" and the generated page "27.69 Rp per kWh | 27.70 Rp per kWh | -0.01 Rp per kWh"
- row: Cost for the consumers / Household bill at 4'500 kWh, cells: the sample shows "1'246 CHF/a | 1'247 CHF/a | -0 CHF/a" and the generated page "1'246 CHF | 1'247 CHF | 0 CHF"
- row: Cost for the consumers / Break-even construction cost, cells: the sample shows "8'891 CHF/kW |  | " and the generated page "8'891 CHF per kW |  | "
- row: Construction cost / 5'000 CHF/kW, cells: the sample shows "66 (-0.77) | 82 (-0.53) | 111 (-0.07)" and the generated page " |  | "
- row: Construction cost / 8'000 CHF/kW, cells: the sample shows "83 (-0.51) | 107 (-0.12) | 154 (+0.62)" and the generated page " |  | "
- row: Construction cost / 8'826 CHF/kW, cells: the sample shows "87 (-0.44) | 114 (-0.01) | 166 (+0.81)" and the generated page " |  | "
- row: Construction cost / 12'000 CHF/kW, cells: the sample shows "105 (-0.17) | 141 (+0.42) | 211 (+1.54)" and the generated page " |  | "
- row: Construction cost / 16'000 CHF/kW, cells: the sample shows "126 (+0.18) | 176 (+0.97) | 268 (+2.45)" and the generated page " |  | "

## Only in the generated page (0)

## Different order (0)
