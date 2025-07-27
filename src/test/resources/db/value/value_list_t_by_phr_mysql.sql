PREPARE value_list_t_by_phr FROM
   'SELECT NULL AS group_id,
           NULL AS user_group_id,
           phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           0 AS user_id,
           text_value,
           source_id,
           now() AS last_update,
           0 AS excluded,
           0 AS protect_id,
           0 AS change_user_id,
           0 AS share_type_id
      FROM values_text_standard_prime
     WHERE (phrase_id_1 = ? OR phrase_id_2 = ? OR phrase_id_3 = ? OR phrase_id_4 = ?)
UNION
    SELECT group_id,
           NULL AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           0 AS user_id,
           text_value,
           source_id,
           now() AS last_update,
           0 AS excluded,
           0 AS protect_id,
           0 AS change_user_id,
           0 AS share_type_id
      FROM values_text_standard
     WHERE group_id like ?
UNION
    SELECT s.group_id,
           u.group_id AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           s.user_id,
           IF(u.text_value IS NULL,  s.text_value, u.text_value) AS text_value,
           IF(u.source_id     IS NULL,  s.source_id,     u.source_id)     AS source_id,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_text s
 LEFT JOIN user_values_text u ON s.group_id = u.group_id AND u.user_id = ?
     WHERE s.group_id like ?
UNION
    SELECT NULL AS group_id,
           NULL AS user_group_id,
           s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           s.user_id,
           IF(u.text_value IS NULL,  s.text_value, u.text_value) AS text_value,
           IF(u.source_id     IS NULL,  s.source_id,     u.source_id)     AS source_id,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_text_prime s
 LEFT JOIN user_values_text_prime u ON s.phrase_id_1 = u.phrase_id_1
       AND s.phrase_id_2 = u.phrase_id_2
       AND s.phrase_id_3 = u.phrase_id_3
       AND s.phrase_id_4 = u.phrase_id_4 AND u.user_id = ?
     WHERE (s.phrase_id_1 = ? OR s.phrase_id_2 = ? OR s.phrase_id_3 = ? OR s.phrase_id_4 = ?)
UNION
    SELECT s.group_id,
           u.group_id AS user_group_id,
           0 AS phrase_id_1,
           0 AS phrase_id_2,
           0 AS phrase_id_3,
           0 AS phrase_id_4,
           s.user_id,
           IF(u.text_value IS NULL,  s.text_value, u.text_value) AS text_value,
           IF(u.source_id     IS NULL,  s.source_id,     u.source_id)     AS source_id,
           IF(u.last_update   IS NULL,  s.last_update,   u.last_update)   AS last_update,
           IF(u.excluded      IS NULL,  s.excluded,      u.excluded)      AS excluded,
           IF(u.protect_id    IS NULL,  s.protect_id,    u.protect_id)    AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_text_big s
 LEFT JOIN user_values_text_big u ON s.group_id = u.group_id AND u.user_id = ?
     WHERE s.group_id like ?';
