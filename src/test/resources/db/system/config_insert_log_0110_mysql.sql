DROP PROCEDURE IF EXISTS config_insert_log_0110;
CREATE PROCEDURE config_insert_log_0110
    (_code_id          text,
     _user_id          bigint,
     _change_action_id smallint,
     _field_id_code_id smallint,
     _field_id_value   smallint,
     _value            text)
BEGIN

    INSERT INTO config ( code_id)
         SELECT            _code_id ;

    SELECT LAST_INSERT_ID() AS @new_config_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,_code_id,  @new_config_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_value, _value,     @new_config_id ;

         UPDATE config
            SET `value` = _value
          WHERE config.config_id = @new_config_id;

END;

PREPARE config_insert_log_0110_call FROM
    'SELECT config_insert_log_0110 (?, ?, ?, ?, ?, ?)';

SELECT config_insert_log_0110
       ('version_database',
        1,
        1,
        177,
        178,
        '0.0.2');