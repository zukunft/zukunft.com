-- --------------------------------------------------------

--
-- indexes for table view_relations
--

CREATE INDEX view_relations_parent_view_idx ON view_relations (parent_view_id);
CREATE INDEX view_relations_child_view_idx ON view_relations (child_view_id);
CREATE INDEX view_relations_user_idx ON view_relations (user_id);
CREATE INDEX view_relations_view_relation_type_idx ON view_relations (view_relation_type_id);

--
-- indexes for table user_view_relations
--

ALTER TABLE user_view_relations
    ADD CONSTRAINT user_view_relations_pkey PRIMARY KEY (view_relation_id,user_id);
CREATE INDEX user_view_relations_view_relation_idx ON user_view_relations (view_relation_id);
CREATE INDEX user_view_relations_user_idx ON user_view_relations (user_id);
