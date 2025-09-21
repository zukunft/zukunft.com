Terms to SPARQL translator (concept)
-------------

Target of the term-to-sparql translator is to use the zukunft.com frontend for the QLever Server. 

Sample: "mountain by country" should lead to this query:

`PREFIX wikibase: <http://wikiba.se/ontology#>
PREFIX psn: <http://www.wikidata.org/prop/statement/value-normalized/>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX p: <http://www.wikidata.org/prop/>
SELECT DISTINCT ?country_name ?mountain_name ?max_height WHERE {
{ SELECT ?country (MAX(?height) AS ?max_height) WHERE {
?mountain wdt:P31/wdt:P279* wd:Q8502 .
?mountain p:P2044/psn:P2044/wikibase:quantityAmount ?height .
?mountain wdt:P17 ?country .
} GROUP BY ?country }
?mountain wdt:P31/wdt:P279* wd:Q8502 .
?mountain p:P2044/psn:P2044/wikibase:quantityAmount ?max_height .
?mountain wdt:P17 ?country .
?mountain rdfs:label ?mountain_name FILTER (LANG(?mountain_name) = "en")
?country rdfs:label ?country_name FILTER (LANG(?country_name) = "en")
}
ORDER BY DESC(?max_height)`

To do the translation these steps should be performed:

1. Take "mountain" and request the best RDF server
2. get "http://wikiba.se/ontology#" which is the fallback server if nothing else is selected
3. Take "mountain" and request the "most relevant selection criteria" from the backend
4. get from backend for "mountain" that the "prime selector" is the "highest peak" formula
5. Take the "highest peak" formula expression "MAX(?height) AS ?max_height" and request the best data server for "height" of "mountain"
6. get "http://www.wikidata.org/entity/" from the pod load balancer
7. Take "http://www.wikidata.org/entity/" and request p, psn, wdt and rdfs servers
8. get "http://www.wikidata.org/prop/", "http://www.wikidata.org/prop/direct/", "http://www.wikidata.org/prop/statement/value-normalized/", "http://www.wikidata.org/prop/direct/" from the wikidata pod cache
9. Take "by" and request the view related to the verb. (Why not "mountain" or "country". Always start with the verb?)
10. get the "3 column table" view from the server, which contains a table with the column "object phrase", "subject phrase", "prime selection value"
11. translate the "object phrase" to "country" based on the original request, "subject phrase" to "mountain name" amd "prime selection value" to "max_height" and use the translated phrases for the columns
12. loop over the columns
13. Take "mountain" and request the properties from the wdt server
14. get "wdt:P31/wdt:P279*"
15. Take "mountain" and request the data item wd server
16. get "wd:Q8502"
17. Take "mountain" and request the data item rdfs server
18. get "rdfs:label ?mountain_name FILTER (LANG(?mountain_name) = 'en')"
19. Take "height" and request the properties from the wdt server
20. get "p:P2044/psn:P2044/wikibase:quantityAmount"
21. Take "height" and request the data item wd server
22. get "?height"
23. Take "height" and request the data item rdfs server
24. get ""
25. Take "country" and request the properties from the wdt server
26. get "wdt:P17"
27. Take "country" and request the data item wd server
28. get "?country"
29. Take "country" and request the data item rdfs server
30. get "rdfs:label ?country_name FILTER (LANG(?country_name) = 'en')"
31. Take "max_height" and request the "default sort"
32. get "DESC"
33. ORDER BY DESC(?max_height)


if the "prime selector" for "mountain" is not yet set, ask the user to set it and remember the answer for all future question. This way any missing knowledge how the query und output should look like is filled interactively. 
