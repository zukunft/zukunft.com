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
     _url                     text) RETURNS bigint AS
$$
DECLARE new_source_id bigint;
BEGIN

    INSERT INTO sources ( source_name)
         SELECT          _source_name
      RETURNING           source_id INTO new_source_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name,new_source_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,   new_source_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,new_source_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_type_id,_source_type_id,new_source_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_url,   _url,       new_source_id ;

    UPDATE sources
       SET user_id        = _user_id,
           description    = _description,
           source_type_id = _source_type_id,
           url            = _url
     WHERE sources.source_id = new_source_id;

    RETURN new_source_id;

END
$$ LANGUAGE plpgsql;

SELECT source_insert_log_0111110000
       ('The International System of Units'::text,
        1::bigint,
        1::smallint,
        57::smallint,
        56::smallint,
        58::smallint,
        'Bureau International des Poids et Mesures - The intergovernmental organization through which Member States act together on matters related to measurement science and measurement standards'::text,
        59::smallint,
        4::bigint,
        60::smallint,
        'https://www.bipm.org/documents/20126/41483022/SI-Brochure-9.pdf'::text);