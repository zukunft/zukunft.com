DROP PROCEDURE IF EXISTS ref_insert_log_11000001000_user;
CREATE PROCEDURE ref_insert_log_11000001000_user (
    _user_id               bigint,
    _change_action_id      smallint,
    _field_id_description  smallint,
    _description           text,
    _ref_id                bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description,_ref_id ;

    INSERT INTO user_refs (ref_id, user_id, description)
         SELECT           _ref_id,_user_id,_description ;

END;

PREPARE ref_insert_log_11000001000_user_call FROM
    'SELECT ref_insert_log_11000001000_user (?,?,?,?,?)';

SELECT ref_insert_log_11000001000_user (
               1,
               1,
               65,
               'pi - ratio of the circumference of a circle to its diameter',
               4);