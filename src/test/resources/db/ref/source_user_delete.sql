PREPARE source_user_delete (bigint, bigint) AS
    DELETE FROM user_sources
           WHERE source_id = $1
             AND user_id = $2;
