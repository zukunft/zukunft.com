PREPARE formula_link_list_by_frm_id FROM
    'SELECT s.formula_link_id,
            u.formula_link_id AS user_formula_link_id,
            s.user_id,
            s.formula_id,
            s.phrase_id,
            l.word_type_id AS word_type_id1,
            IF(u.link_type_id   IS NULL, s.link_type_id,   u.link_type_id)  AS link_type_id,
            IF(u.excluded       IS NULL, s.excluded,       u.excluded)      AS excluded,
            IF(u.share_type_id  IS NULL, s.share_type_id,  u.share_type_id) AS share_type_id,
            IF(u.protect_id     IS NULL, s.protect_id,     u.protect_id)    AS protect_id,
            IF(ul.name_used     IS NULL, l.name_used,     ul.name_used)     AS name_used1,
            IF(ul.description   IS NULL, l.description,   ul.description)   AS description1,
            IF(ul.`values`      IS NULL, l.`values`,      ul.`values`)      AS values1,
            IF(ul.excluded      IS NULL, l.excluded,      ul.excluded)      AS excluded1,
            IF(ul.share_type_id IS NULL, l.share_type_id, ul.share_type_id) AS share_type_id1,
            IF(ul.protect_id    IS NULL, l.protect_id,    ul.protect_id)    AS protect_id1
       FROM formula_links s
  LEFT JOIN user_formula_links u ON s.formula_link_id =  u.formula_link_id AND  u.user_id = ?
  LEFT JOIN phrases l            ON s.phrase_id       =  l.phrase_id
  LEFT JOIN user_phrases ul      ON l.phrase_id       = ul.phrase_id       AND ul.user_id = ?
      WHERE s.formula_id = ?';