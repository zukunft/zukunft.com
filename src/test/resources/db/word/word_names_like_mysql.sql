PREPARE word_names_like FROM
   'SELECT
                s.word_id,
                u.word_id AS user_word_id,
                s.user_id,
                IF(u.word_name IS NULL, s.word_name, u.word_name) AS word_name
           FROM words s
      LEFT JOIN user_words u ON s.word_id = u.word_id
            AND u.user_id = ?
          WHERE s.word_name like ?
            AND s.phrase_type_id <> 10
       ORDER BY s.word_name
          LIMIT ?
         OFFSET ?';