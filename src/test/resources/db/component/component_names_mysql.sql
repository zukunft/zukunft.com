PREPARE component_names FROM
   'SELECT
                s.component_id,
                u.component_id AS user_component_id,
                s.user_id,
                IF(u.component_name IS NULL, s.component_name, u.component_name) AS component_name
           FROM components s
      LEFT JOIN user_components u ON s.component_id = u.component_id
            AND u.user_id = ?
          WHERE ( s.component_type_id NOT IN (17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32) OR s.component_type_id IS NULL )
       ORDER BY s.component_name
          LIMIT ?
         OFFSET ?';