# How to create a new view e.g. a "PV lifetime CO₂ balance" calculator

1. **Create a mock up of the html code.** Write the page as it should look by
   hand and add it e.g. to `src/test/resources/web/html/use_cases`, here
   `pv_lifetime_balance_sample.html`. The mock up is the reference for the
   html code that the view creates. The test compares the created page with it,
   and the mock up is never adapted to the renderer.
2. **Decide the main components, which are independent of the given phrase.**
   The given phrase is the phrase of the url, here `PV installation`. The main components are the parts
   that every phrase of the view shows in the same way. For a calculator these
   are `title calculator`, `calculator input` and `calculation result` in the
   `Calculator` view (see "The views" and "The components").
3. **Define the parameters of the main components with triples.** Each main
   component reads its parameters from triples that are linked to it by a
   keyword triple with a `code_id` (see "The keyword triples"):
   - `title calculator`: the title and the lead text (`calculator label`,
     `calculator hint`)
   - `calculator input`: which input fields and assumptions are relevant and in
     which order they are shown (`calculator input`, `calculator assumption`,
     *is follower of*)
   - `calculation result`: which columns, row groups, rows and detail tables
     are shown and in which order (`calculator result column`,
     `calculator row group`, `calculator detail table`, *is follower of*)

   The triples for the given phrase go to the use case json (see "The use case
   triples of the PV page"). What can be derived from the values, results and
   formulas gets no triple.
4. **Link the phrase to the view**, so that its url shows the new view (see
   "How the url leads to the view").
5. **Add the editor view** that lets the user change all these triples (see
   "The calculator editor").
6. **Build and test** until the created page matches the mock up (see
   "Implementation steps").

## How the url leads to the view

1. **The url** of the page is

   `/http/view.php?m=calculator&id=-2489`

   - `m` (`url_var::MASK`) is the view. `frontend::url_to_html` takes a
     numeric `m` as the database id and any other `m` as the code id
     (`typ_lst_cache->get_view`). So `m=calculator` selects the `Calculator`
     view on every pod, whatever its database id is.
   - `id` (`url_var::ID`) is the phrase of the page. A negative id is a
     triple, here `example PV installation` (phrase id -2489 in the test
     database).
2. **The link to the page** comes from the phrase. The page phrase has the
   `Calculator` view as its default view (the `view` field of the word or triple,
   `"view": "Calculator"` on `example PV installation` in the use case json), and
   every link to the phrase uses it. A url with `id` and without `m` shows the
   phrase with its default view. Both don't work yet: `phrase::name_link`
   always uses the general word or triple view (`phrase::view_id`), and
   `frontend::default_view_id` only fills a missing `m` if there is no `id`
   either (see "Implementation steps").
3. **The view** is loaded by `url_to_html` with its three components. The
   triple with the id is the main object (`dbo`) and is given to every
   component.
4. **The components find their triples from the page phrase.** First they
   look at the triples of the page phrase to a calculator keyword. If there are
   none, they look at the phrase it is built from: `example PV installation` =
   PV installation *kind of* example, so `PV installation` *has a*
   `calculator input` gives the inputs. Every installation that is built from
   `PV installation` gets the same page without its own triples. Its own triples
   overwrite single parts, e.g. an own label.
5. **A phrase without calculator triples** shows the `title calculator` with
   the phrase name and a message with a link to the `Calculator editor`.

The editor of the same page is `/http/view.php?m=calculator_editor&id=-2489`.
It is reached from the edit menu of the navbar by the view relation
`Calculator` → `Calculator editor` (`editor`).

## Overview: from the three components to the html

One line per piece of the page, in the order of the page. The view frame
around the lines comes from the view style `use-case` and the position types:
`<div class="wrap"><header>title</header><div class="grid"><aside class="panel">input</aside><section class="panel">result</section></div></div>`.

Notation: `name` = from *verb* to; `x label` → "text" is the label triple with
the text as its description. [code_id] marks a keyword triple. A value is
written as (phrases) = number. † marks a value or triple that is not in
`pv_lifetime_balance.json` yet. The html is the target, as in
`pv_lifetime_balance_sample.html`.

| step | components | parameter triples | sample triples | subcomponents | subcomponent parameter triples | subcomponent sample triples | resulting HTML |
|---|---|---|---|---|---|---|---|
| fill title | **title calculator** | `calculator label` [system_calculator_label]<br>`calculator hint` [system_calculator_hint] | `PV installation label` = PV installation *has a* calculator label → "PV lifetime CO₂ balance"<br>`PV installation hint` = PV installation *has a* calculator hint → "Rooftop photovoltaic installation … open below them." | – | – | – | `<header><h4>PV lifetime CO₂ balance</h4><p>Rooftop photovoltaic installation in the Swiss plateau: … all assumptions are open below them.</p></header>` |
| select input | **calculator input** | `calculator input` [system_calculator_input]<br>`calculator label` of the group (fallback `msg_id` "Inputs") | `PV installation inputs` = PV installation *has a* calculator input | input heading | – | – | `<h2>Inputs</h2>` |
| add field system size | calculator input | `calculator input`<br>order: *is follower of* [follow] | `PV installation input PV capacity` = PV installation inputs *has a* PV capacity<br>first input: no order triple | number field | `number with limits` [system_field_number_with_limits]<br>`field limit down` [system_field_limit_down]<br>`field step` [system_field_step]<br>`calculator label`<br>shown number: the value with the page phrase and the input phrase | `field PV capacity` = PV capacity *can be* number with limits<br>(PV capacity, field limit down) = 0.5<br>(PV capacity, field step) = 0.5<br>`PV installation input PV capacity label` → "System size"<br>(example PV installation, PV capacity, kWp, assumed) = 10 | `<div class="field"><label for="kwp">System size</label><div class="row"><input id="kwp" type="number" min="0.5" step="0.5" value="10"><span class="unit">kWp</span></div></div>` |
| add selector orientation | calculator input | `calculator input`<br>*is follower of* | `PV installation input yield factor` = PV installation inputs *has a* yield factor<br>`… order` = … *is follower of* `PV installation input PV capacity` | phrase selector field | `phrase selector` [system_field_phrase_selector_type]<br>`field phrase selector value based` [system_field_phrase_selector_value_based]<br>`calculator label`, `calculator hint`<br>options: the sub-items of the input with label and order<br>selected: the option that the page phrase *uses* | `field yield factor` = yield factor *can be* phrase selector<br>`yield factor options` = yield factor *can be* field phrase selector value based<br>`orientation of the example PV installation` = example PV installation *uses* east-west<br>`PV installation input yield factor label` → "Orientation"<br>`PV installation input yield factor hint` → "Yield factor relative to south 30°"<br>`PV installation option south` … `north` = PV installation input yield factor *has a* south … north, each option *is follower of* the one before, labels "South, ~30° tilt" … "North, ~30°"<br>(yield factor, south) = 1.00 … (yield factor, north) = 0.59 | `<div class="field"><label for="orient">Orientation</label><select id="orient"><option value="1.00">South, ~30° tilt</option><option value="0.95">South-east / south-west, ~30°</option><option value="0.85" selected>East-west split, flat tilt</option><option value="0.82">East or west only, ~30°</option><option value="0.88">Flat (0°)</option><option value="0.59">North, ~30°</option></select><span class="hint">Yield factor relative to south 30°</span></div>` |
| add field battery capacity | calculator input | `calculator input`<br>*is follower of* | `PV installation input battery capacity` = PV installation inputs *has a* battery capacity<br>`… order` = … *is follower of* `PV installation input yield factor` | number field | as the number field above | `field battery capacity` = battery capacity *can be* number with limits<br>(battery capacity, field limit down) = 0<br>(battery capacity, field step) = 1<br>`PV installation input battery capacity label` → "Battery capacity (usable)"<br>(example PV installation, battery capacity, kWh, …) = 10 | `<div class="field"><label for="batt">Battery capacity (usable)</label><div class="row"><input id="batt" type="number" min="0" step="1" value="10"><span class="unit">kWh</span></div></div>` |
| add field consumption | calculator input | `calculator input`<br>*is follower of* | `PV installation input household consumption` = PV installation inputs *has a* household consumption<br>`… order` = … *is follower of* `PV installation input battery capacity` | number field | as above plus<br>`field value phrases` [system_field_value_phrases]: the value with these phrases is shown and stored | `field household consumption` = household consumption *can be* number with limits<br>`household consumption input period` = household consumption *has a* annual<br>`household consumption field value phrase` = household consumption input period *can be* field value phrases<br>(household consumption, field limit down) = 0<br>(household consumption, field step) = 100<br>`PV installation input household consumption label` → "Local electricity consumption"<br>(example PV installation, household consumption, annual, kWh, …) = 4500 | `<div class="field"><label for="cons">Local electricity consumption</label><div class="row"><input id="cons" type="number" min="0" step="100" value="4500"><span class="unit">kWh/a</span></div></div>` |
| add save button | calculator input | – | – | refresh button | – (the text is the `msg_id` of the component) | – | `<button class="btn btn-outline-primary" type="submit">Save</button>` |
| select assumptions | calculator input | `calculator assumption` [system_calculator_assumption]<br>`calculator label` of the group (fallback `msg_id` "Assumptions") | `PV installation assumptions` = PV installation *has a* calculator assumption | assumption block | – | – | `<details><summary>Assumptions</summary>` … the assumption fields … `</details>` |
| add assumption yield | calculator input | `calculator assumption`<br>*is follower of* | `PV installation assumption PV yield` = PV installation assumptions *has a* PV yield<br>first assumption: no order triple | assumption field | `calculator label`<br>`field step`<br>the value: number, unit, description as hint, source as [n] and tag "measured"; or the confidence value as tag "assumed 0.6" | `PV installation assumption PV yield label` → "Specific yield, south 30°"<br>(PV yield, annual, kWh per kWp, 2023) = 976, source IEA PVPS Task 12 fact sheet 2023<br>(PV yield, field step) = 10 † | `<div class="field"><label for="yield">Specific yield, south 30° <span class="tag m">measured</span></label><div class="row"><input id="yield" type="number" step="10" value="976"><span class="unit">kWh/kWp·a</span></div><span class="hint">Lifetime average incl. 0.7 %/a degradation [1]</span></div>` |
| add assumption lifetime | calculator input | ″ | `PV installation assumption lifetime` … *has a* lifetime<br>`… order` = … *is follower of* `PV installation assumption PV yield` | assumption field | ″ | no label: the phrase name<br>(PV installation, lifetime, year) = 30, source IEA PVPS Task 12 fact sheet 2023<br>(lifetime, field step) = 1 † | `<div class="field"><label for="life">Lifetime <span class="tag m">measured</span></label><div class="row"><input id="life" type="number" step="1" value="30"><span class="unit">a</span></div><span class="hint">Reference lifetime used in the LCA [1]</span></div>` |
| add assumption PV emissions | calculator input | ″ | `PV installation assumption PV embodied intensity` … *has a* PV embodied intensity<br>`… order` = … *is follower of* `PV installation assumption lifetime` | assumption field | ″ | `… label` → "PV life-cycle emissions"<br>(PV embodied intensity, …) = 35.8, source IEA PVPS Task 12 fact sheet 2023<br>(PV embodied intensity, field step) = 0.1 † | `<div class="field"><label for="pvlca">PV life-cycle emissions <span class="tag m">measured</span></label><div class="row"><input id="pvlca" type="number" step="0.1" value="35.8"><span class="unit">g/kWh</span></div><span class="hint">Swiss 3 kWp mono-Si pitched roof, 2023 update [1]; converted to a fixed amount per kWp</span></div>` |
| add assumption consumer mix | calculator input | ″ | `PV installation assumption consumption mix` … *has a* consumption mix<br>`… order` = … *is follower of* `PV installation assumption PV embodied intensity` | assumption field | ″ | `… label` → "Swiss consumer mix, annual"<br>(consumption mix, 2018, CO2-eq, …) = 128, source BAFU Swiss electricity mixes 2018<br>(consumption mix, field step) = 1 † | `<div class="field"><label for="mixA">Swiss consumer mix, annual <span class="tag m">measured</span></label><div class="row"><input id="mixA" type="number" step="1" value="128"><span class="unit">g/kWh</span></div><span class="hint">2018 consumer mix, low voltage [2]</span></div>` |
| add selector replacement mix | calculator input | ″ | `PV installation assumption replacement mix` … *has a* replacement mix<br>`… order` = … *is follower of* `PV installation assumption consumption mix` | phrase selector field | `phrase selector`<br>options: the phrases that *is a* replacement mix, each with its value and source<br>selected: the option that the page phrase *uses* | `field replacement mix` = replacement mix *can be* phrase selector<br>residual mix *is a* replacement mix<br>production mix *is a* replacement mix<br>`replacement mix of the example PV installation` = example PV installation *uses* residual mix | `<div class="field"><label for="mixC">Replacement mix</label><select id="mixC"><option value="523" selected>European residual mix — 523 g/kWh [4]</option><option value="395">ENTSO-E production mix 2022 — ~395 g/kWh (midpoint of 350–440) [3]</option></select></div>` |
| add assumption load share | calculator input | ″ | `PV installation assumption day share` … *has a* day share<br>`… order` = … *is follower of* `PV installation assumption replacement mix` | assumption field | ″ | `… label` → "Load share during PV hours"<br>(example PV installation, day share, household consumption, percent, assumed) = 0.35<br>(…, assumed, confidence) = 0.6 †<br>(day share, field step) = 0.05 † | `<div class="field"><label for="dayshare">Load share during PV hours <span class="tag a">assumed 0.6</span></label><div class="row"><input id="dayshare" type="number" step="0.05" min="0" max="1" value="0.35"><span class="unit">share</span></div><span class="hint">Household; raise for home office or a heat pump running at noon</span></div>` |
| add assumption efficiency | calculator input | ″ | `PV installation assumption round-trip efficiency` … *has a* round-trip efficiency<br>`… order` = … *is follower of* `PV installation assumption day share` | assumption field | ″ | `… label` → "Battery round-trip efficiency"<br>(battery, round-trip efficiency, percent, assumed) = 0.9<br>(…, assumed, confidence, percent) = 0.7 | `<div class="field"><label for="eta">Battery round-trip efficiency <span class="tag a">assumed 0.7</span></label><div class="row"><input id="eta" type="number" step="0.01" value="0.90"><span class="unit">share</span></div></div>` |
| add assumption battery emissions | calculator input | ″ | `PV installation assumption battery production emission` … *has a* battery production emission<br>`… order` = … *is follower of* `PV installation assumption round-trip efficiency` | assumption field | ″ | `… label` → "Battery production emissions"<br>(battery, battery production emission, kg per kWh, assumed) = 100<br>(…, assumed, confidence, percent) = 0.5 | `<div class="field"><label for="bco2">Battery production emissions <span class="tag a">assumed 0.5</span></label><div class="row"><input id="bco2" type="number" step="5" value="100"><span class="unit">kg/kWh</span></div><span class="hint">Literature spans ~37–212 kg CO₂-eq per kWh capacity [5][6]</span></div>` |
| add assumption battery lifetime | calculator input | ″ | `PV installation assumption battery lifetime` … *has a* battery lifetime<br>`… order` = … *is follower of* `PV installation assumption battery production emission` | assumption field | ″ | no label: the phrase name<br>(battery, battery lifetime, year, assumed) = 15<br>(…, assumed, confidence, percent) = 0.6 | `<div class="field"><label for="blife">Battery lifetime <span class="tag a">assumed 0.6</span></label><div class="row"><input id="blife" type="number" step="1" value="15"><span class="unit">a</span></div></div>` |
| select result | **calculation result** | `calculator result column` [system_calculator_result_column]<br>`calculator label` of the group | `PV installation result columns` = PV installation *has a* calculator result column<br>`PV installation result columns label` → "Main results over the lifetime" | result heading | – | – | `<h2>Main results over the lifetime</h2>` |
| fill table header | calculation result | `calculator result column`<br>*is follower of* | `PV installation column lifetime` … *has a* lifetime<br>`PV installation column annual` … *has a* annual<br>`… order` = … *is follower of* `PV installation column lifetime` | table header | `calculator label` (fallback: the phrase name; first column: `msg_id` "Result") | `PV installation column annual label` → "Per year" | `<thead><tr><th>Result</th><th class="n">Lifetime</th><th class="n">Per year</th></tr></thead>` |
| add section electricity | calculation result | `calculator row group` [system_calculator_row_group]<br>*is follower of* | `PV installation row groups` = PV installation *has a* calculator row group<br>`PV installation rows electricity` … *has a* electricity (first group) | section row | `calculator label` (fallback: the phrase name) | – | `<tr class="sec"><td colspan="3">Electricity</td></tr>` |
| add row PV production | calculation result | ″ | `PV installation row PV production` = PV installation rows electricity *has a* PV production (first row) | result row | `calculator label`, `calculator hint`<br>`calculator highlight` [system_calculator_highlight] †<br>sub text: the formula of the result with its operands<br>cells: the result with the page phrase, the row phrase and the column phrase | `PV installation row PV production highlight` = … *has a* calculator highlight<br>formula `PV production by orientation` | `<tr class="key"><td>PV production<span class="sub">10.0 kWp × 976 kWh/kWp × factor 0.85</span></td><td class="n">248'880 kWh</td><td class="n">8'296 kWh</td></tr>` |
| add row direct use | calculation result | ″ | `PV installation row direct use` … *has a* direct use<br>`… order` = … *is follower of* `PV installation row PV production` | result row | ″ | `… label` → "Used directly" | `<tr><td>Used directly</td><td class="n">47'250 kWh</td><td class="n">1'575 kWh</td></tr>` |
| add row via battery | calculation result | ″ | `PV installation row battery discharge` … *has a* battery discharge<br>`… order` = … *is follower of* `PV installation row direct use` | result row | ″ | `… label` → "Used via battery"<br>`… hint` → "after losses" | `<tr><td>Used via battery<span class="sub">after losses</span></td><td class="n">66'713 kWh</td><td class="n">2'224 kWh</td></tr>` |
| add row export | calculation result | ″ | `PV installation row export` … *has a* export<br>`… order` = … *is follower of* `PV installation row battery discharge` | result row | ″ | `… label` → "Exported to grid" | `<tr><td>Exported to grid</td><td class="n">127'504 kWh</td><td class="n">4'250 kWh</td></tr>` |
| add row battery losses | calculation result | ″ | `PV installation row battery losses` … *has a* battery losses<br>`… order` = … *is follower of* `PV installation row export` | result row | ″ | no label: the phrase name | `<tr><td>Battery losses</td><td class="n">7'413 kWh</td><td class="n">247 kWh</td></tr>` |
| add row grid import | calculation result | ″ | `PV installation row grid import` … *has a* grid import<br>`… order` = … *is follower of* `PV installation row battery losses` | result row | ″ | `… label` → "Grid import still needed" | `<tr><td>Grid import still needed</td><td class="n">21'037 kWh</td><td class="n">701 kWh</td></tr>` |
| add row self-consumption | calculation result | ″ | `PV installation row self-consumption share` … *has a* self-consumption share<br>`… order` = … *is follower of* `PV installation row grid import` | result row | ″ | `… label` → "Self-consumption rate"<br>`… hint` → "share of PV used on site" | `<tr><td>Self-consumption rate<span class="sub">share of PV used on site</span></td><td class="n"></td><td class="n">49 %</td></tr>` |
| add row autarky | calculation result | ″ | `PV installation row autarky` … *has a* autarky<br>`… order` = … *is follower of* `PV installation row self-consumption share` | result row | ″ | `… hint` → "share of load covered by PV"<br>`PV installation row autarky highlight` = … *has a* calculator highlight | `<tr class="key"><td>Autarky<span class="sub">share of load covered by PV</span></td><td class="n"></td><td class="n">84 %</td></tr>` |
| add section production emissions | calculation result | `calculator row group` | `PV installation rows production emissions` … *has a* embodied emission<br>`… order` = … *is follower of* `PV installation rows electricity` | section row | ″ | `… label` → "Production emissions" | `<tr class="sec"><td colspan="3">Production emissions</td></tr>` |
| add row PV system | calculation result | ″ | `PV installation row PV embodied emission` … *has a* PV embodied emission (first row) | result row | ″ plus the side result: a sub-item *has a* phrase, shown in the sub text | `… label` → "PV system"<br>`… side result` = … *has a* PV site intensity<br>formula `PV embodied emission formula` | `<tr><td>PV system<span class="sub">1'048 kg CO₂-eq/kWp → 42.1 g/kWh at this site</span></td><td class="n">10.5 t</td><td class="n">349 kg</td></tr>` |
| add row battery | calculation result | ″ | `PV installation row battery embodied emission` … *has a* battery embodied emission<br>`… order` = … *is follower of* `PV installation row PV embodied emission` | result row | ″ | `… label` → "Battery"<br>formula `battery embodied emission formula` | `<tr><td>Battery<span class="sub">2 × 10 kWh × 100 kg/kWh</span></td><td class="n">2.0 t</td><td class="n">67 kg</td></tr>` |
| add section saving | calculation result | `calculator row group`<br>`calculator signed` [system_calculator_signed] † | `PV installation rows saving` … *has a* net avoided emission<br>`… order` = … *is follower of* `PV installation rows production emissions`<br>`PV installation rows saving signed` = … *has a* calculator signed | section row | ″ | `… label` → "CO₂-eq saved, net of production emissions" | `<tr class="sec"><td colspan="3">CO₂-eq saved, net of production emissions</td></tr>` |
| add row saving annual mix | calculation result | ″ | `PV installation row saving consumption mix` … *has a* consumption mix (first row) | result row | ″ plus the filter: a sub-item *with* phrase; signed: the cells as `pos` / `neg` | `… filter` = … *with* net avoided emission<br>`… side result` = … *has a* emission payback, `… side result label` → "payback"<br>`… label` → "vs. Swiss consumer mix, annual"<br>`… highlight` | `<tr class="key"><td>vs. Swiss consumer mix, annual (128 g/kWh)<span class="sub">payback 12.1 a</span></td><td class="n"><span class="pos">18.4 t</span></td><td class="n"><span class="pos">614 kg</span></td></tr>` |
| add row saving monthly mix | calculation result | ″ | `PV installation row saving PV-weighted mix` … *has a* PV-weighted consumption mix<br>`… order` = … *is follower of* `PV installation row saving consumption mix` | result row | ″ | filter, side result, highlight as above<br>`… label` → "vs. Swiss consumer mix, monthly" | `<tr class="key"><td>vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted)<span class="sub">payback 14.7 a</span></td><td class="n"><span class="pos">13.1 t</span></td><td class="n"><span class="pos">436 kg</span></td></tr>` |
| add row saving replacement mix | calculation result | ″ | `PV installation row saving replacement mix` … *has a* replacement mix<br>`… order` = … *is follower of* `PV installation row saving PV-weighted mix` | result row | ″ | filter, side result, highlight as above<br>`… label` → "vs. replacement mix" | `<tr class="key"><td>vs. replacement mix (523 g/kWh)<span class="sub">payback 3.0 a</span></td><td class="n"><span class="pos">113.8 t</span></td><td class="n"><span class="pos">3'794 kg</span></td></tr>` |
| add row battery effect | calculation result | ″ | `PV installation row battery effect` … *has a* battery effect<br>`… order` = … *is follower of* `PV installation row saving replacement mix` | result row | ″ | `… filter` = … *with* consumption mix<br>`… label` → "Effect of the battery"<br>`… hint` → "vs. same PV without battery, annual Swiss mix" | `<tr><td>Effect of the battery<span class="sub">vs. same PV without battery, annual Swiss mix</span></td><td class="n"><span class="neg">-2.9 t</span></td><td class="n"><span class="neg">-98 kg</span></td></tr>` |
| select details | calculation result | `calculator detail table` [system_calculator_detail_table]<br>`calculator label` of the group | `PV installation detail tables` = PV installation *has a* calculator detail table<br>`PV installation detail tables label` → "Monthly details and sources" | detail block | – | – | `<details><summary>Monthly details and sources</summary><div class="more">` … detail tables, notes, sources … `</div></details>` |
| add monthly table | calculation result | `calculator detail table`<br>*is follower of* | `PV installation detail monthly balance` … *has a* month (the only table) | detail table | `calculator label` of the table as heading<br>columns: sub-items *has a* phrase with label and order<br>rows: the phrases of the table phrase (January … December)<br>extra rows: sub-items *with* phrase<br>first header: the table phrase | `… label` → "Monthly energy balance, average year"<br>`PV installation detail column PV production` … `consumption mix` (7), labels "PV kWh", "Load kWh", "Direct", "Battery", "Export", "Grid import", "CH mix g/kWh" | `<h2>Monthly energy balance, average year</h2>`<br>`<thead><tr><th>Month</th><th class="n">PV kWh</th><th class="n">Load kWh</th><th class="n">Direct</th><th class="n">Battery</th><th class="n">Export</th><th class="n">Grid import</th><th class="n">CH mix g/kWh</th></tr></thead>` |
| add month rows | calculation result | ″ | ″ | detail row | the results and values with the page phrase, the row phrase and the column phrase | 12 rows: January … December | `<tr><td>Jan</td><td class="n">207</td><td class="n">450</td><td class="n">158</td><td class="n">45</td><td class="n">0</td><td class="n">248</td><td class="n">151</td></tr>` … `<tr><td>Dec</td>…</tr>` |
| add year row | calculation result | ″ | ″ | detail total row | sub-item *with* phrase, `calculator label`, `calculator highlight` | `PV installation detail total` = … *with* annual<br>`… label` → "Year"<br>`PV installation detail total highlight` | `<tr class="key"><td>Year</td><td class="n">8'296</td><td class="n">4'500</td><td class="n">1'575</td><td class="n">2'224</td><td class="n">4'250</td><td class="n">701</td><td class="n">106</td></tr>` |
| add notes | calculation result | – (derived) | – | formula notes | the descriptions of the formulas of the shown results | `net avoided emission formula`, `gross avoided emission by PV-weighted consumption mix`, `battery discharge formula`, … | `<div class="notes"><p><strong>How the CO₂ saving is counted.</strong> Every PV kilowatt-hour that reaches a consumer … </p><p><strong>Seasonal Swiss mix.</strong> …</p><p><strong>Self-consumption model.</strong> …</p></div>` |
| add sources | calculation result | – (derived) | – | sources | the sources of the shown values, numbered in the order of the first use | IEA PVPS Task 12 fact sheet 2023, BAFU Swiss electricity mixes 2018, treeze monthly electricity mixes 2025, Swissolar PV fact sheet 2024, Lithium-ion cell LCA comparison 2025, HTW Berlin solar storage climate 2019, gruenes.haus tilt and orientation table | `<div class="src"><h2>Sources</h2><ol><li><a href="https://iea-pvps.org/…/Task-12-Fact-Sheet-Stucki.pdf" target="_blank" rel="noopener">Stucki, Götz, de Wild-Scholten, Frischknecht: IEA PVPS Task 12 fact sheet, LCA of PV electricity, 2023 update</a></li>` … 7 sources … `</ol></div>` |

The gaps between the triples and the target html are in "Open points" at the end.

### Parts of the mock up that are not in the table

The table covers the content of the three components. These parts of
`pv_lifetime_balance_sample.html` are still missing in the created page. Line =
the line in the mock up.

Status: **partly** = only a part is created, **open** = not created yet.

| part | line | created by | html | suggested solution | status |
|---|---|---|---|---|---|
| document head | 1–11 | the page frame of every view | `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" …><title>example PV installation - PV lifetime CO2 balance - zukunft.com</title><link rel="stylesheet" href="…/bootstrap.css">…<link rel="stylesheet" href="…/style_html.css"></head>` | The title is the phrase name, the view name and the pod name. The `Calculator` view would give "example PV installation - Calculator - zukunft.com", so the title should use the calculator label of the phrase if the view has one. | partly |
| page wrapper | 185, 191, 349–350 | the view style `use-case` and the position types (see the frame line above the table) | `<div class="wrap">` … `<div class="grid">` … `</div></div>` | The created page has `<div class="use-case">` with bootstrap `row` / `col` divs. For the style `use-case`, the view renders the first row as a `<header>` and the side-or-below group as `<div class="grid">`. Use `use-case` as the class instead of `wrap`. | partly |
| input panel | 192–193, 221, 271 | `calculator input`: the panel around the fields, the button and the assumptions | `<aside class="panel"><div class="inputs">` … `</div>` … `</aside>` | The column with `side_or_first_below` of a `use-case` view is an `<aside class="panel">`. `calculator input` wraps the fields and the button in `<div class="inputs">`, without the inline style of the mock up (the css has `.use-case .inputs`). | open |
| refresh form | – (missing in the mock up) | `calculator input`: the save button submits a form with the view and the phrase as hidden fields, and the fields join it by their `form` attribute (`process_catch_button_refresh`) | `<form id="…" action="…/view.php"><input type="hidden" name="m" value="calculator"><input type="hidden" name="id" value="-2489"><button class="btn btn-outline-primary" type="submit">Save</button></form>` | The form, the hidden fields and the `form` attribute of the fields are created already. The button says "calculate" where the mock up says "Save": change the `ui_msg_code_id` text, and replace the placeholder button of the mock up with this form. | partly |
| result panel | 273, 348 | `calculation result` | `<section class="panel">` … `</section>` | The column with `side_or_last_below` of a `use-case` view is a `<section class="panel">`, without the inline style of the mock up (the css has `.use-case section.panel`). | open |
| main table frame | 275–276, 280, 298–300 | `calculation result`: around the table header and the rows | `<div class="scroll"><table>` … `<tbody>` … `</tbody></table></div>` | The main table renderer of `calculation result` wraps the table in `<div class="scroll">` and the rows in `<tbody>`, without the "…" menu of the simple table. | open |
| detail table frame | 307–308, 310, 324–326 | the detail table sub-component | `<div class="scroll"><table>` … `<tbody>` … `</tbody></table></div>` | The same renderer as the main table. | open |
| sources heading | 335 | the sources sub-component | `<h2>Sources</h2>` | Render the heading in `<div class="src">`, without the inline margin of the mock up (the css has `.use-case .src h2`). | open |
| orientation hint | 209, 558 | the phrase selector field of the orientation; the mock up writes it by its script, but a page has no javascript (`docs/llm/frontend.md` "Pure HTML, no JavaScript") | `<span class="hint">Yield factor 0.85 relative to south 30° [7]</span>` | The server writes the hint from the value and the source of the selected option, and the refresh form shows it again for a changed selection. | open |

Shortened in the table with "…" but created in the same way as the lines
shown: the lead text (line 188), the month rows February to November (lines
312–321), the texts of the three notes (lines 329–331) and the sources 2 to 7
(lines 338–343). The closing tags of the detail block are on lines 344–347.

## 1. The rules

1. **Components** are the parts that are the same for every calculator: the
   title, the input block and the result block. They have a `code_id` and are
   system components.
2. **Triples** are everything that depends on the initial phrase: which inputs,
   which assumptions, which columns, which rows, which detail tables, all labels
   and hints.
3. **Every triple that the view reads has a parent triple with a `code_id`.**
   The parent selects all triples of one kind in one load, so the calculator
   editor can show e.g. all labels of a calculator at once.
   - A **text property** (label, hint) is a triple from the item **to** the
     keyword, e.g. `… has a calculator label`. All labels have the same `to`:
     one load by `to`.
   - A **list** (inputs, rows, …) is a group triple from the phrase to the
     keyword, e.g. `PV installation has a calculator input`. Its
     items are the triples **from** the group. All inputs of the calculator are
     the children of the group, and all inputs of every calculator are two levels
     below the keyword: one `load_related_by_name` with depth, as
     `ui_list::input_fields` already does for `process catch field (system)`.
   - A **sub-item** (a row filter, a side result, a table column, a selector
     option) is a triple from the item. Its parent is the item.
4. **Texts are descriptions, never names.** A label or hint triple gets the
   mechanical name `<item> label` / `<item> hint`, because a triple from a
   named triple needs its own name, and the text is its `description`. A name must be unique among all phrases, and a label like
   `orientation` or `lifetime` already is a word. A description is free text
   in any case and can be changed per user.
5. **A label is optional.** Without a label the item shows the name of its
   phrase with the first letter in capitals. A label triple exists only if the
   page needs another text.
6. **Order by follower triples.** One triple adds an item to a list (`has a`),
   and a second triple sets its relative order: `<item> order` = item
   *is follower of* the item before (verb `follow`, read backwards: the item
   before *is followed by* this item). The first item of a list has no order
   triple. Items that are in no chain come after the chain, sorted by label.
   Inserting or moving an item changes only the order triples next to it, and no
   numbers are renumbered.
7. **Derived parts need no triple.** The assumptions not listed, the formula
   notes, the sources, the units, the measured/assumed tags and the sub text of
   a computed row come from the values, the results and the formulas.
8. **The phrase knows its field, the calculator knows its page.** Field
   parameters that are true for every page using the phrase (type, limits,
   step, selector options, value phrases) stay on the phrase with the
   `process catch field (system)` keywords. Everything that is only true for
   this page (is an input here, order, label, hint) is a calculator triple.

The `has a` verb is used for all list items. The verb `with` marks a filter
phrase of a row and an extra row of a table. A triple whose `to` is a calculator
keyword is a property, and any other `to` is an item.

## 2. The views

| view | code_id | type | style | components |
|---|---|---|---|---|
| `Calculator` | `calculator` | triple | `use-case` | 1 `title calculator`, 2 `calculator input` (`side_or_first_below`), 3 `calculation result` (`side_or_last_below`) |
| `Calculator editor` | `calculator_editor` | triple | – | see section 6 |

View relation: `{ "parent": "Calculator", "child": "Calculator editor", "type": "editor" }`,
so the edit menu of the navbar opens the editor with the same phrase.

How a phrase gets this view and how the view finds its triples is described in
"How the url leads to the view" at the top. There is no extra triple that
makes a phrase a calculator: the group triples of the phrase are enough
(reduce to the max).

## 3. The components

### 3.1 In the view

| name | type | code_id | renders |
|---|---|---|---|
| `title calculator` | `calculator_title` | `title_calculator` | the calculator label of the phrase as `h4` (fallback: the phrase name) and its hint as the lead paragraph |
| `calculator input` | `calculator_input` | `calculator_input` | the heading (label of the input group or `msg_id` "Inputs"), one input field per item, the refresh button ("Save"), then a `details` block (label of the assumption group or "Assumptions") with one assumption field per item and then the derived assumptions |
| `calculation result` | `calculation_result` | `calculation_result` | the heading (label of the column group) and the main table, then a `details` block (label of the detail table group) with the detail tables, the formula notes ("How the CO₂ saving is counted", …) and the sources ("Sources" `msg_id`) |

### 3.2 Built at runtime, not linked in the view

The view components create one sub-component per item. Each one gets the
context phrases and reads its parameters from the triples and values:

| sub-component | context | parameters from | reuses |
|---|---|---|---|
| input field | page phrase + input phrase, e.g. `example PV installation` + `PV capacity` | field type, limits, step, value phrases: the field triples and values of the input phrase; label, hint, options: the calculator triples of the item; shown number: the value with all context phrases, or the url | `ui_list::input_fields` |
| assumption field | page phrase + operand phrase | label: calculator triple; number, measured/assumed, confidence, source: the value | `ui_list::assumption_field` |
| main table | page phrase + column phrases + per row: row phrase, `with` phrases | columns, row groups, rows, labels, hints, side results | `ui_list::table_with_related_columns` |
| detail table | page phrase + table phrase | columns (`has a`), extra rows (`with`), labels | `ui_list::table_with_related_columns` |
| formula notes | page phrase | the descriptions of the formulas of the results | `ui_list::formula_notes` |
| sources | page phrase | the sources of the values used | `ui_list::sources_used` |

## 4. The keyword triples (system file, with `code_id`)

Home: a new system file `calculator_keywords.json` in
`files::SYSTEM_DATA_APPENDED_FILES`, like `process_catch_fields.json`.

| name | from | verb | to | code_id | kind |
|---|---|---|---|---|---|
| `calculator (system)` | calculator | can be | zukunft.com system | `system_calculator` | root keyword |
| `calculator label` | calculator (system) | has a | label | `system_calculator_label` | text property: title, heading, field label, column header, row label |
| `calculator hint` | calculator (system) | has a | hint | `system_calculator_hint` | text property: lead text, help line, row sub text |
| `calculator input` | calculator (system) | has a | input | `system_calculator_input` | list of input phrases |
| `calculator assumption` | calculator (system) | has a | assumption | `system_calculator_assumption` | list of assumption phrases shown first and with a label |
| `calculator result column` | calculator (system) | has a | column | `system_calculator_result_column` | list of the column phrases of the main table |
| `calculator row group` | calculator (system) | has a | group | `system_calculator_row_group` | list of row groups, each a list of rows |
| `calculator detail table` | calculator (system) | has a | table | `system_calculator_detail_table` | list of detail tables, each with columns and extra rows |
| `calculator highlight` | calculator (system) | has a | highlight | `system_calculator_highlight` | flag of an item: the row is shown as a key row (`class="key"`) |
| `calculator signed` | calculator (system) | has a | sign | `system_calculator_signed` | flag of a row group: the cells show the sign as `pos` / `neg` |

New words: `calculator`, `hint`, `highlight`, `sign`. All other words are already defined
elsewhere and are repeated by name only.

Already there and reused unchanged (`process_catch_fields.json` and the use
case): `field PV capacity` … `number with limits`, `field limit down`,
`field step`, `field value phrases`, `phrase selector`,
`field phrase selector value based`, `field replacement mix`.

The order needs no keyword: it uses the existing verb `is follower of`
(`follow`). Its description in `verbs.json` names only the time order, so it
should also name the order of the items of a list.

Open: `field label` (`system_field_label`) of the process catch and
`calculator label` overlap. Later both could become one `label (system)`.

## 5. The use case triples of the PV page (no `code_id`)

Home: `pv_lifetime_balance.json`. Columns: **item** = the named triple, **to** = its phrase, **pos** =
the place in the list that the order triples give (`<item> order` = item
*is follower of* the item one line above), **label / hint** = the description of the
`<item> label` / `<item> hint` triple (– = no triple, the phrase name is shown).

### 5.1 The title

The page title and the lead text are the label and the hint of the phrase
itself. They need no extra triple:

| item | from | verb | to | description |
|---|---|---|---|---|
| `PV installation label` | PV installation | has a | calculator label | PV lifetime CO₂ balance |
| `PV installation hint` | PV installation | has a | calculator hint | Rooftop photovoltaic installation in the Swiss plateau: … all assumptions are open below them. |

### 5.2 Inputs, group `PV installation inputs` = `PV installation` has a `calculator input`

| pos | item | to | label | hint |
|---|---|---|---|---|
| 1 | `PV installation input PV capacity` | PV capacity | System size | – |
| 2 | `PV installation input yield factor` | yield factor | Orientation | Yield factor relative to south 30° |
| 3 | `PV installation input battery capacity` | battery capacity | Battery capacity (usable) | – |
| 4 | `PV installation input household consumption` | household consumption | Local electricity consumption | – |

The options of the orientation selector are sub-items of
`PV installation input yield factor` (`has a`):

| pos | item | to | label |
|---|---|---|---|
| 1 | `PV installation option south` | south | South, ~30° tilt |
| 2 | `PV installation option south-east` | south-east | South-east / south-west, ~30° |
| 3 | `PV installation option east-west` | east-west | East-west split, flat tilt |
| 4 | `PV installation option east` | east | East or west only, ~30° |
| 5 | `PV installation option horizontal` | horizontal | Flat (0°) |
| 6 | `PV installation option north` | north | North, ~30° |

Without option items the selector offers all phrases of the yield factor
values (`yield factor options`), sorted by name.

Field parameters, unchanged on the phrase: `field PV capacity`,
`field battery capacity` and `field household consumption` (`can be number
with limits`) with their `field limit down` / `field step` values,
`household consumption field value phrase` (annual), and `field yield factor`
with `yield factor options`. The unit (kWp, kWh, kWh/a) is the unit phrase
of the shown value.

### 5.3 Assumptions, group `PV installation assumptions` = `PV installation` has a `calculator assumption`

| pos | item | to | label |
|---|---|---|---|
| 1 | `PV installation assumption PV yield` | PV yield | Specific yield, south 30° |
| 2 | `PV installation assumption lifetime` | lifetime | – |
| 3 | `PV installation assumption PV embodied intensity` | PV embodied intensity | PV life-cycle emissions |
| 4 | `PV installation assumption consumption mix` | consumption mix | Swiss consumer mix, annual |
| 5 | `PV installation assumption replacement mix` | replacement mix | – (a selector by `field replacement mix`) |
| 6 | `PV installation assumption day share` | day share | Load share during PV hours |
| 7 | `PV installation assumption round-trip efficiency` | round-trip efficiency | Battery round-trip efficiency |
| 8 | `PV installation assumption battery production emission` | battery production emission | Battery production emissions |
| 9 | `PV installation assumption battery lifetime` | battery lifetime | – |

The line below each field ("Lifetime average incl. 0.7 %/a degradation [1]")
is the description and the source of the value. The tag "measured" or
"assumed 0.6" comes from the value and its confidence. A derived
operand without an item is added after these, sorted by label.

### 5.4 Main table columns, group `PV installation result columns` = `PV installation` has a `calculator result column`

Group label: **Main results over the lifetime**. The header of the first
column ("Result") is a `msg_id` of the component.

| pos | item | to | label |
|---|---|---|---|
| 1 | `PV installation column lifetime` | lifetime | – |
| 2 | `PV installation column annual` | annual | Per year |

### 5.5 Main table rows, group `PV installation row groups` = `PV installation` has a `calculator row group`

The phrase of a row group only names the group. It doesn't filter the rows.

| pos | row group item | to | label |
|---|---|---|---|
| 1 | `PV installation rows electricity` | electricity | – |
| 2 | `PV installation rows production emissions` | embodied emission | Production emissions |
| 3 | `PV installation rows saving` | net avoided emission | CO₂-eq saved, net of production emissions |

Rows (`<row group> has a <phrase>`). Sub-items: `with` = filter phrase of the
row, `has a` = side result shown in the sub text after the hint.

| group | pos | row item | to | with | side result | label | hint |
|---|---|---|---|---|---|---|---|
| electricity | 1 | `PV installation row PV production` | PV production | – | – | – | – (the sub text "10.0 kWp × 976 kWh/kWp × factor 0.85" is the formula with its operands) |
| electricity | 2 | `PV installation row direct use` | direct use | – | – | Used directly | – |
| electricity | 3 | `PV installation row battery discharge` | battery discharge | – | – | Used via battery | after losses |
| electricity | 4 | `PV installation row export` | export | – | – | Exported to grid | – |
| electricity | 5 | `PV installation row battery losses` | battery losses | – | – | – | – |
| electricity | 6 | `PV installation row grid import` | grid import | – | – | Grid import still needed | – |
| electricity | 7 | `PV installation row self-consumption share` | self-consumption share | – | – | Self-consumption rate | share of PV used on site |
| electricity | 8 | `PV installation row autarky` | autarky | – | – | – | share of load covered by PV |
| production emissions | 1 | `PV installation row PV embodied emission` | PV embodied emission | – | PV site intensity | PV system | – |
| production emissions | 2 | `PV installation row battery embodied emission` | battery embodied emission | – | – | Battery | – |
| saving | 1 | `PV installation row saving consumption mix` | consumption mix | net avoided emission | emission payback | vs. Swiss consumer mix, annual | – |
| saving | 2 | `PV installation row saving PV-weighted mix` | PV-weighted consumption mix | net avoided emission | emission payback | vs. Swiss consumer mix, monthly | – |
| saving | 3 | `PV installation row saving replacement mix` | replacement mix | net avoided emission | emission payback | vs. replacement mix | – |
| saving | 4 | `PV installation row battery effect` | battery effect | consumption mix | – | Effect of the battery | vs. same PV without battery, annual Swiss mix |

The rows of the saving group use the mix as their item phrase and
`net avoided emission` as a `with` filter. That keeps `from`/`verb`/`to` unique:
three items `has a net avoided emission` in the same group would be one key.

### 5.6 Detail tables, group `PV installation detail tables` = `PV installation` has a `calculator detail table`

Group label: **Monthly details and sources**. This is the `details` summary that
also holds the notes and the sources.

| pos | item | to | label |
|---|---|---|---|
| 1 | `PV installation detail monthly balance` | month | Monthly energy balance, average year |

The rows are the phrases of the item phrase `month` (January … December). The
header of the first column is the item phrase ("Month"). Sub-items:

| pos | sub-item | verb | to | label |
|---|---|---|---|---|
| 1 | `PV installation detail column PV production` | has a | PV production | PV kWh |
| 2 | `PV installation detail column household consumption` | has a | household consumption | Load kWh |
| 3 | `PV installation detail column direct use` | has a | direct use | Direct |
| 4 | `PV installation detail column battery discharge` | has a | battery discharge | Battery |
| 5 | `PV installation detail column export` | has a | export | Export |
| 6 | `PV installation detail column grid import` | has a | grid import | Grid import |
| 7 | `PV installation detail column consumption mix` | has a | consumption mix | CH mix g/kWh |
| – | `PV installation detail total` | with | annual | Year |

### 5.7 Count

5 group triples, 4 inputs + 6 options, 9 assumptions, 2 columns, 3 row groups
+ 14 rows + 4 filters + 4 side results, 1 detail table + 8 sub-items. 44 label
triples, 6 hint triples, 6 highlight and 1 signed triple, and 36 order
triples. That is 163 new triples with the 10 keywords. The number is the price
for a page that the user can change without a developer.

## 6. The calculator editor

The editor follows the same split: its components are the same for every
calculator, and its content is the triples of section 5 for the phrase.

| pos | component | type | renders |
|---|---|---|---|
| 1 | `title calculator editor` | `calculator_editor_title` | "Edit calculator" with the calculator label and a link back to the `Calculator` view of the same phrase |
| 2 | `calculator setting tree` | `calculator_setting_tree` | one section per keyword below `calculator (system)` (found by its `code_id`), each a table of the items of this calculator in their order with up and down links that change the order triples, phrase (phrase selector), label and hint (text) and a remove link. Sub-items are indented below their item. An empty last row adds an item. |
| 3 | `calculator label list` | `calculator_label_list` | all label and hint triples of this calculator in one table, loaded by `to` = `calculator label` / `calculator hint` and filtered to the tree of the calculator, for proofreading and translating all texts at once |
| 4 | `calculator field settings` | `calculator_field_settings` | the phrase-level field triples and values of every input and assumption phrase (type, limits, step, value phrases, selector options), with a note that a change here changes the field on every page that uses the phrase |
| 5 | `calculator derived parts` | `calculator_derived_parts` | read only: the derived assumptions, notes and sources with links to the value, formula and source editors, so the user sees what the page shows without a triple |
| 6 | save button | `process_catch_button_refresh` | the form of the editor; shows the editor again with the changes |

Each change is a change of the user's own sandbox triple or value. The
calculator of other users stays unchanged until they take over the change.
A removed item is deleted through the logged `del()`.

The parent rule (section 1, rule 3) is what makes the editor fast. The setting
tree loads the calculator tree with one `load_related_by_name(calculator (system), depth 4)`.
The label list loads all labels with one load by `to`.

## 7. Implementation steps

1. `calculator_keywords.json` with the keyword triples of section 4, the const
   pairs in `shared/const/triples.php` and appended to
   `files::SYSTEM_DATA_APPENDED_FILES`.
2. The component types `calculator_title`, `calculator_input`,
   `calculation_result` in `component_types.php`, appended at the end (seed ids,
   see `docs/llm/json_views.md` "append, then pin"), and the dispatch in
   `component_exe::dsp_entries`.
3. A `calculator` reader in the frontend: from the page phrase, or the phrase
   it is built from, to the groups, items, sub-items, labels, hints and
   order triples from the request cache (`data_object`), sorted by the
   follower chain.
3a. The view selection by the phrase: `phrase::name_link` uses the default
   view of the phrase if it has one, and `frontend::default_view_id` takes the
   default view of the phrase for a url with `id` and without `m`.
   `example PV installation` gets `"view": "Calculator"` in the use case json.
4. The three renderers, built from the existing `ui_list` functions of
   section 3.2. The fine-grained component types stay for other views.
5. The use case triples of section 5 in
   `pv_lifetime_balance.json`, which then has no components and no view of
   its own.
6. Tests: `use_case_ui_tests.php` per renderer with a fixed `data_object`.
   `run_pv_lifetime_balance` renders the `Calculator` view for
   `example PV installation` and compares with `pv_lifetime_balance.html`.
   The difference to `pv_lifetime_balance_sample.html` should shrink to the
   numbers.
7. The `Calculator editor` view with the components of section 6, the view
   relation, and a snapshot test of the editor for `example PV installation`.

## 8. Open points

- Merge `field label` and `calculator label` into one `label (system)`?
- Row phrases that the results don't carry yet, e.g. `export` or
  `battery effect` with `consumption mix`: check the phrase groups of the
  results before the rows are fixed.
- The hint of a saving row shows the mix intensity "(128 g/kWh)". That is a
  value of the item phrase, not a result. Is it a second side result type, or
  part of the row hint?
- A selector option label belongs to the calculator, but the same option list
  may be wanted on every page of the phrase. Then the options move to the field
  triples of the phrase.
- The notes of the target have their own titles ("How the CO₂ saving is
  counted.") and texts that span several formulas. Formula descriptions do not
  give that. Options are a `calculator note` list or notes as descriptions of
  the row groups.
- The detail table shows "Jan" for `January`. That needs a short name of the
  phrase, e.g. a `calculator label` per month or a symbol triple.
- The sources show a long citation as the link text, but the json has short
  source names. Should the citation be the description of the source?
- The `field step` values of the assumptions (marked † in the overview) are
  missing in `pv_lifetime_balance.json`, and so is the confidence of the day
  share.
- The html ids in the overview (`kwp`, `orient`, …) are those of the sample.
  The renderer uses the url var as `name` and the label as `id`
  (`docs/llm/frontend.md` "Form field `name` is the url var").
