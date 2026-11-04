# häufige Fragen

[in English](faq.md)

## Was ist zukunft.com?

Ein Programm, um mit Wörtern zu rechnen. Jede Zahl wird durch eine Menge von Wörtern beschrieben,
z.B. "Schweiz, Bevölkerung, 2020" für 8,7 Millionen, und jede Formel wird mit Wörtern statt mit
Zellbezügen geschrieben, z.B. "Bevölkerungsdichte = Bevölkerung / Fläche". Das Ergebnis einer
Formel ist wieder eine Zahl mit ihren Wörtern, so dass jeder nachvollziehen kann, woher es kommt.
Die einfache Einführung ist [Die Idee von zukunft.com](idee_und_einstieg.md)
([in English](idea_and_intro.md)).

## Kann das nicht ein LLM-KI-Chatbot erledigen?

Nicht mit derselben Sicherheit. Ein LLM-Chatbot erzeugt seine Antwort aus Milliarden von Gewichten
und einem Zufallsschritt, der das nächste Wort nach seiner Wahrscheinlichkeit auswählt. Bei einer
einfachen Frage ist die Antwort oft richtig, aber bei einer komplexen Frage, die von vielen Zahlen
und Annahmen abhängt, kann der Chatbot vom Konzept her nie sicherstellen, dass er wieder zu 100%
dasselbe Ergebnis liefert: eine andere Formulierung der Frage, eine neue Modellversion oder einfach
der nächste Durchlauf kann die Antwort ändern, und niemand kann Schritt für Schritt zeigen, warum
die Antwort so ist, wie sie ist.

zukunft.com arbeitet umgekehrt. Das Erfassen der benötigten Daten kann langsamer sein, weil jeder
Wert, jedes Wort und jede Formel einmal eingegeben oder importiert werden muss. Aber sobald alle
Daten erfasst sind, ist das Ergebnis immer dasselbe. Ein Ergebnis ändert sich nur, wenn jemand
einen Wert oder eine Formel absichtlich ändert, und jede solche Änderung wird mit dem Benutzer und
der Zeit im Änderungsprotokoll festgehalten, so dass das alte und das neue Ergebnis verglichen und
jeder Schritt der Berechnung von einem Menschen gelesen werden kann.

Beides kann zusammenarbeiten: ein Chatbot kann helfen, Daten zu finden oder einzugeben, und
zukunft.com bewahrt die Daten und berechnet das Ergebnis nachvollziehbar und reproduzierbar.

## Warum werden Wörter verwendet und nicht nur Zahlen?

Eine Zahl allein hat keine Bedeutung: "8,7" sagt nichts, bis die Wörter "Schweiz, Bevölkerung,
2020, Millionen" dazukommen. In zukunft.com tragen die Wörter die Bedeutung und die Zahl ist nur
der Wert, so dass ein Wert über seine Wörter gefunden, kombiniert und berechnet werden kann. Die
Reihenfolge der Wörter spielt keine Rolle; eine Menge von Wörtern benennt genau einen Wert.

## Was ist der Unterschied zu einer Tabellenkalkulation?

Eine Formel in einer Tabellenkalkulation verweist auf Zellen, z.B. `=B2/C2`, und verliert ihre
Bedeutung, wenn die Tabelle umgebaut wird. Eine Formel in zukunft.com nennt Wörter, z.B.
"Bevölkerung / Fläche", und kann so für jedes Land und jedes Jahr verwendet werden, für das die
benötigten Werte vorhanden sind, ohne die Formel zu kopieren. Die Beziehungen zwischen Wörtern,
z.B. "Zürich (ist ein) Kanton" und "Kanton (ist Teil von) Schweiz", sind ebenfalls Daten, so dass
eine Summe über die Kantone keinen zusätzlichen Programmcode braucht.

## Kann ich sehen, woher ein Ergebnis kommt?

