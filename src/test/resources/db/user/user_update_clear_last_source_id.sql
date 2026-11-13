PREPARE user_update_clear_last_source_id (bigint, bigint) AS
    UPDATE users
       SET source_id = $1
     WHERE source_id = $2;
