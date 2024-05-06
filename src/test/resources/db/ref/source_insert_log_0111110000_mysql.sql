DROP PROCEDURE IF EXISTS source_insert_log_0111110000;
CREATE PROCEDURE source_insert_log_0111110000
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
     _url                     text)
BEGIN

    INSERT INTO sources ( source_name)
         SELECT          _source_name ;

    SELECT LAST_INSERT_ID() AS @new_source_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name,@new_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,  @new_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_type_id,_source_type_id,@new_source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_url,   _url,      @new_source_id ;

    UPDATE sources
       SET user_id        = _user_id,
           description    = _description,
           source_type_id = _source_type_id,
           `url`          = _url
     WHERE sources.source_id = @new_source_id;

END;

SELECT source_insert_log_0111110000
       ('The International System of Units',
        1,
        1,
        57,
        56,
        58,
        'Bureau International des Poids et Mesures - The intergovernmental organization through which Member States act together on matters related to measurement science and measurement standards',
        59,
        4,
        60,
        'https://www.bipm.org/documents/20126/41483022/SI-Brochure-9.pdf');