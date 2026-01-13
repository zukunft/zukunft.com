DROP PROCEDURE IF EXISTS term_view_update_log_000010000;
CREATE PROCEDURE term_view_update_log_000010000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_description smallint,
     _description_old      text,
     _description          text,
     _term_view_id         bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description_old,_description,_term_view_id ;

    UPDATE term_views
       SET description = _description
     WHERE term_view_id = _term_view_id;

END;

PREPARE term_view_update_log_000010000_call FROM
    'SELECT term_view_update_log_000010000 (?,?,?,?,?,?)';

SELECT term_view_update_log_000010000
    (3,
     2,
     727,
     null,
     'System Test description for a view term link',
     0);