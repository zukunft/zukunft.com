# Differences of portfolio_split_calculator_js.html#high to portfolio_split_calculator_sample.html#high

Change the generated page portfolio_split_calculator_js.html#high so that it shows what the hand made sample portfolio_split_calculator_sample.html#high shows: add what is missing, correct what differs and remove what only the generated page has, unless the sample lacks it by mistake. A number can differ for a good reason, because the sample calculates its numbers with its own script, so check the value or the formula before changing it. Fix a finding in the use case json or in the calculator renderer, never in the sample or in the generated snapshot.

## Missing in the generated page (111)

- heading: Check against the sample files
- heading: Cumulative deviation of the children along the walk
- heading: Notes
- heading: Inputs from
- field: Target weight first child
- field: Target weight second child
- table: CHF | Parent | First child | Second child | Total
- row: Realised / Deviation from target
- table: Value | Expected | Calculated | Status
- row: Value / Portfolio value original
- row: Value / LISP share, first child portfolio
- row: Value / LISP share, second child portfolio
- row: Value / LISP share, parent portfolio
- row: Value / LISN share, first child portfolio
- row: Value / LISN share, second child portfolio
- row: Value / LISN share, parent portfolio
- table: # | Position | Original | Lot value CHF | Target first | First | Second | Parent | Cum. dev. first CHF
- row: # / 1
- row: # / 2
- row: # / 3
- row: # / 4
- row: # / 5
- row: # / 6
- row: # / 7
- row: # / 8
- row: # / 9
- row: # / 10
- row: # / 11
- row: # / 12
- row: # / 13
- row: # / 14
- row: # / 15
- row: # / 16
- row: # / 17
- row: # / 18
- row: # / 19
- row: # / 20
- row: # / 21
- row: # / 22
- row: # / 23
- row: # / 24
- row: # / 25
- row: # / 26
- row: # / 27
- row: # / 28
- row: # / 29
- row: # / 30
- row: # / 31
- row: # / 32
- row: # / 33
- row: # / 34
- row: # / 35
- row: # / 36
- row: # / 37
- row: # / 38
- row: # / 39
- row: # / 40
- row: # / 41
- row: # / 42
- table: Position | Ccy | Quantity | Price | Round lot
- row: Position / LISP
- row: Position / LISN
- row: Position / GIVN
- row: Position / PGHN
- row: Position / GEBN
- row: Position / LONN
- row: Position / ZURN
- row: Position / SCMN
- row: Position / VACN
- row: Position / ROG
- row: Position / SOON
- row: Position / SCHP
- row: Position / SIKA
- row: Position / KNIN
- row: Position / UHR
- row: Position / CFR
- row: Position / SREN
- row: Position / STMN
- row: Position / LOGN
- row: Position / NOVN
- row: Position / SGSN
- row: Position / HOLN
- row: Position / NESN
- row: Position / ALC
- row: Position / BAER
- row: Position / BANB
- row: Position / ABBN
- row: Position / SDZ
- row: Position / ADEN
- row: Position / UBSG
- row: Position / ConfedBond30
- row: Position / NestleBond27
- row: Position / RocheBond28
- row: Position / BundDE30
- row: Position / TreasuryUS27
- row: Position / cash CHF
- row: Position / cash EUR
- row: Position / cash USD
- row: Position / cash GBP
- row: Position / cash JPY
- row: Position / cash DKK
- row: Position / cash SEK
- button: 
- button:  (2)
- button: Add child
- button: Download
- button: Report
- button: Copy as zukunft.com JSON
- button: Copy as CSV
- button: Import portfolio
- button: Import FX rates

## Different (11)

- field: Target weight parent, unit: the sample shows "" and the generated page "percent"
- field: Target weight parent, hint: the sample shows "The parent keeps the remainder of every position" and the generated page "The parent keeps the remainder of every position [1]"
- field: Target weight parent, value: the sample shows "" and the generated page "0.30"
- field: Walk order, value: the sample shows "Bank report order" and the generated page ""
- row: Target / Target weight, cells: the sample shows "10.0 % | 45.0 % | 45.0 % | 100.0 %" and the generated page "30 % |  | 100 %"
- row: Target / Target value, cells: the sample shows "1'961'090 | 8'824'905 | 8'824'905 | 19'610'900" and the generated page "5'883'270 CHF |  | "
- row: Realised / Stocks, cells: the sample shows "976'876 | 4'395'812 | 4'395'812 | 9'768'500" and the generated page "2'920'474 CHF |  | 9'768'500 CHF"
- row: Realised / Bonds, cells: the sample shows "600'450 | 2'702'025 | 2'702'025 | 6'004'500" and the generated page "1'811'400 CHF |  | 6'004'500 CHF"
- row: Realised / Cash, cells: the sample shows "383'764 | 1'727'068 | 1'727'068 | 3'837'900" and the generated page "1'151'396 CHF |  | 3'837'900 CHF"
- row: Realised / Portfolio value, cells: the sample shows "1'961'090 | 8'824'905 | 8'824'905 | 19'610'900" and the generated page "5'883'270 CHF |  | "
- row: Realised / Realised weight, cells: the sample shows "10.000 % | 45.000 % | 45.000 % | " and the generated page "30 % |  | "

## Only in the generated page (16)

- heading: Details and sources
- heading: Sources
- field: Target weight
- field: Mirror the children
- field: Split cash
- field: Exchange rate
- table: Result | Parent | Child portfolio | Total
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

## Case fields that the sample does not have (1)

- Target weight

## Case fields that the generated page does not have (3)

- Target weight first child
- Target weight second child
- Walk order
