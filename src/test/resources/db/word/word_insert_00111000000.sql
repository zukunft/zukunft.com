PREPARE word_insert_00111000000 (text,text,bigint) AS
    INSERT INTO words (word_name,description,phrase_type_id)
    VALUES ($1,$2,$3)
    RETURNING word_id;