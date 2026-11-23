# npp assumptions (2026-11-23)

Part of #247. Started before the first edit.

## Request

From "## Calculator editor" / "### reduce complexity" of `docs/llm/pending.md`, for the calculator of the Swiss vote
on lifting the ban on new nuclear power plants (`src/main/resources/messages/use_cases/npp_ch_vote.json`; the
request names it "ch_new_nnp"): move 'New nuclear capacity', 'Cost of the renewable alternative', 'System cost of
intermittent supply' and 'Value of secured winter supply' to 'Assumptions', so that they are initially not shown, but
keep the existing functionality.

The four fields are items of the input group `new NPP inputs`; the assumption group `new NPP assumptions` is rendered
collapsed (`system_page::calculator_input` with `html_base::details`, `calculator_json.js` with the same block). Both
renderers handle an assumption with its label, hint, own tag and slider like an input (`ui_list::calculator_fields`,
`calculator_json.js::numberField`), and the presets, the sources and the field plan read the inputs and the
assumptions together, so the move needs no code change.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/npp_ch_vote.json` | the "has a" triples `new NPP input capacity`, `new NPP input alternative cost`, `new NPP input system cost`, `new NPP input security value` from `new NPP inputs` | the same triples from `new NPP assumptions` | the four fields are shown in the collapsed assumptions |
| `src/main/resources/messages/use_cases/npp_ch_vote.json` | `new NPP input capacity order` is follower of `new NPP input construction time` | is follower of `new NPP assumption threshold` | the four keep their order and follow the last assumption, without an added or removed object that would shift the ids of the later imports |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/web/html/use_cases/npp_ch_vote*.html` and the diff reports | — | — | the generated pages show the four fields in the assumptions; written by the run, never by the LLM |
| `src/test/resources/web/html/use_cases/npp_ch_vote_sample.html` | — | — | the hand made sample still shows the four in "Your assumptions", so the compare report names them until the developer updates the sample |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|
