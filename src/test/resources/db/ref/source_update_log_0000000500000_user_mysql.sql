DROP PROCEDURE IF EXISTS source_update_log_0000000500000_user;
CREATE PROCEDURE source_update_log_0000000500000_user
    (_user_id          bigint,
     _change_action_id smallint,
     _field_id_view_id smallint,
     _view_name_old    text,
     _view_id_old      bigint,
     _view_name        text,
     _view_id          bigint,
     _source_id        bigint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, old_id,      new_id,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_id,_view_name,_view_id_old,_view_id,_source_id ;

    UPDATE user_sources
       SET view_id = _view_id
     WHERE source_id = _source_id
       AND user_id = _user_id;

END;

PREPARE source_update_log_0000000500000_user_call FROM
    'SELECT source_update_log_0000000500000_user (?,?,?,?,?,?,?,?)';

SELECT source_update_log_0000000500000_user
       (3,
        2,
        906,
        null,
        null,
        null,
        93,
        1);