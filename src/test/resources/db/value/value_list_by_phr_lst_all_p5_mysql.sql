PREPARE value_list_by_phr_lst_all_p5 FROM
   'SELECT NULL AS group_id,
           NULL AS user_group_id,
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
     WHERE phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ? OR phrase_id_4 = ?
        OR phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ? OR phrase_id_4 = ?
        OR phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ? OR phrase_id_4 = ?
        OR phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ? OR phrase_id_4 = ?
        OR phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ? OR phrase_id_4 = ?
UNION
    SELECT group_id,
           NULL AS user_group_id,
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
     WHERE group_id like ?
        OR group_id like ?
        OR group_id like ?
        OR group_id like ?
        OR group_id like ?
UNION
    SELECT s.group_id,
           u.group_id AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           s.user_id,
           IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
           IF(u.source_id     IS NULL,  s.source_id,     u.source_id)     AS source_id,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM `values` s
 LEFT JOIN user_values u ON s.group_id = u.group_id AND u.user_id = ?
     WHERE s.group_id like ?
        OR s.group_id like ?
        OR s.group_id like ?
        OR s.group_id like ?
        OR s.group_id like ?
UNION
    SELECT NULL AS group_id,
           NULL AS user_group_id,
           s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           s.user_id,
           IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
           IF(u.source_id     IS NULL,  s.source_id,     u.source_id)     AS source_id,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_prime s
 LEFT JOIN user_values_prime u ON s.phrase_id_1 = u.phrase_id_1
       AND s.phrase_id_2 = u.phrase_id_2
       AND s.phrase_id_3 = u.phrase_id_3
       AND s.phrase_id_4 = u.phrase_id_4 AND u.user_id = ?
     WHERE s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ?
        OR s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ?
        OR s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ?
        OR s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ?
        OR s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ?
UNION
    SELECT s.group_id,
           u.group_id AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           s.user_id,
           IF(u.numeric_value IS NULL,  s.numeric_value, u.numeric_value) AS numeric_value,
           IF(u.source_id     IS NULL,  s.source_id,     u.source_id)     AS source_id,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_big s
 LEFT JOIN user_values_big u ON s.group_id = u.group_id AND u.user_id = ?
    WHERE s.group_id like ?
       OR s.group_id like ?
       OR s.group_id like ?
       OR s.group_id like ?
       OR s.group_id like ?';
