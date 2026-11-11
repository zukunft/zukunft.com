DROP PROCEDURE IF EXISTS value_text_update_log_0210000;
CREATE PROCEDURE value_text_update_log_0210000
    (_user_id             bigint,
     _change_action_id    smallint,
     _field_id_text_value smallint,
     _text_value_old      text,
     _text_value          text,
     _group_id            text)
BEGIN

    INSERT INTO change_values_text_norm ( user_id, change_action_id, change_field_id,     old_value,      new_value,  group_id)
         SELECT                          _user_id,_change_action_id,_field_id_text_value,_text_value_old,_text_value,_group_id ;

    UPDATE values_text
       SET text_value  = _text_value,
           last_update = Now()
     WHERE group_id = _group_id;

END;

PREPARE value_text_update_log_0210000_call FROM
    'SELECT value_text_update_log_0210000 (?,?,?,?,?,?)';

SELECT value_text_update_log_0210000
       (3,
        1,
        350,
        'old db text sample value',
        'zukunft.com',
        '0A-1O+1S+1X+');