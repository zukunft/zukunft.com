CREATE OR REPLACE FUNCTION user_status_insert_log_1111
    (_user_status_name          text,
     _user_id                   bigint,
     _change_action_id          smallint,
     _field_id_user_status_name smallint,
     _field_id_code_id          smallint,
     _code_id                   text,
     _field_id_description      smallint,
     _description               text) RETURNS bigint AS
$$
DECLARE new_user_status_id bigint;
BEGIN

        INSERT INTO user_statuum (user_status_name)
             SELECT              _user_status_name
          RETURNING user_status_id INTO new_user_status_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_user_status_name,  _user_status_name,  new_user_status_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_user_status_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,new_user_status_id ;

             UPDATE user_statuum
                SET code_id     = _code_id,
                    description = _description
              WHERE user_statuum.user_status_id = new_user_status_id;

             RETURN new_user_status_id;

END
$$ LANGUAGE plpgsql;

PREPARE user_status_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT user_status_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT user_status_insert_log_1111
    ('Verified'::text,
     1::bigint,
     1::smallint,
     255::smallint,
     256::smallint,
     'verified'::text,
     257::smallint,
     'verified by email or mobile'::text);