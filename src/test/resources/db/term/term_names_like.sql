PREPARE term_names_like (bigint, text, bigint, bigint) AS
         SELECT s.term_id,
                u.term_id AS user_term_id,
                s.user_id,
                CASE WHEN (u.term_name <> '' IS NOT TRUE) THEN s.term_name ELSE u.term_name END AS term_name
           FROM terms s
      LEFT JOIN user_terms u ON s.term_id = u.term_id
            AND u.user_id = $1
          WHERE s.term_name like $2
       ORDER BY s.term_name
          LIMIT $3
         OFFSET $4;
