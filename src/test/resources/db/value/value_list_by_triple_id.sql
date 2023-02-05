PREPARE value_list_by_triple_id (int, int, int) AS
    SELECT v.value_id,
           u.value_id                                                                  AS user_value_id,
           v.user_id,
           CASE WHEN (u.word_value IS NULL) THEN v.word_value ELSE u.word_value END    AS word_value,
           CASE WHEN (u.excluded IS NULL) THEN v.excluded ELSE u.excluded END          AS excluded,
           CASE WHEN (u.last_update IS NULL) THEN v.last_update ELSE u.last_update END AS last_update,
           CASE WHEN (u.source_id IS NULL) THEN v.source_id ELSE u.source_id END       AS source_id,
           v.phrase_group_id,
           g.word_ids,
           g.triple_ids
    FROM phrase_groups g,
         values v
             LEFT JOIN user_values u ON u.value_id = v.value_id
             AND u.user_id = $1
    WHERE g.phrase_group_id = v.phrase_group_id
      AND v.value_id IN (SELECT value_id
                         FROM value_phrase_links
                         WHERE phrase_id = $2
                         GROUP BY value_id)
    ORDER BY v.phrase_group_id
    LIMIT $3;