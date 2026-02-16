CREATE OR REPLACE FUNCTION view_style_insert_log_1111
    (_view_style_name          text,
     _user_id                  bigint,
     _change_action_id         smallint,
     _field_id_view_style_name smallint,
     _field_id_code_id         smallint,
     _code_id                  text,
     _field_id_description     smallint,
     _description              text) RETURNS bigint AS
$$
DECLARE new_view_style_id bigint;
BEGIN

        INSERT INTO view_styles (view_style_name)
             SELECT              _view_style_name
          RETURNING view_style_id INTO new_view_style_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value,       row_id)
             SELECT          _user_id,_change_action_id,_field_id_view_style_name,_view_style_name, new_view_style_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value,       row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,        _code_id,         new_view_style_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value,       row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,    _description,     new_view_style_id ;

             UPDATE view_styles
                SET code_id     = _code_id,
                    description = _description
              WHERE view_styles.view_style_id = new_view_style_id;

             RETURN new_view_style_id;

END
$$ LANGUAGE plpgsql;

PREPARE view_style_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT view_style_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT view_style_insert_log_1111
    ('1/3 width'::text,
     1::bigint,
     1::smallint,
     785::smallint,
     786::smallint,
     'col-md-4'::text,
     783::smallint,
     'use 1/3 of the width (col-md-4)'::text);