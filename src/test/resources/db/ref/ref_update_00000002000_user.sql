PREPARE ref_update_00000002000_user (text,bigint,bigint) AS
    UPDATE user_refs
       SET description = $1
     WHERE ref_id = $2
       AND user_id = $3;