CREATE OR REPLACE FUNCTION value_text_delete_log
    (_user_id             bigint,
     _change_action_id    smallint,
     _field_id_text_value smallint,
     _text_value          text,
     _group_id            text) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_text_norm ( user_id, change_action_id, change_field_id,     old_value,  group_id)
         SELECT                          _user_id,_change_action_id,_field_id_text_value,_text_value,_group_id ;

    DELETE
      FROM values_text
     WHERE group_id = _group_id;

END
$$ LANGUAGE plpgsql;

SELECT value_text_delete_log
       (3::bigint,
        3::smallint,
        350::smallint,
        'zukunft.com'::text,
        '0A-1O+1S+1X+'::text);