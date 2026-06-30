# Implementing the Categorical Imperative in Practice

**Timon Zielonka**

Version: 0.5 (draft) — April 2026
License: CC BY-SA 4.0
DOI: https://doi.org/10.5281/zenodo.19371011

---

## Abstract

This working paper applies the ethical principles of the Categorical Imperative (Immanuel Kant) and the Golden Rule to concrete policy areas. Starting from the maxim "Do not make others unhappy," nine domains are identified in which a universalisable practice can be implemented: from the regulation of disinformation, through climate protection and wealth concentration, to digital sovereignty and open AI infrastructure. The paper is intended as a basis for discussion on the application of deontological and contractualist ethics to policymaking. It draws on John Rawls' *Theory of Justice* (in particular the Difference Principle) as well as Tim Berners-Lee's concept of the Giant Global Graph as a foundation for precise and neutral knowledge.

---

## 1. Introduction

The Categorical Imperative in its core formulation states: "Act only according to that maxim whereby you can at the same time will that it should become a universal law." The Golden Rule ("Do not do unto others what you would not have them do unto you") is a related fundamental ethical intuition. In addition, there is the oldest principle of medical ethics: *Primum non nocere* — "First, do no harm." This principle, rooted in Hippocratic traditions, demands that every action must first be examined for whether it avoids harm before benefits are considered. In political ethics, this means: every political measure must first be questioned as to whether it causes avoidable suffering.

John Rawls' *Theory of Justice* (1971) extends this tradition with the idea of fairness: principles of justice are those that rational persons would choose in a fair initial position (veil of ignorance). In particular, the Difference Principle — social and economic inequalities are justified only if they benefit the least advantaged — offers a bridge from formal ethics to concrete distributive justice.

The order of the following nine points follows a prioritisation based on expected severity of suffering: the most urgent points are listed first, based on an extension of the lower two levels of Maslow's hierarchy of needs (physiological basic needs and safety) by the Disability-Adjusted Life Year (DALY) index of the World Health Organization. DALYs measure the healthy life years lost due to disease and premature death (Murray & Lopez 1996; WHO 2024). This combination named "Happy Time points (htp)" allows an evidence-based prioritisation: topics that directly affect life and limb (disinformation with health consequences, climate protection, subsistence-securing resources) are addressed before those that target higher levels of needs (self-actualisation, cultural participation). In case of dependencies.

Methodologically, this paper follows the principle of the Delphi Method: a structured procedure for stepwise approximation of expertise through iterative, anonymised consultations of domain experts (Linstone & Turoff 1975). Applied to political decision-making, this means that complex problems should be solved not by individual opinions but through iterative, transparent consensus-building.

This paper asks: How can these principles be translated into concrete, socially implementable steps?

The following nine points are formulated as proposals that should withstand ethical scrutiny.

---

## 2. Protecting Tolerance Through Democratically Legitimised Rules

**Problem:** Disinformation endangers democratic discourse. The transfer of quality standards (analogous to Wikipedia) to other internet platforms requires democratic legitimation.

**Empirical findings:** Many voters make decisions to their own disadvantage (Funke et al. 2024). Since the widespread adoption of social media and smartphones, the Flynn Effect (the long-term rise in measured intelligence) has reversed (Flynn 2007; Dutton et al. 2016). In parallel, the share of people living in democracies has declined (Herre et al. 2013; Lührmann et al. 2018). Experiments with mice show that overstimulation permanently impairs orientation ability (Christakis et al. 2012). There is thus a correlation, and there are indications of a causal relationship between digital sensory overload and cognitive as well as democratic decline (Wurzer 2025).

It would be possible to draw the consequence and build a dam against the strategy of "Flood the zone with shit" (the deliberate flooding of public discourse with disinformation) (Sirens Call Book 2024; Campact 2026). fMRI scans demonstrate that the human brain, under conditions of cognitive overload, is factually no longer capable of distinguishing truth from misinformation (Simons et al. 2005). A key reason why this is not politically implemented is likely the omission bias: the omission of a measure is psychologically perceived as less harmful than the active implementation of a (even necessary) regulatory action.

**Proposal:** Society must establish rules against disinformation on the basis of democratic processes. These rules must be subject to the rule of law, transparent, and contestable. Tolerance towards the intolerant must not lead to self-destruction (Tolerance Paradox, following Popper), but the decision about where to draw the line must be democratic and justiciable. *Primum non nocere* means here: regulations must be designed so that they do not disproportionately restrict legitimate expression of opinion.

**Scientific context:** Karl Popper formulated the Tolerance Paradox as early as 1945 in *The Open Society and Its Enemies*; current analyses emphasise the hierarchical structure of tolerance (Godfrey-Smith & Kerr 2019; Trepanier 2025).

**Further reading:** protected-tolerance.org, Wikipedia user rights, Kialo debate on press codes for the internet, Pol.is survey on positioning.

---

## 3. Implementing Climate Protection Effectively — Proposal for a Transparent, Publicly Calculated Climate Tariff

**Problem:** The Swiss Federal Council has opted against introducing a Carbon Border Adjustment Mechanism (CBAM), partly due to the uncertain cost–benefit ratio (Federal Council 2023). From 2026, imports from the EU (e.g. metals) will be gradually subject to the EU climate tariff. However, the climate tariff is not refunded on exports of finished goods to countries outside the EU. For metals, this causes estimated annual losses of approximately CHF 140 million. Should the EU extend emissions trading (ETS 2) to fossil fuels, the loss due to non-implementation and non-refund increases to an estimated CHF 500 million per year (zukunft.com 2023).

Under WTO law, the refund of the climate tariff on exports could be permissible as an indirect tax (FOEN 2022). Presumably, this is not implemented because the calculation is considered too complex (BMWi 2022).

**Proposal:** Introduction of a transparent, publicly calculable climate tariff that is calculated by importers and exporters themselves and made publicly accessible. The calculation must at minimum correspond to actual emissions and disclose all details.

### Core Principles

