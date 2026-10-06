PREPARE component_names_like (bigint, text, bigint, bigint) AS
    SELECT     s.component_id,
               u.component_id AS user_component_id,
               s.user_id,
               CASE WHEN (u.component_name <> ''  IS NOT TRUE) THEN s.component_name ELSE u.component_name END AS component_name
          FROM components s
     LEFT JOIN user_components u ON s.component_id = u.component_id
                                AND u.user_id = $1
         WHERE s.component_name ilike $2
           AND ( s.component_type_id NOT IN (2,121,122,128,129,138,139,123,130,131,85,87,88,86,132,127,134,135,136,137,133,141,142,143,144,145,147,148,157,158,159,160,151,152,149,150,153,154,155,156,161,164,167,168,165,166,163,223,221,169,192,173,174,172,175,176,177,178,179,184,188,181,187,182,185,180,183,186,170,171,189,16,15,17,18,224,222,19,21,23,22,24,13,14,255,256,257,258,259,120,260,261,262,263,264,227,89,90,91,104,196,197,198,199,80,225,226,228,204,205,206,207,203,202,201,70,29,30,1,12,211,208,209,210,212,213,253,251,252)
            OR   s.component_type_id IS NULL )
      ORDER BY s.component_name
         LIMIT $3
        OFFSET $4;