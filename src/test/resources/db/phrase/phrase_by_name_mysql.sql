PREPARE phrase_by_name FROM
    'SELECT s.phrase_id,
            u.phrase_id AS user_phrase_id,
            s.user_id,
            s.phrase_type_id,
            IF(u.phrase_name    IS NULL, s.phrase_name,    u.phrase_name)    AS phrase_name,
            IF(u.description    IS NULL, s.description,    u.description)    AS description,
            IF(u.`values`       IS NULL, s.`values`,       u.`values`)       AS `values`,
            IF(u.excluded       IS NULL, s.excluded,       u.excluded)       AS excluded,
            IF(u.share_type_id  IS NULL, s.share_type_id,  u.share_type_id)  AS share_type_id,
            IF(u.protect_id IS NULL, s.protect_id, u.protect_id) AS protect_id
       FROM phrases s
  LEFT JOIN user_phrases u ON s.phrase_id = u.phrase_id AND u.user_id = ?
      WHERE s.phrase_name = ?';