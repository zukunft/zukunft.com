SELECT f.formula_id,
       f.formula_name,
       CASE WHEN (u.formula_text      <> '' IS NOT TRUE) THEN f.formula_text      ELSE u.formula_text      END AS formula_text,
       CASE WHEN (u.resolved_text     <> '' IS NOT TRUE) THEN f.resolved_text     ELSE u.resolved_text     END AS resolved_text,
       CASE WHEN (u.description       <> '' IS NOT TRUE) THEN f.description       ELSE u.description       END AS description,
       CASE WHEN (u.formula_type_id         IS     NULL) THEN f.formula_type_id   ELSE u.formula_type_id   END AS formula_type_id,
       CASE WHEN (c.code_id           <> '' IS NOT TRUE) THEN t.code_id           ELSE c.code_id           END AS code_id,
       CASE WHEN (u.all_values_needed       IS     NULL) THEN f.all_values_needed ELSE u.all_values_needed END AS all_values_needed,
       CASE WHEN (u.last_update             IS     NULL) THEN f.last_update       ELSE u.last_update       END AS last_update,
       CASE WHEN (u.excluded                IS     NULL) THEN f.excluded          ELSE u.excluded          END AS excluded
  FROM formula_links l, formulas f
  LEFT JOIN user_formulas u ON u.formula_id = f.formula_id
                           AND u.user_id = 3
  LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
  LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
 WHERE l.phrase_id = 1 AND l.formula_id = f.formula_id
 GROUP BY f.formula_id;