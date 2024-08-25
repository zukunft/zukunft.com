DROP PROCEDURE IF EXISTS ref_update_log_0000000002000;
CREATE PROCEDURE ref_update_log_0000000002000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_description smallint,
     _description_old      text,
     _description          text,
     _ref_id               bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description_old,_description,_ref_id ;

    UPDATE refs
       SET description = _description
      WHERE ref_id = _ref_id;

END;

PREPARE ref_update_log_0000000002000_call FROM
    'SELECT ref_update_log_0000000002000 (?,?,?,?,?,?)';

SELECT ref_update_log_0000000002000 (
               1,
               2,
               65,
               'Q901028',
               null,
               12);