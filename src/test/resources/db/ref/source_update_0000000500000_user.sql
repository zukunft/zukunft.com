PREPARE source_update_0000000500000_user (bigint, bigint, bigint) AS
    UPDATE user_sources
       SET view_id = $1
     WHERE source_id = $2
       AND user_id = $3;