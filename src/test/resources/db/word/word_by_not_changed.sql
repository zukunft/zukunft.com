PREPARE word_by_not_changed (int) AS
     SELECT user_id
       FROM words
      WHERE word_id = $1
        AND (excluded <> 1 OR excluded is NULL);