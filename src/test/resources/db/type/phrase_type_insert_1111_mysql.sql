PREPARE phrase_type_insert_1111 FROM

    'INSERT INTO phrase_types ( type_name, user_id, change_action_id, field_id_type_name, field_id_code_id, code_id, field_id_description, description)
          VALUES              (_type_name,_user_id,_change_action_id,_field_id_type_name,_field_id_code_id,_code_id,_field_id_description,_description)

RETURNING phrase_type_id';BEGIN

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

PREPARE phrase_type_insert_1111_call
    FROM 'SELECT phrase_type_insert_1111 (?,?,?,?,?,?,?,?)';

SELECT phrase_type_insert_1111
    ('standard',
     2,
     1,
     null,
     null,
     'default',
     null,
     '1');
