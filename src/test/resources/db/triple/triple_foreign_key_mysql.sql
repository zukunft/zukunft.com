-- --------------------------------------------------------

--
-- constraints for table triples
--
ALTER TABLE triples
    ADD CONSTRAINT code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT triples_user_fk   FOREIGN KEY (user_id)        REFERENCES users (user_id),
    ADD CONSTRAINT triples_verb_fk   FOREIGN KEY (verb_id)        REFERENCES verbs (verb_id),
    ADD CONSTRAINT triples_phrase_fk FOREIGN KEY (phrase_type_id) REFERENCES phrases (phrase_type_id),
    ADD CONSTRAINT triples_view_fk   FOREIGN KEY (view_id)        REFERENCES views (view_id);

--
-- constraints for table user_triples
--
ALTER TABLE user_triples
    ADD CONSTRAINT user_triples_triple_fk   FOREIGN KEY (triple_id)      REFERENCES triples (triple_id),
    ADD CONSTRAINT user_triples_user_fk     FOREIGN KEY (user_id)        REFERENCES users (user_id),
    ADD CONSTRAINT user_triples_language_fk FOREIGN KEY (language_id)    REFERENCES languages (language_id),
    ADD CONSTRAINT user_triples_phrase_fk   FOREIGN KEY (phrase_type_id) REFERENCES phrases (phrase_type_id),
    ADD CONSTRAINT user_triples_view_fk     FOREIGN KEY (view_id)        REFERENCES views (view_id);
