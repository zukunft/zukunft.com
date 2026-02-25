CREATE OR REPLACE FUNCTION user_profile_insert_log_11111
    (_user_profile_name          text,
     _user_id                    bigint,
     _change_action_id           smallint,
     _field_id_user_profile_name smallint,
     _field_id_code_id           smallint,
     _code_id                    text,
     _field_id_description       smallint,
     _description                text,
     _field_id_right_level       smallint,
     _right_level                smallint) RETURNS bigint AS
$$
DECLARE new_user_profile_id bigint;
BEGIN

        INSERT INTO user_profiles (user_profile_name)
             SELECT               _user_profile_name
          RETURNING user_profile_id INTO new_user_profile_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
             SELECT          _user_id,_change_action_id,_field_id_user_profile_name,_user_profile_name, new_user_profile_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,          _code_id,           new_user_profile_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,      _description,       new_user_profile_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
             SELECT          _user_id,_change_action_id,_field_id_right_level,      _right_level,       new_user_profile_id ;

             UPDATE user_profiles
                SET code_id     = _code_id,
                    description = _description,
                    right_level = _right_level
              WHERE user_profiles.user_profile_id = new_user_profile_id;

             RETURN new_user_profile_id;

END
$$ LANGUAGE plpgsql;

PREPARE user_profile_insert_log_11111_call
    (text, bigint, smallint, smallint, smallint, text, smallint, text, smallint, smallint) AS
SELECT user_profile_insert_log_11111
    ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10);

SELECT user_profile_insert_log_11111
    ('ip only'::text,
     1::bigint,
     1::smallint,
     250::smallint,
     251::smallint,
     'ip'::text,
     252::smallint,
     'if only the ip of the request is known'::text,
     253::smallint,
     1::smallint);