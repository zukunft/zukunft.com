PREPARE word_insert (bigint, bigint, text, text, bigint, text, text, text, text, text, text) AS
    INSERT INTO words (word_id,user_id,word_name,description,excluded,share_type_id,protect_id,phrase_type_id,view_id,plural, values)
         VALUES       ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11)
    RETURNING word_id;