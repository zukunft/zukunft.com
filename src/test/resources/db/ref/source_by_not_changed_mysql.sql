PREPARE source_by_not_changed FROM
    'SELECT user_id
       FROM sources
      WHERE source_id = ?
        AND (excluded <> 1 OR excluded is NULL)';