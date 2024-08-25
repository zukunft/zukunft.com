DROP PROCEDURE IF EXISTS ref_update_log_00000002000_user;
CREATE PROCEDURE ref_update_log_00000002000_user
(_user_id              bigint,
 _change_action_id     smallint,
 _field_id_description smallint,
 _description_old      text,
 _description          text,
 _ref_id               bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description_old,_description,_ref_id ;

    UPDATE user_refs
       SET description = _description
     WHERE ref_id = _ref_id
       AND user_id = _user_id;

END;

PREPARE ref_update_log_00000002000_user_call FROM
    'SELECT ref_update_log_00000002000_user (?,?,?,?,?,?)';

SELECT ref_update_log_00000002000_user (
               1,
               2,
               65,
               'Q901028',
               null,
               12);