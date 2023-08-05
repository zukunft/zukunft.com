PREPARE phrase_names (int, int, int) AS
         SELECT s.phrase_id,
                u.phrase_id AS user_phrase_id,
                s.user_id,
                CASE WHEN (u.phrase_name <> '' IS NOT TRUE) THEN s.phrase_name ELSE u.phrase_name END AS phrase_name
           FROM phrases s
      LEFT JOIN user_phrases u ON s.phrase_id = u.phrase_id
            AND u.user_id = $1
       ORDER BY s.phrase_name
          LIMIT $2
         OFFSET $3;
