PREPARE user_list_by_count_changes AS
        SELECT
             s.user_id,
             s.user_name,
             s.code_id,
             s.ip_address,
             s.email,
             s.first_name,
             s.last_name,
             s.last_word_id,
             s.source_id,
             s.user_profile_id,
             l.changes
        FROM users s
   LEFT JOIN
    ( SELECT g.user_id,
             SUM (g.changes) AS changes
        FROM
    ( SELECT user_id,
             COUNT (word_id) AS changes
        FROM user_words
    GROUP BY user_id
UNION SELECT user_id,
             COUNT (triple_id) AS changes
        FROM user_triples
    GROUP BY user_id
UNION SELECT user_id,
             COUNT (phrase_id1) AS changes
        FROM user_values
    GROUP BY user_id
UNION SELECT user_id,
             COUNT (formula_id) AS changes
        FROM user_formulas
    GROUP BY user_id
UNION SELECT user_id,
             COUNT (ref_id) AS changes
        FROM user_refs
    GROUP BY user_id
UNION SELECT user_id,
             COUNT (source_id) AS changes
        FROM user_sources
     GROUP BY user_id
UNION SELECT user_id,
             COUNT (view_id) AS changes
        FROM user_views
    GROUP BY user_id) g
    GROUP BY user_id) l
          ON s.user_id = l.user_id
       WHERE l.changes IS NOT NULL
    ORDER BY l.changes DESC;
