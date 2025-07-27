PREPARE formula_count AS
    SELECT COUNT(s.formula_id) + COUNT(u.formula_id) AS count
      FROM formulas s
 LEFT JOIN user_formulas  u ON s.formula_id = u.formula_id;