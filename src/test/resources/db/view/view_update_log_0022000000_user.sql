CREATE OR REPLACE FUNCTION view_update_log_0022000000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_name      smallint,
     _view_name_old           text,
     _view_name               text,
     _view_id                 bigint,
     _field_id_description    smallint,
     _description_old         text,
     _description             text) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_name,_view_name_old,_view_name,_view_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_view_id ;

    UPDATE user_views
       SET view_name      = _view_name,
           description    = _description
     WHERE view_id = _view_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE view_update_log_0022000000_user_call
        (bigint,smallint,smallint,text,text,bigint,smallint,text,text) AS
SELECT view_update_log_0022000000_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9);

SELECT view_update_log_0022000000_user
       (1::bigint,
        2::smallint,
        42::smallint,
        'Start view'::text,
        'System Test View Renamed'::text,
        1::bigint,
        43::smallint,
        'A dynamic entry mask that initially shows a table for calcalations with the biggest problems from the user point of view and suggestions what the user can do to solve these problems. Used also as fallback view.'::text,
        null::text);