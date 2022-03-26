PREPARE view_components_by_view_id (int, int) AS
    SELECT      s.view_component_link_id,
                u.view_component_link_id AS user_view_component_link_id,
                s.user_id,
                s.view_id,
                s.view_component_id,
                CASE WHEN (u.order_nbr                   IS     NULL) THEN s.order_nbr               ELSE u.order_nbr                END AS order_nbr,
                CASE WHEN (u.position_type               IS     NULL) THEN s.position_type           ELSE u.position_type            END AS position_type,
                CASE WHEN (u.excluded                    IS     NULL) THEN s.excluded                ELSE u.excluded                 END AS excluded,
                CASE WHEN (u.share_type_id               IS     NULL) THEN s.share_type_id           ELSE u.share_type_id            END AS share_type_id,
                CASE WHEN (u.protect_id                  IS     NULL) THEN s.protect_id              ELSE u.protect_id               END AS protect_id,
                CASE WHEN (ul2.comment           <> ''   IS NOT TRUE) THEN l2.comment                ELSE ul2.comment                END AS comment2,
                CASE WHEN (ul3.view_component_type_id    IS     NULL) THEN l3.view_component_type_id ELSE ul3.view_component_type_id END AS view_component_type_id3,
                CASE WHEN (ul3.word_id_row               IS     NULL) THEN l3.word_id_row            ELSE ul3.word_id_row            END AS word_id_row3,
                CASE WHEN (ul3.link_type_id              IS     NULL) THEN l3.link_type_id           ELSE ul3.link_type_id           END AS link_type_id3,
                CASE WHEN (ul3.formula_id                IS     NULL) THEN l3.formula_id             ELSE ul3.formula_id             END AS formula_id3,
                CASE WHEN (ul3.word_id_col               IS     NULL) THEN l3.word_id_col            ELSE ul3.word_id_col            END AS word_id_col3,
                CASE WHEN (ul3.word_id_col2              IS     NULL) THEN l3.word_id_col2           ELSE ul3.word_id_col2           END AS word_id_col23,
                CASE WHEN (ul3.excluded                  IS     NULL) THEN l3.excluded               ELSE ul3.excluded               END AS excluded3,
                CASE WHEN (ul3.share_type_id             IS     NULL) THEN l3.share_type_id          ELSE ul3.share_type_id          END AS share_type_id3,
                CASE WHEN (ul3.protect_id                IS     NULL) THEN l3.protect_id             ELSE ul3.protect_id             END AS protect_id3
           FROM view_component_links s
      LEFT JOIN user_view_component_links u ON s.view_component_link_id = u.view_component_link_id
                                           AND u.user_id = $1
      LEFT JOIN view_components l           ON s.view_component_id = l.view_component_id
      LEFT JOIN view_components l2          ON s.view_component_id = l2.view_component_id
      LEFT JOIN user_view_components ul2    ON l2.view_component_id = ul2.view_component_id
                                           AND ul2.user_id = $1
      LEFT JOIN view_components l3          ON s.view_component_id = l3.view_component_id
      LEFT JOIN user_view_components ul3    ON l3.view_component_id = ul3.view_component_id
                                           AND ul3.user_id = $1
          WHERE s.view_id = $2
       ORDER BY s.order_nbr;
