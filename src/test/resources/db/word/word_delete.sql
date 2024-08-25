PREPARE word_delete (bigint) AS
    DELETE FROM words
           WHERE word_id = $1;