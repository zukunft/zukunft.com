PREPARE phrase_group_triple_link_by_grp_id FROM
    'SELECT
             phrase_group_triple_link_id,
             phrase_group_id,
             triple_id
        FROM phrase_group_triple_links
       WHERE phrase_group_id = ?';