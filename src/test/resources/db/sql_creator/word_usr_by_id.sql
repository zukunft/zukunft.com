SELECT word_id,
       word_name,
       plural,
       description,
       phrase_type_id,
       view_id
  FROM user_words
 WHERE word_id = $1
   AND user_id = $2;