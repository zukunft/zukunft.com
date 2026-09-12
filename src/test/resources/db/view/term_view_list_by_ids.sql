PREPARE term_view_list_by_ids (bigint, bigint[]) AS
    SELECT     s.term_view_id,
               u.term_view_id AS user_term_view_id,
               s.user_id,
               s.term_id,
               s.view_link_type_id,
               s.view_id,
               CASE WHEN (u.description  <> ''  IS NOT TRUE) THEN s.description   ELSE u.description   END AS description,
               CASE WHEN (u.order_nbr           IS     NULL) THEN s.order_nbr     ELSE u.order_nbr     END AS order_nbr,
               CASE WHEN (u.view_style_id       IS     NULL) THEN s.view_style_id ELSE u.view_style_id END AS view_style_id,
               CASE WHEN (u.excluded            IS     NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
               CASE WHEN (u.share_type_id       IS     NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
               CASE WHEN (u.protect_id          IS     NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id,
               CASE WHEN (ul.view_name   <> ''  IS NOT TRUE) THEN l.view_name     ELSE ul.view_name    END AS view_name1,
               CASE WHEN (ul.description <> ''  IS NOT TRUE) THEN l.description   ELSE ul.description  END AS description1,
               CASE WHEN (ul2.term_name  <> ''  IS NOT TRUE) THEN l2.term_name    ELSE ul2.term_name   END AS term_name2
          FROM term_views s
     LEFT JOIN user_term_views u ON s.term_view_id = u.term_view_id
                                AND u.user_id = $1 LEFT JOIN views l ON s.view_id = l.view_id LEFT JOIN user_views ul ON l.view_id = ul.view_id
                                AND ul.user_id = $1 LEFT JOIN terms l2 ON s.term_id = l2.term_id LEFT JOIN user_terms ul2 ON l2.term_id = ul2.term_id
                                AND ul2.user_id = $1
         WHERE s.term_view_id = ANY ($2);