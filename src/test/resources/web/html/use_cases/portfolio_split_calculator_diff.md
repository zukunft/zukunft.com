# Differences of portfolio_split_calculator_no_js.html to portfolio_split_calculator_sample.html

Change the generated page portfolio_split_calculator_no_js.html so that it shows what the hand made sample portfolio_split_calculator_sample.html shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (15)

- heading: Check against the sample files
- heading: Cumulative deviation of the children along the walk
- heading: Notes
- heading: Inputs from
- table: 
- table: Value | Expected | Calculated | Status
- table:  (2)
- table: Position | Ccy | Quantity | Price | Round lot
- button: Add child
- button: Download
- button: Report
- button: Copy as zukunft.com JSON
- button: Copy as CSV
- button: Import portfolio
- button: Import FX rates

## Different (4)

- field: Target weight parent, unit: the sample shows "" and the generated page "percent"
- field: Target weight parent, hint: the sample shows "The parent keeps the remainder of every position" and the generated page "The parent keeps the remainder of every position [1]"
- field: Target weight parent, value: the sample shows "" and the generated page "0.30"
- field: Walk order, value: the sample shows "Unit value × round lot, descending" and the generated page "please select ..."

## Only in the generated page (25)

- heading: Details and sources
- heading: Sources
- field: Target weight
- field: Mirror the children
- field: Split cash
- field: Exchange rate
- table: Result | Parent | Child portfolio | Total
- table section: Target
- row: Target / Target weight
- row: Target / Target value
- table section: Realised
- row: Realised / Stocks
- row: Realised / Bonds
- row: Realised / Cash
- row: Realised / Portfolio value
- row: Realised / Realised weight
- table section: Deviation
- row: Deviation / Deviation from target
- table: Split walk | Original | Lot value CHF | Child portfolio | Parent | Cum. dev. first CHF
- table: Holding | Ccy | Quantity | Share price | Bond price | Round lot
- row: Holding / Bond (finance)
- row: Holding / Cash
- row: Holding / Stock
- list item: split agreement
- list item: bank reporting bank leu client xy

## Different order (0)
