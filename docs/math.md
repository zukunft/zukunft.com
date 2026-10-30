# Math functions

do not edit manually: this file is written by `php bin/math_functions.php` and checked by the test run

A formula calculates in up to five places. The backend calculates the result that is saved, the
others repeat the calculation on the page, so a difference between them shows a user a number
that the saved result does not confirm.

- functions missing in one of the calculations: 4
- edge cases with a different result: 15
- functions used in the data that no calculation has: 24

## Functions

| function | what it does | example | result | missing in |
| --- | --- | --- | ---: | --- |
| `+` | addition | `2 + 3` | 5 |  |
| `-` | subtraction, also the sign of a number | `10 - 3 - 2` | 5 |  |
| `*` | multiplication, before an addition | `1 + 2 * 3` | 7 |  |
| `/` | division, a division by zero gives no number | `100 / 5 / 2` | 10 |  |
| `( )` | brackets are calculated first | `( 1 + 2 ) * 3` | 9 |  |
| `min` | the smallest of its arguments, separated by `;` | `min( 3 ; 1 + 1 ; 5 )` | 2 |  |
| `max` | the largest of its arguments, separated by `;` | `max( 3 ; 1 + 1 ; 5 )` | 5 |  |
| `if` | the then or the else part, only the chosen part is calculated | `if( 1 = 2 ; 3 ; 4 )` | 4 | arithmetic, calculator, calculator_json, formula_editor |
| `=` | a condition of `if`: true if both sides are the same | `if( 2 = 2 ; 3 ; 4 )` | 3 | arithmetic, calculator, calculator_json, formula_editor |
| `&` | a condition of `if`: true if both conditions are true | `if( 1 & 0 ; 3 ; 4 )` | 4 | arithmetic, calculator, calculator_json, formula_editor |
| `\|` | a condition of `if`: true if one condition is true | `if( 1 \| 0 ; 3 ; 4 )` | 3 | arithmetic, calculator, calculator_json, formula_editor |

## Missing in a calculation

A function that one calculation does not have gives no number there, so a page keeps the stored result.

- `if`: arithmetic, calculator, calculator_json, formula_editor
- `=`: arithmetic, calculator, calculator_json, formula_editor
- `&`: arithmetic, calculator, calculator_json, formula_editor
- `\|`: arithmetic, calculator, calculator_json, formula_editor

## Used in the data but not calculated

A formula may name such a function e.g. to document a law; calculating it shows the user an error.

