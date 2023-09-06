PREPARE component_link_by_link_type_ids FROM
    'SELECT     s.component_link_id,
                u.component_link_id  AS user_component_link_id,
                s.user_id,
                s.view_id,
                s.component_id,
                IF(u.order_nbr     IS NULL, s.order_nbr,     u.order_nbr)     AS order_nbr,
                IF(u.position_type IS NULL, s.position_type, u.position_type) AS position_type,
                IF(u.excluded      IS NULL, s.excluded,      u.excluded)      AS excluded,
                IF(u.share_type_id IS NULL, s.share_type_id, u.share_type_id) AS share_type_id,
                IF(u.protect_id    IS NULL, s.protect_id,    u.protect_id)    AS protect_id
           FROM component_links s
      LEFT JOIN user_component_links u ON s.component_link_id = u.component_link_id AND u.user_id = ?
          WHERE s.view_id = ?
            AND s.position_type = ?
            AND s.component_id = ?';
