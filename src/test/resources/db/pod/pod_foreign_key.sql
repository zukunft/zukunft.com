--
-- constraints for table pods
--

ALTER TABLE pods
    ADD CONSTRAINT type_name_uk UNIQUE (type_name),
    ADD CONSTRAINT code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT pods_pod_type_fk FOREIGN KEY (pod_type_id) REFERENCES pod_types (pod_type_id),
    ADD CONSTRAINT pods_pod_status_fk FOREIGN KEY (pod_status_id) REFERENCES pod_status (pod_status_id),
    ADD CONSTRAINT pods_triple_fk FOREIGN KEY (param_triple_id) REFERENCES triples (triple_id);
