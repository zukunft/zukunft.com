PREPARE user_update_last_source_id (bigint, bigint) AS
    UPDATE users
       SET source_id = $1
     WHERE user_id = $2;
