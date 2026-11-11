DROP PROCEDURE IF EXISTS value_text_insert_log_11000_user;
CREATE PROCEDURE value_text_insert_log_11000_user
    (_user_id             bigint,
     _change_action_id    smallint,
     _field_id_text_value smallint,
     _text_value          text,
     _group_id            text,
     _source_id           bigint)
BEGIN

    INSERT INTO change_values_text_norm ( user_id, change_action_id, change_field_id,     new_value,  group_id)
         SELECT                          _user_id,_change_action_id,_field_id_text_value,_text_value,_group_id ;
    INSERT INTO user_values_text ( group_id, user_id, source_id, text_value,last_update)
         SELECT                   _group_id,_user_id,_source_id,_text_value,Now() ;

END;

PREPARE value_text_insert_log_11000_user_call FROM
    'SELECT value_text_insert_log_11000_user (?,?,?,?,?,?)';

SELECT value_text_insert_log_11000_user
       (3,
        1,
        359,
        'zukunft.com',
        '0A-1O+1S+1X+',
        null);