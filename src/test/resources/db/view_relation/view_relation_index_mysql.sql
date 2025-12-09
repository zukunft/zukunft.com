-- --------------------------------------------------------

--
-- indexes for table view_relations
--

ALTER TABLE view_relations
    ADD KEY view_relations_parent_view_idx (parent_view_id),
    ADD KEY view_relations_child_view_idx (child_view_id),
    ADD KEY view_relations_user_idx (user_id),
    ADD KEY view_relations_view_relation_type_idx (view_relation_type_id);

--
-- indexes for table user_view_relations
--

ALTER TABLE user_view_relations
    ADD KEY user_view_relations_view_relation_idx (view_relation_id),
    ADD KEY user_view_relations_user_idx (user_id);
