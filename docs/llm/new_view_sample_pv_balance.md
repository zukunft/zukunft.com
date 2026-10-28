# How to create a new view e.g. a "PV lifetime CO₂ balance" calculator

1. **Create a mock up of the html code.** Write the page as it should look by
   hand and add it e.g. to `src/test/resources/web/html/use_cases`, here
   `pv_lifetime_balance_sample.html`. The mock up is the reference for the
   html code that the view creates. The test compares the created page with it,
   and the mock up is never adapted to the renderer.
2. **Decide the main components, which are independent of the given phrase.**
   The given phrase is the phrase of the url, here `example PV installation`,
   which is built from `PV installation`. The main components are the parts
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

   The triples for the given phrase and the general triples of the listed
   phrases go to the use case json (see "The use case triples of the PV page").
   Prefer general triples (rule 6). What can be derived from the values,
   results and formulas gets no triple.
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
   every link to the phrase uses it (`phrase::name_link` with the phrase id,
   negative for a triple). A url with `id` and without `m` shows the phrase
   with its default view, else with the word or triple view
   (`frontend::phrase_view_id`).
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

One line per piece of the page, in the order of the page, from the document
head to the sources. The frame around the three components comes from the view
`Calculator` with the view style `use-case` and the position types.

Notation: `name` = from *verb* to; `x label` → "text" is the label triple with
the text as its description. [code_id] marks a keyword triple. A value is
written as (phrases) = number. † marks a value or triple that is not in
`pv_lifetime_balance.json` yet. ‡ marks a step that the renderer does not do
yet, ‡ partly a step that it does only in part. The html is the target, as in
`pv_lifetime_balance_sample.html`, without its inline styles (the css has them
as `.use-case` rules) and without its javascript (`docs/llm/frontend.md` "Pure
HTML, no JavaScript").

| step | components | parameter triples | sample triples | subcomponents | subcomponent parameter triples | subcomponent sample triples | resulting HTML |
|---|---|---|---|---|---|---|---|
| open page | **Calculator** (view) | `calculator label` of the page phrase | `PV installation label` → "PV lifetime CO₂ balance" | document head | the page frame of every view<br>title: the phrase name, the calculator label (fallback: the view name) and the pod name | – | `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" …><title>example PV installation - PV lifetime CO2 balance - zukunft.com</title><link rel="stylesheet" href="…/bootstrap.css">…<link rel="stylesheet" href="…/style_html.css"></head>` |
| frame page | Calculator (view) | view style `use-case` | – | page wrapper | the view style `use-case` gives the class of the wrapper (the mock up has `wrap`) and renders the first row as a `<header>` instead of a bootstrap `row` | – | `<div class="use-case">` … `</div>` around the whole page |
| fill title | **title calculator** | `calculator label` [system_calculator_label]<br>`calculator hint` [system_calculator_hint] | `PV installation label` = PV installation *has a* calculator label → "PV lifetime CO₂ balance"<br>`PV installation hint` = PV installation *has a* calculator hint → "Rooftop photovoltaic installation … open below them." | – | – | – | `<header><h4>PV lifetime CO₂ balance</h4><p>Rooftop photovoltaic installation in the Swiss plateau: … all assumptions are open below them.</p></header>` |
| open grid | Calculator (view) | position type `side_or_last_below` of `calculation result` | – | grid | for the view style `use-case` the side-or-below group is one `grid` instead of bootstrap `row` / `col` divs; as in every view, the side-or-below position type closes the row collected before it as a column of the group | – | `<div class="grid">` … `</div>` around the input panel and the result panel |
| open input panel | calculator input | the first column of the side-or-below group | – | input panel | the first column of the group of a `use-case` view is an `aside` panel; `calculator input` wraps the heading, the fields and the button in `inputs` | – | `<aside class="panel"><div class="inputs">` … `</div>` after the save button … `</aside>` after the assumptions |
| select input | **calculator input** | `calculator input` [system_calculator_input]<br>`calculator label` of the group (fallback `msg_id` "Inputs") | `PV installation inputs` = PV installation *has a* calculator input | input heading | – | – | `<h2>Inputs</h2>` |
| add field system size | calculator input | `calculator input`<br>order: *is follower of* [follow] | `PV installation input PV capacity` = PV installation inputs *has a* PV capacity<br>first input: no order triple | number field | `number with limits` [system_field_number_with_limits]<br>`field limit down` [system_field_limit_down]<br>`field step` [system_field_step]<br>`calculator label`<br>shown number: the value with the page phrase and the input phrase | `field PV capacity` = PV capacity *can be* number with limits<br>(PV capacity, field limit down) = 0.5<br>(PV capacity, field step) = 0.5<br>`PV capacity label` = PV capacity *has a* calculator label → "System size"<br>(example PV installation, PV capacity, kWp, assumed) = 10 | `<div class="field"><label for="kwp">System size</label><div class="row"><input id="kwp" type="number" min="0.5" step="0.5" value="10"><span class="unit">kWp</span></div></div>` |
| add selector orientation ‡ partly | calculator input | `calculator input`<br>*is follower of* | `PV installation input yield factor` = PV installation inputs *has a* yield factor<br>`… order` = … *is follower of* `PV installation input PV capacity` | phrase selector field | `phrase selector` [system_field_phrase_selector_type]<br>`field phrase selector value based` [system_field_phrase_selector_value_based]<br>`calculator label`, `calculator hint`<br>options: the `has a` triples of the input phrase with label and order (general, rule 6)<br>selected: the option that the page phrase *uses*<br>hint: the calculator hint without a source number, because an option is chosen and not measured; no form group around the selector; the value of the selected option in the hint ‡ | `field yield factor` = yield factor *can be* phrase selector<br>`yield factor options` = yield factor *can be* field phrase selector value based<br>`orientation of the example PV installation` = example PV installation *uses* east-west<br>`yield factor label` = yield factor *has a* calculator label → "Orientation"<br>`yield factor hint` = yield factor *has a* calculator hint → "Yield factor relative to south 30°"<br>`yield factor option south` … `north` = yield factor *has a* south … north, each option *is follower of* the one before, labels "South, ~30° tilt" … "North, ~30°"<br>(yield factor, south) = 1.00 … (yield factor, north) = 0.59 | `<div class="field"><label for="orient">Orientation</label><select id="orient"><option value="1.00">South, ~30° tilt</option><option value="0.95">South-east / south-west, ~30°</option><option value="0.85" selected>East-west split, flat tilt</option><option value="0.82">East or west only, ~30°</option><option value="0.88">Flat (0°)</option><option value="0.59">North, ~30°</option></select><span class="hint">Yield factor 0.85 relative to south 30° [7]</span></div>` |
| add field battery capacity | calculator input | `calculator input`<br>*is follower of* | `PV installation input battery capacity` = PV installation inputs *has a* battery capacity<br>`… order` = … *is follower of* `PV installation input yield factor` | number field | as the number field above | `field battery capacity` = battery capacity *can be* number with limits<br>(battery capacity, field limit down) = 0<br>(battery capacity, field step) = 1<br>`battery capacity label` = battery capacity *has a* calculator label → "Battery capacity (usable)"<br>(example PV installation, battery capacity, kWh, …) = 10 | `<div class="field"><label for="batt">Battery capacity (usable)</label><div class="row"><input id="batt" type="number" min="0" step="1" value="10"><span class="unit">kWh</span></div></div>` |
| add field consumption | calculator input | `calculator input`<br>*is follower of* | `PV installation input household consumption` = PV installation inputs *has a* household consumption<br>`… order` = … *is follower of* `PV installation input battery capacity` | number field | as above plus<br>`field value phrases` [system_field_value_phrases]: the value with these phrases is shown and stored | `field household consumption` = household consumption *can be* number with limits<br>`household consumption input period` = household consumption *has a* annual<br>`household consumption field value phrase` = household consumption input period *can be* field value phrases<br>(household consumption, field limit down) = 0<br>(household consumption, field step) = 100<br>`household consumption label` = household consumption *has a* calculator label → "Local electricity consumption"<br>`kilowatthours per year` = kWh *per* year, a measure unit<br>`kWh/a` *is symbol for* kilowatthours per year<br>(example PV installation, household consumption, annual, kilowatthours per year, …) = 4500 | `<div class="field"><label for="cons">Local electricity consumption</label><div class="row"><input id="cons" type="number" min="0" step="100" value="4500"><span class="unit">kWh/a</span></div></div>` |
| add save button | calculator input | – | – | save button (`calculator_button_save`) | a post form with the session token, the view, the phrase and the save action as hidden fields; the fields join it by their `form` attribute and the save writes the changed numbers (docs/llm/frontend.md "The save of a calculator page")<br>the text is the `msg_id` of the component: "Save" | – | `<form action="…/view.php" method="post" id="…"><input type="hidden" name="m" value="calculator"><input type="hidden" name="id" value="-2489"><input type="hidden" name="a" value="save"><button class="btn btn-outline-primary btn-space" type="submit" name="…">Save</button></form>` |
| select assumptions | calculator input | `calculator assumption` [system_calculator_assumption]<br>`calculator label` of the group (fallback `msg_id` "Assumptions") | `PV installation assumptions` = PV installation *has a* calculator assumption | assumption block | – | – | `<details><summary>Assumptions</summary>` … the assumption fields … `</details>` |
| add assumption yield | calculator input | `calculator assumption`<br>*is follower of* | `PV installation assumption PV yield` = PV installation assumptions *has a* PV yield<br>first assumption: no order triple | assumption field | `calculator label`<br>`field step`<br>the value: number, unit, description as hint, source as [n] and tag "measured"; or the confidence value as tag "assumed 0.6" | `PV installation assumption PV yield label` → "Specific yield, south 30°"<br>`kilowatthours per kilowatt peak and year` = kWh per kWp *per* year, a measure unit<br>`kWh/kWp·a` *is symbol for* kilowatthours per kilowatt peak and year<br>(PV yield, annual, kilowatthours per kilowatt peak and year, 2023) = 976, source IEA PVPS Task 12 fact sheet 2023<br>(PV yield, field step) = 10<br>`… hint` → "Lifetime average incl. 0.7 %/a degradation" | `<div class="field"><label for="yield">Specific yield, south 30° <span class="tag m">measured</span></label><div class="row"><input id="yield" type="number" step="10" value="976"><span class="unit">kWh/kWp·a</span></div><span class="hint">Lifetime average incl. 0.7 %/a degradation [1]</span></div>` |
| add assumption lifetime | calculator input | ″ | `PV installation assumption lifetime` … *has a* lifetime<br>`… order` = … *is follower of* `PV installation assumption PV yield` | assumption field | ″ | no label: the phrase name<br>`year (duration unit)` = year *must be one of* duration unit, a measure unit<br>`a` *is symbol for* year (duration unit)<br>(PV installation, lifetime, year (duration unit)) = 30, source IEA PVPS Task 12 fact sheet 2023<br>(lifetime, field step) = 1<br>`… hint` → "Reference lifetime used in the LCA" | `<div class="field"><label for="life">Lifetime <span class="tag m">measured</span></label><div class="row"><input id="life" type="number" step="1" value="30"><span class="unit">a</span></div><span class="hint">Reference lifetime used in the LCA [1]</span></div>` |
| add assumption PV emissions | calculator input | ″ | `PV installation assumption PV in Switzerland` … *has a* PV in Switzerland<br>`… order` = … *is follower of* `PV installation assumption lifetime` | assumption field | ″ | `… label` → "PV life-cycle emissions"<br>`PV in Switzerland input year` = PV in Switzerland *has a* 2023<br>`PV in Switzerland field value phrase` = PV in Switzerland input year *can be* field value phrases<br>(PV in Switzerland, life cycle, CO2-eq, gram per kWh, 2023) = 35.8, source IEA PVPS Task 12 fact sheet 2023<br>`g/kWh` *is symbol for* gram per kWh<br>(PV in Switzerland, field step) = 0.1<br>`… hint` → "Swiss 3 kWp mono-Si pitched roof, 2023 update; converted to a fixed amount per kWp" ‡ the source number inside the hint | `<div class="field"><label for="pvlca">PV life-cycle emissions <span class="tag m">measured</span></label><div class="row"><input id="pvlca" type="number" step="0.1" value="35.8"><span class="unit">g/kWh</span></div><span class="hint">Swiss 3 kWp mono-Si pitched roof, 2023 update [1]; converted to a fixed amount per kWp</span></div>` |
| add assumption consumer mix | calculator input | ″ | `PV installation assumption consumption mix` … *has a* consumption mix<br>`… order` = … *is follower of* `PV installation assumption PV in Switzerland` | assumption field | ″ | `… label` → "Swiss consumer mix, annual"<br>`consumption mix input year` = consumption mix *has a* 2018<br>`consumption mix field value phrase` = consumption mix input year *can be* field value phrases<br>(consumption mix, 2018, CO2-eq, …) = 128, source BAFU Swiss electricity mixes 2018<br>(consumption mix, field step) = 1<br>`… hint` → "2018 consumer mix, low voltage" | `<div class="field"><label for="mixA">Swiss consumer mix, annual <span class="tag m">measured</span></label><div class="row"><input id="mixA" type="number" step="1" value="128"><span class="unit">g/kWh</span></div><span class="hint">2018 consumer mix, low voltage [2]</span></div>` |
| add selector replacement mix | calculator input | ″ | `PV installation assumption replacement mix` … *has a* replacement mix<br>`… order` = … *is follower of* `PV installation assumption consumption mix` | phrase selector field | `phrase selector`<br>options: the phrases that *is a* replacement mix, each with its value and source<br>selected: the option that the page phrase *uses* | `field replacement mix` = replacement mix *can be* phrase selector<br>residual mix *is a* replacement mix<br>production mix *is a* replacement mix<br>`production mix order` = production mix *is follower of* residual mix<br>`replacement mix of the example PV installation` = example PV installation *uses* residual mix | `<div class="field"><label for="mixC">Replacement mix</label><select id="mixC"><option value="523" selected>European residual mix — 523 g/kWh [4]</option><option value="395">ENTSO-E production mix 2022 — ~395 g/kWh (midpoint of 350–440) [3]</option></select></div>` |
| add assumption load share | calculator input | ″ | `PV installation assumption day share` … *has a* day share<br>`… order` = … *is follower of* `PV installation assumption replacement mix` | assumption field | ″ | `day share label` = day share *has a* calculator label → "Load share during PV hours"<br>`share (ratio)` = share *must be one of* ratio, a percent unit<br>`share` *is symbol for* share (ratio)<br>(example PV installation, day share, household consumption, share (ratio), assumed) = 0.35<br>(…, assumed, confidence, share (ratio)) = 0.6<br>(day share, field limit down) = 0, (day share, field limit up) = 1, (day share, field step) = 0.05<br>`… hint` → "Household; raise for home office or a heat pump running at noon" | `<div class="field"><label for="dayshare">Load share during PV hours <span class="tag a">assumed 0.6</span></label><div class="row"><input id="dayshare" type="number" step="0.05" min="0" max="1" value="0.35"><span class="unit">share</span></div><span class="hint">Household; raise for home office or a heat pump running at noon</span></div>` |
| add assumption efficiency | calculator input | ″ | `PV installation assumption round-trip efficiency` … *has a* round-trip efficiency<br>`… order` = … *is follower of* `PV installation assumption day share` | assumption field | ″ | `… label` → "Battery round-trip efficiency"<br>(battery, round-trip efficiency, share (ratio), assumed) = 0.9<br>(…, assumed, confidence, share (ratio)) = 0.7<br>(round-trip efficiency, field step) = 0.01 | `<div class="field"><label for="eta">Battery round-trip efficiency <span class="tag a">assumed 0.7</span></label><div class="row"><input id="eta" type="number" step="0.01" value="0.90"><span class="unit">share</span></div></div>` |
| add assumption battery emissions | calculator input | ″ | `PV installation assumption battery production emission` … *has a* battery production emission<br>`… order` = … *is follower of* `PV installation assumption round-trip efficiency` | assumption field | ″ | `battery production emission label` = battery production emission *has a* calculator label → "Battery production emissions"<br>(battery, battery production emission, kg per kWh, assumed) = 100<br>`kg/kWh` *is symbol for* kg per kWh<br>(…, assumed, confidence, percent) = 0.5 ‡ the tag, because the confidence has no kg per kWh<br>(battery production emission, field step) = 5<br>`… hint` → "Literature spans ~37–212 kg CO₂-eq per kWh capacity" ‡ the two source numbers | `<div class="field"><label for="bco2">Battery production emissions <span class="tag a">assumed 0.5</span></label><div class="row"><input id="bco2" type="number" step="5" value="100"><span class="unit">kg/kWh</span></div><span class="hint">Literature spans ~37–212 kg CO₂-eq per kWh capacity [5][6]</span></div>` |
| add assumption battery lifetime | calculator input | ″ | `PV installation assumption battery lifetime` … *has a* battery lifetime<br>`… order` = … *is follower of* `PV installation assumption battery production emission` | assumption field | ″ | no label: the phrase name<br>(battery, battery lifetime, year (duration unit), assumed) = 15<br>(…, assumed, confidence, percent) = 0.6 ‡ the tag, because the confidence has no year (duration unit)<br>(battery lifetime, field step) = 1 | `<div class="field"><label for="blife">Battery lifetime <span class="tag a">assumed 0.6</span></label><div class="row"><input id="blife" type="number" step="1" value="15"><span class="unit">a</span></div></div>` |
| open result panel | calculation result | position type `side_or_last_below` | – | result panel | every following column of the group of a `use-case` view is a `section` panel | – | `<section class="panel">` … `</section>` after the sources |
| select result | **calculation result** | `calculator result column` [system_calculator_result_column]<br>`calculator label` of the group | `PV installation result columns` = PV installation *has a* calculator result column<br>`PV installation result columns label` → "Main results over the lifetime" | result heading | – | – | `<h2>Main results over the lifetime</h2>` |
| open main table | calculation result | – | – | main table frame | the main table renderer wraps the table in `scroll` and the rows in `tbody` | – | `<div class="scroll"><table>` … `<tbody>` before the first row … `</tbody></table></div>` after the last row |
| fill table header | calculation result | `calculator result column`<br>*is follower of* | `PV installation column lifetime` … *has a* lifetime<br>`PV installation column annual` … *has a* annual<br>`… order` = … *is follower of* `PV installation column lifetime` | table header | `calculator label` (fallback: the phrase name; first column: `msg_id` "Result") | `annual label` = annual *has a* calculator label → "Per year" | `<thead><tr><th>Result</th><th class="n">Lifetime</th><th class="n">Per year</th></tr></thead>` |
| add section electricity | calculation result | `calculator row group` [system_calculator_row_group]<br>*is follower of* | `PV installation row groups` = PV installation *has a* calculator row group<br>`PV installation rows electricity` … *has a* electricity (first group) | section row | `calculator label` (fallback: the phrase name) | – | `<tr class="sec"><td colspan="3">Electricity</td></tr>` |
| add row PV production | calculation result | ″ | `PV installation row PV production` = PV installation rows electricity *has a* PV production (first row) | result row | `calculator label`, `calculator hint`<br>`calculator highlight` [system_calculator_highlight] of the item, the row phrase or the filter phrase<br>sub text: the formula of the result with its operands<br>cells: the result with the page phrase, the row phrase and the column phrase | `PV production highlight` = PV production *has a* calculator highlight<br>formula `PV production by orientation` | `<tr class="key"><td>PV production<span class="sub">10.0 kWp × 976 kWh/kWp × factor 0.85</span></td><td class="n">248'880 kWh</td><td class="n">8'296 kWh</td></tr>` |
| add row direct use | calculation result | ″ | `PV installation row direct use` … *has a* direct use<br>`… order` = … *is follower of* `PV installation row PV production` | result row | ″ | `direct use label` = direct use *has a* calculator label → "Used directly" | `<tr><td>Used directly</td><td class="n">47'250 kWh</td><td class="n">1'575 kWh</td></tr>` |
| add row via battery | calculation result | ″ | `PV installation row battery discharge` … *has a* battery discharge<br>`… order` = … *is follower of* `PV installation row direct use` | result row | ″ | `battery discharge label` = battery discharge *has a* calculator label → "Used via battery"<br>`battery discharge hint` = battery discharge *has a* calculator hint → "after losses" | `<tr><td>Used via battery<span class="sub">after losses</span></td><td class="n">66'713 kWh</td><td class="n">2'224 kWh</td></tr>` |
| add row export | calculation result | ″ | `PV installation row export` … *has a* export<br>`… order` = … *is follower of* `PV installation row battery discharge` | result row | ″ | `… label` → "Exported to grid" (on the item, because the phrase name "Export" is the general text) | `<tr><td>Exported to grid</td><td class="n">127'504 kWh</td><td class="n">4'250 kWh</td></tr>` |
| add row battery losses | calculation result | ″ | `PV installation row battery losses` … *has a* battery losses<br>`… order` = … *is follower of* `PV installation row export` | result row | ″ | no label: the phrase name | `<tr><td>Battery losses</td><td class="n">7'413 kWh</td><td class="n">247 kWh</td></tr>` |
| add row grid import | calculation result | ″ | `PV installation row grid import` … *has a* grid import<br>`… order` = … *is follower of* `PV installation row battery losses` | result row | ″ | `… label` → "Grid import still needed" | `<tr><td>Grid import still needed</td><td class="n">21'037 kWh</td><td class="n">701 kWh</td></tr>` |
| add row self-consumption | calculation result | ″ | `PV installation row self-consumption share` … *has a* self-consumption share<br>`… order` = … *is follower of* `PV installation row grid import` | result row | ″ | `self-consumption share label` = self-consumption share *has a* calculator label → "Self-consumption rate"<br>`self-consumption share hint` = self-consumption share *has a* calculator hint → "share of PV used on site" | `<tr><td>Self-consumption rate<span class="sub">share of PV used on site</span></td><td class="n"></td><td class="n">49 %</td></tr>` |
| add row autarky | calculation result | ″ | `PV installation row autarky` … *has a* autarky<br>`… order` = … *is follower of* `PV installation row self-consumption share` | result row | ″ | `autarky hint` = autarky *has a* calculator hint → "share of load covered by PV"<br>`autarky highlight` = autarky *has a* calculator highlight | `<tr class="key"><td>Autarky<span class="sub">share of load covered by PV</span></td><td class="n"></td><td class="n">84 %</td></tr>` |
| add section production emissions | calculation result | `calculator row group` | `PV installation rows production emissions` … *has a* embodied emission<br>`… order` = … *is follower of* `PV installation rows electricity` | section row | ″ | `embodied emission label` = embodied emission *has a* calculator label → "Production emissions" | `<tr class="sec"><td colspan="3">Production emissions</td></tr>` |
| add row PV system | calculation result | ″ | `PV installation row PV embodied emission` … *has a* PV embodied emission (first row) | result row | ″ plus the side result: a phrase that the row phrase *has a* and that has a result for the page, shown in the sub text<br>label: the row phrase is built from the group phrase, so the other phrase `PV` gives the label (rule 8) | `PV label` = PV *has a* calculator label → "PV system"<br>`PV embodied emission side result` = PV embodied emission *has a* PV site intensity<br>formula `PV embodied emission formula` | `<tr><td>PV system<span class="sub">1'048 kg CO₂-eq/kWp → 42.1 g/kWh at this site</span></td><td class="n">10.5 t</td><td class="n">349 kg</td></tr>` |
| add row battery | calculation result | ″ | `PV installation row battery embodied emission` … *has a* battery embodied emission<br>`… order` = … *is follower of* `PV installation row PV embodied emission` | result row | ″ | no label: `battery embodied emission` = embodied emission *of* battery, so the row shows the name of `battery` (rule 8)<br>formula `battery embodied emission formula` | `<tr><td>Battery<span class="sub">2 × 10 kWh × 100 kg/kWh</span></td><td class="n">2.0 t</td><td class="n">67 kg</td></tr>` |
| add section saving | calculation result | `calculator row group`<br>`calculator signed` [system_calculator_signed] | `PV installation rows saving` … *has a* net avoided emission<br>`… order` = … *is follower of* `PV installation rows production emissions`<br>`PV installation rows saving signed` = … *has a* calculator signed | section row | ″ | `net avoided emission label` = net avoided emission *has a* calculator label → "CO₂-eq saved, net of production emissions" | `<tr class="sec"><td colspan="3">CO₂-eq saved, net of production emissions</td></tr>` |
| add row saving annual mix | calculation result | ″ | `PV installation row saving consumption mix` … *has a* consumption mix (first row) | result row | ″ plus the filter: a sub-item *with* phrase; signed: the cells as `pos` / `neg`; side result and highlight also from the filter phrase | `… filter` = … *with* net avoided emission<br>`net avoided emission side result` = net avoided emission *has a* emission payback<br>`emission payback label` = emission payback *has a* calculator label → "payback"<br>`net avoided emission highlight` = net avoided emission *has a* calculator highlight<br>`… label` → "vs. Swiss consumer mix, annual" | `<tr class="key"><td>vs. Swiss consumer mix, annual (128 g/kWh)<span class="sub">payback 12.1 a</span></td><td class="n"><span class="pos">18.4 t</span></td><td class="n"><span class="pos">614 kg</span></td></tr>` |
| add row saving monthly mix | calculation result | ″ | `PV installation row saving PV-weighted mix` … *has a* PV-weighted consumption mix<br>`… order` = … *is follower of* `PV installation row saving consumption mix` | result row | ″ | `… filter` as above; side result and highlight from the filter phrase<br>`… label` → "vs. Swiss consumer mix, monthly" | `<tr class="key"><td>vs. Swiss consumer mix, monthly (106 g/kWh PV-weighted)<span class="sub">payback 14.7 a</span></td><td class="n"><span class="pos">13.1 t</span></td><td class="n"><span class="pos">436 kg</span></td></tr>` |
| add row saving replacement mix | calculation result | ″ | `PV installation row saving replacement mix` … *has a* replacement mix<br>`… order` = … *is follower of* `PV installation row saving PV-weighted mix` | result row | ″ | `… filter` as above; side result and highlight from the filter phrase<br>`… label` → "vs. replacement mix" | `<tr class="key"><td>vs. replacement mix (523 g/kWh)<span class="sub">payback 3.0 a</span></td><td class="n"><span class="pos">113.8 t</span></td><td class="n"><span class="pos">3'794 kg</span></td></tr>` |
| add row battery effect | calculation result | ″ | `PV installation row battery effect` … *has a* battery effect<br>`… order` = … *is follower of* `PV installation row saving replacement mix` | result row | ″ | `… filter` = … *with* consumption mix<br>`battery effect label` = battery effect *has a* calculator label → "Effect of the battery"<br>`… hint` → "vs. same PV without battery, annual Swiss mix" (on the item, because it names the filter) | `<tr><td>Effect of the battery<span class="sub">vs. same PV without battery, annual Swiss mix</span></td><td class="n"><span class="neg">-2.9 t</span></td><td class="n"><span class="neg">-98 kg</span></td></tr>` |
| select details | calculation result | `calculator detail table` [system_calculator_detail_table]<br>`calculator label` of the group | `PV installation detail tables` = PV installation *has a* calculator detail table<br>`PV installation detail tables label` → "Monthly details and sources" | detail block | – | – | `<details><summary>Monthly details and sources</summary><div class="more">` … detail tables, notes, sources … `</div></details>` |
| add monthly table | calculation result | `calculator detail table`<br>*is follower of* | `PV installation detail monthly balance` … *has a* month (the only table) | detail table | `calculator label` of the table as heading<br>columns: sub-items *has a* phrase with label and order<br>rows: the phrases of the table phrase (January … December)<br>extra rows: sub-items *with* phrase<br>first header: the table phrase | `… label` → "Monthly energy balance, average year"<br>`PV installation detail column PV production` … `consumption mix` (7), item labels "PV kWh", "Load kWh", "Direct", "Battery", "CH mix g/kWh"; no label for export and grid import: the phrase name |  `<h2>Monthly energy balance, average year</h2>`<br>`<thead><tr><th>Month</th><th class="n">PV kWh</th><th class="n">Load kWh</th><th class="n">Direct</th><th class="n">Battery</th><th class="n">Export</th><th class="n">Grid import</th><th class="n">CH mix g/kWh</th></tr></thead>` |
| open detail table | calculation result | – | – | detail table frame | the same renderer as the main table | – | `<div class="scroll"><table>` after the heading … `<tbody>` before the first month … `</tbody></table></div>` after the year row |
| add month rows | calculation result | ″ | ″ | detail row | the results and values with the page phrase, the row phrase and the column phrase | 12 rows: January … December | `<tr><td>Jan</td><td class="n">207</td><td class="n">450</td><td class="n">158</td><td class="n">45</td><td class="n">0</td><td class="n">248</td><td class="n">151</td></tr>` … `<tr><td>Dec</td>…</tr>` |
| add year row | calculation result | ″ | ″ | detail total row | sub-item *with* phrase, `calculator label`, `calculator highlight` | `PV installation detail total` = … *with* annual<br>`… label` → "Year"<br>`PV installation detail total highlight` | `<tr class="key"><td>Year</td><td class="n">8'296</td><td class="n">4'500</td><td class="n">1'575</td><td class="n">2'224</td><td class="n">4'250</td><td class="n">701</td><td class="n">106</td></tr>` |
| add notes | calculation result | – (derived) | – | formula notes | the descriptions of the formulas of the shown results | `net avoided emission formula`, `gross avoided emission by PV-weighted consumption mix`, `battery discharge formula`, … | `<div class="notes"><p><strong>How the CO₂ saving is counted.</strong> Every PV kilowatt-hour that reaches a consumer … </p><p><strong>Seasonal Swiss mix.</strong> …</p><p><strong>Self-consumption model.</strong> …</p></div>` |
| add sources | calculation result | – (derived) | – | sources | the sources of the shown values, numbered in the order of the first use<br>heading: the `msg_id` "Sources" in the `src` div | IEA PVPS Task 12 fact sheet 2023, BAFU Swiss electricity mixes 2018, treeze monthly electricity mixes 2025, Swissolar PV fact sheet 2024, Lithium-ion cell LCA comparison 2025, HTW Berlin solar storage climate 2019, gruenes.haus tilt and orientation table | `<div class="src"><h2>Sources</h2><ol><li><a href="https://iea-pvps.org/…/Task-12-Fact-Sheet-Stucki.pdf" target="_blank" rel="noopener">Stucki, Götz, de Wild-Scholten, Frischknecht: IEA PVPS Task 12 fact sheet, LCA of PV electricity, 2023 update</a></li>` … 7 sources … `</ol></div>` |

The gaps between the triples and the target html are in "Open points" at the end.

Differences to the mock up: the wrapper class is `use-case` instead of `wrap`,
the placeholder button (line 220) is the refresh form, and the orientation hint
(line 209) is written by the server instead of the script (line 558).

Shortened in the table with "…" but created in the same way as the lines
shown: the lead text (line 188), the month rows February to November (lines
312–321), the texts of the three notes (lines 329–331) and the sources 2 to 7
(lines 338–343). The closing tags of the detail block are on lines 344–347.

### The components with their triples and parent triples

Each component reads its triples through a parent triple with a `code_id`
(section 1, rule 3). The order triples (*is follower of*) need no parent: they
are read from the items of the list. "Their labels" or "their highlights" means
the triple of the item or, if it has none, the triple of its phrase (rule 6).

| component | level | triples used | parent triples to select them [code_id] |
|---|---|---|---|
| `Calculator` | view | `example PV installation` with `"view": "Calculator"` (the page phrase and the phrase it is built from) | – |
| `title calculator` | view component | `PV installation label`, `PV installation hint` | `calculator label` [system_calculator_label], `calculator hint` [system_calculator_hint] |
| `calculator input` | view component | `PV installation inputs`, `PV installation assumptions` | `calculator input` [system_calculator_input], `calculator assumption` [system_calculator_assumption] |
| number field | sub-component | the items `PV installation input PV capacity`, `… battery capacity`, `… household consumption` with their labels and order triples<br>the field triples `field PV capacity`, `field battery capacity`, `field household consumption`, `household consumption input period`, `household consumption field value phrase` | items: `calculator input`<br>labels: `calculator label`<br>field: `number with limits` [system_field_number_with_limits], `field value phrases` [system_field_value_phrases], `field limit down` [system_field_limit_down], `field step` [system_field_step] |
| phrase selector field (orientation) | sub-component | the item `PV installation input yield factor` with its order triple and the label and hint of `yield factor`<br>the 6 options `yield factor option south` … `north` of the phrase with their labels and order triples<br>the field triples `field yield factor`, `yield factor options`<br>the selection `orientation of the example PV installation` | items: `calculator input`<br>labels, hint: `calculator label`, `calculator hint`<br>field: `phrase selector` [system_field_phrase_selector_type], `field phrase selector value based` [system_field_phrase_selector_value_based]<br>options: the verb *has a* from the input phrase<br>selection: the verb *uses* |
| refresh button | sub-component | – (the text is a `msg_id`) | – |
| assumption field | sub-component | the 8 items `PV installation assumption PV yield` … `battery lifetime` (without the replacement mix) with their labels and order triples | items: `calculator assumption`<br>labels: `calculator label`<br>step: `field step` [system_field_step] |
| phrase selector field (replacement mix) | sub-component | the item `PV installation assumption replacement mix` with its order triple<br>the field triple `field replacement mix`, the options residual mix *is a* replacement mix and production mix *is a* replacement mix<br>the selection `replacement mix of the example PV installation` | item: `calculator assumption`<br>field: `phrase selector` [system_field_phrase_selector_type]<br>options: the verb *is a*<br>selection: the verb *uses* |
| `calculation result` | view component | `PV installation result columns`, `PV installation row groups`, `PV installation detail tables` with their labels | `calculator result column` [system_calculator_result_column], `calculator row group` [system_calculator_row_group], `calculator detail table` [system_calculator_detail_table], `calculator label` |
| main table header | sub-component | the columns `PV installation column lifetime`, `… annual` with the label and order triple | columns: `calculator result column`<br>labels: `calculator label` |
| section row | sub-component | the row groups `PV installation rows electricity`, `… production emissions`, `… saving` with their labels, order triples and `PV installation rows saving signed` | groups: `calculator row group`<br>labels: `calculator label`<br>sign: `calculator signed` [system_calculator_signed] |
| result row | sub-component | the 14 rows `PV installation row …` with their labels, hints, highlights and order triples<br>the 4 filters `… filter` (*with*)<br>the side results `PV embodied emission side result` and `net avoided emission side result` of the row and filter phrases with the label of `emission payback` | rows: `calculator row group` (two levels below)<br>labels, hints: `calculator label`, `calculator hint` of the item, the row phrase or, for a row built from the group phrase, the other phrase<br>key rows: `calculator highlight` [system_calculator_highlight] of the item, the row phrase or the filter phrase<br>filters: sub-items of the row<br>side results: the verb *has a* from the row phrase or the filter phrase, with a result for the page |
| detail table | sub-component | `PV installation detail monthly balance` with its label, the 7 columns `PV installation detail column …` with their labels and order triples, `PV installation detail total` with its label and highlight | table: `calculator detail table`<br>labels: `calculator label`<br>total row: `calculator highlight`<br>columns, total: sub-items of the table |
| formula notes | sub-component | – (derived from the formulas of the shown results) | – |
| sources | sub-component | – (derived from the sources of the shown values) | – |

All calculator keywords are children of `calculator (system)` [system_calculator]
and all field keywords of `process catch field (system)`
[system_process_catch_field], so one load of a root gives all its keywords.

### All triples used

`name` = from *verb* to [code_id] → "description of a label or hint".

**Calculator keywords** (`calculator_keywords.json`; the root `zukunft.com system` is reused from `process_catch_fields.json`)

- `zukunft.com system` = zukunft.com *is a* system [zukunft_com_system]
- `calculator (system)` = calculator *can be* zukunft.com system [system_calculator]
- `calculator label` = calculator (system) *has a* label [system_calculator_label]
- `calculator hint` = calculator (system) *has a* hint [system_calculator_hint]
- `calculator input` = calculator (system) *has a* input [system_calculator_input]
- `calculator assumption` = calculator (system) *has a* assumption [system_calculator_assumption]
- `calculator result column` = calculator (system) *has a* column [system_calculator_result_column]
- `calculator row group` = calculator (system) *has a* group [system_calculator_row_group]
- `calculator detail table` = calculator (system) *has a* table [system_calculator_detail_table]
- `calculator highlight` = calculator (system) *has a* highlight [system_calculator_highlight]
- `calculator signed` = calculator (system) *has a* sign [system_calculator_signed]

**Field keywords** (`process_catch_fields.json`, reused)

- `process catch field (system)` = process catch field *can be* zukunft.com system [system_process_catch_field]
- `field type` = process catch field (system) *has a* type [system_field_type]
- `number with limits` = field type *can be* number [system_field_number_with_limits]
- `phrase selector` = field type *can be* selector [system_field_phrase_selector_type]
- `field limit down` = process catch field (system) *has a* minimum [system_field_limit_down]
- `field step` = process catch field (system) *has a* step [system_field_step]
- `field value phrases` = process catch field (system) *has a* phrase [system_field_value_phrases]
- `field phrase selector` = process catch field (system) *has a* selector [system_field_phrase_selector]
- `field phrase selector value based` = field phrase selector *has a* value [system_field_phrase_selector_value_based]

**Page phrase**

- `example PV installation` = PV installation *kind of* example, with `"view": "Calculator"`

**Title**

- `PV installation label` = PV installation *has a* calculator label → "PV lifetime CO₂ balance"
- `PV installation hint` = PV installation *has a* calculator hint → "Rooftop photovoltaic installation in the Swiss plateau: … all assumptions are open below them."

**General triples of the phrases** (rule 6: used by every calculator that lists the phrase, without a `PV installation` prefix)

Labels and hints:

- `PV capacity label` = PV capacity *has a* calculator label → "System size"
- `yield factor label` = yield factor *has a* calculator label → "Orientation"
- `yield factor hint` = yield factor *has a* calculator hint → "Yield factor relative to south 30°"
- `battery capacity label` = battery capacity *has a* calculator label → "Battery capacity (usable)"
- `household consumption label` = household consumption *has a* calculator label → "Local electricity consumption"
- `day share label` = day share *has a* calculator label → "Load share during PV hours"
- `battery production emission label` = battery production emission *has a* calculator label → "Battery production emissions"
- `embodied emission label` = embodied emission *has a* calculator label → "Production emissions"
- `net avoided emission label` = net avoided emission *has a* calculator label → "CO₂-eq saved, net of production emissions"
- `direct use label` = direct use *has a* calculator label → "Used directly"
- `battery discharge label` = battery discharge *has a* calculator label → "Used via battery"
- `battery discharge hint` = battery discharge *has a* calculator hint → "after losses"
- `self-consumption share label` = self-consumption share *has a* calculator label → "Self-consumption rate"
- `self-consumption share hint` = self-consumption share *has a* calculator hint → "share of PV used on site"
- `autarky hint` = autarky *has a* calculator hint → "share of load covered by PV"
- `emission payback label` = emission payback *has a* calculator label → "payback"
- `battery effect label` = battery effect *has a* calculator label → "Effect of the battery"
- `annual label` = annual *has a* calculator label → "Per year"
- `PV label` = PV *has a* calculator label → "PV system"

Flags:

- `PV production highlight` = PV production *has a* calculator highlight
- `autarky highlight` = autarky *has a* calculator highlight
- `net avoided emission highlight` = net avoided emission *has a* calculator highlight

Side results:

- `PV embodied emission side result` = PV embodied emission *has a* PV site intensity
- `net avoided emission side result` = net avoided emission *has a* emission payback

Options of the orientation selector (field level, with the field triples below):

- `yield factor option south` = yield factor *has a* south
- `yield factor option south label` = yield factor option south *has a* calculator label → "South, ~30° tilt"
- `yield factor option south-east` = yield factor *has a* south-east
- `yield factor option south-east label` = yield factor option south-east *has a* calculator label → "South-east / south-west, ~30°"
- `yield factor option south-east order` = yield factor option south-east *is follower of* yield factor option south
- `yield factor option east-west` = yield factor *has a* east-west
- `yield factor option east-west label` = yield factor option east-west *has a* calculator label → "East-west split, flat tilt"
- `yield factor option east-west order` = yield factor option east-west *is follower of* yield factor option south-east
- `yield factor option east` = yield factor *has a* east
- `yield factor option east label` = yield factor option east *has a* calculator label → "East or west only, ~30°"
- `yield factor option east order` = yield factor option east *is follower of* yield factor option east-west
- `yield factor option horizontal` = yield factor *has a* horizontal
- `yield factor option horizontal label` = yield factor option horizontal *has a* calculator label → "Flat (0°)"
- `yield factor option horizontal order` = yield factor option horizontal *is follower of* yield factor option east
- `yield factor option north` = yield factor *has a* north
- `yield factor option north label` = yield factor option north *has a* calculator label → "North, ~30°"
- `yield factor option north order` = yield factor option north *is follower of* yield factor option horizontal

**Inputs**

- `PV installation inputs` = PV installation *has a* calculator input
- `PV installation input PV capacity` = PV installation inputs *has a* PV capacity
- `PV installation input yield factor` = PV installation inputs *has a* yield factor
- `PV installation input yield factor order` = PV installation input yield factor *is follower of* PV installation input PV capacity
- `PV installation input battery capacity` = PV installation inputs *has a* battery capacity
- `PV installation input battery capacity order` = PV installation input battery capacity *is follower of* PV installation input yield factor
- `PV installation input household consumption` = PV installation inputs *has a* household consumption
- `PV installation input household consumption order` = PV installation input household consumption *is follower of* PV installation input battery capacity

**Field triples of the input and assumption phrases** (`pv_lifetime_balance.json`, unchanged)

- `field PV capacity` = PV capacity *can be* number with limits
- `field battery capacity` = battery capacity *can be* number with limits
- `field household consumption` = household consumption *can be* number with limits
- `household consumption input period` = household consumption *has a* annual
- `household consumption field value phrase` = household consumption input period *can be* field value phrases
- `field yield factor` = yield factor *can be* phrase selector
- `yield factor options` = yield factor *can be* field phrase selector value based
- `orientation of the example PV installation` = example PV installation *uses* east-west
- `replacement mix` = mix *kind of* replacement
- residual mix *is a* replacement mix
- production mix *is a* replacement mix
- `field replacement mix` = replacement mix *can be* phrase selector
- `replacement mix of the example PV installation` = example PV installation *uses* residual mix

**Assumptions**

- `PV installation assumptions` = PV installation *has a* calculator assumption
- `PV installation assumption PV yield` = PV installation assumptions *has a* PV yield
- `PV installation assumption PV yield label` = PV installation assumption PV yield *has a* calculator label → "Specific yield, south 30°"
- `PV installation assumption lifetime` = PV installation assumptions *has a* lifetime
- `PV installation assumption lifetime order` = PV installation assumption lifetime *is follower of* PV installation assumption PV yield
- `PV installation assumption PV in Switzerland` = PV installation assumptions *has a* PV in Switzerland
- `PV installation assumption PV in Switzerland label` = PV installation assumption PV in Switzerland *has a* calculator label → "PV life-cycle emissions"
- `PV installation assumption PV in Switzerland order` = PV installation assumption PV in Switzerland *is follower of* PV installation assumption lifetime
- `PV installation assumption consumption mix` = PV installation assumptions *has a* consumption mix
- `PV installation assumption consumption mix label` = PV installation assumption consumption mix *has a* calculator label → "Swiss consumer mix, annual"
- `PV installation assumption consumption mix order` = PV installation assumption consumption mix *is follower of* PV installation assumption PV in Switzerland
- `PV installation assumption replacement mix` = PV installation assumptions *has a* replacement mix
- `PV installation assumption replacement mix order` = PV installation assumption replacement mix *is follower of* PV installation assumption consumption mix
- `PV installation assumption day share` = PV installation assumptions *has a* day share
- `PV installation assumption day share order` = PV installation assumption day share *is follower of* PV installation assumption replacement mix
- `PV installation assumption round-trip efficiency` = PV installation assumptions *has a* round-trip efficiency
- `PV installation assumption round-trip efficiency label` = PV installation assumption round-trip efficiency *has a* calculator label → "Battery round-trip efficiency"
- `PV installation assumption round-trip efficiency order` = PV installation assumption round-trip efficiency *is follower of* PV installation assumption day share
- `PV installation assumption battery production emission` = PV installation assumptions *has a* battery production emission
- `PV installation assumption battery production emission order` = PV installation assumption battery production emission *is follower of* PV installation assumption round-trip efficiency
- `PV installation assumption battery lifetime` = PV installation assumptions *has a* battery lifetime
- `PV installation assumption battery lifetime order` = PV installation assumption battery lifetime *is follower of* PV installation assumption battery production emission

**Main table columns**

- `PV installation result columns` = PV installation *has a* calculator result column
- `PV installation result columns label` = PV installation result columns *has a* calculator label → "Main results over the lifetime"
- `PV installation column lifetime` = PV installation result columns *has a* lifetime
- `PV installation column annual` = PV installation result columns *has a* annual
- `PV installation column annual order` = PV installation column annual *is follower of* PV installation column lifetime

**Row groups**

- `PV installation row groups` = PV installation *has a* calculator row group
- `PV installation rows electricity` = PV installation row groups *has a* electricity
- `PV installation rows production emissions` = PV installation row groups *has a* embodied emission
- `PV installation rows production emissions order` = PV installation rows production emissions *is follower of* PV installation rows electricity
- `PV installation rows saving` = PV installation row groups *has a* net avoided emission
- `PV installation rows saving signed` = PV installation rows saving *has a* calculator signed
- `PV installation rows saving order` = PV installation rows saving *is follower of* PV installation rows production emissions

**Rows of the electricity group**

- `PV installation row PV production` = PV installation rows electricity *has a* PV production
- `PV installation row direct use` = PV installation rows electricity *has a* direct use
- `PV installation row direct use order` = PV installation row direct use *is follower of* PV installation row PV production
- `PV installation row battery discharge` = PV installation rows electricity *has a* battery discharge
- `PV installation row battery discharge order` = PV installation row battery discharge *is follower of* PV installation row direct use
- `PV installation row export` = PV installation rows electricity *has a* export
- `PV installation row export label` = PV installation row export *has a* calculator label → "Exported to grid"
- `PV installation row export order` = PV installation row export *is follower of* PV installation row battery discharge
- `PV installation row battery losses` = PV installation rows electricity *has a* battery losses
- `PV installation row battery losses order` = PV installation row battery losses *is follower of* PV installation row export
- `PV installation row grid import` = PV installation rows electricity *has a* grid import
- `PV installation row grid import label` = PV installation row grid import *has a* calculator label → "Grid import still needed"
- `PV installation row grid import order` = PV installation row grid import *is follower of* PV installation row battery losses
- `PV installation row self-consumption share` = PV installation rows electricity *has a* self-consumption share
- `PV installation row self-consumption share order` = PV installation row self-consumption share *is follower of* PV installation row grid import
- `PV installation row autarky` = PV installation rows electricity *has a* autarky
- `PV installation row autarky order` = PV installation row autarky *is follower of* PV installation row self-consumption share

**Rows of the production emissions group**

- `PV installation row PV embodied emission` = PV installation rows production emissions *has a* PV embodied emission
- `PV installation row battery embodied emission` = PV installation rows production emissions *has a* battery embodied emission
- `PV installation row battery embodied emission order` = PV installation row battery embodied emission *is follower of* PV installation row PV embodied emission

**Rows of the saving group**

- `PV installation row saving consumption mix` = PV installation rows saving *has a* consumption mix
- `PV installation row saving consumption mix label` = PV installation row saving consumption mix *has a* calculator label → "vs. Swiss consumer mix, annual"
- `PV installation row saving consumption mix filter` = PV installation row saving consumption mix *with* net avoided emission
- `PV installation row saving PV-weighted mix` = PV installation rows saving *has a* PV-weighted consumption mix
- `PV installation row saving PV-weighted mix label` = PV installation row saving PV-weighted mix *has a* calculator label → "vs. Swiss consumer mix, monthly"
- `PV installation row saving PV-weighted mix filter` = PV installation row saving PV-weighted mix *with* net avoided emission
- `PV installation row saving PV-weighted mix order` = PV installation row saving PV-weighted mix *is follower of* PV installation row saving consumption mix
- `PV installation row saving replacement mix` = PV installation rows saving *has a* replacement mix
- `PV installation row saving replacement mix label` = PV installation row saving replacement mix *has a* calculator label → "vs. replacement mix"
- `PV installation row saving replacement mix filter` = PV installation row saving replacement mix *with* net avoided emission
- `PV installation row saving replacement mix order` = PV installation row saving replacement mix *is follower of* PV installation row saving PV-weighted mix
- `PV installation row battery effect` = PV installation rows saving *has a* battery effect
- `PV installation row battery effect hint` = PV installation row battery effect *has a* calculator hint → "vs. same PV without battery, annual Swiss mix"
- `PV installation row battery effect filter` = PV installation row battery effect *with* consumption mix
- `PV installation row battery effect order` = PV installation row battery effect *is follower of* PV installation row saving replacement mix

**Detail tables**

- `PV installation detail tables` = PV installation *has a* calculator detail table
- `PV installation detail tables label` = PV installation detail tables *has a* calculator label → "Monthly details and sources"
- `PV installation detail monthly balance` = PV installation detail tables *has a* month
- `PV installation detail monthly balance label` = PV installation detail monthly balance *has a* calculator label → "Monthly energy balance, average year"
- `PV installation detail column PV production` = PV installation detail monthly balance *has a* PV production
- `PV installation detail column PV production label` = PV installation detail column PV production *has a* calculator label → "PV kWh"
- `PV installation detail column household consumption` = PV installation detail monthly balance *has a* household consumption
- `PV installation detail column household consumption label` = PV installation detail column household consumption *has a* calculator label → "Load kWh"
- `PV installation detail column household consumption order` = PV installation detail column household consumption *is follower of* PV installation detail column PV production
- `PV installation detail column direct use` = PV installation detail monthly balance *has a* direct use
- `PV installation detail column direct use label` = PV installation detail column direct use *has a* calculator label → "Direct"
- `PV installation detail column direct use order` = PV installation detail column direct use *is follower of* PV installation detail column household consumption
- `PV installation detail column battery discharge` = PV installation detail monthly balance *has a* battery discharge
- `PV installation detail column battery discharge label` = PV installation detail column battery discharge *has a* calculator label → "Battery"
- `PV installation detail column battery discharge order` = PV installation detail column battery discharge *is follower of* PV installation detail column direct use
- `PV installation detail column export` = PV installation detail monthly balance *has a* export
- `PV installation detail column export order` = PV installation detail column export *is follower of* PV installation detail column battery discharge
- `PV installation detail column grid import` = PV installation detail monthly balance *has a* grid import
- `PV installation detail column grid import order` = PV installation detail column grid import *is follower of* PV installation detail column export
- `PV installation detail column consumption mix` = PV installation detail monthly balance *has a* consumption mix
- `PV installation detail column consumption mix label` = PV installation detail column consumption mix *has a* calculator label → "CH mix g/kWh"
- `PV installation detail column consumption mix order` = PV installation detail column consumption mix *is follower of* PV installation detail column grid import
- `PV installation detail total` = PV installation detail monthly balance *with* annual
- `PV installation detail total label` = PV installation detail total *has a* calculator label → "Year"
- `PV installation detail total highlight` = PV installation detail total *has a* calculator highlight

## 1. The rules

1. **Components** are the parts that are the same for every calculator: the
   title, the input block and the result block. They have a `code_id` and are
   system components.
2. **Triples** are everything that is not the same for every calculator: the
   triples of the initial phrase (which inputs, which assumptions, which
   columns, which rows, which detail tables, their order and the texts that need
   the page) and the general triples of the listed phrases (rule 6).
3. **Every list and every property that the view reads has a parent triple
   with a `code_id`.** The parent selects all triples of one kind in one load,
   so the calculator editor can show e.g. all labels of a calculator at once.
   Only the order triples, the selector options and the side results have no
   keyword: they are found from their item or phrase.
   - A **text property** (label, hint) is a triple from the phrase or the item
     **to** the keyword, e.g. `battery discharge label` = battery discharge
     *has a* calculator label (see rule 6). All labels have the same `to`: one
     load by `to`.
   - A **list** (inputs, rows, …) is a group triple from the phrase to the
     keyword, e.g. `PV installation has a calculator input`. Its
     items are the triples **from** the group. All inputs of the calculator are
     the children of the group, and all inputs of every calculator are two levels
     below the keyword: one `load_related_by_name` with depth, as
     `ui_list::input_fields` already does for `process catch field (system)`.
   - A **sub-item** (a row filter, a table column, an extra table row) is a
     triple from the item. Its parent is the item. The selector options and
     the side results are not sub-items but general triples of the phrase
     (rule 6): they are found from the phrase of the item, not by a keyword.
4. **Texts are descriptions, never names.** A label or hint triple gets the
   mechanical name `<phrase> label` / `<item> label` (and `… hint`), because a
   triple from a named phrase needs its own name, and the text is its `description`. A name must be unique among all phrases, and a label like
   `orientation` or `lifetime` already is a word. A description is free text
   in any case and can be changed per user.
5. **A label is optional.** Without a label of the item or of its phrase the
   item shows the name of its phrase with the first letter in capitals. A label triple exists only if the
   page needs another text, so a label that equals the name is never added.
6. **Prefer general triples: what is true without the page goes on the
   phrase, not on the item.** The page should create and use as few specific
   triples as possible. Only the lists and their order (which inputs,
   assumptions, columns, row groups, rows, filters and detail tables), the
   title and the texts that need the page are triples from the page phrase.
   Everything that fits the phrase wherever a calculator shows it is a triple
   from the phrase, so every calculator that lists the phrase gets it without
   an own triple:
   - a **label or hint**, e.g. `battery discharge label` = battery discharge
     *has a* calculator label → "Used via battery"
   - a **flag**, e.g. `autarky highlight` = autarky *has a* calculator
     highlight: autarky is a key figure on every page
   - the **options of a selector**, e.g. `yield factor option south` =
     yield factor *has a* south: the field offers the same options on every
     page (rule 9)
   - a **side result**, e.g. `net avoided emission side result` =
     net avoided emission *has a* emission payback: the payback belongs to the
     net avoided emission on every page

   A text that only makes sense in its place stays on the item: a short column
   header ("PV kWh") or a text that names the page context ("Swiss consumer
   mix, annual" for the general phrase `consumption mix`, whose values are
   Swiss only on this pod). The text or
   flag of an item is found in this order:
   1. the triple of the item
   2. the triple of the phrase of the item; for the flag and the side result of
      a row also of its filter phrase, but never its label or hint, because a
      filter like `net avoided emission` names the group, not the row
   3. for the label of a row whose phrase is built from the group phrase: the
      label or the name of the other phrase of the triple (rule 8)
   4. the phrase name for the label, nothing for a hint or a flag

   If the same phrase needs two texts on one page, the phrase gets the text
   without context and the other place an item label, e.g. `direct use` →
   "Used directly" for the row and the detail column item → "Direct". If one
   of the two texts is the phrase name itself, the phrase gets no label (rule
   5) and the other text is an item label, e.g. the column shows "Export" by
   the name of `export` and the row item has the label "Exported to grid".
7. **Order by follower triples.** One triple adds an item to a list (`has a`),
   and a second triple sets its relative order: `<item> order` = item
   *is follower of* the item before (verb `follow`, read backwards: the item
   before *is followed by* this item). The first item of a list has no order
   triple. Items that are in no chain come after the chain, sorted by label.
   Inserting or moving an item changes only the order triples next to it, and no
   numbers are renumbered.
8. **Derived parts need no triple.** The assumptions not listed, the formula
   notes, the sources, the units, the measured/assumed tags and the sub text of
   a computed row come from the values, the results and the formulas. The
   label of a row whose phrase is built from the group phrase is the other
   phrase of the triple: below the group `embodied emission` the row
   `battery embodied emission` = embodied emission *of* battery shows
   "Battery", the name of `battery`, and `PV embodied emission` shows the
   label of `PV`, "PV system".
9. **The phrase knows its field, the calculator knows its page.** Field
   parameters that are true for every page using the phrase (type, limits,
   step, value phrases) stay on the phrase with the
   `process catch field (system)` keywords, and so do the labels, hints, flags,
   selector options and side results of rule 6. Everything that is only true for this
   page (is an input here, order, filter, sign, a label, hint or flag in its
   place) is a triple of the item.

The `has a` verb is used for all list items. The verb `with` marks a filter
phrase of a row and an extra row of a table. A triple whose `to` is a calculator
keyword is a property, and any other `to` is an item. A `has a` triple of the
phrase of a row is a side result only if a result with the page phrase and the
row phrases exists for its `to`, so `household consumption` *has a* `annual`
(the input period) never shows up as a side result.

## 2. The views

| view | code_id | type | style | components |
|---|---|---|---|---|
| `Calculator` | `calculator` | triple | `use-case` | 1 `title calculator`, 2 `calculator input`, 3 `calculation result` (`side_or_last_below`) |
| `Calculator editor` | `calculator_editor` | triple | – | see section 6 |

View relation: `{ "parent": "Calculator", "child": "Calculator editor", "type": "editor" }`,
so the edit menu of the navbar opens the editor with the same phrase.

Home: the `Calculator` view is created by `system_views.json` (`views::CALCULATOR_ID`)
and gets its three components and the view relation from the system file
`calculator_views.json` in `files::SYSTEM_DATA_APPENDED_FILES`, which also creates the
`Comparison table`, the view of the values of a phrase with the defined columns. The
`Calculator editor` is the view of `editor_views.json` with the component of section 6.

How a phrase gets this view and how the view finds its triples is described in
"How the url leads to the view" at the top. There is no extra triple that
makes a phrase a calculator: the group triples of the phrase are enough
(reduce to the max).

## 3. The components

### 3.1 In the view

| name | type | code_id | renders |
|---|---|---|---|
| `title calculator` | `calculator_title` | `title_calculator` | the calculator label of the phrase as `h4` (fallback: the phrase name) and its hint as the lead paragraph |
| `calculator input` | `calculator_input` | `calculator_input` | the heading (label of the input group or `msg_id` "Inputs"), one input field per item, the save button ("Save"), then a `details` block (label of the assumption group or "Assumptions") with one assumption field per item and then the derived assumptions |
| `calculation result` | `calculation_result` | `calculation_result` | the heading (label of the column group) and the main table, then a `details` block (label of the detail table group) with the detail tables, the formula notes ("How the CO₂ saving is counted", …) and the sources ("Sources" `msg_id`) |

### 3.2 Built at runtime, not linked in the view

The view components create one sub-component per item. Each one gets the
context phrases and reads its parameters from the triples and values:

| sub-component | context | parameters from | reuses |
|---|---|---|---|
| input field | page phrase + input phrase, e.g. `example PV installation` + `PV capacity` | field type, limits, step, value phrases: the field triples and values of the input phrase; label, hint: the calculator triples of the item or of the input phrase (rule 6); options: the `has a` triples of the input phrase (rule 6), else the phrases that *is a* the input phrase, each with its number as the value, and selected the option that the page phrase *uses*; shown number: the url, else the value of the input phrase with the field value phrases, the one with the page phrase first | `ui_list::calculator_fields` with the field helpers of `ui_list::input_fields` |
| assumption field | page phrase + operand phrase | label, hint: the calculator triples of the item or of the operand phrase, else the description of the value as the hint; number, unit, measured/assumed, confidence: the value; the values that the results use and the calculator does not list follow as derived assumptions | `ui_list::calculator_fields` with the tag of `ui_list::assumption_fields`, which also shows the derived assumptions |
| main table | page phrase + column phrases + per row: row phrase, `with` phrases | columns, row groups, rows, labels, hints, side results, highlight, sign; a cell is the result or value with the page phrase, the row phrase, the filter phrase and the column phrase, checked by the calculation of the page | `ui_list::calculation_table` |
| detail table | page phrase + table phrase | columns (`has a`) with an optional filter of a column (`with`, e.g. the unit gram per kWh, which a cell must have too), rows (the phrases that *is a* the table phrase, in their order), extra rows (`with`), labels | `ui_list::calculation_detail_tables` |
| formula notes | page phrase | the descriptions of the formulas of the results | `ui_list::formula_notes` |
| sources | page phrase | the sources of the values of the number fields of the inputs and the assumptions and of the results, numbered in this order, so that a hint names the same [n]; a selector names no source | `ui_list::calculator_sources` |

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
| `calculator highlight` | calculator (system) | has a | highlight | `system_calculator_highlight` | flag of an item or of a phrase: the row is shown as a key row (`class="key"`) |
| `calculator signed` | calculator (system) | has a | sign | `system_calculator_signed` | flag of a row group: the cells show the sign as `pos` / `neg` |

New words: `calculator`, `hint`, `highlight`, `sign`. All other words are already defined
elsewhere and are repeated by name only.

Already there and reused unchanged (`process_catch_fields.json` and the use
case): `field PV capacity` … `number with limits`, `field limit down`,
`field step`, `field value phrases`, `phrase selector`,
`field phrase selector value based`, `field replacement mix`.

The order needs no keyword: it uses the existing verb `is follower of`
(`follow`), whose description in `verbs.json` names the time order and the
order of the items of a list.

Open: `field label` (`system_field_label`) of the process catch and
`calculator label` overlap. Later both could become one `label (system)`.

## 5. The use case triples of the PV page (no `code_id`)

Home: `pv_lifetime_balance.json`, for the triples of the page and for the
general triples of the phrases (rule 6). Columns: **item** = the named triple, **to** = its phrase, **pos** =
the place in the list that the order triples give (`<item> order` = item
*is follower of* the item one line above), **label / hint** = the description of the
`<item> label` / `<item> hint` triple, or with *(phrase)* of the `<phrase> label` /
`<phrase> hint` triple (rule 6) (– = no triple, the phrase name is shown).

### 5.1 The title

The page title and the lead text are the label and the hint of the phrase
itself. They need no group and no item triple:

| item | from | verb | to | description |
|---|---|---|---|---|
| `PV installation label` | PV installation | has a | calculator label | PV lifetime CO₂ balance |
| `PV installation hint` | PV installation | has a | calculator hint | Rooftop photovoltaic installation in the Swiss plateau: … all assumptions are open below them. |

### 5.2 Inputs, group `PV installation inputs` = `PV installation` has a `calculator input`

| pos | item | to | label | hint |
|---|---|---|---|---|
| 1 | `PV installation input PV capacity` | PV capacity | System size *(phrase)* | – |
| 2 | `PV installation input yield factor` | yield factor | Orientation *(phrase)* | Yield factor relative to south 30° *(phrase)* |
| 3 | `PV installation input battery capacity` | battery capacity | Battery capacity (usable) *(phrase)* | – |
| 4 | `PV installation input household consumption` | household consumption | Local electricity consumption *(phrase)* | – |

The options of the orientation selector are general triples of the field
phrase `yield factor` (`has a`, rule 6), so every page with this selector
offers the same options:

| pos | option | to | label |
|---|---|---|---|
| 1 | `yield factor option south` | south | South, ~30° tilt |
| 2 | `yield factor option south-east` | south-east | South-east / south-west, ~30° |
| 3 | `yield factor option east-west` | east-west | East-west split, flat tilt |
| 4 | `yield factor option east` | east | East or west only, ~30° |
| 5 | `yield factor option horizontal` | horizontal | Flat (0°) |
| 6 | `yield factor option north` | north | North, ~30° |

Without option triples the selector offers all phrases of the yield factor
values (`yield factor options`), sorted by name. The option labels stay on the
options: the tilt "~30°" is true for the yield factor, not for the direction
`south` everywhere.

Field parameters, unchanged on the phrase: `field PV capacity`,
`field battery capacity` and `field household consumption` (`can be number
with limits`) with their `field limit down` / `field step` values,
`household consumption field value phrase` (annual), and `field yield factor`
with `yield factor options`. The unit (kWp, kWh, kWh/a) is the symbol of the
unit phrase of the shown value, else its name; the percent word is named,
because its "%" would misread the stored ratio, while a qualified percent unit
(`share (ratio)`) shows its symbol "share".

### 5.3 Assumptions, group `PV installation assumptions` = `PV installation` has a `calculator assumption`

| pos | item | to | label |
|---|---|---|---|
| 1 | `PV installation assumption PV yield` | PV yield | Specific yield, south 30° |
| 2 | `PV installation assumption lifetime` | lifetime | – |
| 3 | `PV installation assumption PV in Switzerland` | PV in Switzerland | PV life-cycle emissions |
| 4 | `PV installation assumption consumption mix` | consumption mix | Swiss consumer mix, annual |
| 5 | `PV installation assumption replacement mix` | replacement mix | – (a selector by `field replacement mix`) |
| 6 | `PV installation assumption day share` | day share | Load share during PV hours *(phrase)* |
| 7 | `PV installation assumption round-trip efficiency` | round-trip efficiency | Battery round-trip efficiency |
| 8 | `PV installation assumption battery production emission` | battery production emission | Battery production emissions *(phrase)* |
| 9 | `PV installation assumption battery lifetime` | battery lifetime | – |

On the items stay: "Specific yield, south 30°" (south 30° is true for the
value of this page, not for `PV yield`), "Swiss consumer mix, annual"
(`consumption mix` is the mix of any country) and "Battery round-trip
efficiency" (`round-trip efficiency` is also used for other storages).

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
| 2 | `PV installation column annual` | annual | Per year *(phrase)* |

### 5.5 Main table rows, group `PV installation row groups` = `PV installation` has a `calculator row group`

The phrase of a row group only names the group. It doesn't filter the rows.

| pos | row group item | to | label |
|---|---|---|---|
| 1 | `PV installation rows electricity` | electricity | – |
| 2 | `PV installation rows production emissions` | embodied emission | Production emissions *(phrase)* |
| 3 | `PV installation rows saving` | net avoided emission | CO₂-eq saved, net of production emissions *(phrase)* |

Rows (`<row group> has a <phrase>`). Sub-item: `with` = filter phrase of the
row. **side result** = a phrase that the row phrase or the filter phrase
*has a* (general triple, rule 6), shown in the sub text after the hint.
**key** = the row has a highlight, (phrase) = by the highlight of the row
phrase or the filter phrase.

| group | pos | row item | to | with | side result | key | label | hint |
|---|---|---|---|---|---|---|---|---|
| electricity | 1 | `PV installation row PV production` | PV production | – | – | yes *(phrase)* | – | – (the sub text "10.0 kWp × 976 kWh/kWp × factor 0.85" is the formula with its operands) |
| electricity | 2 | `PV installation row direct use` | direct use | – | – | – | Used directly *(phrase)* | – |
| electricity | 3 | `PV installation row battery discharge` | battery discharge | – | – | – | Used via battery *(phrase)* | after losses *(phrase)* |
| electricity | 4 | `PV installation row export` | export | – | – | – | Exported to grid | – |
| electricity | 5 | `PV installation row battery losses` | battery losses | – | – | – | – | – |
| electricity | 6 | `PV installation row grid import` | grid import | – | – | – | Grid import still needed | – |
| electricity | 7 | `PV installation row self-consumption share` | self-consumption share | – | – | – | Self-consumption rate *(phrase)* | share of PV used on site *(phrase)* |
| electricity | 8 | `PV installation row autarky` | autarky | – | – | yes *(phrase)* | – | share of load covered by PV *(phrase)* |
| production emissions | 1 | `PV installation row PV embodied emission` | PV embodied emission | – | PV site intensity *(phrase)* | – | PV system *(phrase `PV`, rule 8)* | – |
| production emissions | 2 | `PV installation row battery embodied emission` | battery embodied emission | – | – | – | – (the name of `battery`, rule 8) | – |
| saving | 1 | `PV installation row saving consumption mix` | consumption mix | net avoided emission | emission payback *(filter phrase)* | yes *(filter phrase)* | vs. Swiss consumer mix, annual | – |
| saving | 2 | `PV installation row saving PV-weighted mix` | PV-weighted consumption mix | net avoided emission | emission payback *(filter phrase)* | yes *(filter phrase)* | vs. Swiss consumer mix, monthly | – |
| saving | 3 | `PV installation row saving replacement mix` | replacement mix | net avoided emission | emission payback *(filter phrase)* | yes *(filter phrase)* | vs. replacement mix | – |
| saving | 4 | `PV installation row battery effect` | battery effect | consumption mix | – | – | Effect of the battery *(phrase)* | vs. same PV without battery, annual Swiss mix |

The two side result triples and the three highlight triples are general: the
payback and the key flag belong to `net avoided emission` on every page, the
site intensity to `PV embodied emission`. The side result `emission payback`
is shown with the label of its phrase, "payback" *(phrase)*.

The rows of the production emissions group are built from the group phrase
(`embodied emission` *of* PV / battery), so their labels are derived from the
other phrase (rule 8): "PV system" is the general label of `PV`, "Battery"
the name of `battery`.

On the items stay the labels that need the page: "Grid import still needed"
(true only after a PV self-supply), the three "vs. …" labels (the mix of this
pod's values, like "CH mix g/kWh") and the hint of the battery effect, which
names the filter of the row. "Exported
to grid" stays on the item too, because the phrase name "Export" is already
the general text and is used by the detail column.

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
| 5 | `PV installation detail column export` | has a | export | – (the phrase name) |
| 6 | `PV installation detail column grid import` | has a | grid import | – (the phrase name) |
| 7 | `PV installation detail column consumption mix` | has a | consumption mix | CH mix g/kWh |
| – | `PV installation detail total` | with | annual | Year |

The column headers are short texts for this table, so they stay on the items
and override the labels of the phrases, e.g. "Direct" instead of "Used
directly".

### 5.7 Count

Triples of the page (from `PV installation`): 5 group triples, 4 inputs, 9
assumptions, 2 columns, 3 row groups + 14 rows + 4 filters, 1 detail table +
8 sub-items, 18 label, 2 hint, 1 highlight and 1 signed triple, and 31 order
triples: 103.

General triples of the phrases, used by every calculator with these phrases:
16 label, 4 hint, 3 highlight and 2 side result triples, and the 6 options of
the yield factor with 6 labels and 5 order triples: 42.

That is 145 new triples with the 10 keywords: 155. The number is the price
for a page that the user can change without a developer, and the general
part is paid only once for all calculators.

## 6. The calculator editor

The editor follows the same split: its component is the same for every
calculator, and its content is the triples of section 5 for the phrase.

The view has one component, `calculator editor` (`calculator_editor`), which embeds
the answer of `/api/phraseData/?id=<phrase>&pw=pwf&is=1` for the page phrase as the
seed of the script `calculator_editor.js` (see `docs/llm/frontend.md`). The script
shows the parts of the editor in three columns:

| part | shows |
|---|---|
| phrase bar | the page phrase; another phrase is read with one phraseData call |
| page structure | one section per keyword below `calculator (system)`, each with the items of this calculator in their order, with up, down and remove links and an add link per list; sub-items are indented below their item; below a result row the formulas that calculate its number: every formula whose result is the row phrase (a number may have more than one, e.g. one per mix) and then the formulas of their operands, each once |
| settings | the phrase, label and hint of the selected item, with the general label or hint of the phrase as placeholder, and the general triples and values of its phrase: the field triples of the input and assumption phrases (type, limits, step, value phrases, selector options) and the highlights and side results of the row phrases (rule 6), with a note that a change here changes every page that uses the phrase; for a selected formula the formula editor (`formula_editor.js`) with the expression as selectable tokens |
| page preview | the page as the `Calculator` view shows it, with the derived notes and sources read only; for a selected formula the results until now against the results after the save of the formula editor |
| all texts | all label and hint triples of this calculator in one table for proofreading and translating; a label of a phrase is marked, because a change of it changes every calculator that lists the phrase |
| changes | the triples, formulas and values that the save writes |

The save sends all changes in one update put message to `/api/updatePut`:
the new words, the added and changed triples and values with their database
ids, the changed formulas by their id with the new expression, and the class
and id of each removed triple and value.
Each change is a change of the user's own sandbox triple, formula or value. The
calculator of other users stays unchanged until they take over the change.
A removed item is deleted through the logged `del()`, which excludes it only
for the user if another user uses it.

## 7. Implementation steps

1. `calculator_keywords.json` with the keyword triples of section 4, the const
   pairs in `shared/const/triples.php` and appended to
   `files::SYSTEM_DATA_APPENDED_FILES`.
2. The component types `calculator_title`, `calculator_input`,
   `calculation_result` in `component_types.php`, appended at the end (seed ids,
   see `docs/llm/json_views.md` "append, then pin"), and the dispatch in
   `component_exe::dsp_entries`.
3. A `calculator` reader in the frontend: from the page phrase, or the phrase
   it is built from, to the groups, items, sub-items, labels and hints (of
   the items and of their phrases) and order triples from the request cache (`data_object`), sorted by the
   follower chain.
3a. The view selection by the phrase: `phrase::name_link` uses the default
   view of the phrase if it is a phrase view, and `frontend::phrase_view_id`
   takes it for a url with `id` and without `m`.
   `example PV installation` gets `"view": "Calculator"` in the use case json.
4. The three renderers, built from the existing `ui_list` functions of
   section 3.2. The fine-grained component types stay for other views.
5. The use case triples of section 5 in
   `pv_lifetime_balance.json`, which then has no components and no view of
   its own. The `Calculator` view fills the request cache before the title
   and the components are created (`view_exe::add_page_cache`): the tree of
   the page phrase and of the phrase it is built from (the triples *from* a
   phrase, four levels, one api call per level), then the general triples of
   the phrases that the reader lists (`calculator::phrases`, two levels incl.
   the phrase a triple points to, for the option labels and the side result
   labels), and last the triples *to* the input, assumption and detail table
   phrases for the options and rows that *is a* the phrase.
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
- The notes of the target have their own titles ("How the CO₂ saving is
  counted.") and texts that span several formulas. Formula descriptions do not
  give that. Options are a `calculator note` list or notes as descriptions of
  the row groups.
- The detail column header "CH mix g/kWh" is an item label because it joins
  the country and the unit of the values with the phrase. If the detail table
  header is composed by the renderer as label + unit of the column values,
  "PV", "Load" and "CH mix" are enough as labels, but "Direct", "Battery",
  "Export" and "Grid import" get a unit too and the mock up changes.
- The detail table shows "Jan" for `January`. That needs a short name of the
  phrase, e.g. a `calculator label` per month or a symbol triple.
- The rows of the monthly table are the phrases that *is a* `month`, but no
  json has the triples `January` *is a* `month` … `December` *is a* `month`
  with their order yet, so the table shows only the year row.
- The sources show a long citation as the link text, but the json has short
  source names. Should the citation be the description of the source?
- The page title takes the calculator label as it is, "PV lifetime CO₂
  balance", but the mock up title writes "CO2". Either the mock up title gets
  the subscript or the comparison of the page head ignores it.
- The sub text of a computed row is its hint, else its side results with their
  number. The formula with its operands as in the mock up ("10.0 kWp × 976
  kWh/kWp × factor 0.85") needs the formula text with the operand numbers.
- A number is shown in the unit of its value e.g. kg. The mock up shows the
  lifetime column in t and the annual column in kg, which needs a unit conversion.
- The sources are numbered in the order of the number fields of the inputs, the
  assumptions and the other values that the results use; a selector names no
  source, so the orientation table is numbered by its use in the results. The
  mock up numbers them in another order.
- An option of a selector shows its label only and has its number as the value,
  a share with the percent decimals of the user (`1.00` for south). The mock up
  adds the number, the unit and the source to the text of a replacement mix
  option ("European residual mix — 523 g/kWh [4]").
- The html ids in the overview (`kwp`, `orient`, …) are those of the sample.
  The renderer uses the url var as `name` and the label as `id`
  (`docs/llm/frontend.md` "Form field `name` is the url var").
