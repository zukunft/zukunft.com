PREPARE value_p1_by_grp FROM
    'SELECT s.phrase_id_1,
            u.phrase_id_1 AS user_phrase_id_1,
            s.user_id,
            IF(u.numeric_value      IS NULL, s.numeric_value,      u.numeric_value)       AS numeric_value,
            IF(u.source_id          IS NULL, s.source_id,          u.source_id)           AS source_id,
            IF(u.last_update        IS NULL, s.last_update,        u.last_update)         AS last_update,
            IF(u.excluded           IS NULL, s.excluded,           u.excluded)            AS excluded,
            IF(u.protect_id         IS NULL, s.protect_id,         u.protect_id)          AS protect_id,
            u.share_type_id
       FROM values_prime s
  LEFT JOIN user_values_prime u ON s.phrase_id_1 = u.phrase_id_1 AND u.user_id = ?
      WHERE s.phrase_id_1 = ?';

