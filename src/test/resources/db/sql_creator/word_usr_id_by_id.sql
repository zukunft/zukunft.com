SELECT word_id,
       word_name
  FROM user_words
 WHERE word_id = $1
   AND user_id = $2;