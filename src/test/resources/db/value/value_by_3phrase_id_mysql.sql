PREPARE value_by_3phrase_id FROM
    'SELECT
            s.group_id,
            u.group_id AS user_group_id,
            s.user_id,
            s.group_id,
            IF(u.numeric_value IS NULL, s.numeric_value, u.numeric_value) AS numeric_value,
            IF(u.source_id     IS NULL, s.source_id,     u.source_id)     AS source_id,
            IF(u.last_update   IS NULL, s.last_update,   u.last_update)   AS last_update,
            IF(u.excluded      IS NULL, s.excluded,      u.excluded)      AS excluded,
            IF(u.protect_id    IS NULL, s.protect_id,    u.protect_id)    AS protect_id,
            u.share_type_id
       FROM `values` s
  LEFT JOIN user_values u ON s.group_id = u.group_id AND u.user_id = ?
      WHERE s.group_id IN (SELECT l1.group_id
                                    FROM group_links l1,
                                         group_links l2
                                   WHERE l1.word_id = ?
                                     AND l1.group_id = l2.group_id
                                     AND l2.word_id = ?)';

