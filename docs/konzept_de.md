# Konzept

[in English](concept.md)

Eine Idee ist ein Konzept "aus einem Guss" für Berechnungen und Entscheidungen.
Für Berechnungen braucht es Zahlen, aber für Menschen sind Zahlen ohne Worte fast nutzlos,
deshalb ist jede Zahl mit einer Liste von Wörtern verknüpft. Ein Wort kann mehr als eine Bedeutung haben;
welche Bedeutung gemeint ist, legen Tripel aus Subjekt, Prädikat und Objekt fest.

## Warum nicht einfach RDF?

Das [W3C RDF](https://www.w3.org/RDF/) scheint den Nachteil zu haben, dass eine Zahl über eine Eigenschaft zugeordnet wird.
Dadurch kann die Zahl der Eigenschaften sehr gross werden, was viel Speicher brauchen kann (z.B. https://www.wikidata.org/wiki/Wikidata:Property_proposal/number_of_comments_per_day)

## Die Dokumente des Projekts

Die Dokumente unten sind von der allgemeinen Idee bis zu den Einzelheiten des Codes geordnet, so dass jemand,
der wissen will, wie zukunft.com funktioniert, und vielleicht mitprogrammieren will, sie von oben nach unten lesen kann.
Die Installation eines Entwicklungssystems beschreibt das [README](../README.md) des Projekts.
Die meisten Dokumente gibt es nur auf Englisch.

### Die Idee

#### Die Idee von zukunft.com

Die einfache Einführung: warum zukunft.com mit Worten rechnet, was ein Wort, ein Tripel, ein Wert und eine
Formel ist und wo man weiterlesen kann. Siehe [Idee und Einstieg](idee_und_einstieg.md).

#### Häufige Fragen

Kurze Antworten auf die ersten Fragen, z.B. warum ein KI-Chatbot nicht dasselbe kann und wie die Daten
importiert und exportiert werden können. Siehe [häufige Fragen](faq_de.md).

### Die Hintergrundpapiere

#### Real-Time-Delphi auf Basis des Giant Global Graph

Das Papier, das den Schleier des Nichtwissens von John Rawls, die Real-Time-Delphi-Methode und das semantische Web
zu einem Verfahren für faire und faktenbasierte Entscheidungen verbindet, was das langfristige Ziel von zukunft.com ist.
Siehe [Konzeptpapier](konzeptpapier_de.md).

#### Den kategorischen Imperativ in der Praxis umsetzen

Ein Arbeitspapier, das den kategorischen Imperativ und die goldene Regel auf zehn Politikbereiche anwendet,
von Desinformation bis Klimaschutz, als Beispiele für die Entscheidungen, die zukunft.com unterstützen soll.
Siehe [Konzeptpapier lang](konzeptpapier_lang_de.md).

### Das Datenmodell

#### Phrasen

Eine Phrase ist entweder ein Wort oder ein Tripel; dieses kurze Dokument nennt die vier Ebenen, auf denen
einer Phrase eine Funktion zugeordnet werden kann. Siehe [phrases](phrases.md).

#### Das Austausch-JSON

Das JSON-Format, mit dem die Pods Daten austauschen und das für den Import und Export verwendet wird, mit einem
vollständigen Beispiel aller Hauptabschnitte. Siehe [exchange json](exchange_json.md).

#### Das JSON des Rechnereditors

Das JSON einer Rechnerseite, das das Frontend mit der phraseData-Anfrage erhält und das der Rechnereditor
exportiert und importiert. Siehe [json structure](json_structure.md).

#### Die Benutzer-Sandbox beim Import

Wie der Import desselben Objekts durch viele Benutzer zusammengeführt wird, so dass jeder Benutzer den "besten bekannten"
Wert sieht, und welche Funktionen im Code ein ähnliches oder dasselbe Objekt erkennen. Siehe [user sandbox import](user_sandbox_import.md).

#### Die Rechenfunktionen

Die Funktionen, die eine Formel verwenden kann, und die Stellen, an denen ein Ergebnis berechnet wird, mit den
Grenzfällen, in denen diese Berechnungen voneinander abweichen; vom Testlauf erzeugt. Siehe [math](math.md).

#### Terme zu SPARQL

Das Konzept, die Wörter einer Anfrage wie "Berg nach Land" in eine SPARQL-Abfrage zu übersetzen, so dass das
Frontend von zukunft.com für einen RDF-Server verwendet werden kann. Siehe [terms to sparql](terms_to_sparql.md).

### Das Frontend

#### Bedienbarkeit und Gestaltung

Die Gestaltungsregeln, denen alle Ansichten und Abläufe folgen, z.B. dass ein Klick auf einen Link mehr Informationen zeigt.
Siehe [usability and design](usability_and_design.md).

#### Systemansichten

Die Seiten, die das System für das Hinzufügen, Ändern und Löschen verwendet und die ein Benutzer nicht ändern kann,
und die Liste der Systemansichten. Siehe [system views](system%20views.md).

#### Eine Systemansicht ändern

Die Schritte, um eine Systemansicht von der JSON-Datei bis zur aktualisierten Datenbank und den Testressourcen zu ändern.
Siehe [change system views](change_system_views.md).

#### Eine Tabelle erstellen

Eine Schritt-für-Schritt-Anleitung für Benutzer, die die Tabelle der Lösungen gegen die Klimaerwärmung mit ihrem
möglichen Nutzen, dem anfänglichen Aufwand und dem Verhältnis von Ertrag zu Aufwand erstellt. Siehe [table creation](table_creation.md).

#### Sicherheit

Eine geplante Anleitung zur Sicherheit von zukunft.com. Siehe [security](tutorial/security.md).

### Der Code

#### Richtlinien für den Code

Der Ablauf für eine neue Funktion, vom Unit-Test bis zum Commit, und die Regeln für den Code dieses Projekts.
Siehe [code guidelines](code_guidelines.md).

#### Hauptfunktionen

Die Hauptfunktionen, die jedes Objekt des Backends hat, z.B. für das Speichern und die Zuordnung der Datenbankzeile,
des API-JSON und des Import-JSON. Siehe [main functions](main_functions.md).

#### Objekte

Die Abschnitte, die die meisten Objekte in derselben Reihenfolge haben, so dass eine Funktion in jeder Klasse an
derselben Stelle zu finden ist. Siehe [code objects](code_objects.md).

#### Alle Objekte

Der erzeugte Baum aller Klassen mit einer kurzen Beschreibung jeder Klasse. Siehe [code objects all](code_objects_all.md).

#### Alle Funktionen

Die erzeugte Liste aller Funktionen pro Klasse und der Abschnitte, die erwartet werden, aber fehlen.
Siehe [code functions all](code_functions_all.md).

#### Ausnahmen bei den Objektnamen

Die erzeugte Liste der globalen Variablen und der Namen, die als Ausnahme von den Namensregeln erlaubt sind.
Siehe [code object name exceptions](code_object_name_exceptions.md).

#### Ausnahmen bei den Benutzermeldungen

Die erzeugte Liste der Stellen, die eine eigene Benutzermeldung erstellen, und warum jede davon eine braucht.
Siehe [code user message exceptions](code_user_message_exceptions.md).

#### Abdeckung durch Unit-Tests

Die erzeugte Zahl der Unit-Tests pro Funktion und die Funktionen, die kein Code aufruft.
Siehe [code test coverage](code_test_coverage.md).

### Installation, Test und Bereitstellung

#### Installation

Wie eine Entwicklungsinstallation repariert wird, z.B. indem die Datenbank neu erstellt wird. Siehe [install](install.md).

#### Bereitstellung

Die Schritte von einem Issue bis zum Zusammenführen in den Entwicklungszweig, mit der empfohlenen Reihenfolge
der Tests. Siehe [deployment](deployment.md).

#### Build-Prozess

Der geplante automatische Ablauf vom vollständigen Test des Entwicklungszweigs bis zu den produktiven Pods.
Siehe [build process](build_process.md).

#### Serverstruktur

Die fünf Serverschichten des Pods zukunft.com, vom Lastverteiler bis zu den Datenbankservern, so dass jede
Schicht durch weitere Server wachsen kann. Siehe [server structure](server_structure.md).

### Ergebnisse und offene Aufgaben

#### JSON-Ergebnisse

Die erzeugte Liste der Ergebnisse der JSON-Prüfung pro Importdatei. Siehe [json findings](json_findings.md).

#### Ergebnisse der festen IDs

Der erzeugte Vergleich der festen Test-IDs mit der Datenbank nach dem Zurücksetzen.
Siehe [pinned id findings](pinned_id_findings.md).

#### TODO-Notizen

Die Notizen zu den offenen Aufgaben, für die noch ein Issue erstellt werden muss. Siehe [todo](todo.md).
