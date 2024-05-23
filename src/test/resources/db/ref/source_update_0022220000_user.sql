PREPARE source_update_0022220000_user (text,text,smallint,text,bigint,bigint) AS
    UPDATE user_sources
       SET source_name    = $1,
           description    = $2,
           source_type_id = $3,
           url            = $4
     WHERE source_id = $5
       AND user_id = $6;