| function | formulas | example |
| --- | ---: | --- |
| `C` | 1 | `("a" + "b")^"n" = sum(C("n","k") * "a"^("n"-"k") * "b"^"k", "k"=0.."n")` |
| `IRF` | 1 | `IRF(t) = a0 + a1 × exp(−t ÷ tau1) + a2 × exp(−t ÷ tau2) + a3 × exp(−t ÷ tau3)` |
| `^` | 56 | `"sigma of f" = abs("f" / ("x" + "y")) * sqrt("y"^2 / "x"^2 * "variance of x" + "variance o …` |
| `abs` | 10 | `"sigma of f" = abs("f" / ("x" + "y")) * sqrt("y"^2 / "x"^2 * "variance of x" + "variance o …` |
| `cos` | 2 | `"sigma of f" = abs("a" * "b" * cos("b" * "x") * "sigma of x")` |
| `count` | 1 | `"average impact weighted reach" = sum( "impact weighted reach" ) / count( "entity" )` |
| `d` | 8 | `d/d"x" ("x"^"n") = "n" * "x"^("n" - 1)` |
| `exp` | 4 | `"saturation vapor pressure" = 6.112 * exp(17.67 * "temperature in celsius" / ( "temperatur …` |
| `integral` | 8 | `integral("x"^"n", "x") = "x"^("n" + 1) / ("n" + 1) + "C"` |
| `lim` | 1 | `"f"'("x") = lim(("f"("x" + "h") - "f"("x")) / "h", "h"->0)` |
| `ln` | 6 | `"sigma of f" = abs("a" * "sigma of x" / ("x" * ln(10)))` |
| `log` | 9 | `"pH" = - log("hydrogen ion concentration")` |
| `log_a` | 1 | `log_b("x") = log_a("x") / log_a("b")` |
| `log_b` | 1 | `log_b("x") = log_a("x") / log_a("b")` |
| `mean` | 1 | `"Pearson correlation coefficient" = sum((x - mean(x)) * (y - mean(y))) / sqrt(sum((x - mea …` |
| `round` | 1 | `"rounded shares" = round( "target shares" / "round lot" ) * "round lot"` |
| `sec` | 1 | `"sigma of f" = abs("a" * "b" * sec("b" * "x")^2 * "sigma of x")` |
| `sin` | 6 | `"sigma of f" = abs("a" * "b" * sin("b" * "x") * "sigma of x")` |
| `sqrt` | 17 | `"sigma of f" = sqrt("variance of f")` |
| `sum` | 8 | `"variance of f" = sum((d"f"/d"x"_"i")^2 * "variance of x"_"i", "i"=1.."n")` |
| `×` | 13 | `"specific emissions" = ( "direct emissions" + "indirect emissions" + Σ ( "mass fraction" × …` |
| `÷` | 9 | `years = 2800 × 1000 ÷ 1000 ÷ 123.6` |
| `Σ` | 1 | `"specific emissions" = ( "direct emissions" + "indirect emissions" + Σ ( "mass fraction" × …` |
| `−` | 3 | `IRF(t) = a0 + a1 × exp(−t ÷ tau1) + a2 × exp(−t ÷ tau2) + a3 × exp(−t ÷ tau3)` |

## Edge cases

Each text is calculated by each calculation; a difference is marked with **diff**.

- backend: backend (`calc_internal.php`, the result that is saved)
- arithmetic: page check (`arithmetic.php`, compares a stored result on the server page)
- calculator: calculator page (`calculator.js`)
- calculator_json: json calculator page (`calculator_json.js`)
- formula_editor: formula editor (`formula_editor.js`)

