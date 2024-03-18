--
-- constraints for table elements
--

ALTER TABLE elements
    ADD CONSTRAINT elements_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT elements_element_type_fk FOREIGN KEY (element_type_id) REFERENCES element_types (element_type_id),
    ADD CONSTRAINT elements_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);
