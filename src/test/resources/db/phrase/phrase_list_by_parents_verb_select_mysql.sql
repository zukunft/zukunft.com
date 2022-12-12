PREPARE phrase_list_by_parents_verb_select FROM
   'SELECT s.phrase_id,
           u.phrase_id AS user_phrase_id,
           s.user_id,
           s.word_type_id,
           l.verb_id,
           IF(u.phrase_name   IS NULL, s.phrase_name,   u.phrase_name)   AS phrase_name,
           IF(u.description   IS NULL, s.description,   u.description)   AS description,
           IF(u.`values`      IS NULL, s.`values`,      u.`values`)      AS `values`,
           IF(u.excluded      IS NULL, s.excluded,      u.excluded)      AS excluded,
           IF(u.share_type_id IS NULL, s.share_type_id, u.share_type_id) AS share_type_id,
           IF(u.protect_id    IS NULL, s.protect_id,    u.protect_id)    AS protect_id
      FROM phrases s
 LEFT JOIN user_phrases u ON s.phrase_id = u.phrase_id AND u.user_id = ?
 LEFT JOIN triples l ON s.phrase_id = l.to_phrase_id
     WHERE l.from_phrase_id IN (?)
       AND l.verb_id = ?
  ORDER BY s.`values` DESC,phrase_name';