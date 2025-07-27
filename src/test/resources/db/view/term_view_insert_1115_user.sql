PREPARE term_view_insert_1115_user
    (bigint, bigint, text, smallint) AS
INSERT INTO user_term_views (term_view_id, user_id, description, view_link_type_id)
     VALUES ($1, $2, $3, $4)
  RETURNING term_view_id;