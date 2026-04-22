# Real-Time Delphi Based on the Giant Global Graph: A Framework for Fair and Evidence-Based Decision-Making

**Author:** Timon Zielonka  
**Project:** [zukunft.com](https://zukunft.com) — *calc with words*  
**Date:** March 2026  
**Keywords:** Real-Time Delphi, Veil of Ignorance, John Rawls, Giant Global Graph, Semantic Web, decision-making, futures research, collective intelligence

---

## Abstract

This paper proposes combining three concepts that have so far been treated separately into a single operational method: (1) John Rawls' *Veil of Ignorance* as a normative principle for unbiased decision-making, (2) the *Delphi method* in its real-time variant as a procedure for anonymous, iterative consensus-building, and (3) Tim Berners-Lee's *Giant Global Graph* as a semantically linked data foundation. The result is a system that enables decisions that are simultaneously fair (because anonymized), rational (because empirically grounded), and transparent (because based on open, linked data). The prototype zukunft.com pursues the technical implementation of this approach.

---

## 1. The Problem: Three Deficits in Decision-Making

Societal decisions — whether about tax policy, climate action, or infrastructure — suffer from three systematic deficits:

**Bias through self-interest.** Decision-makers know their position in society and decide accordingly. Rawls identified this problem in 1971 in *A Theory of Justice* and proposed the Veil of Ignorance as a thought experiment — one that has remained theoretical.

**Lack of structured expert knowledge.** The Delphi method, developed since the 1950s by the RAND Corporation, addresses this problem through anonymous, iterative consultation of experts. In its classical form, however, it is slow, expensive, and limited to small expert groups.

**Lack of empirical foundation.** Even good procedures fail when the underlying data is incomplete, outdated, or unconnected. Tim Berners-Lee articulated the concept of the *Giant Global Graph* in 2007 as a vision for semantically linked data that addresses this problem.

Each of these three concepts addresses one of the three deficits. None addresses all three simultaneously. The synthesis proposed here does.

---

## 2. The Three Building Blocks

### 2.1 Rawls' Veil of Ignorance

John Rawls proposed that just rules are those that rational individuals would agree upon if they did not know what position they would occupy in society. Behind this *Veil of Ignorance*, no one knows their gender, ethnicity, wealth, or abilities. Empirical studies (Huang, Greene & Bazerman, 2019, PNAS) have confirmed that individuals placed behind such a veil in experimental settings do in fact make fairer and more socially beneficial decisions.

The Veil of Ignorance, however, remains a thought experiment — it has not been implemented as an operational decision-making procedure.

### 2.2 The Delphi Method (Real-Time Variant)

The Delphi method aggregates expert judgments across multiple anonymous rounds, with aggregated results fed back after each round so that participants can adjust their estimates. Anonymity prevents status differences and groupthink from distorting results.

The real-time variant (Gordon & Pease, 2006) eliminates discrete rounds and enables continuous, web-based participation. This makes the procedure scalable and faster.

The Delphi method thus technically implements a substantial part of what Rawls demands normatively: the anonymity of participants functions as an operational Veil of Ignorance, since the identity — and therefore the self-interest — of decision-makers becomes invisible.

### 2.3 The Giant Global Graph

Tim Berners-Lee described the Giant Global Graph (GGG) in 2007 as the third layer of internet architecture: after the Net (connecting computers) and the Web (connecting documents) comes the Graph (connecting entities and their relationships). Technically, the GGG is based on Semantic Web standards — RDF, OWL, Linked Data — enabling facts, relationships, and connections to be made machine-readable and linkable.

For decision-making, the GGG means that a Delphi process need not remain limited to the subjective estimates of participants, but can be fed in real time with empirical data from the linked knowledge network.

---

## 3. The Synthesis: Real-Time Delphi Based on the GGG

The proposed method connects the three building blocks as follows:

**Normative layer (Rawls):** Participants in a decision-making process are anonymized. Their personal characteristics — income, profession, origin, political affiliation — are unknown to other participants and to the system. This implements the Veil of Ignorance operationally, not merely as a thought experiment.

**Procedural layer (Delphi):** Decision-making occurs iteratively and in real time. Participants submit their assessments, view aggregated results, and can adjust their positions. The process converges toward consensus or identifies systematic dissent.

**Empirical layer (GGG):** Participants have access to semantically linked data from the Giant Global Graph. Claims can be checked against empirical data in real time. This prevents consensus from being based on false premises — a central problem of both the classical Delphi method and the Rawlsian thought experiment, which provides no empirical foundation.

---

## 4. Application Example: Tax Referendums in Direct Democracy

The Swiss 99% Initiative (referendum of September 26, 2021) illustrates the problem this method could solve. The constitutional text contained two technical terms — "capital income components" (*Kapitaleinkommensteile*) and "taxable at 150 percent" (*im Umfang von 150 Prozent steuerbar*) — that were misunderstood by a substantial portion of voters. Informal tests showed that even finance professionals frequently could not correctly interpret the mechanism.

A Real-Time Delphi based on the GGG could have enabled in this context:

- Anonymous assessment of actual comprehension (functional understanding tests with concrete calculation examples)
- Provision of linked data on wealth and income distribution in Switzerland
- Iterative convergence toward an evidence-based understanding of the proposal before the vote took place

---

## 5. Technical Implementation: zukunft.com

The prototype [zukunft.com](https://zukunft.com) — working title *calc with words* — pursues the technical realization of this approach. Data is available under Creative Commons CC0, program code under GPL3. The project is at the prototype stage.

Source code: [github.com/zukunft](https://github.com/zukunft)

---

## 6. Limitations and Open Questions

The proposed method is not a replacement for democratic votes but a preliminary instrument for improving the quality of decision-making foundations. Open questions include:

- Practical scalability to large numbers of participants
- Quality assurance of data in the Giant Global Graph
- How completely the Veil of Ignorance can actually be established in a digital system
- Acceptance of such a procedure by voters and institutions

---

## References

- Rawls, J. (1971). *A Theory of Justice.* Harvard University Press.
- Gordon, T. J., & Pease, A. (2006). RT Delphi: An efficient, "round-less" almost real time Delphi method. *Technological Forecasting and Social Change*, 73(4), 321–333.
- Berners-Lee, T. (2007). Giant Global Graph. *timbl's blog*, November 2007.
- Huang, K., Greene, J. D., & Bazerman, M. (2019). Veil-of-ignorance reasoning favors the greater good. *Proceedings of the National Academy of Sciences*, 116(48), 23989–23995.

---

*Contact: Timon Zielonka — zukunft.com*
