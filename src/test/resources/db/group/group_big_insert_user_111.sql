PREPARE group_big_insert_user_111 (text, text, bigint, text) AS
    INSERT INTO user_groups_big
                (group_id, group_name, user_id, description)
         VALUES ($1, $2, $3, $4)
    RETURNING group_id;