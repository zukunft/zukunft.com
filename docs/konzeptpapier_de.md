# Real-Time Delphi auf Basis des Giant Global Graph: Ein Rahmenwerk für faire und faktenbasierte Entscheidungen

**Autor:** Timon Zielonka  
**Projekt:** [zukunft.com](https://zukunft.com) — *calc with words*  
**Datum:** März 2026  
**Schlagwörter:** Real-Time Delphi, Schleier des Nichtwissens, John Rawls, Giant Global Graph, Semantic Web, Entscheidungsfindung, Zukunftsforschung, kollektive Intelligenz

---

## Zusammenfassung

Dieses Papier schlägt vor, drei bisher getrennt behandelte Konzepte zu einer operativen Methode zu verbinden: (1) John Rawls' *Schleier des Nichtwissens* als normatives Prinzip für unvoreingenommene Entscheidungen, (2) die *Delphi-Methode* in ihrer Echtzeit-Variante als Verfahren zur anonymen, iterativen Konsensfindung, und (3) Tim Berners-Lees *Giant Global Graph* als semantisch verknüpfte Datengrundlage. Das Ergebnis ist ein System, das Entscheidungen ermöglicht, die gleichzeitig fair (weil anonymisiert), rational (weil empirisch fundiert) und transparent (weil auf offenen, verknüpften Daten basierend) sind. Der Prototyp zukunft.com verfolgt die technische Umsetzung dieses Ansatzes.

---

## 1. Ausgangslage: Drei Probleme der Entscheidungsfindung

Gesellschaftliche Entscheidungen — ob über Steuerpolitik, Klimamassnahmen oder Infrastruktur — leiden unter drei systematischen Defiziten:

**Verzerrung durch Eigeninteresse.** Entscheidungsträger wissen, welche Position sie in der Gesellschaft einnehmen, und entscheiden entsprechend. Rawls hat dieses Problem 1971 in *A Theory of Justice* identifiziert und den Schleier des Nichtwissens als Gedankenexperiment vorgeschlagen, das aber theoretisch geblieben ist.

**Mangel an strukturiertem Expertenwissen.** Die Delphi-Methode, seit den 1950er-Jahren von der RAND Corporation entwickelt, löst dieses Problem durch anonyme, iterative Befragung von Fachleuten. In ihrer klassischen Form ist sie jedoch langsam, teuer und auf kleine Expertengruppen beschränkt.

**Mangel an empirischer Grundlage.** Selbst gute Verfahren scheitern, wenn die zugrunde liegenden Daten lückenhaft, veraltet oder nicht miteinander verknüpft sind. Tim Berners-Lee hat 2007 mit dem Konzept des *Giant Global Graph* eine Vision für semantisch verknüpfte Daten formuliert, die dieses Problem adressiert.

Jedes dieser drei Konzepte adressiert eines der drei Defizite. Keines adressiert alle drei gleichzeitig. Die hier vorgeschlagene Synthese tut dies.

---

## 2. Die drei Bausteine

### 2.1 Rawls' Schleier des Nichtwissens

John Rawls schlug vor, dass gerechte Regeln diejenigen sind, auf die sich rationale Personen einigen würden, wenn sie nicht wüssten, welche Position sie in der Gesellschaft einnehmen werden. Hinter diesem *Schleier des Nichtwissens* kennt niemand sein Geschlecht, seine ethnische Zugehörigkeit, sein Vermögen oder seine Begabungen. Empirische Studien (Huang, Greene & Bazerman, 2019, PNAS) haben bestätigt, dass Personen, die in Experimenten hinter einen solchen Schleier versetzt werden, tatsächlich fairere und stärker am Gemeinwohl orientierte Entscheidungen treffen.

Der Schleier des Nichtwissens ist jedoch ein Gedankenexperiment — er wurde bisher nicht als operatives Entscheidungsverfahren implementiert.

### 2.2 Die Delphi-Methode (Real-Time-Variante)

Die Delphi-Methode aggregiert Expertenurteile in mehreren anonymen Runden, wobei nach jeder Runde die aggregierten Ergebnisse rückgemeldet werden, sodass die Teilnehmenden ihre Einschätzungen anpassen können. Die Anonymität verhindert, dass Statusunterschiede oder Gruppendenken die Ergebnisse verzerren.

Die Real-Time-Variante (Gordon & Pease, 2006) eliminiert die diskreten Runden und ermöglicht eine kontinuierliche, webbasierte Teilnahme. Dies macht das Verfahren skalierbar und schneller.

Die Delphi-Methode implementiert damit technisch einen wesentlichen Teil dessen, was Rawls normativ fordert: Die Anonymität der Teilnehmenden entspricht funktional dem Schleier des Nichtwissens, da die Identität und damit die Eigeninteressen der Entscheidenden unsichtbar werden.

### 2.3 Der Giant Global Graph

Tim Berners-Lee beschrieb 2007 den Giant Global Graph (GGG) als die dritte Ebene der Internetarchitektur: Nach dem Netz (Verbindung von Computern) und dem Web (Verbindung von Dokumenten) folgt der Graph (Verbindung von Entitäten und ihren Beziehungen). Technisch basiert der GGG auf den Standards des Semantic Web — RDF, OWL, Linked Data — und ermöglicht es, Fakten, Beziehungen und Zusammenhänge maschinenlesbar und verknüpfbar zu machen.

Für die Entscheidungsfindung bedeutet der GGG, dass ein Delphi-Verfahren nicht auf die subjektiven Einschätzungen der Teilnehmenden beschränkt bleiben muss, sondern in Echtzeit mit empirischen Daten aus dem verknüpften Wissensnetz gespeist werden kann.

---

## 3. Die Synthese: Real-Time Delphi auf Basis des GGG

Die vorgeschlagene Methode verbindet die drei Bausteine wie folgt:

**Normative Ebene (Rawls):** Die Teilnehmenden eines Entscheidungsprozesses werden anonymisiert. Ihre persönlichen Merkmale — Einkommen, Beruf, Herkunft, politische Zugehörigkeit — sind den anderen Teilnehmenden und dem System nicht bekannt. Dies implementiert den Schleier des Nichtwissens operativ, nicht nur als Gedankenexperiment.

**Verfahrensebene (Delphi):** Die Entscheidungsfindung erfolgt iterativ und in Echtzeit. Teilnehmende geben ihre Einschätzungen ab, sehen die aggregierten Ergebnisse, und können ihre Positionen anpassen. Der Prozess konvergiert auf Konsens oder identifiziert systematische Dissense.

**Empirische Ebene (GGG):** Die Teilnehmenden haben Zugriff auf semantisch verknüpfte Daten aus dem Giant Global Graph. Behauptungen können in Echtzeit gegen empirische Daten geprüft werden. Dies verhindert, dass der Konsens auf falschen Prämissen basiert — ein zentrales Problem sowohl der klassischen Delphi-Methode als auch des Rawls'schen Gedankenexperiments, das keine empirische Grundlage vorsieht.

---

## 4. Anwendungsbeispiel: Steuervorlagen in der direkten Demokratie

Die Schweizer 99%-Initiative (Abstimmung vom 26. September 2021) illustriert das Problem, das diese Methode lösen könnte. Der Verfassungstext enthielt zwei Fachbegriffe — «Kapitaleinkommensteile» und «im Umfang von 150 Prozent steuerbar» —, die von einem erheblichen Teil der Stimmenden falsch verstanden wurden. Informelle Tests zeigten, dass selbst Finanzfachleute den Mechanismus häufig nicht korrekt interpretieren konnten.

Ein Real-Time-Delphi auf Basis des GGG hätte in diesem Kontext ermöglicht:

- Die anonyme Erhebung des tatsächlichen Verständnisses (funktionale Verständnistests mit konkreten Rechenbeispielen)
- Die Bereitstellung verknüpfter Daten zur Vermögens- und Einkommensverteilung in der Schweiz
- Eine iterative Annäherung an ein faktenbasiertes Verständnis der Vorlage, bevor die Stimmabgabe erfolgt

---

## 5. Technische Umsetzung: zukunft.com

Der Prototyp [zukunft.com](https://zukunft.com) — mit dem Arbeitstitel *calc with words* — verfolgt die technische Realisierung dieses Ansatzes. Die Daten stehen unter Creative Commons CC0, der Programmcode unter GPL3. Das Projekt befindet sich im Prototyp-Stadium.

Quellcode: [github.com/zukunft](https://github.com/zukunft)

---

## 6. Abgrenzung und offene Fragen

Die vorgeschlagene Methode ist kein Ersatz für demokratische Abstimmungen, sondern ein vorgelagertes Instrument zur Verbesserung der Entscheidungsgrundlagen. Offene Fragen betreffen unter anderem:

- Die praktische Skalierbarkeit auf grosse Teilnehmerzahlen
- Die Qualitätssicherung der Daten im Giant Global Graph
- Die Frage, wie vollständig der Schleier des Nichtwissens in einem digitalen System tatsächlich hergestellt werden kann
- Die Akzeptanz eines solchen Verfahrens bei Stimmberechtigten und Institutionen

---

## Referenzen

- Rawls, J. (1971). *A Theory of Justice.* Harvard University Press.
- Gordon, T. J., & Pease, A. (2006). RT Delphi: An efficient, "round-less" almost real time Delphi method. *Technological Forecasting and Social Change*, 73(4), 321–333.
- Berners-Lee, T. (2007). Giant Global Graph. *timbl's blog*, November 2007.
- Huang, K., Greene, J. D., & Bazerman, M. (2019). Veil-of-ignorance reasoning favors the greater good. *Proceedings of the National Academy of Sciences*, 116(48), 23989–23995.

---

*Kontakt: Timon Zielonka — zukunft.com*
