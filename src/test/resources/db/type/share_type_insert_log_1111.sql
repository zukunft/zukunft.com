CREATE OR REPLACE FUNCTION share_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_share_type_id bigint;
BEGIN

        INSERT INTO share_types (type_name)
             SELECT              _type_name
          RETURNING share_type_id INTO new_share_type_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  new_share_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_share_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,new_share_type_id ;

             UPDATE share_types
                SET code_id     = _code_id,
                    description = _description
              WHERE share_types.share_type_id = new_share_type_id;

             RETURN new_share_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE share_type_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT share_type_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT share_type_insert_log_1111
    ('public'::text,
     1::bigint,
     1::smallint,
     871::smallint,
     872::smallint,
     'public'::text,
     873::smallint,
     'value can be seen and used by everyone (default)'::text);