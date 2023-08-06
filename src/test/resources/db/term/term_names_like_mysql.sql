PREPARE term_names_like FROM
   'SELECT
                s.term_id,
                u.term_id AS user_term_id,
                s.user_id,
                IF(u.term_name IS NULL, s.term_name, u.term_name) AS term_name
           FROM terms s
      LEFT JOIN user_terms u ON s.term_id = u.term_id
            AND u.user_id = ?
          WHERE s.term_name like ?
       ORDER BY s.term_name
          LIMIT ?
         OFFSET ?';