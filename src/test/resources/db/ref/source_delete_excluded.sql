PREPARE source_delete_excluded (bigint) AS
    DELETE FROM sources
           WHERE source_id = $1
             AND excluded = 1;
