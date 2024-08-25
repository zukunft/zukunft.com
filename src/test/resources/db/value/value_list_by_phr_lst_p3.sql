PREPARE value_list_by_phr_lst_p3 (bigint, bigint, bigint, text, text, text, bigint) AS
    SELECT '' AS group_id,
           '' AS user_group_id,
           phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           0 AS user_id,
           numeric_value,
           source_id,
           now() AS last_update,
           0 AS excluded,
           0 AS protect_id,
           0 AS change_user_id,
           0 AS share_type_id
      FROM values_standard_prime
     WHERE (phrase_id_1 = $1 OR phrase_id_2 = $1 OR phrase_id_3 = $1 OR phrase_id_4 = $1)
       AND (phrase_id_1 = $2 OR phrase_id_2 = $2 OR phrase_id_3 = $2 OR phrase_id_4 = $2)
       AND (phrase_id_1 = $3 OR phrase_id_2 = $3 OR phrase_id_3 = $3 OR phrase_id_4 = $3)
UNION
    SELECT group_id,
           '' AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           0 AS user_id,
           numeric_value,
           source_id,
           now() AS last_update,
           0 AS excluded,
           0 AS protect_id,
           0 AS change_user_id,
           0 AS share_type_id
      FROM values_standard
     WHERE group_id like $4
       AND group_id like $5
       AND group_id like $6
UNION
    SELECT s.group_id,
           u.group_id AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           s.user_id,
           CASE WHEN (u.numeric_value IS NULL) THEN s.numeric_value ELSE u.numeric_value END AS numeric_value,
           CASE WHEN (u.source_id     IS NULL) THEN s.source_id     ELSE u.source_id     END AS source_id,
           CASE WHEN (u.last_update   IS NULL) THEN s.last_update   ELSE u.last_update   END AS last_update,
           CASE WHEN (u.excluded      IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
           CASE WHEN (u.protect_id    IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values s
 LEFT JOIN user_values u ON s.group_id = u.group_id AND u.user_id = $7
     WHERE s.group_id like $4
       AND s.group_id like $5
       AND s.group_id like $6
UNION
    SELECT '' AS group_id,
           '' AS user_group_id,
           s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           s.user_id,
           CASE WHEN (u.numeric_value IS NULL) THEN s.numeric_value ELSE u.numeric_value END AS numeric_value,
           CASE WHEN (u.source_id     IS NULL) THEN s.source_id     ELSE u.source_id     END AS source_id,
           CASE WHEN (u.last_update   IS NULL) THEN s.last_update   ELSE u.last_update   END AS last_update,
           CASE WHEN (u.excluded      IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
           CASE WHEN (u.protect_id    IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_prime s
 LEFT JOIN user_values_prime u ON s.phrase_id_1 = u.phrase_id_1
       AND s.phrase_id_2 = u.phrase_id_2
       AND s.phrase_id_3 = u.phrase_id_3
       AND s.phrase_id_4 = u.phrase_id_4 AND u.user_id = $7
     WHERE (s.phrase_id_1 = $1 OR s.phrase_id_2 = $1 OR s.phrase_id_3 = $1 OR s.phrase_id_4 = $1)
       AND (s.phrase_id_1 = $2 OR s.phrase_id_2 = $2 OR s.phrase_id_3 = $2 OR s.phrase_id_4 = $2)
       AND (s.phrase_id_1 = $3 OR s.phrase_id_2 = $3 OR s.phrase_id_3 = $3 OR s.phrase_id_4 = $3)
UNION
    SELECT s.group_id,
           u.group_id AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           s.user_id,
           CASE WHEN (u.numeric_value IS NULL) THEN s.numeric_value ELSE u.numeric_value END AS numeric_value,
           CASE WHEN (u.source_id     IS NULL) THEN s.source_id     ELSE u.source_id     END AS source_id,
           CASE WHEN (u.last_update   IS NULL) THEN s.last_update   ELSE u.last_update   END AS last_update,
           CASE WHEN (u.excluded      IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
           CASE WHEN (u.protect_id    IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_big s
 LEFT JOIN user_values_big u ON s.group_id = u.group_id AND u.user_id = $7
     WHERE s.group_id like $4
       AND s.group_id like $5
       AND s.group_id like $6;
