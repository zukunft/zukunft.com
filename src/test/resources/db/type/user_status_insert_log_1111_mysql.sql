DROP PROCEDURE IF EXISTS user_status_insert_log_1111;
CREATE PROCEDURE user_status_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO user_statuum ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_user_status_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_user_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_user_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_user_status_id ;

        UPDATE user_statuum
           SET code_id     = _code_id,
               description = _description
         WHERE user_statuum.user_status_id = @new_user_status_id;

END;

PREPARE user_status_insert_log_1111_call
    FROM 'SELECT user_status_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT user_status_insert_log_1111
    ('Verified',
     1,
     1,
     255,
     256,
     'verified',
     257,
     'verified by email or mobile');
