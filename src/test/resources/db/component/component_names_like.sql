PREPARE component_names_like (bigint, text, bigint, bigint) AS
         SELECT s.component_id,
                u.component_id AS user_component_id,
                s.user_id,
                CASE WHEN (u.component_name <> '' IS NOT TRUE) THEN s.component_name ELSE u.component_name END AS component_name
           FROM components s
      LEFT JOIN user_components u ON s.component_id = u.component_id
            AND u.user_id = $1
          WHERE s.component_name like $2
            AND ( s.component_type_id NOT IN (17,21,22,71,55,53,54,150,69,70,85,117,139,72,73,74,23,75,24,76,118,119,120,121,77,78,79,80,81,82,83,84,40,86,171,172,87,88,90,91,92,25,122,39,48,123,49,50,51,52,124,173,174,100,127,101,125,99,153,126,26,27,93,28,29,30,94,95,31,32,33,34,18,19,168,169,170,179,180,20,147,128,175,176,177,178,154,155,156,157,158,159,160,161,162,143,144,56,129,130,63) OR s.component_type_id IS NULL )
       ORDER BY s.component_name
          LIMIT $3
         OFFSET $4;
