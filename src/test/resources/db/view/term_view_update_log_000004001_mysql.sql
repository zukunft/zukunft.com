DROP PROCEDURE IF EXISTS term_view_update_log_000004001;
CREATE PROCEDURE term_view_update_log_000004001
    (_user_id                    bigint,
     _change_action_id           smallint,
     _field_id_view_link_type_id smallint,
     _type_name_old              text,
     _view_link_type_id_old      smallint,
     _type_name                  text,
     _view_link_type_id          smallint,
     _term_view_id               bigint,
     _field_id_protect_id        smallint,
     _protect_id_old             smallint,
     _protect_id                 smallint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,            old_value,      new_value, old_id,new_id,                            row_id)
         SELECT         _user_id,_change_action_id,_field_id_view_link_type_id,_type_name_old, _type_name,_view_link_type_id_old,_view_link_type_id,_term_view_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,           old_value,      new_value,                                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,      _protect_id_old,_protect_id,                                         _term_view_id ;

    UPDATE term_views
       SET view_link_type_id = _view_link_type_id,
           protect_id        = _protect_id
     WHERE term_view_id = _term_view_id;

END;

PREPARE term_view_update_log_000004001_call FROM
    'SELECT term_view_update_log_000004001 (?,?,?,?,?,?,?,?,?,?,?)';

SELECT term_view_update_log_000004001
    (3,
     2,
     726,
     'standard',
     1,
     null,
     null,
     1,
     730,
     null,
     3);