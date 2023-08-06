PREPARE triple_names_like FROM
   'SELECT
                s.triple_id,
                u.triple_id AS user_triple_id,
                s.user_id,
                IF(u.triple_name IS NULL, s.triple_name, u.triple_name) AS triple_name
           FROM triples s
      LEFT JOIN user_triples u ON s.triple_id = u.triple_id
            AND u.user_id = ?
          WHERE s.triple_name like ?
       ORDER BY s.triple_name
          LIMIT ?
         OFFSET ?';