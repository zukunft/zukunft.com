-- --------------------------------------------------------

--
-- constraints for table values_standard_prime
--
ALTER TABLE values_standard_prime

    ADD CONSTRAINT values_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_standard
--
ALTER TABLE values_standard
    ADD CONSTRAINT values_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values
--
ALTER TABLE `values`
    ADD CONSTRAINT values_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values
--
ALTER TABLE user_values
    ADD CONSTRAINT user_values_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_prime
--
ALTER TABLE values_prime
    ADD CONSTRAINT values_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_prime
--
ALTER TABLE user_values_prime
    ADD CONSTRAINT user_values_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_big
--
ALTER TABLE values_big
    ADD CONSTRAINT values_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_big
--
ALTER TABLE user_values_big
    ADD CONSTRAINT user_values_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

--
-- constraints for table values_text_standard_prime
--
ALTER TABLE values_text_standard_prime

    ADD CONSTRAINT values_text_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_text_standard
--
ALTER TABLE values_text_standard
    ADD CONSTRAINT values_text_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_text
--
ALTER TABLE values_text
    ADD CONSTRAINT values_text_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_text
--
ALTER TABLE user_values_text
    ADD CONSTRAINT user_values_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_text_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_text_prime
--
ALTER TABLE values_text_prime
    ADD CONSTRAINT values_text_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_text_prime
--
ALTER TABLE user_values_text_prime
    ADD CONSTRAINT user_values_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_text_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_text_big
--
ALTER TABLE values_text_big
    ADD CONSTRAINT values_text_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_text_big
--
ALTER TABLE user_values_text_big
    ADD CONSTRAINT user_values_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_text_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

--
-- constraints for table values_time_standard_prime
--
ALTER TABLE values_time_standard_prime

    ADD CONSTRAINT values_time_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time_standard
--
ALTER TABLE values_time_standard
    ADD CONSTRAINT values_time_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time
--
ALTER TABLE values_time
    ADD CONSTRAINT values_time_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_time
--
ALTER TABLE user_values_time
    ADD CONSTRAINT user_values_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_time_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time_prime
--
ALTER TABLE values_time_prime
    ADD CONSTRAINT values_time_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_time_prime
--
ALTER TABLE user_values_time_prime
    ADD CONSTRAINT user_values_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_time_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time_big
--
ALTER TABLE values_time_big
    ADD CONSTRAINT values_time_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_time_big
--
ALTER TABLE user_values_time_big
    ADD CONSTRAINT user_values_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_time_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

--
-- constraints for table values_geo_standard_prime
--
ALTER TABLE values_geo_standard_prime

    ADD CONSTRAINT values_geo_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_geo_standard
--
ALTER TABLE values_geo_standard
    ADD CONSTRAINT values_geo_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_geo
--
ALTER TABLE values_geo
    ADD CONSTRAINT values_geo_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_geo
--
ALTER TABLE user_values_geo
    ADD CONSTRAINT user_values_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_geo_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_geo_prime
--
ALTER TABLE values_geo_prime
    ADD CONSTRAINT values_geo_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_geo_prime
--
ALTER TABLE user_values_geo_prime
    ADD CONSTRAINT user_values_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_geo_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_geo_big
--
ALTER TABLE values_geo_big
    ADD CONSTRAINT values_geo_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_geo_big
--
ALTER TABLE user_values_geo_big
    ADD CONSTRAINT user_values_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_geo_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);