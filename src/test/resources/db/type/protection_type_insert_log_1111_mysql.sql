DROP PROCEDURE IF EXISTS protection_type_insert_log_1111;
CREATE PROCEDURE protection_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO protection_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_protection_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_protection_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_protection_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_protection_type_id ;

        UPDATE protection_types
           SET code_id     = _code_id,
               description = _description
         WHERE protection_types.protection_type_id = @new_protection_type_id;

END;

PREPARE protection_type_insert_log_1111_call
    FROM 'SELECT protection_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT protection_type_insert_log_1111
    ('no protection',
     1,
     1,
     190,
     191,
     'no_protection',
     192,
     'anyone can take the ownership');
