PREPARE component_link_norm_by_ids (bigint[]) AS
    SELECT     component_link_id,
               view_id,
               component_id,
               order_nbr,
               position_type_id,
               view_style_id,
               excluded,
               share_type_id,
               protect_id,
               user_id
          FROM component_links
         WHERE component_link_id = ANY ($1);