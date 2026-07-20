PREPARE user_list_by_count_changes FROM
   'SELECT     s.user_id,
               s.user_name,
               s.code_id,
               s.ip_address,
               s.email,
               s.first_name,
               s.last_name,
               s.term_id,
               s.view_id,
               s.source_id,
               s.user_profile_id,
               l.changes
          FROM users s
     LEFT JOIN ( SELECT g.user_id, SUM (g.changes) AS changes
                   FROM ( SELECT user_id, COUNT (*) AS changes FROM user_words GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_triples GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_sources GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_refs GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_groups GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_values GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_formulas GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_formula_links GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_results GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_views GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_view_relations GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_term_views GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_components GROUP BY user_id
                    UNION SELECT user_id, COUNT (*) AS changes FROM user_component_links GROUP BY user_id ) g
               GROUP BY user_id ) l
            ON s.user_id = l.user_id
         WHERE l.changes IS NOT NULL
      ORDER BY l.changes DESC';