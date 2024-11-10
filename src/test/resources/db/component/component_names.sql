PREPARE component_names (bigint, bigint, bigint) AS
         SELECT s.component_id,
                u.component_id AS user_component_id,
                s.user_id,
                CASE WHEN (u.component_name <> '' IS NOT TRUE) THEN s.component_name ELSE u.component_name END AS component_name
           FROM components s
      LEFT JOIN user_components u ON s.component_id = u.component_id
            AND u.user_id = $1
          WHERE ( s.component_type_id NOT IN (17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34) OR s.component_type_id IS NULL )
       ORDER BY s.component_name
          LIMIT $2
         OFFSET $3;
