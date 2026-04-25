CREATE OR REPLACE FUNCTION verb_update_log_1111111111
    (_user_id                      bigint,
     _change_action_id             smallint,
     _field_id_verb_name           smallint,
     _verb_name_old                text,
     _verb_name                    text,
     _verb_id                      bigint,
     _field_id_code_id             smallint,
     _code_id_old                  text,
     _code_id                      text,
     _field_id_description         smallint,
     _description_old              text,
     _description                  text,
     _field_id_name_plural         smallint,
     _name_plural_old              text,
     _name_plural                  text,
     _field_id_name_reverse        smallint,
     _name_reverse_old             text,
     _name_reverse                 text,
     _field_id_name_plural_reverse smallint,
     _name_plural_reverse_old      text,
     _name_plural_reverse          text,
     _field_id_formula_name        smallint,
     _formula_name_old             text,
     _formula_name                 text,
     _field_id_usage               smallint,
     _usage_old                    bigint,
     _usage                        bigint,
     _field_id_impact              smallint,
     _impact_old                   numeric,
     _impact                       numeric) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_verb_name,_verb_name_old,_verb_name,_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  old_value,   new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,_code_id_old,_code_id,  _verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_name_plural,_name_plural_old,_name_plural,_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,        new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_name_reverse,_name_reverse_old,_name_reverse,_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,              old_value,               new_value,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_name_plural_reverse,_name_plural_reverse_old,_name_plural_reverse,_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,        new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,_formula_name_old,_formula_name,_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_usage, _usage_old,_usage,    _verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_impact,_impact_old,_impact,   _verb_id ;

    UPDATE verbs
       SET verb_name           = _verb_name,
           code_id             = _code_id,
           description         = _description,
           name_plural         = _name_plural,
           name_reverse        = _name_reverse,
           name_plural_reverse = _name_plural_reverse,
           formula_name        = _formula_name,
           usage               = _usage,
           impact              = _impact
     WHERE verb_id = _verb_id;

END
$$ LANGUAGE plpgsql;

PREPARE verb_update_log_1111111111_call
    (bigint, smallint, smallint, text, text, bigint, smallint, text, text, smallint, text, text, smallint, text, text, smallint, text, text, smallint, text, text, smallint, text, text, smallint, bigint, bigint, smallint, numeric, numeric) AS
SELECT verb_update_log_1111111111
    ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30);

SELECT verb_update_log_1111111111
        (3::bigint,
         1::smallint,
         23::smallint,
         null::text,
         'is a'::text,
         2::bigint,
         24::smallint,
         null::text,
         'is'::text,
         25::smallint,
         null::text,
         'the main child to parent relation e.g. Zurich is a Canton. The reverse is valid and usually plural is used e.g. Cantons are Zurich,Bern,...'::text,
         26::smallint,
         null::text,
         'are'::text,
         27::smallint,
         null::text,
         'are'::text,
         28::smallint,
         null::text,
         'are'::text,
         29::smallint,
         null::text,
         'of all'::text,
         796::smallint,
         null::bigint,
         23::bigint,
         800::smallint,
         null::numeric,
         123.4::numeric);