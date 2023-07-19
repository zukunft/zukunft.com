PREPARE formula_link_by_link_ids (int, int, int) AS
    SELECT
               s.formula_link_id,
               u.formula_link_id AS user_formula_link_id,
               s.user_id,
               s.formula_id,
               s.phrase_id,
               CASE WHEN (u.link_type_id  IS NULL) THEN s.link_type_id  ELSE u.link_type_id  END AS link_type_id,
               CASE WHEN (u.excluded      IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
               CASE WHEN (u.share_type_id IS NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
               CASE WHEN (u.protect_id    IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id
          FROM formula_links s
     LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id
           AND u.user_id = $1
         WHERE s.formula_id = $2 AND s.phrase_id = $3;