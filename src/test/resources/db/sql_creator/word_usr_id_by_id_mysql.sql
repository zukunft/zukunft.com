SELECT word_id,
       word_name
  FROM user_words
 WHERE word_id = ?
   AND user_id = ?;