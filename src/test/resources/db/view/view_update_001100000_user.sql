PREPARE view_update_001100000_user (text, text, bigint, bigint) AS
    UPDATE user_views
       SET view_name = $1,
           description = $2
     WHERE view_id = $3
       AND user_id = $4;