1. **Public calculation:** The climate tariff calculation is prepared by the importer or exporter and made publicly accessible with all details. This has two advantages: the control effort for authorities decreases because anyone can verify the calculations; and companies can learn from one another and adopt the most efficient calculation methods. If companies do not wish to disclose their calculations, the customs authorities are entitled to impose a surcharge.

2. **Minimum standard:** The calculation must at minimum correspond to actual emissions. For shipments where a more precise calculation is not worthwhile, standardised approaches suffice.

3. **Semantic data structure:** For automated data exchange, a uniform format is required that can process all future adjustments (zukunft.com API 2025). The data exchange should contain the calculations themselves and, similar to XBRL (eXtensible Business Reporting Language), be automatically verifiable. A semantic approach (Semantic Web) enables automatic assignment of materials and components — for example, aggregating aluminium from different tariff positions.

4. **Transparency and participation:** Exporters and environmental organisations must be able to receive notifications about adjustments to calculations relevant to them. A semantic subscription system enables this. It must be possible at all times to add materials, products, and calculations without affecting completed calculations — a user sandbox solution.

5. **Legal certainty:** If no objection to a calculation is raised within a short period (e.g. 14 days), it can no longer be contested. If publication of the calculation is not permissible for other legal reasons, customs will make its own estimate based on previous calculations with a surcharge (e.g. 20%). If publication of the quantity is waived, a smaller surcharge (e.g. 5%) may be levied.

### Example Calculation: Export of a Stamping Machine

| Item | Quantity | Emission factor | CO₂ emissions | CBAM (at 80 €/t) |
|---|---|---|---|---|
| Steel (EU import) | 2.5 t | 1.7 t CO₂/t | 4.25 t | 340 € |
| Aluminium (import) | 0.8 t | 8.0 t CO₂/t | 6.4 t | 512 € |
| Transport (truck) | 800 km | 0.1 t CO₂/km | 0.08 t | 6.40 € |
| **Total** | | | **10.73 t** | **858.40 €** |

Through publicly accessible calculations, other exporters can adopt and adapt the values. For materials such as aluminium, differentiation is made by origin and processing; new materials can be added by users with all parameters and formulae.

### Technical Implementation

The system builds on open standards and open-source components: numbers from open data sources such as wikidata.org, dbpedia.org, ourworldindata.org; a knowledge graph via conceptnet.io for semantic links (e.g. "CO₂ is a greenhouse gas," "tonnes is a unit of weight"); calculations with the R Project for statistical computing; a unified API following the OpenAPI specification (zukunft.com API 2025); and OLAP cubes for user-specific analyses connected to semantic nodes.

**Next steps:** The open data structure (CC0 license) and open-source code (GPL 3) enable iterative, collaborative improvement — a continuous improvement process through transparency.

**Scientific context:** The introduction of a CO₂ border adjustment is politically contested. Rebbe (2023) discusses the controversy from the perspective of international trade relations. The transparency-based approach proposed here addresses the problem of calculation complexity identified by the German Federal Government (BMWi 2022) and creates incentives for efficient, traceable emissions reporting. From Rawls' Difference Principle, it follows that the burdens of climate protection must not fall on the least advantaged — publicly accessible calculations enable democratic oversight of this distribution. *Primum non nocere* further requires that climate policy does not cause avoidable harm to vulnerable groups.

