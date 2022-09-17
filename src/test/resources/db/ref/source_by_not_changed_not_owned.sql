PREPARE source_by_not_changed_not_owned (int, int) AS
    SELECT user_id
      FROM user_sources
     WHERE source_id = $1
       AND (excluded <> 1 OR excluded is NULL)
       AND user_id <> $2;