CREATE OR REPLACE FUNCTION view_update_log_00220000000
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

    UPDATE views
       SET view_name      = _view_name,
           description    = _description
     WHERE view_id = _view_id;

END
$$ LANGUAGE plpgsql;

PREPARE view_update_log_00220000000_call
        (bigint,smallint,smallint,text,text,bigint,smallint,text,text) AS
SELECT view_update_log_00220000000
        ($1,$2,$3,$4,$5,$6,$7,$8,$9);

SELECT view_update_log_00220000000
       (3::bigint,
        2::smallint,
        42::smallint,
        'Historic'::text,
        'System Test View Renamed'::text,
        99::bigint,
        43::smallint,
        'show mainly related words that are relevant in sciences'::text,
        null::text);