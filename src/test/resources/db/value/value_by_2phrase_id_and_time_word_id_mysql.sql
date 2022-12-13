PREPARE value_by_2phrase_id_and_time_word_id FROM
    'SELECT
            s.value_id,
            u.value_id AS user_value_id,
            s.user_id,
            s.phrase_group_id,
            s.time_word_id,
            IF(u.word_value         IS NULL, s.word_value,         u.word_value)         AS word_value,
            IF(u.source_id          IS NULL, s.source_id,          u.source_id)          AS source_id,
            IF(u.last_update        IS NULL, s.last_update,        u.last_update)        AS last_update,
            IF(u.excluded           IS NULL, s.excluded,           u.excluded)           AS excluded,
            IF(u.protect_id IS NULL, s.protect_id, u.protect_id) AS protect_id,
            u.share_type_id
       FROM `values` s
  LEFT JOIN user_values u ON s.value_id = u.value_id
                         AND u.user_id = ?
      WHERE s.phrase_group_id IN (SELECT l1.phrase_group_id
                                    FROM phrase_group_word_links l1
                                   WHERE l1.word_id = ?)
        AND time_word_id = ? ';

