DROP PROCEDURE IF EXISTS term_view_update_log_00000000001;
CREATE PROCEDURE term_view_update_log_00000000001
    (_user_id             bigint,
     _change_action_id    smallint,
     _field_id_protect_id smallint,
     _protect_id_old      smallint,
     _protect_id          smallint,
     _term_view_id        bigint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,     old_value,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,_protect_id_old,_protect_id,_term_view_id ;

    UPDATE term_views
       SET protect_id = _protect_id
     WHERE term_view_id = _term_view_id;

END;

PREPARE term_view_update_log_00000000001_call FROM
    'SELECT term_view_update_log_00000000001 (?,?,?,?,?,?)';

SELECT term_view_update_log_00000000001
       (3,
        2,
        765,
        null,
        3,
        1);