-- --------------------------------------------------------

--
-- indexes for table views
--

CREATE INDEX views_user_idx ON views (user_id);
CREATE INDEX views_view_name_idx ON views (view_name);
CREATE INDEX views_view_type_idx ON views (view_type_id);

--
-- indexes for table user_views
--

ALTER TABLE user_views
    ADD CONSTRAINT user_views_pkey PRIMARY KEY (view_id,user_id,language_id);
CREATE INDEX user_views_view_idx ON user_views (view_id);
CREATE INDEX user_views_user_idx ON user_views (user_id);
CREATE INDEX user_views_language_idx ON user_views (language_id);
CREATE INDEX user_views_view_name_idx ON user_views (view_name);
CREATE INDEX user_views_view_type_idx ON user_views (view_type_id);

