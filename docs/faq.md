# zukunft.com – frequently asked questions

[auf Deutsch](faq_de.md)

## What is zukunft.com?

A program to calculate with words. Every number is described by a set of words, e.g.
"Switzerland, population, 2020" for 8.7 million, and every formula is written with words
instead of cell references, e.g. "population density = population / area". The result of
a formula is again a number with its words, so anyone can follow where it comes from.
The plain-language intro is [The idea behind zukunft.com](idea_and_intro.md)
([auf Deutsch](idee_und_einstieg.md)).

## Can this not be done by an LLM AI chatbot?

Not with the same guarantee. An LLM chatbot creates its answer from billions of weights and
a sampling step that picks the next word by probability. For a simple question the answer is
often right, but for a complex question that depends on many numbers and assumptions the
chatbot can by concept never make sure that it gives 100% the same result again: a different
wording of the question, a new model version or just the next run can change the answer, and
nobody can show step by step why the answer is what it is.

zukunft.com works the other way around. Filling in the needed data may be slower, because every
value, word and formula has to be entered or imported once. But once all data are in, the
result is always the same. A result changes only if someone changes a value or a formula on
purpose, and each such change is written to the change log with the user and the time, so that
the old and the new result can be compared and every step of the calculation can be read by a
human.

Both can work together: a chatbot may help to find or enter data, and zukunft.com keeps the
data and calculates the result in a reproducible way.

## Why are words used and not just numbers?

A number alone has no meaning: "8.7" says nothing until the words "Switzerland, population,
2020, million" are added. In zukunft.com the words carry the meaning and the number is only
the value, so a value can be found, combined and calculated by its words. The order of the
words does not matter; one set of words names exactly one value.

## What is the difference to a spreadsheet?

A spreadsheet formula points to cells, e.g. `=B2/C2`, and loses its meaning when the table
is rebuilt. A zukunft.com formula names words, e.g. "population / area", so it can be used
for every country and year that has the needed values, without copying the formula.
The relations between words, e.g. "Zurich (is a) canton" and "canton (is part of)
Switzerland", are data as well, so a sum over the cantons needs no extra program code.

## Can I see where a result comes from?

Yes. Each result names its formula, the values used and their sources, and each value names
its words and its source. The system shows nothing that it could not disclose.

## What happens if I disagree with a number?

Each user can change a value, a word or a formula for themselves without changing it for the
others. So a disagreement becomes concrete: not "I feel" against "I feel", but "this value is
wrong in my view, and here is why".

## Is the data exchanged with other systems?

Yes, the data can be im- and exported as JSON ([exchange_json.md](exchange_json.md)) and a
word can be linked to an external system such as [Wikidata](https://www.wikidata.org/).

## Why is the system so slow?

This alpha version runs on a single server. There is a concept for a setup with several
servers ([server_structure.md](server_structure.md)), but it is not yet built. You can help
by contributing to the [repository on GitHub](https://github.com/zukunft/zukunft.com) or by
supporting the project with a donation to zukunft.com AG, IBAN CH20 0900 0000 8927 4297 2.

## Is my data safe?

If not stated otherwise, everything you enter in the system is public by definition under the
[CC0 licence](https://creativecommons.org/publicdomain/zero/1.0/). Your personal data such as
your email address and your password are kept private, but this is an alpha version, so program
errors cannot be completely excluded. The password is stored only as a hash, so at least the
password itself should be safe. Because zukunft.com is open source software, you can check the
code yourself, and any report of a problem is welcome as a
[GitHub issue](https://github.com/zukunft/zukunft.com/issues).

## Why do I see no "accept all cookies" message?

This program uses just the one technical session cookie and does no hidden tracking and no
external communication. Of course all your changes are included in the public change log, so
that other users can see the reason for a change.

## How can I create a new calculator?

One way is to pick a calculator that is similar to the case you want to create, export its
parameters as json in the calculator editor, e.g. in the
[calculator editor of the PV example](https://www.zukunft.com/http/view.php?m=calculator_editor&p=example%20PV%20installation),
and ask any LLM chatbot to create a similar json for
the calculator you want. The format of this json is described in [json_structure.md](json_structure.md).
Import the new json in the calculator editor and fine-tune the calculator there by hand or ask
the chatbot to adjust the json.

## How can I try it?

The installation on a Debian system or with docker is described in the
[README](../README.md) and in [install.md](install.md).

## How can I help?

Questions, criticism and especially concrete examples that cannot be described well with
words, values and formulas are welcome, because such cases test whether the data format is
general enough. See [The idea behind zukunft.com](idea_and_intro.md) for where to start.
