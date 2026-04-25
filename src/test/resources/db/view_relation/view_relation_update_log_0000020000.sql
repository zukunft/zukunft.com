CREATE OR REPLACE FUNCTION view_relation_update_log_0000020000
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_start_pos smallint,
     _start_pos_old      bigint,
     _start_pos          bigint,
     _view_relation_id   bigint) RETURNS void AS

$$ BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_start_pos,_start_pos_old,_start_pos,_view_relation_id ;

    UPDATE view_relations
       SET start_pos             = _start_pos
     WHERE view_relation_id = _view_relation_id;

END $$ LANGUAGE plpgsql;

PREPARE view_relation_update_log_0000020000_call
    (bigint, smallint, smallint, bigint, bigint, bigint) AS
SELECT view_relation_update_log_0000020000
    ($1, $2, $3, $4, $5, $6);

SELECT view_relation_update_log_0000020000 (
               3::bigint,
               2::smallint,
               817::smallint,
               15::bigint,
               16::bigint,
               1::bigint);