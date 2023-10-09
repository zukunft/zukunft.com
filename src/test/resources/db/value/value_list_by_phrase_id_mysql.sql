PREPARE value_list_by_phrase_id FROM
   'SELECT s.value_id,
           u.value_id AS user_value_id,
           s.user_id,
           s.group_id,
           l.value_id,
           IF(u.numeric_value      IS NULL, s.numeric_value,      u.numeric_value)       AS numeric_value,
           IF(u.source_id          IS NULL, s.source_id,          u.source_id)           AS source_id,
           IF(u.last_update        IS NULL, s.last_update,        u.last_update)         AS last_update,
           IF(u.excluded           IS NULL, s.excluded,           u.excluded)            AS excluded,
           IF(u.protect_id         IS NULL, s.protect_id,         u.protect_id)          AS protect_id,
           u.share_type_id
      FROM `values` s
 LEFT JOIN user_values u         ON s.value_id = u.value_id AND u.user_id = ?
 LEFT JOIN value_phrase_links l  ON s.value_id = l.value_id
     WHERE l.phrase_id = ?';