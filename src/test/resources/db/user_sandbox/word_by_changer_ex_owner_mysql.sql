PREPARE word_by_changer_ex_owner FROM
   'SELECT word_id,
           word_name,
           user_id
      FROM user_words
     WHERE word_id = ?
       AND user_id <> ?
       AND (excluded <> 1 OR excluded is NULL)';
