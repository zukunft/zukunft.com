SELECT DISTINCT v.value_id,
                         CASE WHEN (u.word_value IS NULL)  THEN v.word_value  ELSE u.word_value  END AS word_value,
                         CASE WHEN (u.excluded IS NULL)    THEN v.excluded    ELSE u.excluded    END AS excluded,
                         CASE WHEN (u.last_update IS NULL) THEN v.last_update ELSE u.last_update END AS last_update,
                         CASE WHEN (u.source_id IS NULL)   THEN v.source_id   ELSE u.source_id   END AS source_id,
                       v.user_id,
                       v.phrase_group_id,
                       v.time_word_id
                  FROM values v
             LEFT JOIN user_values u ON u.value_id = v.value_id
                                    AND u.user_id = 1
                 WHERE v.value_id IN ( SELECT DISTINCT v.value_id
                                         FROM  value_phrase_links l1,  value_phrase_links l2,
                                              values v
                                               WHERE l1.phrase_id = 1 AND l1.value_id = v.value_id
                                                 AND l2.phrase_id = 2 AND l2.value_id = v.value_id  )
              ORDER BY v.phrase_group_id, v.time_word_id;