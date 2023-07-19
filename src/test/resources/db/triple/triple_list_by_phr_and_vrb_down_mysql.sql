PREPARE triple_list_by_phr_and_vrb_down FROM
    'SELECT s.triple_id,
            u.triple_id AS user_triple_id,
            s.user_id,
            s.from_phrase_id,
            s.to_phrase_id,
            s.verb_id,
            s.phrase_type_id,
            s.triple_condition_id,
            s.triple_condition_type_id,
            l.phrase_type_id AS phrase_type_id1,
            l2.phrase_type_id AS phrase_type_id2,
            IF(u.triple_name     IS NULL,  s.triple_name,     u.triple_name)    AS triple_name,
            IF(u.name_given      IS NULL,  s.name_given,      u.name_given)     AS name_given,
            IF(u.name_generated  IS NULL,  s.name_generated,  u.name_generated) AS name_generated,
            IF(u.description     IS NULL,  s.description,     u.description)    AS description,
            IF(u.`values`        IS NULL,  s.`values`,        u.`values`)       AS `values`,
            IF(u.excluded        IS NULL,  s.excluded,        u.excluded)       AS excluded,
            IF(u.share_type_id   IS NULL,  s.share_type_id,   u.share_type_id)  AS share_type_id,
            IF(u.protect_id      IS NULL,  s.protect_id,      u.protect_id)     AS protect_id,
            IF(ul.phrase_name    IS NULL,  l.phrase_name,    ul.phrase_name)    AS phrase_name1,
            IF(ul.description    IS NULL,  l.description,    ul.description)    AS description1,
            IF(ul.`values`       IS NULL,  l.`values`,       ul.`values`)       AS values1,
            IF(ul.excluded       IS NULL,  l.excluded,       ul.excluded)       AS excluded1,
            IF(ul.share_type_id  IS NULL,  l.share_type_id,  ul.share_type_id)  AS share_type_id1,
            IF(ul.protect_id     IS NULL,  l.protect_id,     ul.protect_id)     AS protect_id1,
            IF(ul2.phrase_name   IS NULL, l2.phrase_name,   ul2.phrase_name)    AS phrase_name2,
            IF(ul2.description   IS NULL, l2.description,   ul2.description)    AS description2,
            IF(ul2.`values`      IS NULL, l2.`values`,      ul2.`values`)       AS values2,
            IF(ul2.excluded      IS NULL, l2.excluded,      ul2.excluded)       AS excluded2,
            IF(ul2.share_type_id IS NULL, l2.share_type_id, ul2.share_type_id)  AS share_type_id2,
            IF(ul2.protect_id    IS NULL, l2.protect_id,    ul2.protect_id)     AS protect_id2
       FROM triples s
  LEFT JOIN user_triples u   ON  s.triple_id      =   u.triple_id AND   u.user_id = ?
  LEFT JOIN phrases l        ON  s.from_phrase_id =   l.phrase_id
  LEFT JOIN user_phrases ul  ON  l.phrase_id      =  ul.phrase_id AND  ul.user_id = ?
  LEFT JOIN phrases l2       ON  s.to_phrase_id   =  l2.phrase_id
  LEFT JOIN user_phrases ul2 ON l2.phrase_id      = ul2.phrase_id AND ul2.user_id = ?
      WHERE s.to_phrase_id = ?
        AND s.verb_id = ?
   ORDER BY s.verb_id, name_given';
