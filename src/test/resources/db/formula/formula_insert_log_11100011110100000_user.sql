CREATE OR REPLACE FUNCTION formula_insert_log_11100011110100000_user
    (_user_id                  bigint,
     _change_action_id         smallint,
     _field_id_formula_name    smallint,
     _formula_name             text,
     _formula_id               bigint,
     _field_id_formula_type_id smallint,
     _formula_type_id          smallint,
     _field_id_formula_text    smallint,
     _formula_text             text,
     _field_id_resolved_text   smallint,
     _resolved_text            text,
     _field_id_latex           smallint,
     _latex                    text) RETURNS bigint AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,_formula_name,_formula_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_type_id,_formula_type_id,_formula_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_text,_formula_text,_formula_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_resolved_text,_resolved_text,_formula_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_latex,_latex,_formula_id ;

    INSERT INTO user_formulas
                (formula_id, user_id, formula_name, formula_type_id, formula_text, resolved_text, latex, last_update)
         SELECT _formula_id,_user_id,_formula_name,_formula_type_id,_formula_text,_resolved_text,_latex, Now() ;

END
$$ LANGUAGE plpgsql;

PREPARE formula_insert_log_11100011110100000_user_call
        (bigint, smallint, smallint, text, bigint, smallint, smallint, smallint, text, smallint, text, smallint, text) AS
    SELECT formula_insert_log_11100011110100000_user
        ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13);

SELECT formula_insert_log_11100011110100000_user (
               3::bigint,
               1::smallint,
               30::smallint,
               'scale minute to sec'::text,
               1::bigint,
               31::smallint,
               1::smallint,
               33::smallint,
               '{t20}={w104}*60'::text,
               32::smallint,
               '"second (time)" = "minute" * 60'::text,
               886::smallint,
               '\text{s} = 60 \cdot \text{min}'::text);
