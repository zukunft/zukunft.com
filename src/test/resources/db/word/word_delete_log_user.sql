CREATE OR REPLACE FUNCTION word_delete_log_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_word_name smallint,
     _word_name          text,
     _word_id            bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,_word_id ;

    DELETE
      FROM user_words
     WHERE word_id = _word_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

SELECT word_delete_log_user
       (1::bigint,
        3::smallint,
        10::smallint,
        'mathematics'::text,
        1::bigint);