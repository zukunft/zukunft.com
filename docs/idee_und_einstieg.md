# Die Idee von zukunft.com: Rechnen mit Worten

## Wie alles anfing

1994 habe ich mir ein "KI"-Programm geschrieben und es gefragt, wie ich Geld verdienen kann. Die Antwort war: "Nutze die Angst der Menschen aus." Die Antwort hat mich erschreckt, und ich habe die Entwicklung des Programms abgebrochen und mich stattdessen darauf konzentriert, ein Programm zu schreiben, dessen Antworten von Menschen Schritt für Schritt nachvollzogen werden können.

Das ist die Idee von zukunft.com: **Rechnen mit Worten.**

Für das bewusste Denken nutzt der Mensch Worte. Und Zahlen und Rechenformeln haben ohne beschreibende Worte meist keine Bedeutung für den Menschen. Daraus ergibt sich ein "einfaches" Datenformat, das eigentlich für alle Berechnungen passen sollte.

Ein Ziel dieses Projekts ist, zu zeigen, dass alle sinnvollen Berechnungen mit diesem Datenformat eindeutig beschrieben werden können. Oder anders herum: herauszufinden, ob es etwas gibt, was so *nicht* eindeutig beschrieben werden kann.

Über die Jahre habe ich das immer wieder getestet und das Datenformat immer wieder leicht angepasst. Seit ein paar Jahren ist es recht stabil.

## Das Problem mit nackten Zahlen

Eine Zahl allein bedeutet nichts. Schreibt jemand

    8,7

dann weiß niemand, was gemeint ist. 8,7 was? Wovon? Wann?

Erst die Worte machen die Zahl verständlich:

    Die Einwohnerzahl der Schweiz im Jahr 2020 war 8,7 Millionen.

Hier tragen die Worte die gesamte Bedeutung. Die Zahl ist nur das Ergebnis. Genau dieser Gedanke ist der Kern des Datenformats: **Jeder Wert wird durch eine Liste von Worten eindeutig beschrieben.**

## Das Datenformat an einem einfachen Beispiel

Nehmen wir die Aussage oben. Im Format von zukunft.com besteht sie aus einer Menge von Worten und einem Wert:

| Worte (beschreiben den Wert)                  | Wert       |
|-----------------------------------------------|------------|
| Schweiz, Einwohnerzahl, 2020                  | 8 700 000  |

Die Reihenfolge der Worte spielt in diesem Fall keine Rolle – "2020, Schweiz, Einwohnerzahl" beschreibt denselben Wert. Wichtig ist nur: Die Wortmenge ist eindeutig. Es gibt genau einen Wert, der zu *dieser* Kombination von Worten gehört.

Will man eine zweite Zahl ablegen, fügt man Worte hinzu oder tauscht sie aus:

| Worte                                          | Wert        |
|------------------------------------------------|-------------|
| Schweiz, Einwohnerzahl, 2020                   | 8 700 000   |
| Schweiz, Einwohnerzahl, 2021                   | 8 770 000   |
| Deutschland, Einwohnerzahl, 2020               | 83 200 000  |

Mehr braucht es zunächst nicht, um beliebig viele Fakten abzulegen. Kein Datenbankschema, das man vorher festlegen muss, keine Tabellenspalten, die irgendwann nicht mehr passen. Nur Worte und Werte.

## Vom Wort zur Beziehung: das Tripel

Manche Worte gehören enger zusammen als andere. "Schweiz" *ist ein* "Land". Solche Beziehungen schreibt man als **Tripel** – zwei Worte, verbunden durch ein drittes, das die Art der Beziehung angibt (ein Prädikat):

    Zürich   (ist ein)   Kanton
    Kanton   (ist Teil von)   Schweiz

Damit weiß das System, dass eine Zahl über "Zürich" auch eine Zahl über einen "Kanton" und über einen Teil der "Schweiz" ist. Man kann also fragen: "Wie viele Menschen leben in der Schweiz?" und das System kann die Werte der einzelnen Kantone zusammenzählen, ohne dass man das vorher fest verdrahtet hat. Die Beziehung steckt in den Worten, nicht im Programmcode.

## Vom Wert zur Berechnung: die Formel

