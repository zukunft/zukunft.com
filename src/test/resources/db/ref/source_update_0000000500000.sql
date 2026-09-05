PREPARE source_update_0000000500000 (bigint, bigint) AS
    UPDATE sources
       SET view_id = $1
     WHERE source_id = $2;