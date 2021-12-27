PREPARE formula_std_by_id FROM
    'SELECT
        formula_id,
        formula_name,
        formula_name,
        formula_text,
        resolved_text,
        description,
        formula_type_id,
        all_values_needed,
        excluded,
        user_id
   FROM formulas
  WHERE formula_id = ?';