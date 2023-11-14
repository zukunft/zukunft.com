PREPARE group_big_user_insert (text, bigint, text, text) AS
    INSERT INTO user_groups_big
                (group_id, user_id, group_name, description)
         VALUES ($1, $2, $3, $4)
    RETURNING group_id;