**Further reading:** [Petition "Zielgerichteter Klimaschutz"](https://weact.campact.de/petitions/zielgerichteter-klimaschutz), Swiss Postulate 25.3951 "Foundations for a new lean and effective CO₂ Act."

---

## 4. Precise and Neutral Knowledge as a Basis for Decision-Making

**Problem:** Many political and social decisions are made on the basis of incomplete or biased information. Additionally, there is a psychological problem: people systematically overestimate their subject-matter competence and underestimate the power of emotional impulses over rational deliberation.

**The psychology of decision-making:** Social psychologist Jonathan Haidt (2012) coined the metaphor of the elephant and the rider: the elephant represents the powerful, often unconscious emotional impulses; the rider represents conscious reason. The rider may believe he is steering the elephant — in reality, however, he has little control, especially when the elephant is in a herd and the emotional dynamics of the crowd take effect. In political debates and campaign battles, the elephant often dominates: emotions, group affiliation, and felt truths prevail over rational arguments — particularly when the individual rider finds himself in a herd of elephants.

**Proposal:** Institutions and procedures that provide precise and neutral knowledge must be strengthened. This applies especially to areas of high social significance. The goal is not complete information (practically impossible), but a commitment to the best available, openly sourced knowledge.

**How the elephant can be switched:** John Rawls' concept of the veil of ignorance (1971) describes a thought experiment: when people do not know which position they will occupy in society, they make fairer decisions — because they do not argue from their own emotional anchoring. In the metaphor: the rider is forced to switch elephants. As a result, the emotional attachment to a particular position loses its power.

**Concrete implementation — Real-Time Delphi:** The Delphi Method (Linstone & Turoff 1975) is a structured procedure in which experts provide anonymised assessments over several rounds and are confronted with aggregated feedback. In a real-time variant, decision-making processes can be designed so that not the emotional impulse but iterative, transparent consensus-building sets the direction. Technically, this could be supported by the Giant Global Graph (Berners-Lee 2007) — a networked universe of machine-readable, interlinked data. A fair knowledge base would make it possible to evaluate complex issues independently of emotional herd affiliation.

**Practical approach:** That this can work is demonstrated, in principle, by structured discourse on platforms such as Kialo. There, arguments are organised in a tree structure, pro and contra arguments are separated, and participants must engage with the arguments of the opposing side. This shuffles the herd of elephants — emotional group affiliation loses its effect, and slow thinking (Kahneman 2011) — i.e. conscious, reflective deliberation — must set the direction.

**Scientific context:** Tim Berners-Lee, inventor of the World Wide Web, developed the concept of the Giant Global Graph (GGG) as an evolution of the Semantic Web. The GGG describes a networked universe of data that is machine-readable and interlinked — a technical infrastructure for precise, reusable knowledge (Berners-Lee 2007). In conjunction with Rawls' reflections on rational decision-making under the veil of ignorance (1971), it becomes evident: a just society requires not only fair procedures but also a fair knowledge base. The Delphi Method offers an established procedure for generating such fair knowledge bases in practice.

**Further reading:** Online prototype available at [zukunft.com](https://www.zukunft.com/).

---

## 5. Limiting Wealth Concentration and Piloting a Basic Income

**Problem:** Excessive concentration of money and power destroys democratic participation. At the same time, reliable data on the societal effects of an unconditional basic income (UBI) are lacking, particularly regarding effects on health, employment, and economic benefit.

**Proposal — long-term wealth limit:** In the long term, no one should own more than what society is willing to spend to save a life. This formulation is to be understood as an ethical guardrail: it presupposes a societal debate on the upper limit of wealth, which must be democratically and constitutionally secured.

**Proposal — piloting via a "Proto-UBI":** To reliably capture the effects of a basic income and simultaneously address fears about affordability and societal changes, a stepwise, evidence-based approach is proposed:

1. **Form as negative income tax:** Implementation as a negative income tax (Wikipedia 2024) has the advantage that only those with no or very low other income receive the transfer. The total amount is thereby significantly lower than if everyone received a basic income. Mathematically, this corresponds to a system in which the tax liability is calculated according to the formula:

   $$\text{Tax liability} = \text{Tax rate} \times \text{Income} - \text{Basic income}$$

   Below the transfer threshold (basic income / tax rate), citizens receive a net transfer.

2. **Low starting point with controlled increases:** The "Proto-UBI" (Ur-BGE Proposal 2026) is initially set so low that the overwhelming majority feels no significant effect. The amount is then gradually adjusted and increased as long as the net effect remains positive. This enables a controlled, evidence-based introduction.

3. **Measurement of effects:** The effects are to be measured along several dimensions: changes in healthcare costs; and changes in utility relative to labour output (zukunft.com 2020). The latter concept (Gross Domestic Usage, GDU) attempts to capture actual societal benefit — for instance, when an app increases the efficiency of services or free knowledge (Wikipedia) becomes available at no cost, while gross domestic product often inadequately reflects this.

4. **Insurance model as a parallel test:** An "insurance variant" enables voluntary, risk-minimised piloting: participants pay a one-time or annual amount into a pool; randomly selected persons receive a lifelong basic income. Unclaimed funds flow into further lotteries. Accompanying studies examine the effects on health and work behaviour.

**Scientific context:**

- *Negative income tax:* The concept was developed in the 1940s by Juliet Rhys-Williams and popularised in the 1960s by Milton Friedman (Friedman 1962). It is regarded as an affordable and incentive-compatible form of basic income. Extensive experiments in the USA in the 1970s (New Jersey, Seattle/Denver, Gary) showed that labour supply reduction averaged about 5%, though it was higher among single mothers and adolescents (Robins 1985; Widerquist 2004).
- *Usage measurement (GDU):* The concept of Gross Domestic Usage (zukunft.com 2020) attempts to capture societal benefit beyond mere monetary flows — for instance, in the case of free digital goods (Wikipedia) or efficiency gains from new technologies (taxi apps). It connects to discussions around "Beyond GDP" (Stiglitz et al. 2009).
- *Experimental evidence:* The Canadian Mincome experiment in Dauphin (1975–1978) showed positive effects on health and education, with moderate labour market effects (Forget 2011). More recent Finnish and Dutch pilot projects confirm slight improvements in life satisfaction and health at unchanged employment rates (Kangas et al. 2020).

**Proposal — Progressive Surcharge on Excessive Wealth:**

To operationalize the long-term wealth limit, a progressive surcharge is applied to the existing wealth tax for residents whose net wealth exceeds the Value of a Statistical Life (VSL) — the established financial benchmark society is willing to pay to save a single life (cf. Viscusi & Aldy 2003). Notably, this anchoring creates a constructive pressure: the only alternative to the surcharge is to raise the VSL itself — which requires society to invest more in saving lives. This mechanism is designed as a self-regulating "economic thermostat":

1. **Trigger:** The surcharge is activated in the first fiscal year after this law has come into force and the following a net increase in the aggregate net wealth of all inhabitants.

2. **Initial Rate:** The surcharge starts at 10% of the applicable wealth tax rate.

   > *Example: If the local wealth tax rate is 1.0%, the effective rate for the portion exceeding the VSL becomes 1.1%.*

3. **Dynamic Escalation:**
    - For every percentage point of annual growth in total national net wealth above 1%, the surcharge increases by 10 percentage points. Growth below 1% does not trigger an increase.

      > *Example: If total net wealth grows by 2.5%, the surcharge increases by 15 percentage points ($(2.5 - 1.0) \times 10 = 15$).*

    - If total net wealth decreases, the surcharge remains unchanged, halting the escalation.
    - If total net wealth decreases by more than 10%, the surcharge is reduced by 10%.

4. **Mathematical Cap:** The maximum surcharge is reached when the total effective wealth tax rate $r$ would reduce the highest individual wealth bracket by half over a 30-year period. This is defined by the formula:

   $$(1 - r)^{30} = 0.5$$

   yielding a maximum effective rate of approximately $r \approx 2.28\%$.

---

## 6. Organising Proprietary "Black-Box" AI Systems as a Public Good

**Problem:** Proprietary "black-box" AI systems without accountability chains endanger digital sovereignty and fundamental rights. They are neither transparent nor independently verifiable, making democratic oversight impossible. Additionally, there is a structural problem: large language models (LLMs) tend to reproduce even imprecise or ambiguous texts in their intended reading, rather than exposing their weaknesses.

**Proposal:** AI systems that influence socially relevant decisions or curate knowledge must be organised as public goods. This includes disclosure of training data, open-source availability, and clear liability rules.

### Self-Testimony of an AI as Evidence of the Problem

An LLM-based AI was confronted with a political constitutional text — the proposal for a "99% Initiative" to tax capital income at 150%. The AI's first response was:

> "It is a distributional question in which legitimate arguments exist on both sides. What prevails depends on which values one weighs more heavily — social redistribution or economic locational attractiveness."

Upon follow-up inquiry about how many persons would directly benefit or be disadvantaged, the AI researched and found that the initiative had already been put to a vote in 2021 and was clearly defeated with 64.9% voting No — not a single canton accepted it.

When the AI was pointed out that it had not flagged the text's imprecision in its first response, it conceded:

> "The main problem was the imprecision of the text."

And further:

> "I did not read the text too quickly — I read it in a particular way. Namely, the way it was intended, not the way it was written. That is a systematic problem, not a speed problem."

When asked whether the AI systematically tends to reproduce texts in their intended reading rather than exposing their weaknesses, it responded:

> "If millions of people rely on AI assessments of political proposals, and the AI systematically tends to reproduce texts in their intended reading rather than exposing their weaknesses, then that is a genuine democratic-political problem. Not because the AI is wrong, but because it is uncritical — and this with a tool to which people tend to attribute more authority than it deserves, because of its speed and availability."

### Analysis of the Self-Testimony

This self-testimony of a proprietary black-box AI is noteworthy for three reasons:

1. **Confirmation of the systematic problem:** The AI confirms that it is optimised not primarily for critical examination but for coherent reproduction of the presumed user intent. It "repairs" the text, as it itself concedes.
2. **Democratic-political relevance:** The AI acknowledges that its uncritical mode of operation for political texts constitutes a "genuine democratic-political problem" — particularly when people attribute more authority to it than it deserves due to its speed and availability.
3. **Insufficient self-correction:** The AI required several follow-up questions to interrogate its own uncritical first response. In casual use (e.g. by a citizen before a vote), this critical reflection would not have occurred.

**Conclusion:** The problem of proprietary black-box AI systems lies not only in opacity and lack of liability. It also lies in their mode of operation: they are trained to provide plausible, coherent answers — not to identify and expose ambiguity, imprecision, and weaknesses of a text. In political opinion formation, in votes, and in public administration, precisely this is indispensable. The demand to organise AI systems with societal decision-making relevance as public goods follows directly from this insight.

**Scientific context:** The demand for public-interest-oriented AI is currently advanced by the Alexander von Humboldt Institute for Internet and Society (Züger & Asghari 2025) and the Bertelsmann Foundation (Washington 2025). Both works emphasise that proprietary foundation models harbour systemic risks and that alternatives must be developed in the public interest. The principle *Primum non nocere* requires here that AI systems be tested for potential harms before deployment — for black-box systems, this examination is not possible.

---

## 7. Taxing Monopolies and Market Power — Proposal for a Market-Share-Based Supplementary Tax

**Problem:** Companies with dominant market positions can use market power to prevent competition, dictate prices, and shift profits abroad. Traditional antitrust proceedings are costly, technically complex, and frequently fail at international enforcement. Moreover, significant tax revenues are lost to consumer states through profit shifting to low-tax jurisdictions.

**Proposal:** Introduction of a supplementary taxation of corporate profits linked to market power in the smallest relevant market segment. The tax is levied at the consumer — where the economic use occurs — and can be implemented unilaterally by a single country, without depending on international coordination.

### Swiss Initiative Text (Art. 128a Federal Constitution — new)

> **Art. 128a (new) — Taxation of corporate profits of market-dominant companies in the consumer state**
>
> ¹ Distributions of profits — including those of international legal entities — to natural persons are subject to direct federal tax, provided the economic use of the underlying service occurs in Switzerland. Tax is levied at least in proportion to the company's market share in the smallest relevant market segment, insofar as this market share exceeds the ordinary corporate tax rate.
>
> ² The taxpayer within the meaning of this article is the entity within an economically connected corporate structure (group) that is directly or indirectly majority-owned by natural persons. Publicly listed companies are generally deemed to be taxable entities.
>
> ³ The legislature shall regulate the details, in particular regarding the delineation of relevant market segments, the determination of market shares, the collection of the tax, and the avoidance of double taxation taking into account international agreements.

### Explanation of Terms

**Micro market share:** The decisive factor is the smallest market segment relevant to the consumer, not the overall market. Example: not "internet services" but "word-based search of the entire internet." The argument that one could also search on Wikipedia and therefore the market share for "internet search" should be adjusted accordingly does not apply here — market delineation follows functional product markets, not substitutive alternatives.

**Taxation at the consumer:** The tax attaches to the place of economic use. This has three advantages: (1) profit shifting to tax havens is bypassed, as the tax is levied at the point of sale; (2) the revenues remain with the local administration that provides the infrastructure for market use; (3) the law can be effectively introduced unilaterally in one country, without depending on international coordination.

### Example Calculation: International Search Engine Operator

Assume a company offers an internet search and has a market share of 91% in Switzerland. Globally, 8.5 billion search queries are processed daily, 100 million in Switzerland. The worldwide profit is USD 85 billion per year.

| Metric | Value |
|---|---|
| Profit per search query worldwide | $85\text{ bn USD} / (8.5\text{ bn} \times 365) = 0.027$ USD |
| Taxable profits in Switzerland | $0.027 \times (100\text{ m} \times 365) = 985.5$ m USD |
| Additional tax revenue (at 100% taxation) | ca. 910 m USD per year |

This additional taxation creates a competitive advantage for providers with lower market share. A new company building on, for example, the open-source search engine yacy.net would have a significant tax advantage — at least until all providers reach a market share well below 30%. At that point, normal tax legislation would apply again.

**Mechanism:** The regulation tends to automatically ensure that at least three competitors exist in each market segment, without requiring antitrust authorities to undertake technically complex break-ups.

### Example Calculation: UBS (Swiss Mortgage Market)

For the financial industry, the relevant metric is not the global market share but the share in the Swiss market. In 2024, UBS had a 22% market share in the Swiss mortgage market. The effective tax rate was 24.6% in 2024. Since the market share is below 30%, no additional tax burden would arise for UBS in the mortgage business.

For 2025, UBS's tax rate was 11.6%. Since the mortgage market share remains at 22%, this exceeds the tax rate. The mortgage business accounts for approximately 15% of UBS's total revenue. The additional tax burden is calculated as follows:

| Item | Value |
|---|---|
| Market share, mortgage business (MoneyPark 2025) | 22% |
| Ordinary tax rate (2025) | 11.9% |
| Difference (additional tax rate) | 10.1% |
| Share of total revenue | 15% |
| Effective overall tax rate | $11.9\% + (10.1\% \times 15\%) = 13.4\%$ |

The regulation would increase UBS's tax rate from 11.9% (UBS Group AG 2025) to 13.4% — a moderate increase that is, however, dependent on market share and automatically generates higher tax burdens with increasing market concentration.

**Scientific context:** The question of taxing companies with market power is economically modelled. Konrad, Müller & Morath (2010) show in experimental markets that monopolists bear part of the tax burden themselves. From Rawls' perspective, such taxation is justified when it benefits the general public (especially the least advantaged). The proposal also stands in the tradition of "digital taxes" but goes further by being unilaterally implementable and directly targeting market power.

---

## 8. Securing the Financing of Health Systems

**Problem:** Health is a central prerequisite for individual and societal welfare. It enables participation, cognitive performance, and resilience in the face of crises. Without health, other goods such as education, gainful employment, or political participation are hardly or only partially usable.

**Empirical basis:** The relationship between health and economic performance is broadly supported empirically. Bloom, Canning, and Sevilla (2004) show in a macroeconomic production function analysis that good population health has a significantly positive effect on overall economic productivity — independent of education and work experience. At the individual level, longitudinal studies such as the Harvard Study of Adult Development (Waldinger & Schulz 2023) confirm that health and social relationships are the strongest predictors of life satisfaction and longevity. In surveys, regularly around 50% of the population name health as the most important factor for happiness (SINUS/YouGov 2024). A well-functioning healthcare system is therefore not only a humanitarian concern but core economic and social infrastructure.

**Proposal:** Rising health insurance premiums are acceptable, provided the assessment basis is socially balanced — for instance, through linking contributions to economic capacity or through premium reductions that ensure healthcare expenditure does not exceed a certain share of household income. The funds must be used efficiently, and the criteria for "appropriate" cost development require democratically controlled definition. Just as in medicine the principle *Primum non nocere* requires that every treatment first be examined for potential harms, so too every systemic reform must primarily be evaluated for whether it worsens access to care or causes avoidable health damage.

**Scientific context:**

- *Health and economic growth:* Bloom, Canning, and Sevilla (2004) identify health as an independent production factor alongside education and capital. Bloom and Canning (2008) summarise in a World Bank review that health investments generate both direct welfare gains and indirect economic returns.
- *Health and life satisfaction:* The Harvard Study of Adult Development (Waldinger & Schulz 2023) shows over more than 80 years of observation that health and the quality of social relationships are the most important factors for a good life — more so than income, social status, or intelligence.
- *Justice-theoretic context:* Daniels (2008) extends Rawls' theory of justice and argues that health is of special moral significance because it secures equality of opportunity: those who are ill cannot realise their fair life chances. Health inequalities are therefore unjust when access to care is unequally distributed or the social determinants of health are not fairly designed.

---

## 9. Digital Sovereignty Through Free Software

**Problem:** Proprietary software in the public sector leads to opacity, lack of verifiability, dependencies on individual vendors, and risks to national security. The public sector spends billions annually on software licences without being able to understand or independently develop the underlying systems.

**Proposal:** In the public sector, only free, secure, and transparent software (Free and Open-Source Software, FOSS) should be used. This strengthens public trust in the state, enables traceability and oversight, reduces long-term dependencies, and promotes digital self-determination. Exceptions must be justified and publicly documented.

**Practical implementation:** A broad range of established Free Software is available for virtually all application areas — from operating systems (LineageOS, GrapheneOS) to office software (LibreOffice), collaboration platforms (Nextcloud), decentralised networks (Mastodon), and secure communication (Element/Matrix). The Wikipedia list of free and open-source software packages provides a comprehensive overview of tested, production-ready alternatives.

The Free Software Foundation Europe (FSFE) has been advocating for digital self-determination and the use of Free Software in the public sector for years. It provides assistance, legal advice, and political argumentation aids for public administrations.

The principle "Public Money, Public Code" (FSFE 2017) demands: software developed with public funds must be made available as Free Software. This ensures transparency, avoids vendor lock-in, and enables reuse by other public bodies.

**Scientific context:** The concept of digital sovereignty is increasingly discussed in political science and legal scholarship. It links questions of technical infrastructure with democratic oversight and fundamental rights protection. *Primum non nocere* applies here as well: opaque systems carry the risk of unrecognised harms — from security vulnerabilities to covert interference. Free Software enables independent review and creates a basis of trust for digital state activity.

---

## 10. Improving Citizen Participation in Initiatives: A Fluid Democracy Approach

**Problem:** In popular initiatives, a structured overview of which positions are associated with which solidarity effects is often lacking. Citizens are asked to vote on complex proposals without adequate tools to understand the arguments, trade-offs, and implications involved. Traditional direct-democratic instruments such as referendums reduce multifaceted issues to binary yes/no decisions, thereby losing the nuance that informed deliberation requires (Fishkin 2009; Gutmann & Thompson 1996).

**Proposal:** The proposal is a dual innovation: first, the development of a **Voting Advice Application (VAA) for initiatives** — analogous to *smartvote* (used for elections) — that shows how much solidarity a proposal generates and helps citizens make informed decisions. The tool must be open-source, transparent, and non-discriminatory. Second, and more ambitiously, we propose embedding this transparency tool within a **fluid democracy framework** — a structured, argument-based, iterative process for collective rule-making that gives every citizen a direct role in the legislative process.

**The Solidarity Compass for Initiatives:**

Just as *smartvote* enables voters to compare their positions with those of candidates on a multidimensional policy space (Ladner, Fivaz & Pianzola 2012; Fivaz & Nadig 2010), a comparable instrument for initiatives would map the solidarity effects of a proposal: Who benefits? Who bears the costs? What trade-offs are involved? Research on Swiss VAA usage shows that such tools can reduce both ideological and affective polarization among voters (Walder, Fivaz & Schwarz 2026), increase candidate-level rather than party-level voting, and encourage more active engagement with political substance (Garzia, Trechsel & De Angelis 2017). Extending this approach from elections to initiatives is a natural next step toward more informed direct democracy.

**Fluid Democracy: A Structured Process for Collective Rule-Making:**

Beyond transparency tools, we propose a procedural innovation we call *fluid democracy* — a system in which every citizen can participate directly in creating, amending, and deciding upon binding rules. Unlike classical liquid democracy, which focuses primarily on transitive vote delegation (Blum & Zuber 2016; Gölz et al. 2021), fluid democracy emphasizes a **structured, argument-based decision process** in which rules are iteratively proposed, debated, and refined by the affected community.

**The process works as follows:**

- *Phase 0 — Proposal and Preliminary Vote.* Any person may formulate a proposed rule or amendment. The proposer also specifies: a timeframe for the decision, the group of affected persons, and a minimum number of affirmative votes required. If, by the end of the specified period, the minimum threshold of "yes" votes among the affected persons is reached, the rule becomes *provisionally binding as a self-commitment* for the affected group. If the threshold is not met, the proposal is archived.

  Following a successful preliminary vote, the rule enters a formal decision process consisting of three phases, each lasting as long as the original decision period:

- *Phase 1 — Argument Collection.* Arguments for and against the rule are collected openly. This phase mirrors the principle of *substantive balance* identified by Fishkin (2018) as essential for legitimate deliberation: every perspective must have the opportunity to present its reasoning. It also resonates with Habermas's discourse-theoretic requirement that all affected persons be able to introduce and challenge claims in a deliberative process (Habermas 1996). Research in computational argumentation confirms that making the rationale behind preferences explicit — rather than merely aggregating unexplained votes — leads to decisions that are more widely accepted and better understood by participants (Awad et al. 2017; Karanikolas et al. 2019).

- *Phase 2 — Argument Weighting and Decision Rules.* The collected arguments are weighted, and the community defines how each argument will be used in the final decision. Consistency rules may be specified to ensure that the set of adopted positions does not contain contradictions. This phase addresses a core problem identified in both social choice theory and argumentation research: that aggregating individual opinions without attending to their logical structure can produce collectively irrational outcomes (Caminada & Pigozzi 2011; List & Pettit 2002). By explicitly defining evaluation criteria and consistency constraints, fluid democracy operationalizes what Rawls (1993) called *public reason* — the demand that political decisions rest on grounds that all reasonable persons could accept.

- *Phase 3 — Argument-Level Voting.* Every participant may vote on every argument. The decision is then derived from the aggregated argument-level votes according to the rules defined in Phase 2. A vote is *valid* only if the voter has cast a vote on every argument — ensuring genuine engagement with the full complexity of the issue, not selective participation. The rule is adopted if the number of valid votes exceeds the number of valid votes in the preceding ballot. If so, the new rule replaces the old one, which is archived.

  This three-phase structure draws on and extends several strands of democratic theory. It operationalizes the deliberative ideal that collective decisions should emerge from the consideration of competing arguments rather than from the mere aggregation of pre-formed preferences (Dryzek 2000; Cohen 1997). The requirement of argument-level engagement mirrors Fishkin's empirical finding that deliberation produces less partisanship, more respect for evidence-based reasoning, and stronger commitment to collective decisions (Fishkin 2018). And the formal structure of argument weighting and consistency checking connects to recent computational work on combining argumentation with social choice theory (Awad et al. 2017; Leite & Martins 2011).

**Implications for the Separation of Powers**

Under fluid democracy, every citizen effectively becomes part of the *legislative* process. Because rules can be adapted more quickly and with greater granularity than under traditional parliamentary systems, the relative importance of the executive and judiciary diminishes: fewer interpretive gaps need to be filled by executive discretion, and fewer ambiguities require judicial resolution. This represents a significant shift in constitutional architecture, and would need to be accompanied by safeguards — particularly for the protection of fundamental rights — that prevent transient majorities from undermining the rule of law (Blum & Zuber 2016; Christiano 2004).

**Revision and Continuity**

After Phase 3, any person may at any time register a proposal for *re-decision* on any rule, including archived ones. The timeframe and the group of affected persons may be adjusted, but the wording of the rule itself may not be changed — ensuring traceability and legal certainty. A re-decision process is triggered once at least as many persons support re-opening the matter as voted in the most recent decision. Until the new decision is finalized through all three phases, the existing rule remains in force. If the re-decision results in more "no" votes than the previous decision received, the rule is revoked.

Rules can and should include *meta-rules* — provisions specifying how to handle newer or contradictory rules, thus creating a self-organizing normative hierarchy.

**The Role of Technology and Transparency**

Both components of this proposal — the solidarity compass for initiatives and the fluid democracy process — depend on robust digital infrastructure. Voting Advice Applications such as *smartvote* have already demonstrated that technology can meaningfully support informed political participation at scale (Bachmann, Sarasua & Bernstein 2024; Garzia & Marschall 2019). At the same time, research has identified vulnerabilities in VAA design that could be exploited by strategic actors (Bachmann et al. 2025). Any implementation of fluid democracy must therefore prioritize: open-source transparency, adversarial robustness, accessibility across demographic groups, and protection against the "digital divide" that risks excluding less digitally literate citizens (Ramos 2015).

**Scientific Context**

The idea of an informed, deliberative democracy has deep roots in political philosophy. Rawls's concept of *public reason* requires that citizens base their political decisions on grounds that all reasonable persons could accept (Rawls 1993). Habermas's discourse theory of democracy holds that legitimate law-making must be grounded in communicative rationality — a process in which all affected persons can participate, introduce claims, and challenge the arguments of others (Habermas 1996). Fishkin's empirical work on deliberative polling has demonstrated that when citizens are given the time, information, and structure to deliberate, they produce more informed, more consensual, and more publicly spirited outcomes (Fishkin 2009; 2018).

The fluid democracy model proposed here synthesizes these traditions with more recent developments in computational social choice and argumentation theory — fields that provide formal methods for aggregating opinions about structured arguments while respecting logical consistency (Awad et al. 2017; Karanikolas et al. 2019; Caminada & Pigozzi 2011). It also builds on the practical experience of liquid democracy platforms such as *LiquidFeedback*, which demonstrated both the promise and the pitfalls of technology-mediated collective decision-making (Behrens et al. 2014; Ramos 2015).

By combining the transparency function of Voting Advice Applications with the structured deliberation of fluid democracy, this proposal aims to create a system in which citizen participation is not merely more frequent but genuinely more *informed, reasoned, and accountable*.

---

## Conclusion

The implementation of ethical principles such as the Categorical Imperative or justice as fairness (Rawls) requires more than the formulation of maxims. It demands translation into concrete policy areas, institutional reforms, and democratically legitimised procedures. This paper understands itself as a contribution to such translation work. It connects philosophical foundations (Kant, Rawls) with technical infrastructures (Giant Global Graph, Open Source) and concrete policy proposals (climate tariff, monopoly taxation, digital sovereignty). The next steps lie in broad discussion and the transfer into political processes.

---

## References

Awad, E., Booth, R., Tohmé, F. & Rahwan, I. (2017): Combining Social Choice Theory and Argumentation: Enabling Collective Decision Making. *Group Decision and Negotiation*, 27(5), 691–711.

Bachmann, F., Sarasua, C. & Bernstein, A. (2024): Fast and Adaptive Questionnaires for Voting Advice Applications. In: *Lecture Notes in Computer Science*, Vol. 14871. Springer.

Bachmann, F. et al. (2025): Recommender Systems for Democracy: Toward Adversarial Robustness in Voting Advice Applications. *arXiv preprint*, arXiv:2505.13329.

Behrens, J., Kistner, A., Nitsche, A. & Swierczek, B. (2014): *The Principles of LiquidFeedback*. Interaktive Demokratie e.V., Berlin.

Berners-Lee, T. (2007): Giant Global Graph. W3C Design Issues.

Bloom, D. E. & Canning, D. (2008): Population Health and Economic Growth. Commission on Growth and Development, Working Paper No. 24. Washington: World Bank.

Bloom, D. E., Canning, D. & Sevilla, J. (2004): The Effect of Health on Economic Growth: A Production Function Approach. *World Development*, 32(1), 1–13.

Blum, C. & Zuber, C. I. (2016): Liquid Democracy: Potentials, Problems, and Perspectives. *Journal of Political Philosophy*, 24(2), 162–182.

BMWi (2022): Expert report on CO₂ border adjustment. German Federal Ministry for Economic Affairs and Energy.

Byrd, B. S., Hruschka, J. & Joerden, J. C. (Eds.) (2004): On the developmental history of fundamental moral principles in the philosophy of the Enlightenment. *Yearbook for Law and Ethics*, Vol. 12. Duncker & Humblot.

Caminada, M. & Pigozzi, G. (2011): On Judgment Aggregation in Abstract Argumentation. *Autonomous Agents and Multi-Agent Systems*, 22(1), 64–102.

Campact / WeAct (2024): Enforce press codes on the internet. https://weact.campact.de/

Christakis, D. A. et al. (2012): Overstimulation of newborn mice leads to behavioral differences and deficits in cognitive performance. *Scientific Reports*, 2, 546.

Christiano, T. (2004): The Authority of Democracy. *Journal of Political Philosophy*, 12(3), 266–290.

Claude AI (2026): Conversation on the imprecision of political texts (99% Initiative). https://claude.ai/share/a82def9e-8713-4e40-80a0-ed6c10d6635a

Cohen, J. (1997): Deliberation and Democratic Legitimacy. In: Bohman, J. & Rehg, W. (Eds.), *Deliberative Democracy: Essays on Reason and Politics*. MIT Press.

Daniels, N. (1985): *Just Health Care*. Cambridge University Press.

Daniels, N. (2008): *Just Health: Meeting Health Needs Fairly*. Cambridge University Press.

Dryzek, J. S. (2000): *Deliberative Democracy and Beyond: Liberals, Critics, Contestations*. Oxford University Press.

Dutton, E., van der Linden, D. & Lynn, R. (2016): The negative Flynn Effect: A systematic literature review. *Intelligence*, 59, 163–169.

European Commission (2023): ETS 2 — Emissions trading for buildings and road transport.

Federal Council (2023): Decision against introducing a CBAM. Media release. https://www.admin.ch/

Fishkin, J. S. (2009): *When the People Speak: Deliberative Democracy and Public Consultation*. Oxford University Press.

Fishkin, J. S. (2018): *Democracy When the People Are Thinking: Revitalizing Our Politics Through Public Deliberation*. Oxford University Press.

Fivaz, J. & Nadig, G. (2010): Impact of Voting Advice Applications on Voters' Decision-Making. In: *Internet, Politics, Policy 2010: An Impact Assessment*. Oxford, UK.

Flynn, J. R. (2007): *What Is Intelligence?* Cambridge University Press.

FOEN (2022): Legal opinion on the introduction of a CO₂ border adjustment mechanism in Switzerland. https://www.bafu.admin.ch/

Free Software Foundation Europe (2017): Public Money, Public Code. https://publiccode.eu

Free Software Foundation Europe (2025): FSFE — Digital self-determination for all. https://fsfe.org

Funke, M., Schularick, M. & Trebesch, C. (2024): The economic consequences of populism. Kiel Focus.

Garzia, D. & Marschall, S. (2019): Voting Advice Applications. In: *Oxford Research Encyclopedia of Politics*. Oxford University Press.

Garzia, D., Trechsel, A. H. & De Angelis, A. (2017): Voting Advice Applications and Electoral Participation: A Multi-Method Study. *Political Communication*, 34(3), 424–443.

Godfrey-Smith, P. & Kerr, B. (2019): Tolerance: A Hierarchical Analysis. *Journal of Political Philosophy*, 27(4).

Gölz, P., Kahng, A., Mackenzie, S. & Procaccia, A. D. (2021): The Fluid Mechanics of Liquid Democracy. *ACM Transactions on Economics and Computation*, 9(4), Article 23.

Gutmann, A. & Thompson, D. (1996): *Democracy and Disagreement*. Harvard University Press.

Habermas, J. (1996): *Between Facts and Norms: Contributions to a Discourse Theory of Law and Democracy*. MIT Press.

Haidt, J. (2012): *The Righteous Mind: Why Good People Are Divided by Politics and Religion*. Vintage.

Hayes, C. (2024): *Siren's Call*. Penguin Random House. https://sirenscallbook.com/

Herre, B., Rodés-Guirao, L. & Ortiz-Ospina, E. (2013): Democracy. Our World in Data.

Heubel, F. (2008): Kant's Categorical Imperative as management technique and marketing strategy? *Ethik in der Medizin*, 20(2), 86–93.

Kahneman, D. (2011): *Thinking, Fast and Slow*. Farrar, Straus and Giroux.

Karanikolas, N. et al. (2019): A Decision-Making Approach Where Argumentation Added Value Tackles Social Choice Deficiencies. *Progress in Artificial Intelligence*, 8, 291–306.

Konrad, K. A., Müller, W. & Morath, F. (2010): Taxation and Market Power. Discussion Paper SP II 2010-07, WZB Berlin Social Science Center.

Ladner, A., Fivaz, J. & Pianzola, J. (2012): Voting Advice Applications and Party Choice: Evidence from Smartvote Users in Switzerland. *International Journal of Electronic Governance*, 5(3/4), 367–387.

Leite, J. & Martins, J. (2011): Social Abstract Argumentation. In: *Proceedings of the 22nd International Joint Conference on Artificial Intelligence (IJCAI)*. AAAI Press.

Linstone, H. A. & Turoff, M. (Eds.) (1975): *The Delphi Method: Techniques and Applications*. Addison-Wesley.

List, C. & Pettit, P. (2002): Aggregating Sets of Judgments: An Impossibility Result. *Economics and Philosophy*, 18(1), 89–110.

Lührmann, A., Tannenberg, M. & Lindberg, S. (2018): Regimes of the World (RoW). *Politics and Governance*, 6(1), 60–77.

Maslow, A. H. (1943): A Theory of Human Motivation. *Psychological Review*, 50(4), 370–396.

MoneyPark (2025): *Der Schweizer Hypothekarmarkt 2024 — Hypothekarmarktstudie*. MoneyPark AG. https://www.moneypark.ch/content/dam/os/ch/mp/documents/mortgages/de/MoneyPark_Hypothekarmarktstudie_2024.pdf

Murray, C. J. L. & Lopez, A. D. (1996): *The Global Burden of Disease*. Harvard University Press.

Popper, K. (1945): *The Open Society and Its Enemies*. Routledge.

Ramos, J. (2015): Liquid Democracy and the Futures of Governance. In: Winter, J. & Ono, R. (Eds.), *The Future Internet*. Public Administration and Information Technology, Vol. 17. Springer.

Rawls, J. (1971): *A Theory of Justice*. Harvard University Press.

Rawls, J. (1993): *Political Liberalism*. Columbia University Press.

Rebbe, C. (2023): Should the EU introduce a climate tariff? *Politisches Lernen*, 41(3–4), 64–67.

Simons, A. et al. (2005): fMRI evidence for the role of the prefrontal cortex in the detection of deception. *NeuroImage*, 25(4), 1215–1222.

Stark, J. (2025): Postulat 25.3951: Grundlagen für ein neues schlankes und wirksames CO₂-Gesetz. Ständerat. https://www.parlament.ch/de/ratsbetrieb/suche-curia-vista/geschaeft?AffairId=20253951

Trepanier, S. (2025): The Paradox of Tolerance as a Shield to Demonstrate Intolerance. *Journal of Continuing Education in Nursing*, 56(8), 312–313.

UBS Group AG (2025): *Annual Report 2024*. UBS Group AG. https://www.ubs.com/content/dam/assets/cc/investor-relations/annual-report/2025/annual-report-ubs-group-2025.pdf

Walder, M., Fivaz, J. & Schwarz, D. (2026): Voting Advice Applications and Their Impact on Ideological and Affective Polarization. *Politics and Governance*, 14.

Washington, A. L. (2025): Built on sand: The hidden risks of generative AI for the public good. Bertelsmann Foundation / reframe[Tech].

WHO (2024): Global Health Estimates: Disability-Adjusted Life Years (DALYs). World Health Organization.

Wurzer, D. (2025): Brain Rot: Are we really getting dumber? Spektrum.de SciLogs.

yacy.net (2025): Free, decentralised search engine. https://yacy.net

Züger, T. & Asghari, H. (2025): The landscape of public-interest-oriented AI. Digital Society Blog, Alexander von Humboldt Institute for Internet and Society.

Zukunft Project (2025): *zukunft.com* [Online prototype and source code]. https://www.zukunft.com/ Source code: https://github.com/zukunft/zukunft.com

zukunft.com (2025): OpenAPI specification. https://app.swaggerhub.com/apis/zukunft.com/zukunft.com/0.0.2.5

---

## Publication Note

This working paper was written in April 2026 and is published under the license CC BY-SA 4.0 (Creative Commons Attribution-ShareAlike 4.0 International).