PREPARE formula_norm_by_id FROM
    'SELECT
        formula_id,
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
  WHERE formula_id = ?';