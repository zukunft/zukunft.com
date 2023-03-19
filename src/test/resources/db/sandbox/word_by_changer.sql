PREPARE word_by_changer (int) AS
    SELECT word_id,
           word_name,
           user_id
      FROM user_words
     WHERE word_id = $1
       AND (excluded <> 1 OR excluded is NULL);
