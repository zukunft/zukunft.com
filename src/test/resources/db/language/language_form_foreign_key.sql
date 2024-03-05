--
-- constraints for table language_forms
--

ALTER TABLE language_forms
    ADD CONSTRAINT language_forms_language_form_name_uk UNIQUE (language_form_name),
    ADD CONSTRAINT language_forms_language_fk FOREIGN KEY (language_id) REFERENCES languages (language_id);
