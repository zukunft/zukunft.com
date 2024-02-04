PREPARE word_std_by_id (bigint) AS
    SELECT word_id,
           word_name,
           values,
           plural,
           description,
           phrase_type_id,
           view_id,
           excluded,
           share_type_id,
           protect_id,
           user_id
      FROM words
     WHERE word_id = $1;