| text | backend | arithmetic | calculator | calculator_json | formula_editor | |
| --- | ---: | ---: | ---: | ---: | ---: | --- |
| `1 / 0` | no number | no number | no number | no number | no number |  |
| `0 / 0` | no number | no number | no number | no number | no number |  |
| `5 / ( 2 - 2 )` | no number | no number | no number | no number | no number |  |
| `0 / 5` | 0 | 0 | 0 | 0 | 0 |  |
| `-2 * 3` | -6 | -6 | -6 | -6 | -6 |  |
| `2 - -3` | 5 | 5 | 5 | 5 | 5 |  |
| `2 + -3` | -1 | -1 | -1 | -1 | -1 |  |
| `2 * -3` | -6 | -6 | -6 | -6 | -6 |  |
| `6 / -2` | -3 | -3 | -3 | -3 | -3 |  |
| `--3` | no number | 3 | 3 | 3 | 3 | **diff** |
| `+3` | 3 | 3 | 3 | 3 | 3 |  |
| `-( 1 + 2 )` | -3 | -3 | -3 | -3 | -3 |  |
| `2 * 3 + 4` | 10 | 10 | 10 | 10 | 10 |  |
| `10 - 2 * 3` | 4 | 4 | 4 | 4 | 4 |  |
| `2 / 4 * 2` | 1 | 1 | 1 | 1 | 1 |  |
| `5E3` | 5000 | no number | no number | no number | no number | **diff** |
| `1.5E-5 * 2` | 3e-5 | no number | no number | no number | no number | **diff** |
| `1e3` | 1000 | no number | no number | no number | no number | **diff** |
| `.5` | 0.5 | no number | no number | no number | no number | **diff** |
| `2.` | 2 | no number | no number | no number | no number | **diff** |
| `1000000000000000000000 * 10` | 1e+22 | 1e+22 | 1e+22 | 1e+22 | 1e+22 |  |
| `0.1 + 0.2` | 0.3 | 0.30000000000000004 | 0.30000000000000004 | 0.30000000000000004 | 0.30000000000000004 | **diff** |
| `( 1 / 3 ) * 3` | 0.99999999999999 | 1 | 1 | 1 | 1 | **diff** |
| `( 0.1 + 0.2 ) * 10` | 3 | 3.0000000000000004 | 3.0000000000000004 | 3.0000000000000004 | 3.0000000000000004 | **diff** |
| (empty) | no number | no number | no number | no number | no number |  |
| (blank) | no number | no number | no number | no number | no number |  |
| `2 +` | no number | no number | no number | no number | no number |  |
| `* 2` | no number | no number | no number | no number | no number |  |
| `2 3` | no number | no number | no number | no number | no number |  |
| `2 x 3` | no number | no number | no number | no number | no number |  |
| `( 2 + 3` | no number | no number | no number | no number | no number |  |
| `2 + 3 )` | no number | no number | no number | no number | no number |  |
| `( )` | no number | no number | no number | no number | no number |  |
| `1 ; 2` | no number | no number | no number | no number | no number |  |
| `abc + 1` | no number | no number | no number | no number | no number |  |
| `min( 7 )` | 7 | 7 | 7 | 7 | 7 |  |
| `min( )` | no number | no number | no number | no number | no number |  |
| `min( 1 ; 2` | no number | no number | no number | no number | no number |  |
| `min 1` | no number | no number | no number | no number | no number |  |
| `min( ; 1 )` | no number | no number | no number | no number | no number |  |
| `min( 1 ; )` | no number | no number | no number | no number | no number |  |
| `MIN( 1 ; 2 )` | no number | no number | no number | no number | no number |  |
| `min( 1 ; abc )` | no number | no number | no number | no number | no number |  |
| `max( 7 )` | 7 | 7 | 7 | 7 | 7 |  |
| `max( )` | no number | no number | no number | no number | no number |  |
| `max( 1 ; 2` | no number | no number | no number | no number | no number |  |
| `max 1` | no number | no number | no number | no number | no number |  |
| `max( ; 1 )` | no number | no number | no number | no number | no number |  |
| `max( 1 ; )` | no number | no number | no number | no number | no number |  |
| `max( -5 ; -2 )` | -2 | -2 | -2 | -2 | -2 |  |
| `min( max( 1 ; 2 ) ; 3 ) * 2` | 4 | 4 | 4 | 4 | 4 |  |
| `( min( 1 ; 2 ) ) * 3` | 3 | 3 | 3 | 3 | 3 |  |
| `min ( 1 ; 2 )` | 1 | 1 | 1 | 1 | 1 |  |
| `min( 1 ; 2 ) min( 3 ; 4 )` | no number | no number | no number | no number | no number |  |
| `if( 1 ) 5` | 5 | no number | no number | no number | no number | **diff** |
| `if( 0 ) 5` | no number | no number | no number | no number | no number |  |
| `if( 1 ; 2 )` | no number | no number | no number | no number | no number |  |
| `if( 1 ; 2 ; 3 ; 4 )` | no number | no number | no number | no number | no number |  |
| `if( 1 ; 2 ; 5 / 0 )` | 2 | no number | no number | no number | no number | **diff** |
| `if( abc ; 1 ; 2 )` | no number | no number | no number | no number | no number |  |
| `sqrt( 4 )` | no number | no number | no number | no number | no number |  |
| `10 - sqrt( 4 )` | no number | no number | no number | no number | no number |  |
| `round( 2.6 ) * 3` | no number | no number | no number | no number | no number |  |
| `2 ^ 3` | no number | no number | no number | no number | no number |  |
| `5 %` | no number | no number | no number | no number | no number |  |
