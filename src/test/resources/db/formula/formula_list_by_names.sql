PREPARE formula_list_by_names (bigint, text[]) AS
    SELECT s.formula_id,
           u.formula_id AS user_formula_id,
           s.user_id,
           CASE WHEN (u.formula_name  <> '' IS NOT TRUE) THEN s.formula_name      ELSE u.formula_name      END AS formula_name,
           CASE WHEN (u.formula_text  <> '' IS NOT TRUE) THEN s.formula_text      ELSE u.formula_text      END AS formula_text,
           CASE WHEN (u.resolved_text <> '' IS NOT TRUE) THEN s.resolved_text     ELSE u.resolved_text     END AS resolved_text,
           CASE WHEN (u.description   <> '' IS NOT TRUE) THEN s.description       ELSE u.description       END AS description,
           CASE WHEN (u.formula_type_id     IS     NULL) THEN s.formula_type_id   ELSE u.formula_type_id   END AS formula_type_id,
           CASE WHEN (u.all_values_needed   IS     NULL) THEN s.all_values_needed ELSE u.all_values_needed END AS all_values_needed,
           CASE WHEN (u.last_update         IS     NULL) THEN s.last_update       ELSE u.last_update       END AS last_update,
           CASE WHEN (u.excluded            IS     NULL) THEN s.excluded          ELSE u.excluded          END AS excluded,
           CASE WHEN (u.share_type_id       IS     NULL) THEN s.share_type_id     ELSE u.share_type_id     END AS share_type_id,
           CASE WHEN (u.protect_id          IS     NULL) THEN s.protect_id        ELSE u.protect_id        END AS protect_id
      FROM formulas s
 LEFT JOIN user_formulas  u ON s.formula_id = u.formula_id AND u.user_id = $1
     WHERE s.formula_name = ANY ($2);