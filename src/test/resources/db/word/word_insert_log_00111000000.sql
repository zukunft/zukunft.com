CREATE OR REPLACE FUNCTION word_insert_log_00111000000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_name        smallint,
     _new_word             text,
     _field_id_description bigint,
     _description          text,
     _field_id_type        bigint,
     _type_id              bigint) RETURNS bigint AS
$$
BEGIN

    WITH
    insert_log AS (
        INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value)
             VALUES         (_user_id,_change_action_id,_field_id_name,  _new_word)
          RETURNING changes.change_id),
    insert_word AS (
        INSERT INTO words (word_name)
            VALUES        (_new_word)
            RETURNING word_id )
    UPDATE changes SET row_id = insert_word.word_id
      FROM insert_log, insert_word
     WHERE changes.change_id = insert_log.change_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,     new_value)
         VALUES         (_user_id,_change_action_id,_field_id_description,_description);
    INSERT INTO changes (user_id, change_action_id, change_field_id, new_value)
         VALUES         (_user_id,_change_action_id,_field_id_type,  _type_id);
    WITH get_word_id AS ( SELECT word_id FROM words WHERE word_name = _new_word)
    UPDATE words
       SET description    = _description,
           phrase_type_id = _type_id
    FROM get_word_id
    WHERE words.word_id = get_word_id.word_id;

END
$$ LANGUAGE plpgsql;

