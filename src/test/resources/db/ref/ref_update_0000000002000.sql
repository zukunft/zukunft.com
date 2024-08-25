PREPARE ref_update_0000000002000 (text,bigint) AS
    UPDATE refs
       SET description = $1
     WHERE ref_id = $2;