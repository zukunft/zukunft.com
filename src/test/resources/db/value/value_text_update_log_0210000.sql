CREATE OR REPLACE FUNCTION value_text_update_log_0210000
    (_user_id             bigint,
     _change_action_id    smallint,
     _field_id_text_value smallint,
     _text_value_old      text,
     _text_value          text,
     _group_id            text) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_text_norm ( user_id, change_action_id, change_field_id,     old_value,      new_value,  group_id)
         SELECT                          _user_id,_change_action_id,_field_id_text_value,_text_value_old,_text_value,_group_id ;

    UPDATE values_text
       SET text_value  = _text_value,
           last_update = Now()
     WHERE group_id = _group_id;

END
$$ LANGUAGE plpgsql;

PREPARE value_text_update_log_0210000_call
        (bigint, smallint, smallint, text, text, text) AS
SELECT value_text_update_log_0210000
        ($1,$2,$3,$4,$5,$6);

SELECT value_text_update_log_0210000
       (3::bigint,
        1::smallint,
        350::smallint,
        'old db text sample value'::text,
        'zukunft.com'::text,
        '0A-1O+1S+1X+'::text);