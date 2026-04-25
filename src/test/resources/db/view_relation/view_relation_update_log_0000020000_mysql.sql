DROP PROCEDURE IF EXISTS view_relation_update_log_0000020000;
CREATE PROCEDURE view_relation_update_log_0000020000
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_start_pos smallint,
     _start_pos_old      bigint,
     _start_pos          bigint,
     _view_relation_id   bigint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_start_pos,_start_pos_old,_start_pos,_view_relation_id ;


    UPDATE view_relations
       SET start_pos             = _start_pos
     WHERE view_relation_id = _view_relation_id;

END;

PREPARE view_relation_update_log_0000020000_call FROM
    'SELECT view_relation_update_log_0000020000 (?,?,?,?,?,?)';

SELECT view_relation_update_log_0000020000 (
               3,
               2,
               817,
               15,
               16,
               1);