Ja. Jedes Ergebnis nennt seine Formel, die verwendeten Werte und deren Quellen, und jeder Wert
nennt seine Wörter und seine Quelle. Das System zeigt nichts, was es nicht offenlegen könnte.

## Was passiert, wenn ich mit einer Zahl nicht einverstanden bin?

Jeder Benutzer kann einen Wert, ein Wort oder eine Formel für sich ändern, ohne sie für die anderen
zu ändern. So wird eine Meinungsverschiedenheit konkret: nicht "ich finde" gegen "ich finde",
sondern "dieser Wert ist aus meiner Sicht falsch, und zwar aus diesem Grund".

## Werden die Daten mit anderen Systemen ausgetauscht?

Ja, die Daten können als JSON im- und exportiert werden ([exchange_json.md](exchange_json.md)), und
ein Wort kann mit einem externen System wie [Wikidata](https://www.wikidata.org/) verknüpft werden.

## Warum ist das System so langsam?

Diese Alpha-Version läuft auf einem einzigen Server. Es gibt ein Konzept für einen Aufbau mit
mehreren Servern ([server_structure.md](server_structure.md)), das aber noch nicht umgesetzt ist.
Man kann dabei helfen, indem man zum [Repository auf GitHub](https://github.com/zukunft/zukunft.com)
beiträgt oder das Projekt mit einer Spende an die zukunft.com AG, IBAN CH20 0900 0000 8927 4297 2, unterstützt.

## Sind meine Daten sicher?

Wenn nicht anders angegeben, ist alles, was man im System erfasst, per Definition öffentlich unter
der [CC0-Lizenz](https://creativecommons.org/publicdomain/zero/1.0/deed.de). Persönliche Daten wie
die E-Mail-Adresse und das Passwort bleiben privat, aber dies ist eine Alpha-Version, deshalb
können Programmfehler nicht ganz ausgeschlossen werden. Das Passwort wird nur als Hash gespeichert,
so dass zumindest das Passwort selbst sicher sein sollte. Weil zukunft.com Open-Source-Software ist,
kann man den Code selbst prüfen, und jede Meldung eines Problems ist als
[GitHub-Issue](https://github.com/zukunft/zukunft.com/issues) willkommen.

## Warum sehe ich keine Meldung "alle Cookies akzeptieren"?

Dieses Programm verwendet nur das eine technische Session-Cookie und macht weder verstecktes
Tracking noch externe Kommunikation. Natürlich sind alle eigenen Änderungen im öffentlichen
Änderungsprotokoll enthalten, so dass andere Benutzer den Grund einer Änderung sehen können.

## Wie kann ich einen neuen Rechner erstellen?

Eine Möglichkeit ist, einen Rechner zu wählen, der dem gewünschten Fall ähnlich ist, seine
Parameter im Rechner-Editor als JSON zu exportieren, z.B. im
[Rechner-Editor des PV-Beispiels](https://www.zukunft.com/http/view.php?m=calculator_editor&p=example%20PV%20installation),
und einen beliebigen LLM-Chatbot zu bitten,
ein ähnliches JSON für den gewünschten Rechner zu erstellen. Das Format dieses JSON ist in
[json_structure.md](json_structure.md) beschrieben. Das neue JSON wird im Rechner-Editor importiert
und der Rechner dort von Hand verfeinert, oder der Chatbot passt das JSON an.

## Wie kann ich es ausprobieren?

Die Installation auf einem Debian-System oder mit Docker ist im [README](../README.md) und in
[install.md](install.md) beschrieben.

## Wie kann ich helfen?

Fragen, Kritik und besonders konkrete Beispiele, die sich mit Wörtern, Werten und Formeln nicht gut
beschreiben lassen, sind willkommen, weil genau solche Fälle prüfen, ob das Datenformat allgemein
genug ist. Wo man am besten anfängt, steht in [Die Idee von zukunft.com](idee_und_einstieg.md).
