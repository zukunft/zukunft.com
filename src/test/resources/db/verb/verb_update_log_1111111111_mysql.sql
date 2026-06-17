DROP PROCEDURE IF EXISTS verb_update_log_1111111111;
CREATE PROCEDURE verb_update_log_1111111111
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
     _impact                       numeric)
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
           `usage`             = _usage,
           impact              = _impact
     WHERE verb_id = _verb_id;

END;

PREPARE verb_update_log_1111111111_call FROM
    'SELECT verb_update_log_1111111111 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT verb_update_log_1111111111
     (3,
      1,
      23,
      null,
      'is a',
      2,
      24,
      null,
      'is',
      25,
      null,
      'the main child to parent relation e.g. Zurich is a canton. The reverse is valid and usually plural is used e.g. cantons are Zurich,Bern,...',
      26,
      null,
      'are',
      27,
      null,
      'are',
      28,
      null,
      'are',
      29,
      null,
      'of all',
      796,
      null,
      23,
      800,
      null,
      123.4);