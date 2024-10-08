CREATE OR REPLACE FUNCTION view_insert_log_011100000
    (_view_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_name      smallint,
     _field_id_user_id        smallint,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_view_id bigint;
BEGIN

    INSERT INTO views ( view_name)
         SELECT        _view_name
      RETURNING         view_id INTO new_view_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_name,_view_name, new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,   new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,new_view_id ;

    UPDATE views
       SET user_id        = _user_id,
           description    = _description
     WHERE views.view_id = new_view_id;

    RETURN new_view_id;

END
$$ LANGUAGE plpgsql;

PREPARE view_insert_log_011100000_call
    (text, bigint, smallint, smallint, smallint, smallint, text) AS
SELECT view_insert_log_011100000
    ($1,$2, $3, $4, $5, $6, $7);

SELECT view_insert_log_011100000 (
               'Word'::text,
               1::bigint,
               1::smallint,
               42::smallint,
               278::smallint,
               43::smallint,
               'the default view for words'::text);