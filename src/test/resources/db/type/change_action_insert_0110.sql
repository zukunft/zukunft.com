PREPARE change_action_insert_0110 (text, text) AS
    INSERT INTO change_actions (change_action_name, code_id)
    VALUES       ($1, $2)
    RETURNING change_action_id;