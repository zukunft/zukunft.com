CREATE OR REPLACE FUNCTION ref_update_log_00000002000_user (
    _user_id              bigint,
    _change_action_id     smallint,
    _field_id_description smallint,
    _description_old      text,
    _description          text,
    _ref_id               bigint) RETURNS void AS $$

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,      old_value,       new_value,  row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description_old,_description,_ref_id ;

    UPDATE user_refs
       SET description = _description
     WHERE ref_id = _ref_id
       AND user_id = _user_id;

END $$ LANGUAGE plpgsql;

PREPARE ref_update_log_00000002000_user_call
        (bigint,smallint,smallint,text,text,bigint) AS
SELECT ref_update_log_00000002000_user
        ($1,$2,$3,$4,$5,$6);

SELECT ref_update_log_00000002000_user (
               1::bigint,
               2::smallint,
               65::smallint,
               'Q901028'::text,
               null::text,
               12::bigint);