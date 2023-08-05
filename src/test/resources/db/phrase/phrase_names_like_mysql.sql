PREPARE phrase_names_like FROM
   'SELECT
                s.phrase_id,
                u.phrase_id AS user_phrase_id,
                s.user_id,
                IF(u.phrase_name IS NULL, s.phrase_name, u.phrase_name) AS phrase_name
           FROM phrases s
      LEFT JOIN user_phrases u ON s.phrase_id = u.phrase_id
            AND u.user_id = ?
          WHERE s.phrase_name like ?
       ORDER BY s.phrase_name
          LIMIT ?
         OFFSET ?';