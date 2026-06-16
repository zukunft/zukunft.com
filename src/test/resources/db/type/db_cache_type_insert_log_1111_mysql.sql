DROP PROCEDURE IF EXISTS db_cache_type_insert_log_1111;
CREATE PROCEDURE db_cache_type_insert_log_1111
    (_type_name             text,
     _user_id               bigint,
     _change_action_id      smallint,
     _field_id_type_name    smallint,
     _field_id_code_id      smallint,
     _code_id               text,
     _field_id_description  smallint,
     _description           text)
BEGIN

    INSERT INTO db_cache_types ( type_name)
         SELECT                 _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,@new_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_type_id ;

        UPDATE db_cache_types
           SET code_id     = _code_id,
               description = _description
         WHERE db_cache_types.type_id = @new_type_id;

END;

PREPARE db_cache_type_insert_log_1111_call
    FROM 'SELECT db_cache_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT db_cache_type_insert_log_1111
    ('system configuration',
     1,
     1,
     875,
     876,
     'system_config',
     877,
     'the complete json of the system configuration');
