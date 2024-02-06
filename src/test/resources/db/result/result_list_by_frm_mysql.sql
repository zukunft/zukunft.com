PREPARE result_list_by_frm FROM
    'SELECT s.group_id,
            u.group_id AS user_group_id,
            0 AS phrase_id_1,
            0 AS phrase_id_2,
            0 AS phrase_id_3,
            0 AS phrase_id_4,
            s.user_id,
            s.formula_id,
            s.source_group_id,
            IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
            IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
            IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
            IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
            u.user_id AS change_user_id,
            u.share_type_id
       FROM results s
  LEFT JOIN user_results u ON s.group_id = u.group_id AND u.user_id = ?
      WHERE s.formula_id = ?
UNION
     SELECT NULL AS group_id,
            NULL AS user_group_id,
            s.phrase_id_1,
            s.phrase_id_2,
            s.phrase_id_3,
            s.phrase_id_4,
            s.user_id,
            s.formula_id,
            s.source_group_id,
            IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
            IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
            IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
            IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
            u.user_id AS change_user_id,
            u.share_type_id
       FROM results_prime s
  LEFT JOIN user_results_prime u ON s.phrase_id_1 = u.phrase_id_1
                                AND s.phrase_id_2 = u.phrase_id_2
                                AND s.phrase_id_3 = u.phrase_id_3 AND s.phrase_id_4 = u.phrase_id_4 AND u.user_id = ?
      WHERE s.formula_id = ?
UNION
     SELECT s.group_id,
            u.group_id AS user_group_id,
            0 AS phrase_id_1,
            0 AS phrase_id_2,
            0 AS phrase_id_3,
            0 AS phrase_id_4,
            s.user_id,
            s.formula_id,
            s.source_group_id,
            IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
            IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
            IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
            IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
            u.user_id AS change_user_id,
            u.share_type_id
       FROM results_big s
  LEFT JOIN user_results_big u ON s.group_id = u.group_id AND u.user_id = ?
      WHERE s.formula_id = ?';