PREPARE view_update_002200000 (text, text, bigint) AS
    UPDATE views
       SET view_name = $1,
           description = $2
     WHERE view_id = $3;