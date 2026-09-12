PREPARE formula_by_user_list_by_ids FROM
   'SELECT     s.formula_id,
               s.formula_name,
               l.user_id,
               l.user_name
          FROM user_formulas s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.formula_id IN (?)
           AND ( s.excluded <> ? OR s.excluded IS NULL )';