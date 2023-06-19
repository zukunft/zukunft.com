PREPARE value_list_by_2ids FROM
   'SELECT s.value_id,
           u.value_id AS user_value_id,
           s.user_id,
           s.phrase_group_id,
           IF(u.word_value         IS NULL, s.word_value,         u.word_value)          AS word_value,
           IF(u.source_id          IS NULL, s.source_id,          u.source_id)           AS source_id,
           IF(u.last_update        IS NULL, s.last_update,        u.last_update)         AS last_update,
           IF(u.excluded           IS NULL, s.excluded,           u.excluded)            AS excluded,
           IF(u.protect_id IS NULL, s.protect_id, u.protect_id)  AS protect_id,
           u.share_type_id
      FROM `values` s
 LEFT JOIN user_values u         ON s.value_id = u.value_id AND u.user_id = ?
     WHERE s.value_id IN (?,?)
  ORDER BY s.value_id';