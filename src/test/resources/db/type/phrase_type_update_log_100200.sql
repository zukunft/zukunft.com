CREATE OR REPLACE FUNCTION phrase_type_update_log_100200
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_description smallint,
     _description_old      text,
     _description          text,
     _phrase_type_id       bigint,
     _field_id_type_name   smallint,
     _type_name_old        text) RETURNS void AS
$$
BEGIN

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_phrase_type_id ;

             UPDATE phrase_types
                SET description = _description
              WHERE phrase_type_id = _phrase_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE phrase_type_update_log_100200_call
    (bigint,smallint,smallint,text,text,bigint,smallint,text) AS
SELECT phrase_type_update_log_100200
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT phrase_type_update_log_100200
    (1::bigint,
     1::smallint,
     837::smallint,
     'changed description'::text,
     '1'::text,
     1::bigint,
     835::smallint,
     'standard'::text);
