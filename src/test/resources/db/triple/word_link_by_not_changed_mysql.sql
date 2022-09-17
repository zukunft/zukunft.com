PREPARE word_link_by_not_changed FROM
    'SELECT user_id
       FROM word_links
      WHERE word_link_id = ?
        AND (excluded <> 1 OR excluded is NULL)';