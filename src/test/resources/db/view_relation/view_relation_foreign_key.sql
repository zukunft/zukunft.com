-- --------------------------------------------------------

--
-- constraints for table view_relations
--

ALTER TABLE view_relations
    ADD CONSTRAINT view_relations_view_fk FOREIGN KEY (parent_view_id) REFERENCES views (view_id),
    ADD CONSTRAINT view_relations_view2_fk FOREIGN KEY (child_view_id) REFERENCES views (view_id),
    ADD CONSTRAINT view_relations_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT view_relations_view_relation_type_fk FOREIGN KEY (view_relation_type_id) REFERENCES view_relation_types (view_relation_type_id);

--
-- constraints for table user_view_relations
--

ALTER TABLE user_view_relations
    ADD CONSTRAINT user_view_relations_view_relation_fk FOREIGN KEY (view_relation_id) REFERENCES view_relations (view_relation_id),
    ADD CONSTRAINT user_view_relations_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);
