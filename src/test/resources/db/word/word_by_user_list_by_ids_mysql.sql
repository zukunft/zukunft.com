PREPARE word_by_user_list_by_ids FROM
   'SELECT     s.word_id,
               s.word_name,
               l.user_id,
               l.user_name
          FROM user_words s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.word_id IN (?)
           AND ( s.excluded <> ? OR s.excluded IS NULL )';