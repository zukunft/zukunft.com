PREPARE source_user_delete_excluded (bigint) AS
    DELETE FROM user_sources
           WHERE source_id = $1
             AND excluded = 1;
