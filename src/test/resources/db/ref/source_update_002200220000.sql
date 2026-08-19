PREPARE source_update_002200220000 (text, text, smallint, text, bigint) AS
    UPDATE sources
       SET source_name    = $1,
           description    = $2,
           source_type_id = $3,
           url            = $4
     WHERE source_id = $5;