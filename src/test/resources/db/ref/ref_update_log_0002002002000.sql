CREATE OR REPLACE FUNCTION ref_update_log_0002002002000
    (_user_id               bigint,
     _change_action_id      smallint,
     _field_id_external_key smallint,
     _external_key_old      text,
     _external_key          text,
     _ref_id                bigint,
     _field_id_description  smallint,
     _description_old       text,
     _description           text) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,        new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_external_key,_external_key_old,_external_key,_ref_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,        new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_description, _description_old, _description, _ref_id ;

    UPDATE refs
       SET external_key = _external_key,
           description  = _description
     WHERE ref_id = _ref_id;

END
$$ LANGUAGE plpgsql;

PREPARE ref_update_log_0002002002000_call
        (bigint, smallint, smallint, text, text, bigint, smallint, text, text) AS
SELECT ref_update_log_0002002002000
        ($1,$2,$3,$4,$5,$6,$7,$8,$9);

SELECT ref_update_log_0002002002000
       (3::bigint,
        2::smallint,
        226::smallint,
        'Q167'::text,
        'System Test Reference External Key Changed'::text,
        29::bigint,
        229::smallint,
        'pi - ratio of the circumference of a circle to its diameter'::text,
        null::text);