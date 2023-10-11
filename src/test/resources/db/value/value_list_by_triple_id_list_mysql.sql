SELECT DISTINCT v.group_id,
                IF(u.numeric_value IS NULL, v.numeric_value, u.numeric_value) AS numeric_value,
                IF(u.excluded      IS NULL, v.excluded,      u.excluded)      AS excluded,
                IF(u.last_update   IS NULL, v.last_update,   u.last_update)   AS last_update,
                IF(u.source_id     IS NULL, v.source_id,     u.source_id)     AS source_id,
                v.user_id,
                v.group_id
           FROM `values` v
      LEFT JOIN user_values u ON u.group_id = v.group_id AND u.user_id = 1
          WHERE v.group_id IN ( SELECT DISTINCT v.group_id
                                           FROM  value_phrase_links l1,  value_phrase_links l2,
                                                `values` v
                                           WHERE l1.phrase_id = 1 AND l1.group_id = v.group_id
                                             AND l2.phrase_id = 2 AND l2.group_id = v.group_id  )
       ORDER BY v.group_id;