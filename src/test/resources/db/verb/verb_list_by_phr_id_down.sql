PREPARE verb_list_by_phr_id_down (bigint, bigint) AS
    SELECT
           s.triple_id,
           u.triple_id AS user_triple_id,
           s.user_id,
           s.verb_id,
           l.code_id,
           l.description,
           l.name_plural,
           l.name_reverse,
           l.name_plural_reverse,
           l.formula_name,
           l.words,
           l.verb_name,
           CASE WHEN (u.excluded IS NULL) THEN s.excluded ELSE u.excluded END AS excluded
      FROM triples s
 LEFT JOIN user_triples u ON s.triple_id = u.triple_id
                            AND u.user_id = $1
 LEFT JOIN verbs l           ON s.verb_id = l.verb_id
     WHERE s.to_phrase_id = $2;