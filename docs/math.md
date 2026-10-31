# Math functions

do not edit manually: this file is written by `php bin/math_functions.php` and checked by the test run

A formula calculates in up to five places. The backend calculates the result that is saved, the
others repeat the calculation on the page, so a difference between them shows a user a number
that the saved result does not confirm.

- functions missing in one of the calculations: 0
- edge cases with a different result: 10
- functions used in the data that no calculation has: 5

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
| `if` | the then or the else part, only the chosen part is calculated | `if( 1 = 2 ; 3 ; 4 )` | 4 |  |
| `=` | a condition of `if`: true if both sides are the same | `if( 2 = 2 ; 3 ; 4 )` | 3 |  |
| `&` | a condition of `if`: true if both conditions are true | `if( 1 & 0 ; 3 ; 4 )` | 4 |  |
| `\|` | a condition of `if`: true if one condition is true | `if( 1 \| 0 ; 3 ; 4 )` | 3 |  |
| `^` | power, before a multiplication and from right to left | `2 * 3 ^ 2` | 18 |  |
| `abs` | the number without its sign | `abs( -2.5 )` | 2.5 |  |
| `cos` | cosine of an angle in radian | `cos( 0 )` | 1 |  |
| `exp` | e to the power of the number | `exp( 0 )` | 1 |  |
| `ln` | natural logarithm | `ln( 1 )` | 0 |  |
| `log` | logarithm to the base 10 | `log( 100 )` | 2 |  |
| `log( ; )` | logarithm to the base of the second argument | `log( 8 ; 2 )` | 3 |  |
| `round` | the nearest whole number | `round( 2.6 )` | 3 |  |
| `sec` | secant of an angle in radian: one divided by the cosine | `sec( 0 )` | 1 |  |
| `sin` | sine of an angle in radian | `sin( 0 )` | 0 |  |
| `sqrt` | square root | `sqrt( 9 )` | 3 |  |
| `sum` | the sum of a list: a phrase stands for all its stored values | `sum( 1 ; 2 ; 3 )` | 6 |  |
| `count` | the number of values of a list | `count( 1 ; 2 ; 3 )` | 3 |  |
| `mean` | the average of a list | `mean( 1 ; 2 ; 3 )` | 2 |  |

## Missing in a calculation

A function that one calculation does not have gives no number there, so a page keeps the stored result.

Every function is in every calculation.

## Used in the data but not calculated

A formula may name such a function e.g. to document a law; calculating it shows the user an error.

| function | formulas | example |
| --- | ---: | --- |
| `C` | 1 | `("a" + "b")^"n" = sum(C("n","k") * "a"^("n"-"k") * "b"^"k", "k"=0.."n")` |
| `IRF` | 1 | `IRF(t) = a0 + a1 * exp(-t / tau1) + a2 * exp(-t / tau2) + a3 * exp(-t / tau3)` |
| `d` | 8 | `d/d"x" ("x"^"n") = "n" * "x"^("n" - 1)` |
| `integral` | 8 | `integral("x"^"n", "x") = "x"^("n" + 1) / ("n" + 1) + "C"` |
| `lim` | 1 | `"f"'("x") = lim(("f"("x" + "h") - "f"("x")) / "h", "h"->0)` |

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
| `if( 1 ; 2 ; 5 / 0 )` | 2 | 2 | 2 | 2 | 2 |  |
| `if( abc ; 1 ; 2 )` | no number | no number | no number | no number | no number |  |
| `tan( 1 )` | no number | no number | no number | no number | no number |  |
| `10 - tan( 1 )` | no number | no number | no number | no number | no number |  |
| `median( 2.6 ) * 3` | no number | no number | no number | no number | no number |  |
| `sum( ) * 3` | no number | no number | no number | no number | no number |  |
| `sum( 1 ; abc )` | no number | no number | no number | no number | no number |  |
| `5 %` | no number | no number | no number | no number | no number |  |
| `sqrt( -1 )` | no number | no number | no number | no number | no number |  |
| `log( 0 )` | no number | no number | no number | no number | no number |  |
| `log( 8 ; 1 )` | no number | no number | no number | no number | no number |  |
| `log( 8 ; 0 )` | no number | no number | no number | no number | no number |  |
| `log( 8 ; 2 ; 3 )` | no number | no number | no number | no number | no number |  |
| `ln( -1 )` | no number | no number | no number | no number | no number |  |
| `exp( 1000 )` | no number | no number | no number | no number | no number |  |
| `sqrt( 4 ; 9 )` | no number | no number | no number | no number | no number |  |
| `sqrt( abc )` | no number | no number | no number | no number | no number |  |
| `0 ^ -1` | no number | no number | no number | no number | no number |  |
| `( -8 ) ^ 0.5` | no number | no number | no number | no number | no number |  |
| `2 ^ 3 ^ 2` | 512 | 512 | 512 | 512 | 512 |  |
| `-2 ^ 2` | 4 | 4 | 4 | 4 | 4 |  |
| `( -3 ) ^ 2` | 9 | 9 | 9 | 9 | 9 |  |
| `2 ^ -1` | 0.5 | 0.5 | 0.5 | 0.5 | 0.5 |  |
| `round( -2.5 )` | -3 | -3 | -3 | -3 | -3 |  |
| `round( 2.5 )` | 3 | 3 | 3 | 3 | 3 |  |
| `if( 0 ; 5 / 0 + 1 ; 2 )` | 2 | 2 | 2 | 2 | 2 |  |
| `if( 1 ; 2 ; 5 / 0 + 1 )` | 2 | 2 | 2 | 2 | 2 |  |
| `if( 1 ; 5 / 0 ; 2 )` | no number | no number | no number | no number | no number |  |
| `if( 1 = 1 & 2 = 3 ; 5 ; 6 )` | 6 | 6 | 6 | 6 | 6 |  |
| `if( 1 = 1 \| 2 = 3 ; 5 ; 6 )` | 5 | 5 | 5 | 5 | 5 |  |
