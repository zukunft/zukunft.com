PREPARE term_view_insert_015505
    (bigint, bigint, bigint, smallint) AS
INSERT INTO term_views (user_id, view_id, term_id, view_link_type_id)
     VALUES ($1, $2, $3, $4)
  RETURNING term_view_id;