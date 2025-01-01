PREPARE word_update_002240000002 (text, text, smallint, smallint, bigint) AS
    UPDATE words
       SET word_name = $1,
           description = $2,
           phrase_type_id = $3,
           protect_id = $4
     WHERE word_id = $5;