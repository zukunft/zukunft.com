PREPARE word_update_0022004000000 (text, text, smallint, bigint) AS
    UPDATE words
       SET word_name      = $1,
           description    = $2,
           phrase_type_id = $3
     WHERE word_id = $4;