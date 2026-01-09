CREATE OR REPLACE FUNCTION user_profile_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_user_profile_id bigint;
BEGIN

        INSERT INTO user_profiles (type_name)
             SELECT              _type_name
          RETURNING user_profile_id INTO new_user_profile_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  new_user_profile_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_user_profile_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,new_user_profile_id ;

             UPDATE user_profiles
                SET code_id     = _code_id,
                    description = _description
              WHERE user_profiles.user_profile_id = new_user_profile_id;

             RETURN new_user_profile_id;

END
$$ LANGUAGE plpgsql;

PREPARE user_profile_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT user_profile_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT user_profile_insert_log_1111
    ('ip only'::text,
     1::bigint,
     1::smallint,
     859::smallint,
     860::smallint,
     'ip'::text,
     861::smallint,
     'if only the ip of the request is known'::text);