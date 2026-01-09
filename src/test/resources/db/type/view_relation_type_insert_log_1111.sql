CREATE OR REPLACE FUNCTION view_relation_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_view_relation_type_id bigint;
BEGIN

        INSERT INTO view_relation_types (type_name)
             SELECT              _type_name
          RETURNING view_relation_type_id INTO new_view_relation_type_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  new_view_relation_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_view_relation_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,new_view_relation_type_id ;

             UPDATE view_relation_types
                SET code_id     = _code_id,
                    description = _description
              WHERE view_relation_types.view_relation_type_id = new_view_relation_type_id;

             RETURN new_view_relation_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE view_relation_type_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT view_relation_type_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT view_relation_type_insert_log_1111
    ('add components'::text,
     1::bigint,
     1::smallint,
     900::smallint,
     901::smallint,
     'add_components'::text,
     902::smallint,
     'add the components of the child view to the parent view at the start position'::text);