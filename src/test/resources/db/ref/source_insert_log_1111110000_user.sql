CREATE OR REPLACE FUNCTION source_insert_log_1111110000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_source_name    smallint,
     _source_name             text,
     _source_id               bigint,
     _field_id_description    smallint,
     _description             text,
     _field_id_source_type_id smallint,
     _source_type_id          bigint,
     _field_id_url            smallint,
     _url                     text) RETURNS void AS
$$
BEGIN

    WITH
        change_insert_source_name AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
                 SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name,_source_id),
        change_insert_description AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
                 SELECT          _user_id,_change_action_id,_field_id_description,_description,_source_id),
        change_insert_source_type_id AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,      row_id)
                 SELECT          _user_id,_change_action_id,_field_id_source_type_id,_source_type_id,_source_id),
        change_insert_url AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value, row_id)
                 SELECT          _user_id,_change_action_id,_field_id_url,   _url,      _source_id)

    INSERT INTO user_sources
                (source_id, user_id, source_name, description, source_type_id, url)
         SELECT _source_id,_user_id,_source_name,_description,_source_type_id,_url ;

END
$$ LANGUAGE plpgsql;

SELECT source_insert_log_1111110000_user
       (1::bigint,
        3::smallint,
        56::smallint,
        'The International System of Units'::text,
        3::bigint,
        57::smallint,
        'Bureau International des Poids et Mesures - The intergovernmental organization through which Member States act together on matters related to measurement science and measurement standards'::text,
        58::smallint,
        4::bigint,
        59::smallint,
        'https://www.bipm.org/documents/20126/41483022/SI-Brochure-9.pdf'::text);