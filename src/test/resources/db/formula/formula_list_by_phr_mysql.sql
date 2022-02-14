PREPARE formula_list_by_phr FROM
    'SELECT s.formula_id,
            u.formula_id AS user_formula_id,
            s.user_id,
            l.phrase_id,
            IF(u.formula_name      IS NULL, s.formula_name,      u.formula_name)      AS formula_name,
            IF(u.formula_text      IS NULL, s.formula_text,      u.formula_text)      AS formula_text,
            IF(u.resolved_text     IS NULL, s.resolved_text,     u.resolved_text)     AS resolved_text,
            IF(u.description       IS NULL, s.description,       u.description)       AS description,
            IF(u.formula_type_id   IS NULL, s.formula_type_id,   u.formula_type_id)   AS formula_type_id,
            IF(u.all_values_needed IS NULL, s.all_values_needed, u.all_values_needed) AS all_values_needed,
            IF(u.last_update       IS NULL, s.last_update,       u.last_update)       AS last_update,
            IF(u.excluded          IS NULL, s.excluded,          u.excluded)          AS excluded,
            IF(u.share_type_id     IS NULL, s.share_type_id,     u.share_type_id)     AS share_type_id,
            IF(u.protect_id        IS NULL, s.protect_id,        u.protect_id)        AS protect_id
       FROM formulas s
  LEFT JOIN user_formulas u  ON s.formula_id = u.formula_id AND u.user_id = ?
  LEFT JOIN formula_links l  ON s.formula_id = l.formula_id
      WHERE l.phrase_id = ?';