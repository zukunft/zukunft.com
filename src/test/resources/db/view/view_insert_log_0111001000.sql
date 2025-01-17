CREATE OR REPLACE FUNCTION view_insert_log_0111001000
    (_view_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_name      smallint,
     _field_id_user_id        smallint,
     _field_id_description    smallint,
     _description             text,
     _field_id_code_id        smallint,
     _code_id                 text) RETURNS bigint AS
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

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,_code_id,   new_view_id ;

    UPDATE views
       SET user_id        = _user_id,
           description    = _description,
           code_id        = _code_id
     WHERE views.view_id = new_view_id;

    RETURN new_view_id;

END
$$ LANGUAGE plpgsql;

PREPARE view_insert_log_0111001000_call
    (text, bigint, smallint, smallint, smallint, smallint, text, smallint, text) AS
SELECT view_insert_log_0111001000
    ($1,$2, $3, $4, $5, $6, $7, $8, $9);

SELECT view_insert_log_0111001000 (
               'Start view'::text,
               1::bigint,
               1::smallint,
               42::smallint,
               278::smallint,
               43::smallint,
               'A dynamic entry mask that initially shows a table for calculations with the biggest problems from the user point of view and suggestions what the user can do to solve these problems. Used also as fallback view.'::text,
               44::smallint,
               'entry_view'::text);