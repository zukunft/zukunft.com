PREPARE word_by_changer FROM
   'SELECT word_id,
           word_name,
           user_id
      FROM user_words
     WHERE word_id = ?
       AND (excluded <> 1 OR excluded is NULL)';
