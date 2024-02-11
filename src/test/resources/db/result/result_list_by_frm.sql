PREPARE result_list_by_frm (bigint, bigint) AS
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
           CASE WHEN (u.numeric_value IS NULL) THEN s.numeric_value ELSE u.numeric_value END AS numeric_value,
           CASE WHEN (u.last_update   IS NULL) THEN s.last_update   ELSE u.last_update   END AS last_update,
           CASE WHEN (u.excluded      IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
           CASE WHEN (u.protect_id    IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM results s
 LEFT JOIN user_results u ON s.group_id = u.group_id AND u.user_id = $1
     WHERE s.formula_id = $2
UNION
    SELECT '' AS group_id,
           '' AS user_group_id,
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
           CASE WHEN (u.numeric_value IS NULL) THEN s.numeric_value ELSE u.numeric_value END AS numeric_value,
           CASE WHEN (u.last_update   IS NULL) THEN s.last_update   ELSE u.last_update   END AS last_update,
           CASE WHEN (u.excluded      IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
           CASE WHEN (u.protect_id    IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM results_prime s
 LEFT JOIN user_results_prime u ON s.phrase_id_1 = u.phrase_id_1
                               AND s.phrase_id_2 = u.phrase_id_2
                               AND s.phrase_id_3 = u.phrase_id_3
                               AND s.phrase_id_4 = u.phrase_id_4
                               AND u.user_id = $1
    WHERE s.formula_id = $2
UNION
    SELECT '' AS group_id,
           '' AS user_group_id,
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
           CASE WHEN (u.numeric_value IS NULL) THEN s.numeric_value ELSE u.numeric_value END AS numeric_value,
           CASE WHEN (u.last_update   IS NULL) THEN s.last_update   ELSE u.last_update   END AS last_update,
           CASE WHEN (u.excluded      IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
           CASE WHEN (u.protect_id    IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id,
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
                               AND s.phrase_id_8 = u.phrase_id_8
                               AND u.user_id = $1
     WHERE s.formula_id = $2
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
           CASE WHEN (u.numeric_value IS NULL) THEN s.numeric_value ELSE u.numeric_value END AS numeric_value,
           CASE WHEN (u.last_update   IS NULL) THEN s.last_update   ELSE u.last_update   END AS last_update,
           CASE WHEN (u.excluded      IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
           CASE WHEN (u.protect_id    IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM results_big s
 LEFT JOIN user_results_big u ON s.group_id = u.group_id AND u.user_id = $1
    WHERE s.formula_id = $2;