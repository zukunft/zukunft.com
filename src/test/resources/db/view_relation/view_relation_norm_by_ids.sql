PREPARE view_relation_norm_by_ids (bigint[]) AS
    SELECT     view_relation_id,
               parent_view_id,
               view_relation_type_id,
               child_view_id,
               description,
               user_id
          FROM view_relations
         WHERE view_relation_id = ANY ($1);