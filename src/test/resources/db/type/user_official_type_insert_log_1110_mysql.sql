DROP PROCEDURE IF EXISTS user_official_type_insert_log_1110;
CREATE PROCEDURE user_official_type_insert_log_1110
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text)
BEGIN

    INSERT INTO user_official_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_user_official_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_user_official_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_user_official_type_id ;

        UPDATE user_official_types
           SET code_id     = _code_id
         WHERE user_official_types.user_official_type_id = @new_user_official_type_id;

END;

PREPARE user_official_type_insert_log_1110_call
    FROM 'SELECT user_official_type_insert_log_1110 (?,?,?,?,?,?)';

SELECT user_official_type_insert_log_1110
    ('EU passport',
     1,
     1,
     181,
     182,
     'passport_eu');
