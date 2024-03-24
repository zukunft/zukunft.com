CREATE OR REPLACE FUNCTION word_insert_00111000000
    (bigint, smallint, bigint, bigint, bigint, text, text, bigint) RETURNS bigint AS
$$
BEGIN
    INSERT INTO changes (user_id, change_action_id, change_field_id, new_value)
    VALUES ($1,$2,$3,$6);
    INSERT INTO changes (user_id, change_action_id, change_field_id, new_value)
    VALUES ($1,$2,$4,$7);
    INSERT INTO changes (user_id, change_action_id, change_field_id, new_value)
    VALUES ($1,$2,$5,$8);
    INSERT INTO words (word_name,description,phrase_type_id)
    VALUES ($6,$7,$8)
    RETURNING word_id;
END
$$ LANGUAGE plpgsql;