DROP PROCEDURE IF EXISTS user_profile_insert_log_11111;
CREATE PROCEDURE user_profile_insert_log_11111
    (_user_profile_name          text,
     _user_id                    bigint,
     _change_action_id           smallint,
     _field_id_user_profile_name smallint,
     _field_id_code_id           smallint,
     _code_id                    text,
     _field_id_description       smallint,
     _description                text,
     _field_id_right_level       smallint,
     _right_level                smallint)
BEGIN

    INSERT INTO user_profiles ( user_profile_name)
         SELECT                _user_profile_name ;

         SELECT LAST_INSERT_ID()
             AS @new_user_profile_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_profile_name,_user_profile_name,@new_user_profile_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,          _code_id,          @new_user_profile_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,      _description,      @new_user_profile_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_right_level,      _right_level,      @new_user_profile_id ;

        UPDATE user_profiles
           SET code_id     = _code_id,
               description = _description,
               right_level = _right_level
         WHERE user_profiles.user_profile_id = @new_user_profile_id;

END;

PREPARE user_profile_insert_log_11111_call
    FROM 'SELECT user_profile_insert_log_11111 (?,?,?,?,?,?,?,?,?,?)';

SELECT user_profile_insert_log_11111
    ('ip only',
     1,
     1,
     250,
     251,
     'ip',
     252,
     'if only the ip of the request is known',
     253,
     1);
