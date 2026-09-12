DROP PROCEDURE IF EXISTS term_view_update_log_000005000_user;
CREATE PROCEDURE term_view_update_log_000005000_user
    (_user_id                bigint,
     _change_action_id       smallint,
     _field_id_view_style_id smallint,
     _view_style_name_old    text,
     _view_style_id_old      smallint,
     _view_style_name        text,
     _view_style_id          smallint,
     _term_view_id           bigint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        old_value,           new_value,       old_id,            new_id,        row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_style_id,_view_style_name_old,_view_style_name,_view_style_id_old,_view_style_id,_term_view_id ;

    UPDATE user_term_views
       SET view_style_id = _view_style_id
     WHERE term_view_id = _term_view_id
       AND user_id = _user_id;

END;

PREPARE term_view_update_log_000005000_user_call FROM
    'SELECT term_view_update_log_000005000_user (?,?,?,?,?,?,?,?)';

SELECT term_view_update_log_000005000_user
       (3,
        2,
        902,
        null,
        null,
        '2/3 width',
        2,
        0);