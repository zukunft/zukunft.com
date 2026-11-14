CREATE OR REPLACE FUNCTION word_update_log_0200000000000
    (_user_id          bigint,
     _change_action_id smallint,
     _field_id_user_id smallint,
     _user_id_old      bigint,
     _word_id          bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  old_value,   new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id_old,_user_id,  _word_id ;

    UPDATE words
       SET user_id = _user_id
     WHERE word_id = _word_id;

END
$$ LANGUAGE plpgsql;

PREPARE word_update_log_0200000000000_call
        (bigint, smallint, smallint, bigint, bigint) AS
SELECT word_update_log_0200000000000
        ($1,$2,$3,$4,$5);

SELECT word_update_log_0200000000000
       (4::bigint,
        2::smallint,
        111::smallint,
        3::bigint,
        1::bigint);