DROP PROCEDURE IF EXISTS phrase_type_update_log_1002;
CREATE PROCEDURE phrase_type_update_log_1002
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_description smallint,
     _description_old      text,
     _description          text,
     _phrase_type_id       bigint,
     _field_id_type_name   smallint,
     _type_name_old        text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_phrase_type_id ;

         UPDATE phrase_types
            SET description = _description
          WHERE phrase_type_id = _phrase_type_id;

END;

PREPARE phrase_type_update_log_1002_call
    FROM 'SELECT phrase_type_update_log_1002 (?,?,?,?,?,?,?,?)';

SELECT phrase_type_update_log_1002
    (1,
     1,
     837,
     'changed description',
     '1',
     1,
     835,
     'standard');
