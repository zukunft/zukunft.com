CREATE OR REPLACE FUNCTION view_insert_log_111100000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_name      smallint,
     _view_name               text,
     _view_id                 bigint,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_name,_view_name,_view_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,_view_id ;

    INSERT INTO user_views
                (view_id, user_id, view_name, description)
         SELECT _view_id,_user_id,_view_name,_description ;

END
$$ LANGUAGE plpgsql;

PREPARE view_insert_log_111100000_user_call
        (bigint,smallint,smallint,text,bigint,smallint,text) AS
    SELECT view_insert_log_111100000_user
        ($1,$2,$3,$4,$5,$6,$7);

SELECT view_insert_log_111100000_user (
               1::bigint,
               1::smallint,
               42::smallint,
               'Start view'::text,
               1::bigint,
               43::smallint,
               'A dynamic entry mask that initially shows a table for calcalations with the biggest problems from the user point of view and suggestions what the user can do to solve these problems. Used also as fallback view.'::text);