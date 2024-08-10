DROP PROCEDURE IF EXISTS source_update_log_excluded_0022220100_user;
CREATE PROCEDURE source_update_log_excluded_0022220100_user
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
     _url                     text,
     _field_id_excluded       smallint,
     _excluded_old            smallint,
     _excluded                smallint)
BEGIN


    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name_old,_source_name,_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,          new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_type_id,_source_type_id_old,_source_type_id,_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value,new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_url,   _url_old, _url,      _source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,   old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,_excluded_old, _excluded, _source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name_old,_source_name,_source_id ;


    UPDATE user_sources
       SET source_name    = _source_name,
           description    = _description,
           source_type_id = _source_type_id,
           `url`          = _url,
           excluded       = _excluded
     WHERE source_id = _source_id
       AND user_id = _user_id;

END;

PREPARE source_update_log_excluded_0022220100_user_call FROM
    'SELECT source_update_log_excluded_0022220100_user (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT source_update_log_excluded_0022220100_user
       (1,
        2,
        57,
        'The International System of Units',
        'System Test Source Renamed',
        1,
        58,
        'Bureau International des Poids et Mesures - The intergovernmental organization through which Member States act together on matters related to measurement science and measurement standards',
        null,
        59,
        4,
        null,
        60,
        'https://www.bipm.org/documents/20126/41483022/SI-Brochure-9.pdf',
        null,
        169,
        null,
        1);