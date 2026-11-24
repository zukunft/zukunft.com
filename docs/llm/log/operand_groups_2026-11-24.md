# operand groups (2026-11-24)

Part of #247, part 1 of step 9 of the portfolio split page in `docs/llm/pending.md`: the previous row in the backend.

## Request

"start the next open step of step 9"

Part 1 asks for a database test of the previous row in the backend (`element_group::figures`): a first row, a row
in the middle and a negative test with two chains in the context. The preparation found that the backend cannot read
any row of the walk, because the walk formulas name an operand by several phrases with a comma, e.g.
`"cumulative deviation","prior"`, and their operands are triples:

1. `expression::element_lst_all` closes an element group at any text between two symbols, so `{t4451},{f45}` gives
   two groups and `element_group::reads_prior_row` is never true;
2. `element_group::build_symbol` joins the symbols of a group with a space (`{t4451} {f45}`), which is not the text
   of the expression, so the number would not replace the symbol;
3. `element_group::figures` selects one number per element and adds the phrase of a word only if the element has an
   id, which an element of an expression never has, and never the phrase of a triple, so `"unit value"` selects the
   number of the context only;
4. `figure_list::add` drops the number 0 of the row before the first row, because a value that is not stored has the
   group id 0 and `sandbox_list::add_obj` skips an object without an id.

The developer decided on 2026-11-24 to fix all of them with tests ("Fix all three + tests"; the fourth was found
afterwards and is part of the second change, because it is the same selection of the previous row).

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/cfg/formula/expression.php` (`element_lst_all`), `src/main/php/cfg/element/element_group.php` (`build_symbol`, new `set_join`) | a group ends at any text between two symbols; the symbols of a group are joined by a space | a comma or nothing between two symbols joins them to one group, and the group keeps the text between them for its symbol, e.g. `{t4451},{f45}`; tests in `unit/expression_tests.php` with the global HTP formula and a negative test with an operator | an operand with several phrases names one number, as in the import check (`data_object::operand_groups`) and on the page |
| `src/main/php/cfg/element/element_group.php` (`figures`), `src/main/php/cfg/formula/figure_list.php` (`add`) | one number per element, with the word only if the element has an id and never a triple; a figure without id is dropped | one number per group, selected by the context and the words and triples of the group; a figure without id but with a name is kept | the backend selects the number of an operand like `"unit value"` of a row and the 0 before the first row |
| new `src/test/php/unit_read/element_group_read_tests.php`, its registration in `test_base.php`, `all_unit_read_tests.php` and `a_selected_test.php`, consts in `word_names`, `triple_names` and `formula_names`; `docs/llm/pending.md` part 1 | no database test of the previous row | with the stored rows of `portfolio_split_calculator.json`: the operand `"cumulative deviation","prior"` is one group whose symbol is part of the formula text, LISN reads the deviation of LISP (58500), LISP as the first row reads 0, two chains in the context (LISN and stock) give no number, and the operands `"split count"` and `"unit value"` select the stored numbers of their row | part 1 of step 9 |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the backend results of the formulas with an operand of several phrases joined by a comma or an operand that is a triple, e.g. the global HTP formula of `solution_prio.json` and the walk formulas | — | — | such an operand selected the number of the context only or no number; the stored results of the imports do not change, because the import check has its own selection |
| the explanation of a result (`result::element_group_parts`) and its snapshots | — | — | an element group of several phrases is one line with one number instead of one line per phrase |
| the time of a backend calculation | — | — | unchanged: one number is loaded per group instead of per element |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|

## Lessons learned by llm (2026-11-24)
