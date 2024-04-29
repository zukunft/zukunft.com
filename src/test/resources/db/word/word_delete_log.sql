CREATE OR REPLACE FUNCTION word_delete_log
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_word_name smallint,
     _word_name          text,
     _word_id            bigint) RETURNS void AS
$$
BEGIN

    WITH
        change_delete_word_name AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value, row_id)
                 SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,_word_id)
    DELETE
      FROM words
     WHERE word_id = _word_id;

END
$$ LANGUAGE plpgsql;

SELECT word_delete_log
       (1::bigint,
        3::smallint,
        10::smallint,
        'Mathematics'::text,
        1::bigint);