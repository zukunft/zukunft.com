PREPARE word_names (int, int, int) AS
         SELECT s.word_id,
                u.word_id AS user_word_id,
                s.user_id,
                CASE WHEN (u.word_name <> '' IS NOT TRUE) THEN s.word_name ELSE u.word_name END AS word_name
           FROM words s
      LEFT JOIN user_words u ON s.word_id = u.word_id
            AND u.user_id = $1
          WHERE s.phrase_type_id <> 10
       ORDER BY s.word_name
          LIMIT $2
         OFFSET $3;
