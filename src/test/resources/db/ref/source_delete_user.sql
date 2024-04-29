PREPARE source_delete_user (bigint, bigint) AS
    DELETE FROM user_sources
           WHERE source_id = $1
             AND user_id = $2;
