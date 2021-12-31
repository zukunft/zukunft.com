PREPARE formula_std_by_name (text) AS
    SELECT formula_id,
           formula_name,
           formula_name,
           formula_text,
           resolved_text,
           description,
           formula_type_id,
           all_values_needed,
           last_update,
           excluded,
           share_type_id,
           protect_id,
           user_id
      FROM formulas
     WHERE formula_name = $1;