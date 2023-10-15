PREPARE word_view_most_used (bigint) AS
    SELECT s.word_id,
           s.word_name,
           s.view_id
      FROM ( SELECT s.word_id,
                    s.word_name,
                    s.view_id,
                    count(l.user_id) AS user_id_count
               FROM words s
          LEFT JOIN user_words l ON l.word_id = s.word_id
              WHERE s.word_id = $1
           GROUP BY s.word_id, s.word_name, s.view_id ) AS s
  ORDER BY user_id_count DESC;
