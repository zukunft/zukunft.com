SELECT s.formula_id,
       s.formula_name,
       s.user_id,
       s.formula_text,
       s.resolved_text,
       s.description,
       s.formula_type_id,
       s.all_values_needed,
       s.last_update,
       s.excluded,
       l.code_id
  FROM formulas s
  LEFT JOIN formula_types l ON s.formula_type_id = l.formula_type_id
 WHERE s.formula_id = ?;