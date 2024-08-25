PREPARE view_term_link_insert_015505
    (bigint, bigint, bigint, smallint) AS
INSERT INTO view_term_links (user_id, view_id, term_id, view_link_type_id)
     VALUES ($1, $2, $3, $4)
  RETURNING view_term_link_id;