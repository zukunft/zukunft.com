# The Idea Behind zukunft.com: Calculating with Words

## How it started

In 1994 I wrote myself an "AI" program and asked it how I could make money. The answer was: "Exploit people's fears." The answer frightened me, so I stopped developing the program and instead focused on writing a program whose answers can be followed step by step by humans.

That is the idea behind zukunft.com: **calculating with words.**

For conscious thinking, humans use words. And numbers and formulas, without describing words, usually have no meaning for a human. From this follows a "simple" data format that should, in principle, fit all calculations.

One goal of this project is to show that all meaningful calculations can be described unambiguously with this data format. Or the other way around: to find out whether there is anything that *cannot* be described unambiguously this way.

Over the years I have tested this again and again and adjusted the data format slightly each time. For a few years now it has been fairly stable.

## The problem with bare numbers

A number on its own means nothing. If someone writes

    8.7

nobody knows what is meant. 8.7 of what? Of which thing? When?

Only the words make the number understandable:

    The population of Switzerland in 2020 was 8.7 million.

Here the words carry all of the meaning. The number is only the result. This is exactly the core idea of the data format: **every value is described unambiguously by a list of words.**

## The data format in a simple example

Take the statement above. In the zukunft.com format it consists of a set of words and a value:

| Words (describe the value)                     | Value      |
|------------------------------------------------|------------|
| Switzerland, population, 2020                  | 8,700,000  |

The order of the words does not matter – "2020, Switzerland, population" describes the same value. The only thing that matters is that the set of words is unambiguous: there is exactly one value that belongs to *this* combination of words.

To store a second number, you add words or swap them:

| Words                                          | Value       |
|------------------------------------------------|-------------|
| Switzerland, population, 2020                  | 8,700,000   |
| Switzerland, population, 2021                  | 8,770,000   |
| Germany, population, 2020                      | 83,200,000  |

That is all it takes, at first, to store any number of facts. No database schema you have to define in advance, no table columns that eventually no longer fit. Just words and values.

## From word to relationship: the triple

Some words belong together more closely than others. "Switzerland" *is a* "country". Such relationships are written as a **triple** – two words connected by a third that states the kind of relationship (a predicate):

    Zurich   (is a)   canton
    canton   (is part of)   Switzerland

This lets the system know that a number about "Zurich" is also a number about a "canton" and about a part of "Switzerland". So you can ask "How many people live in Switzerland?" and the system can add up the values of the individual cantons without that being hard-wired beforehand. The relationship lives in the words, not in the program code.

## From value to calculation: the formula

A **formula** is, again, described with words. Instead of cell references as in a spreadsheet, you calculate with words:

    population density = population / area

Applied to the words "Switzerland" and "2020", the system looks up the matching values (the population and area of Switzerland in 2020) and calculates. The result is again a value with a set of words:

| Words                                          | Value  |
|------------------------------------------------|--------|
| Switzerland, population density, 2020          | 211    |

And crucially: anyone can check *where* the 211 comes from – which formula, which input values, which sources. The calculation can be followed step by step. That was the goal back in 1994.

## Why this matters for decisions

If every number carries its words and its origin, then a *decision* based on such numbers also becomes fully transparent. You can disclose which values, which assumptions, and which formulas led to a conclusion – and anyone can change a single assumption and see how the result shifts.

This is the bridge to the project's decision idea (see the wiki page "Decisions"): a decision can then be discussed like a calculation – not "I feel" against "I feel", but "this value here is wrong in my view, and here is why".

## The difference from "the AI says so"

A present-day chatbot gives an answer, but you cannot check how it came about. Here it is the other way around: the system outputs nothing it could not disclose. Every value is traceable to its words, its sources, and its formula. The machine should make human thinking easier, not replace it.

## Where to start

**Conceptual overview:**

- **The whole idea:** The [concept paper](concept_paper_en.md) connects Rawls' Veil of Ignorance, the Real-Time Delphi method, and the Giant Global Graph into a procedure for fair, evidence-based decisions. (Also available [in German](konzeptpapier_de.md).)
- **The idea in short:** [concept.md](concept.md) – why numbers without words are useless, and why pure RDF is not used.

**The data format in detail:**

- **The building blocks:** [phrases.md](phrases.md) explains the central notion of a *phrase* (a word or a triple).
- **The exchange format:** [exchange_json.md](exchange_json.md) shows how words, triples, formulas, and values are stored as JSON – useful if you want to try out your own examples.
- **Installation:** [install.md](install.md), if you want to set the system up locally.

**The decision logic:**

- The wiki page [Decisions](https://github.com/zukunft/zukunft.com/wiki/Decisions) explains how traceable numbers turn into traceable decisions (keyword "happy time points").

**Getting involved:** Questions, criticism, and concrete examples that *cannot* be represented well are especially welcome – because it is precisely such cases that test whether the data format is really general enough.

---

*If anything in this description remains unclear, that is more likely a flaw in the description than a flaw in the reader. Pointers to where the thread breaks help the project the most.*