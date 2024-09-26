PREPARE word_list_by_parents_verb_select FROM
   'SELECT
           s.word_id,
           u.word_id AS user_word_id,
           s.user_id,
           s.`values`,
           s.code_id,
           l.verb_id,
           IF(u.word_name      IS NULL, s.word_name,      u.word_name)      AS word_name,
           IF(u.plural         IS NULL, s.plural,         u.plural)         AS plural,
           IF(u.description    IS NULL, s.description,    u.description)    AS description,
           IF(u.phrase_type_id IS NULL, s.phrase_type_id, u.phrase_type_id) AS phrase_type_id,
           IF(u.view_id        IS NULL, s.view_id,        u.view_id)        AS view_id,
           IF(u.excluded       IS NULL, s.excluded,       u.excluded)       AS excluded,
           IF(u.share_type_id  IS NULL, s.share_type_id,  u.share_type_id)  AS share_type_id,
           IF(u.protect_id     IS NULL, s.protect_id,     u.protect_id)     AS protect_id
      FROM words s
 LEFT JOIN user_words u ON s.word_id = u.word_id AND u.user_id = ?
 LEFT JOIN triples l ON s.word_id = l.to_phrase_id
     WHERE l.from_phrase_id IN (?)
       AND l.verb_id = ?
  ORDER BY s.`values` DESC, word_name';
