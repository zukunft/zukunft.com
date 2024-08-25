PREPARE source_delete_excluded_user (bigint) AS
    DELETE FROM user_sources
           WHERE source_id = $1
             AND excluded = 1;
