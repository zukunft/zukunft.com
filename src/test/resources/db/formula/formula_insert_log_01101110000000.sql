CREATE OR REPLACE FUNCTION formula_insert_log_01101110000000
    (_formula_name             text,
     _user_id                  bigint,
     _change_action_id         smallint,
     _field_id_formula_name    smallint,
     _field_id_user_id         smallint,
     _field_id_formula_type_id smallint,
     _formula_type_id          bigint,
     _field_id_formula_text    smallint,
     _formula_text             text,
     _field_id_resolved_text   smallint,
     _resolved_text            text) RETURNS bigint AS
$$
DECLARE new_formula_id bigint;
BEGIN

    INSERT INTO formulas ( formula_name)
         SELECT           _formula_name
      RETURNING            formula_id INTO new_formula_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,  _formula_name,   new_formula_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,       _user_id,        new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          new_value,      row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_type_id,_formula_type_id,new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          new_value,      row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_text,   _formula_text,   new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          new_value,      row_id)
         SELECT         _user_id,_change_action_id,_field_id_resolved_text,  _resolved_text,  new_formula_id ;

    UPDATE formulas
       SET user_id         = _user_id,
           formula_type_id = _formula_type_id,
           formula_text    = _formula_text,
           resolved_text   = _resolved_text
     WHERE formulas.formula_id = new_formula_id;

    RETURN new_formula_id;

END
$$ LANGUAGE plpgsql;

PREPARE formula_insert_log_01101110000000_call
        (text, bigint, smallint, smallint, smallint, smallint, bigint, smallint, text, smallint, text) AS
    SELECT formula_insert_log_01101110000000
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11);

SELECT formula_insert_log_01101110000000 (
               'scale minute to sec'::text,
               1::bigint,
               1::smallint,
               30::smallint,
               173::smallint,
               31::smallint,
               1::bigint,
               33::smallint,
               '{w19}={w101}*60'::text,
               32::smallint,
               '"second" = "minute" * 60'::text);
