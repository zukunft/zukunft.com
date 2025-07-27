CREATE OR REPLACE FUNCTION word_insert_log_110000300002_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_id        smallint,
     _view_name_old           text,
     _view_id_old             bigint,
     _view_name               text,
     _view_id                 bigint,
     _word_id                 bigint,
     _field_id_protect_id     smallint,
     _protect_id_old          smallint,
     _protect_id              smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,old_value,old_id, row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_id,_view_name_old,_view_id_old, _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value,row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,_protect_id_old,_word_id ;

    INSERT INTO user_words
                (word_id, user_id, view_id, protect_id)
         SELECT _word_id,_user_id,_view_id, _protect_id ;

END
$$ LANGUAGE plpgsql;

PREPARE word_insert_log_110000300002_user_call
        (bigint, smallint, smallint, text, bigint, text, bigint, bigint, smallint, smallint, smallint) AS
    SELECT word_insert_log_110000300002_user
        ($1,$2,$3,$4,$5,$6,$7,$8, $9, $10, $11);

SELECT word_insert_log_110000300002_user
        (1::bigint,
         1::smallint,
         85::smallint,
         ''::text,
         51::bigint,
         null::text,
         null::bigint,
         272::bigint,
         87::smallint,
         3::smallint,
         null::smallint);