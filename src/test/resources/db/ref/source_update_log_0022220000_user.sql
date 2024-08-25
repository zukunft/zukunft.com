CREATE OR REPLACE FUNCTION source_update_log_0022220000_user
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

    UPDATE user_sources
       SET source_name    = _source_name,
           description    = _description,
           source_type_id = _source_type_id,
           url            = _url
     WHERE source_id = _source_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE source_update_log_0022220000_user_call
        (bigint,smallint,smallint,text,text,bigint,smallint,text,text,smallint,smallint,smallint,smallint,text,text) AS
SELECT source_update_log_0022220000_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15);

SELECT source_update_log_0022220000_user
       (1::bigint,
        2::smallint,
        57::smallint,
        'The International System of Units'::text,
        'System Test Source Renamed'::text,
        1::bigint,
        58::smallint,
        'Bureau International des Poids et Mesures - The intergovernmental organization through which Member States act together on matters related to measurement science and measurement standards'::text,
        null::text,
        59::smallint,
        4::smallint,
        null::smallint,
        60::smallint,
        'https://www.bipm.org/documents/20126/41483022/SI-Brochure-9.pdf'::text,
        null::text);