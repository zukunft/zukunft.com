PREPARE word_delete_excluded (bigint) AS
    DELETE FROM words
           WHERE word_id = $1
             AND excluded = 1;
