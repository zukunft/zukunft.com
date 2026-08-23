PREPARE formula_link_by_user_list_by_ids (bigint[], bigint) AS
    SELECT     s.formula_link_id,
               l.user_id,
               l.user_name
          FROM user_formula_links s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.formula_link_id = ANY ($1)
           AND ( s.excluded <> $2 OR s.excluded IS NULL );