Eine **Formel** ist selbst wieder mit Worten beschrieben. Statt Zellbezügen wie in einer Tabellenkalkulation rechnet man mit Worten:

    Bevölkerungsdichte = Einwohnerzahl / Fläche

Wendet man diese Formel auf die Worte "Schweiz" und "2020" an, sucht das System die passenden Werte (Einwohnerzahl und Fläche der Schweiz 2020) und rechnet. Das Ergebnis ist wieder ein Wert mit einer Wortmenge:

| Worte                                          | Wert   |
|------------------------------------------------|--------|
| Schweiz, Bevölkerungsdichte, 2020              | 211    |

Und entscheidend: Jeder kann nachsehen, *woher* die 211 kommt – welche Formel, welche Eingabewerte, welche Quellen. Die Rechnung ist Schritt für Schritt nachvollziehbar. Genau das war 1995 das Ziel.

## Warum das für Entscheidungen wichtig ist

Wenn jede Zahl ihre Worte und ihre Herkunft mitführt, wird auch eine *Entscheidung*, die auf solchen Zahlen beruht, vollständig transparent. Man kann offenlegen, welche Werte, welche Annahmen und welche Formeln zu einem Schluss geführt haben – und jeder kann eine einzelne Annahme ändern und sehen, wie sich das Ergebnis verschiebt.

Das ist die Brücke zur Entscheidungsidee des Projekts (siehe die Wiki-Seite "Entscheidungen"): Eine Entscheidung lässt sich dann diskutieren wie eine Rechnung – nicht "ich finde" gegen "ich finde", sondern "dieser Wert hier stimmt meiner Meinung nach nicht, und hier ist warum".

## Der Unterschied zu "die KI sagt es"

Ein heutiger Chatbot gibt eine Antwort, aber man kann nicht wirklich nachsehen, wie sie zustande kam. Und vor allem, was der Chatbot warum weggelassen hat oder was er gar nicht weiss. Hier ist es umgekehrt: Das System gibt nichts aus, was es nicht offenlegen könnte. Jeder Wert ist auf seine Worte, seine Quellen und seine Formel zurückführbar. Die Maschine soll dem Menschen das Nachdenken erleichtern, nicht es ersetzen.

## Wo man anfangen kann

**Auf Deutsch:**

- **Der Gesamtgedanke:** Das [Konzeptpapier (deutsch)](konzeptpapier_de.md) verbindet Rawls' Schleier des Nichtwissens, die Real-Time-Delphi-Methode und den Giant Global Graph zu einem Verfahren für faire, faktenbasierte Entscheidungen.
- **Die Entscheidungslogik:** Die Wiki-Seite [Entscheidungen](https://github.com/zukunft/zukunft.com/wiki/Entscheidungen-(German-page)) erklärt, wie aus nachvollziehbaren Zahlen nachvollziehbare Entscheidungen werden (Stichwort "Glückszeitpunkte").

**Auf Englisch (Datenformat im Detail):**

- **Die Grundidee in Kurzform:** [concept.md](concept.md) – warum Zahlen ohne Worte nutzlos sind und warum nicht einfach reines RDF verwendet wird.
- **Die Bausteine:** [phrases.md](phrases.md) erklärt den zentralen Begriff der *Phrase* (ein Wort oder ein Tripel). Vertiefend dazu das [Konzeptpapier (englisch)](concept_paper_en.md).
- **Das Austauschformat:** [exchange_json.md](exchange_json.md) zeigt, wie Worte, Tripel, Formeln und Werte als JSON abgelegt werden – nützlich, wenn man eigene Beispiele ausprobieren will.
- **Installation:** [install.md](install.md), falls man das System lokal aufsetzen möchte.
  **Mitmachen:** Rückfragen, Kritik und konkrete Beispiele, die sich *nicht* gut abbilden lassen, sind besonders willkommen – denn an genau solchen Fällen prüft sich, ob das Datenformat wirklich allgemein genug ist.

---

*Wenn etwas an dieser Beschreibung unklar bleibt, ist das eher ein Fehler der Beschreibung als ein Fehler beim Leser. Hinweise darauf, an welcher Stelle der Faden reißt, helfen dem Projekt am meisten.*
 
