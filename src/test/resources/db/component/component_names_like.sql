PREPARE component_names_like (bigint, text, bigint, bigint) AS
         SELECT s.component_id,
                u.component_id AS user_component_id,
                s.user_id,
                CASE WHEN (u.component_name <> '' IS NOT TRUE) THEN s.component_name ELSE u.component_name END AS component_name
           FROM components s
      LEFT JOIN user_components u ON s.component_id = u.component_id
            AND u.user_id = $1
          WHERE s.component_name like $2
            AND ( s.component_type_id NOT IN (17,21,22,71,54,53,54,69,70,72,73,74,23,75,24,76,77,78,79,80,81,82,83,84,85,86,87,88,90,91,92,25,39,48,49,50,51,52,100,101,99,26,27,93,29,30,31,94,95,32,33,34,35,18,19,20,63) OR s.component_type_id IS NULL )
       ORDER BY s.component_name
          LIMIT $3
         OFFSET $4;
