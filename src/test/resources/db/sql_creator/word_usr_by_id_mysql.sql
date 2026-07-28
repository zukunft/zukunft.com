SELECT word_id,
       word_name,
       plural,
       description,
       phrase_type_id,
       view_id
  FROM user_words
 WHERE word_id = ?
   AND user_id = ?;