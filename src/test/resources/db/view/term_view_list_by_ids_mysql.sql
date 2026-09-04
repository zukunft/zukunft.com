PREPARE term_view_list_by_ids FROM
   'SELECT     s.term_view_id,
               u.term_view_id AS user_term_view_id,
               s.user_id,
               s.term_id,
               s.view_link_type_id,
               s.view_id,
               IF(u.description   IS NULL, s.description,   u.description)   AS description,
               IF(u.order_nbr     IS NULL, s.order_nbr,     u.order_nbr)     AS order_nbr,
               IF(u.view_style_id IS NULL, s.view_style_id, u.view_style_id) AS view_style_id,
               IF(u.excluded      IS NULL, s.excluded,      u.excluded)      AS excluded,
               IF(u.share_type_id IS NULL, s.share_type_id, u.share_type_id) AS share_type_id,
               IF(u.protect_id    IS NULL, s.protect_id,    u.protect_id)    AS protect_id,
               IF(ul.view_name    IS NULL, l.view_name,     ul.view_name)    AS view_name1,
               IF(ul.description  IS NULL, l.description,   ul.description)  AS description1,
               IF(ul2.term_name   IS NULL, l2.term_name,    ul2.term_name)   AS term_name2
          FROM term_views s
     LEFT JOIN user_term_views u ON s.term_view_id = u.term_view_id
                                AND u.user_id = ? LEFT JOIN views l ON s.view_id = l.view_id LEFT JOIN user_views ul ON l.view_id = ul.view_id
                                AND ul.user_id = ? LEFT JOIN terms l2 ON s.term_id = l2.term_id LEFT JOIN user_terms ul2 ON l2.term_id = ul2.term_id
                                AND ul2.user_id = ?
         WHERE s.term_view_id IN (?)';