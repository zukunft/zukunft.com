CREATE OR REPLACE FUNCTION word_insert_log_1100000300000_user
    (_user_id          bigint,
     _change_action_id smallint,
     _field_id_view_id smallint,
     _view_name_old    text,
     _view_id_old      bigint,
     _view_name        text,
     _view_id          bigint,
     _word_id          bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  old_value,     old_id,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_id,_view_name_old,_view_id_old,_word_id ;
    INSERT INTO user_words ( word_id, user_id, view_id)
         SELECT             _word_id,_user_id,_view_id ;

END
$$ LANGUAGE plpgsql;

PREPARE word_insert_log_1100000300000_user_call
        (bigint, smallint, smallint, text, bigint, text, bigint, bigint) AS
SELECT word_insert_log_1100000300000_user
        ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT word_insert_log_1100000300000_user
       (3::bigint,
        1::smallint,
        85::smallint,
        null::text,
        101::bigint,
        null::text,
        null::bigint,
        190::bigint);