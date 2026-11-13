CREATE OR REPLACE FUNCTION word_update_log_excluded_0000000000100_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_excluded  smallint,
     _excluded_old       smallint,
     _excluded           smallint,
     _word_id            bigint,
     _field_id_word_name smallint,
     _word_name_old      text) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded, _excluded_old, _excluded, _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,                row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name_old,           _word_id ;

    UPDATE user_words
       SET excluded = _excluded
     WHERE word_id = _word_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE word_update_log_excluded_0000000000100_user_call
        (bigint, smallint, smallint, smallint, smallint, bigint, smallint, text) AS
SELECT word_update_log_excluded_0000000000100_user
        ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT word_update_log_excluded_0000000000100_user
       (3::bigint,
        2::smallint,
        134::smallint,
        null::smallint,
        1::smallint,
        1::bigint,
        127::smallint,
        'mathematics'::text);