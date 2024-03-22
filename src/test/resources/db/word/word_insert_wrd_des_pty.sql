PREPARE word_insert_wrd_des_pty (text,text,bigint) AS
    INSERT INTO words (word_name,description,phrase_type_id)
    VALUES ($1,$2,$3)
    RETURNING word_id;