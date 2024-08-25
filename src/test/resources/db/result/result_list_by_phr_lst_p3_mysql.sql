PREPARE result_list_by_phr_lst_p3 FROM
   'SELECT NULL AS group_id,
           NULL AS user_group_id,
           phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           0 AS phrase_id_4,
           0 AS phrase_id_5,
           0 AS phrase_id_6,
           0 AS phrase_id_7,
           0 AS phrase_id_8,
           0 AS user_id,
           0 AS source_group_id,
           formula_id,
           numeric_value,
           now() AS last_update,
           0 AS excluded,
           0 AS protect_id,
           0 AS change_user_id,
           0 AS share_type_id
      FROM results_standard_prime
     WHERE (phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ?)
       AND (phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ?)
       AND (phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ?)
UNION
    SELECT NULL AS group_id,
           NULL AS user_group_id,
           phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           phrase_id_5,
           phrase_id_6,
           phrase_id_7,
           0 AS phrase_id_8,
           0 AS user_id,
           0 AS source_group_id,
           formula_id,
           numeric_value,
           now() AS last_update,
           0 AS excluded,
           0 AS protect_id,
           0 AS change_user_id,
           0 AS share_type_id
      FROM results_standard_main
     WHERE (phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ? OR phrase_id_4 = ? OR phrase_id_5 = ? OR phrase_id_6 = ? OR phrase_id_7 = ?)
       AND (phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ? OR phrase_id_4 = ? OR phrase_id_5 = ? OR phrase_id_6 = ? OR phrase_id_7 = ?)
       AND (phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ? OR phrase_id_4 = ? OR phrase_id_5 = ? OR phrase_id_6 = ? OR phrase_id_7 = ?)
UNION
    SELECT group_id,
           NULL AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           0 AS phrase_id_5,
           0 AS phrase_id_6,
           0 AS phrase_id_7,
           0 AS phrase_id_8,
           0 AS user_id,
           0 AS source_group_id,
           0 AS formula_id,
           numeric_value,
           now() AS last_update,
           0 AS excluded,
           0 AS protect_id,
           0 AS change_user_id,
           0 AS share_type_id
    FROM results_standard
    WHERE group_id like ?
      AND group_id like ?
      AND group_id like ?
UNION
    SELECT s.group_id,
           u.group_id AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           0 AS phrase_id_5,
           0 AS phrase_id_6,
           0 AS phrase_id_7,
           0 AS phrase_id_8,
           s.user_id,
           s.source_group_id,
           s.formula_id,
           IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM results s
 LEFT JOIN user_results u ON s.group_id = u.group_id AND u.user_id = ?
     WHERE s.group_id like ?
       AND s.group_id like ?
       AND s.group_id like ?
UNION
    SELECT NULL AS group_id,
           NULL AS user_group_id,
           s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           0 AS phrase_id_5,
           0 AS phrase_id_6,
           0 AS phrase_id_7,
           0 AS phrase_id_8,
           s.user_id,
           s.source_group_id,
           s.formula_id,
           IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM results_prime s
 LEFT JOIN user_results_prime u ON s.phrase_id_1 = u.phrase_id_1
                               AND s.phrase_id_2 = u.phrase_id_2
                               AND s.phrase_id_3 = u.phrase_id_3
                               AND s.phrase_id_4 = u.phrase_id_4 AND u.user_id = ?
     WHERE (s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ?)
       AND (s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ?)
       AND (s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ?)
UNION
    SELECT NULL AS group_id,
           NULL AS user_group_id,
           s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           s.phrase_id_5,
           s.phrase_id_6,
           s.phrase_id_7,
           s.phrase_id_8,
           s.user_id,
           s.source_group_id,
           s.formula_id,
           IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM results_main s
 LEFT JOIN user_results_main u ON s.phrase_id_1 = u.phrase_id_1
                              AND s.phrase_id_2 = u.phrase_id_2
                              AND s.phrase_id_3 = u.phrase_id_3
                              AND s.phrase_id_4 = u.phrase_id_4
                              AND s.phrase_id_5 = u.phrase_id_5
                              AND s.phrase_id_6 = u.phrase_id_6
                              AND s.phrase_id_7 = u.phrase_id_7
                              AND s.phrase_id_8 = u.phrase_id_8 AND u.user_id = ?
     WHERE (s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ? OR s.phrase_id_5 = ? OR s.phrase_id_6 = ? OR s.phrase_id_7 = ? OR s.phrase_id_8 = ?)
       AND (s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ? OR s.phrase_id_5 = ? OR s.phrase_id_6 = ? OR s.phrase_id_7 = ? OR s.phrase_id_8 = ?)
       AND (s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ? OR s.phrase_id_5 = ? OR s.phrase_id_6 = ? OR s.phrase_id_7 = ? OR s.phrase_id_8 = ?)
UNION
    SELECT s.group_id,
           u.group_id AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           0 AS phrase_id_5,
           0 AS phrase_id_6,
           0 AS phrase_id_7,
           0 AS phrase_id_8,
           s.user_id,
           s.source_group_id,
           s.formula_id,
           IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM results_big s
 LEFT JOIN user_results_big u ON s.group_id = u.group_id AND u.user_id = ?
     WHERE s.group_id like ?
       AND s.group_id like ?
       AND s.group_id like ?';
