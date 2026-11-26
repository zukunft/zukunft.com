# npp triple order (2026-11-23)

Part of #247. A fix of the change of `docs/llm/log/2026-11-23_npp_assumptions.md`, in its own log as the developer
has asked.

## Request

Fix the import error of the test run:

> import of .../use_cases/npp_ch_vote.json failed due to triple phrase from id is 0; ... the triple
> ""new NPP assumptions" "has a" "capacity" (0,23,1153 ...)" is incomplete, id of from ... is missing, ... the triple
> ""new NPP input capacity" "is follower of" "new NPP assumption threshold" (0,33,0 ...)" is incomplete ... and 70 more

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/npp_ch_vote.json` | the four input blocks (`new NPP input capacity`, `alternative cost`, `system cost`, `security value`, each with its label, hint, slider and order) in front of `new NPP assumptions` (lines 1442-1569) | the same four blocks unchanged behind `new NPP assumption threshold order` | each triple stands below the triples it names, so that the import resolves its from and to |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the database ids of the triples of `npp_ch_vote.json` from the old to the new place of the blocks | — | — | the import gives a triple the id of its position; no object is added or removed, so the triples of the other files keep their ids, and a use case triple is selected by its name (`docs/llm/coding.md`) |
| `src/test/resources/web/html/use_cases/npp_ch_vote*.html`, the phrase data fixtures | — | — | the ids of the moved triples in the embedded json change; written by the run |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/npp_ch_vote.json` | the move to the assumptions (`2026-11-23_npp_assumptions.md`) has changed the from of the four "has a" triples to `new NPP assumptions` (defined at line 1571) and the to of `new NPP input capacity order` to `new NPP assumption threshold` (line 1706), both below the changed triples at 1442-1569 | the four blocks moved behind the last assumption; a read-only scan finds no forward reference to a triple of the file any more, and the triple count stays 502 as in the commit | a request of 2026-11-23: the import resolves the from and the to of a triple by the phrases it knows at that point of the file (`docs/llm/json_structure.md` "A building block is defined before the triple that uses it"), so a forward reference to a triple is mapped as an unknown word with id 0 and the whole file fails; I have changed the from and the to without checking the file order, and the order check of the groups I ran then only checked the follower chains, not the position in the file |

## Lessons learned by llm

- A change of the from or the to of a triple in an import file is also a change of the file order: the named triple
  must stand above. Check it with a scan for forward references, not only with the logic of the change.
