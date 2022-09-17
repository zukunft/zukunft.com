PREPARE source_by_not_changed (int) AS
     SELECT user_id
       FROM user_sources
      WHERE source_id = $1
        AND (excluded <> 1 OR excluded is NULL);