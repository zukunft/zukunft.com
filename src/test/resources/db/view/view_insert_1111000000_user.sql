PREPARE view_insert_1111000000_user (bigint, bigint, text, text) AS
    INSERT INTO user_views (view_id,user_id,view_name,description)
         VALUES ($1,$2,$3,$4) RETURNING view_id;
