PREPARE word_link_list_by_ids FROM
    'SELECT s.word_link_id,
            u.word_link_id AS user_word_link_id, 
            s.user_id, 
            s.from_phrase_id,
            s.to_phrase_id,
            s.verb_id,
            s.word_type_id,
            s.word_link_condition_id,
            s.word_link_condition_type_id,
            IF(u.word_link_name IS NULL,  s.word_link_name, u.word_link_name) AS word_link_name,
            IF(u.description    IS NULL,  s.description,    u.description)    AS description,
            IF(u.excluded       IS NULL,  s.excluded,       u.excluded)       AS excluded,
            IF(u.share_type_id  IS NULL,  s.share_type_id,  u.share_type_id)  AS share_type_id,
            IF(u.protect_id     IS NULL,  s.protect_id,     u.protect_id)     AS protect_id,
            IF(ul.word_name     IS NULL,  l.word_name,     ul.word_name)      AS word_name,
            IF(ul.plural        IS NULL,  l.plural,        ul.plural)         AS plural,
            IF(ul.description   IS NULL,  l.description,   ul.description)    AS description,
            IF(ul.word_type_id  IS NULL,  l.word_type_id,  ul.word_type_id)   AS word_type_id,
            IF(ul.view_id       IS NULL,  l.view_id,       ul.view_id)        AS view_id,
            IF(ul.excluded      IS NULL,  l.excluded,      ul.excluded)       AS excluded,
            IF(ul2.word_name    IS NULL, l2.word_name,    ul2.word_name)      AS word_name2,
            IF(ul2.plural       IS NULL, l2.plural,       ul2.plural)         AS plural2,
            IF(ul2.description  IS NULL, l2.description,  ul2.description)    AS description2,
            IF(ul2.word_type_id IS NULL, l2.word_type_id, ul2.word_type_id)   AS word_type_id2,
            IF(ul2.view_id      IS NULL, l2.view_id,      ul2.view_id)        AS view_id2,
            IF(ul2.excluded     IS NULL, l2.excluded,     ul2.excluded)       AS excluded2
       FROM word_links s
  LEFT JOIN user_word_links u ON s.word_link_id   =   u.word_link_id AND   u.user_id = ?
  LEFT JOIN words l           ON s.from_phrase_id =   l.word_id
  LEFT JOIN user_words ul     ON l.word_id        =  ul.word_id      AND  ul.user_id = ?
  LEFT JOIN words l2          ON s.to_phrase_id   =  l2.word_id
  LEFT JOIN user_words ul2    ON l2.word_id       = ul2.word_id      AND ul2.user_id = ?
      WHERE s.word_link_id IN (?)
   ORDER BY s.verb_id, word_link_name';