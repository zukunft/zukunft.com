PREPARE triple_names_like (bigint, text, bigint, bigint) AS
         SELECT s.triple_id,
                u.triple_id AS user_triple_id,
                s.user_id,
                CASE WHEN (u.triple_name <> '' IS NOT TRUE) THEN s.triple_name ELSE u.triple_name END AS triple_name
           FROM triples s
      LEFT JOIN user_triples u ON s.triple_id = u.triple_id
            AND u.user_id = $1
          WHERE s.triple_name like $2
       ORDER BY s.triple_name
          LIMIT $3
         OFFSET $4;
