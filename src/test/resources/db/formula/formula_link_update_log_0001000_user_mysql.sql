DROP PROCEDURE IF EXISTS formula_link_update_log_0001000_user;
CREATE PROCEDURE formula_link_update_log_0001000_user
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_description smallint,
     _description_old      text,
     _description          text,
     _formula_link_id      bigint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_formula_link_id ;

    UPDATE user_formula_links
       SET description = _description
     WHERE formula_link_id = _formula_link_id
       AND user_id = _user_id;

END;

PREPARE formula_link_update_log_0001000_user_call FROM
    'SELECT formula_link_update_log_0001000_user (?,?,?,?,?,?)';

SELECT formula_link_update_log_0001000_user
       (3,
        2,
        904,
        null,
        'System Test description for a formula link',
        1);