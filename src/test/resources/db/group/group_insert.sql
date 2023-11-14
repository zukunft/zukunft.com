PREPARE group_insert (text, bigint, text, text) AS
    INSERT INTO groups
                (group_id, user_id, group_name, description)
         VALUES ($1, $2, $3, $4)
    RETURNING group_id;