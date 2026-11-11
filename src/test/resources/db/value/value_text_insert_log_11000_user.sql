CREATE OR REPLACE FUNCTION value_text_insert_log_11000_user
    (_user_id             bigint,
     _change_action_id    smallint,
     _field_id_text_value smallint,
     _text_value          text,
     _group_id            text,
     _source_id           bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_text_norm ( user_id, change_action_id, change_field_id,     new_value,  group_id)
         SELECT                          _user_id,_change_action_id,_field_id_text_value,_text_value,_group_id ;
    INSERT INTO user_values_text ( group_id, user_id, source_id, text_value,last_update)
         SELECT                   _group_id,_user_id,_source_id,_text_value,Now() ;

END
$$ LANGUAGE plpgsql;

PREPARE value_text_insert_log_11000_user_call
        (bigint, smallint, smallint, text, text, bigint) AS
SELECT value_text_insert_log_11000_user
        ($1,$2,$3,$4,$5,$6);

SELECT value_text_insert_log_11000_user
       (3::bigint,
        1::smallint,
        359::smallint,
        'zukunft.com'::text,
        '0A-1O+1S+1X+'::text,
        null::bigint);