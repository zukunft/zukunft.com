PREPARE source_delete (bigint) AS
    DELETE FROM sources
           WHERE source_id = $1;