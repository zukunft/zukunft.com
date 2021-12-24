PREPARE value_list_by_word_id (int, int, int) AS
    SELECT s.value_id,
           u.value_id AS user_value_id,
           s.user_id,
           s.phrase_group_id,
           s.time_word_id,
           l.phrase_group_id,
           l2.word_id,
           CASE WHEN (u.word_value         IS NULL) THEN s.word_value         ELSE u.word_value         END AS word_value,
           CASE WHEN (u.source_id          IS NULL) THEN s.source_id          ELSE u.source_id          END AS source_id,
           CASE WHEN (u.last_update        IS NULL) THEN s.last_update        ELSE u.last_update        END AS last_update,
           CASE WHEN (u.excluded           IS NULL) THEN s.excluded           ELSE u.excluded           END AS excluded,
           CASE WHEN (u.protection_type_id IS NULL) THEN s.protection_type_id ELSE u.protection_type_id END AS protection_type_id,
           u.share_type_id
    FROM values s
             LEFT JOIN user_values u               ON s.value_id = u.value_id AND u.user_id = $3
             LEFT JOIN phrase_groups l    ON s.phrase_group_id = l.phrase_group_id
             LEFT JOIN phrase_group_word_links l2  ON s.phrase_group_id = l2.phrase_group_id
    WHERE l2.word_id = $1
    LIMIT $2;
