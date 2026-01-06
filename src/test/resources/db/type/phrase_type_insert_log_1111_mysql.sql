DROP PROCEDURE IF EXISTS phrase_type_insert_log_1111;
CREATE PROCEDURE phrase_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO phrase_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_phrase_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_phrase_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_phrase_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_phrase_type_id ;

        UPDATE phrase_types
           SET code_id     = _code_id,
               description = _description
         WHERE phrase_types.phrase_type_id = @new_phrase_type_id;

END;

PREPARE phrase_type_insert_log_1111_call
    FROM 'SELECT phrase_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT phrase_type_insert_log_1111
    ('standard',
     1,
     1,
     835,
     836,
     'default',
     837,
     '1');
