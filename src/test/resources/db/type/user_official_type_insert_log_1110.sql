CREATE OR REPLACE FUNCTION user_official_type_insert_log_1110
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text) RETURNS bigint AS
$$
DECLARE new_user_official_type_id bigint;
BEGIN

        INSERT INTO user_official_types (type_name)
             SELECT              _type_name
          RETURNING user_official_type_id INTO new_user_official_type_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  new_user_official_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_user_official_type_id ;

             UPDATE user_official_types
                SET code_id     = _code_id
              WHERE user_official_types.user_official_type_id = new_user_official_type_id;

             RETURN new_user_official_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE user_official_type_insert_log_1110_call
    (text,bigint,smallint,smallint,smallint,text) AS
SELECT user_official_type_insert_log_1110
    ($1,$2,$3,$4,$5,$6);

SELECT user_official_type_insert_log_1110
    ('EU passport'::text,
     1::bigint,
     1::smallint,
     863::smallint,
     864::smallint,
     'passport_eu'::text);