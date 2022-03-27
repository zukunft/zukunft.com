PREPARE verb_list_by_phr_id_down (int, int) AS
    SELECT
           s.word_link_id,
           u.word_link_id AS user_word_link_id,
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
      FROM word_links s
 LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id
                            AND u.user_id = $1
 LEFT JOIN verbs l           ON s.verb_id = l.verb_id
     WHERE s.to_phrase_id = $2;