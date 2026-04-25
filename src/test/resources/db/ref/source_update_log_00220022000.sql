CREATE OR REPLACE FUNCTION source_update_log_00220022000
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_source_name    smallint,
     _source_name_old         text,
     _source_name             text,
     _source_id               bigint,
     _field_id_description    smallint,
     _description_old         text,
     _description             text,
     _field_id_source_type_id smallint,
     _source_type_id_old      smallint,
     _source_type_id          smallint,
     _field_id_url            smallint,
     _url_old                 text,
     _url                     text) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name_old,_source_name,_source_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_source_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,          new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_type_id,_source_type_id_old,_source_type_id,_source_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_url,   _url_old,  _url,      _source_id ;

    UPDATE sources
       SET source_name    = _source_name,
           description    = _description,
           source_type_id = _source_type_id,
           url            = _url
     WHERE source_id = _source_id;

END
$$ LANGUAGE plpgsql;

PREPARE source_update_log_00220022000_call
        (bigint,smallint,smallint,text,text,bigint,smallint,text,text,smallint,smallint,smallint,smallint,text,text) AS
SELECT source_update_log_00220022000
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15);

SELECT source_update_log_00220022000
       (3::bigint,
        2::smallint,
        57::smallint,
        'Federal Statistical Office'::text,
        'System Test Source Renamed'::text,
        7::bigint,
        58::smallint,
        'The Federal Statistical Office is a Federal agency of the Swiss Confederation. It is the statistics office of Switzerland.'::text,
        null::text,
        59::smallint,
        4::smallint,
        null::smallint,
        60::smallint,
        'https://www.bfs.admin.ch/bfs/en/home.html'::text,
        null::text);