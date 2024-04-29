CREATE OR REPLACE FUNCTION source_insert_log_0111110000
    (_source_name             text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_source_name    smallint,
     _field_id_user_id        smallint,
     _field_id_description    smallint,
     _description             text,
     _field_id_source_type_id smallint,
     _source_type_id          bigint,
     _field_id_url            smallint,
     _url                     text) RETURNS void AS
$$
BEGIN

    WITH
        source_insert  AS (
            INSERT INTO sources ( source_name)
                 VALUES       (_source_name)
              RETURNING         source_id ),

        change_insert_source_name AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,                row_id)
                 SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name,source_insert.source_id
                   FROM source_insert),
        change_insert_user_id
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value,              row_id)
                 SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,  source_insert.source_id
                   FROM source_insert),
        change_insert_description
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,                row_id)
                 SELECT          _user_id,_change_action_id,_field_id_description,_description,source_insert.source_id
                   FROM source_insert),
        change_insert_source_type_id
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,                   row_id)
                 SELECT          _user_id,_change_action_id,_field_id_source_type_id,_source_type_id,source_insert.source_id
                   FROM source_insert),
        change_insert_url
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value,        row_id)
                 SELECT          _user_id,_change_action_id,_field_id_url,   _url,source_insert.source_id
                   FROM source_insert)
    UPDATE sources
       SET user_id        = _user_id,
           description    = _description,
           source_type_id = _source_type_id,
           url            = _url
      FROM source_insert
     WHERE sources.source_id = source_insert.source_id;

END
$$ LANGUAGE plpgsql;

SELECT source_insert_log_0111110000
       ('The International System of Units'::text,
        1::bigint,
        9::smallint,
        56::smallint,
        9::smallint,
        57::smallint,
        'Bureau International des Poids et Mesures - The intergovernmental organization through which Member States act together on matters related to measurement science and measurement standards'::text,
        58::smallint,
        4::bigint,
        59::smallint,
        'https://www.bipm.org/documents/20126/41483022/SI-Brochure-9.pdf'::text);