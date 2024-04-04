CREATE OR REPLACE FUNCTION word_insert_log_00111000000
    (bigint, smallint, smallint, text, bigint, text, bigint, bigint) RETURNS bigint AS
$$
BEGIN
    INSERT INTO changes (user_id, change_action_id, change_field_id, new_value)
    VALUES ($1,$2,$3,$4);
    INSERT INTO changes (user_id, change_action_id, change_field_id, new_value)
    VALUES ($1,$2,$5,$6);
    INSERT INTO changes (user_id, change_action_id, change_field_id, new_value)
    VALUES ($1,$2,$7,$8);
    INSERT INTO words (word_name,description,phrase_type_id)
    VALUES ($4,$6,$8)
    RETURNING word_id;
END
$$ LANGUAGE plpgsql;