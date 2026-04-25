DROP PROCEDURE IF EXISTS source_update_log_00220022000;
CREATE PROCEDURE source_update_log_00220022000
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
     _url                     text)
BEGIN


    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name_old,_source_name,_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,          new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_type_id,_source_type_id_old,_source_type_id,_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value,new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_url,   _url_old, _url,      _source_id ;

    UPDATE sources
       SET source_name    = _source_name,
           description    = _description,
           source_type_id = _source_type_id,
           `url`          = _url
     WHERE source_id = _source_id;

END;

PREPARE source_update_log_00220022000_call FROM
    'SELECT source_update_log_00220022000 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT source_update_log_00220022000
       (3,
        2,
        57,
        'Federal Statistical Office',
        'System Test Source Renamed',
        7,
        58,
        'The Federal Statistical Office is a Federal agency of the Swiss Confederation. It is the statistics office of Switzerland.',
        null,
        59,
        4,
        null,
        60,
        'https://www.bfs.admin.ch/bfs/en/home.html',
        null);