PREPARE view_relation_list_by_parent_ids (bigint, bigint[]) AS
    SELECT     s.view_relation_id,
               u.view_relation_id AS user_view_relation_id,
               s.user_id,
               s.parent_view_id,
               s.view_relation_type_id,
               s.child_view_id,
               CASE WHEN (u.description   <> ''  IS NOT TRUE) THEN s.description  ELSE u.description   END AS description,
               CASE WHEN (u.start_pos         IS     NULL) THEN s.start_pos     ELSE u.start_pos     END AS start_pos,
               CASE WHEN (u.excluded          IS     NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
               CASE WHEN (u.share_type_id     IS     NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
               CASE WHEN (u.protect_id        IS     NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id,
               CASE WHEN (ul.view_name    <> ''  IS NOT TRUE) THEN l.view_name    ELSE ul.view_name    END AS view_name1,
               CASE WHEN (ul.description  <> ''  IS NOT TRUE) THEN l.description  ELSE ul.description  END AS description1,
               CASE WHEN (ul2.view_name   <> ''  IS NOT TRUE) THEN l2.view_name   ELSE ul2.view_name   END AS view_name2,
               CASE WHEN (ul2.description <> ''  IS NOT TRUE) THEN l2.description ELSE ul2.description END AS description2
          FROM view_relations s
     LEFT JOIN user_view_relations u ON s.view_relation_id = u.view_relation_id
                                    AND u.user_id = $1 LEFT JOIN views l ON s.parent_view_id = l.view_id LEFT JOIN user_views ul ON l.view_id = ul.view_id
                                    AND ul.user_id = $1 LEFT JOIN views l2 ON s.child_view_id = l2.view_id LEFT JOIN user_views ul2 ON l2.view_id = ul2.view_id
                                    AND ul2.user_id = $1
         WHERE s.parent_view_id = ANY ($2);
