-- --------------------------------------------------------

--
-- constraints for table results_standard_prime
--
ALTER TABLE results_standard_prime

    ADD CONSTRAINT results_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_standard 
-- 
ALTER TABLE results_standard
    ADD CONSTRAINT results_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results 
-- 
ALTER TABLE results
    ADD CONSTRAINT results_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results 
-- 
ALTER TABLE user_results
    ADD CONSTRAINT user_results_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_prime 
-- 
ALTER TABLE results_prime
    ADD CONSTRAINT results_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_prime 
-- 
ALTER TABLE user_results_prime
    ADD CONSTRAINT user_results_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_big 
-- 
ALTER TABLE results_big
    ADD CONSTRAINT results_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_big 
-- 
ALTER TABLE user_results_big
    ADD CONSTRAINT user_results_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

-- 
-- constraints for table results_text_standard_prime 
-- 
ALTER TABLE results_text_standard_prime

    ADD CONSTRAINT results_text_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_text_standard 
-- 
ALTER TABLE results_text_standard
    ADD CONSTRAINT results_text_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_text 
-- 
ALTER TABLE results_text
    ADD CONSTRAINT results_text_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text 
-- 
ALTER TABLE user_results_text
    ADD CONSTRAINT user_results_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_text_prime 
-- 
ALTER TABLE results_text_prime
    ADD CONSTRAINT results_text_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text_prime 
-- 
ALTER TABLE user_results_text_prime
    ADD CONSTRAINT user_results_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_text_big 
-- 
ALTER TABLE results_text_big
    ADD CONSTRAINT results_text_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text_big 
-- 
ALTER TABLE user_results_text_big
    ADD CONSTRAINT user_results_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

-- 
-- constraints for table results_time_standard_prime 
-- 
ALTER TABLE results_time_standard_prime

    ADD CONSTRAINT results_time_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_time_standard 
-- 
ALTER TABLE results_time_standard
    ADD CONSTRAINT results_time_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_time 
-- 
ALTER TABLE results_time
    ADD CONSTRAINT results_time_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time 
-- 
ALTER TABLE user_results_time
    ADD CONSTRAINT user_results_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_time_prime 
-- 
ALTER TABLE results_time_prime
    ADD CONSTRAINT results_time_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_prime 
-- 
ALTER TABLE user_results_time_prime
    ADD CONSTRAINT user_results_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_time_big 
-- 
ALTER TABLE results_time_big
    ADD CONSTRAINT results_time_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_big 
-- 
ALTER TABLE user_results_time_big
    ADD CONSTRAINT user_results_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

-- 
-- constraints for table results_geo_standard_prime
-- 
ALTER TABLE results_geo_standard_prime

    ADD CONSTRAINT results_geo_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_geo_standard 
-- 
ALTER TABLE results_geo_standard
    ADD CONSTRAINT results_geo_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_geo 
-- 
ALTER TABLE results_geo
    ADD CONSTRAINT results_geo_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo 
-- 
ALTER TABLE user_results_geo
    ADD CONSTRAINT user_results_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_geo_prime 
-- 
ALTER TABLE results_geo_prime
    ADD CONSTRAINT results_geo_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo_prime 
-- 
ALTER TABLE user_results_geo_prime
    ADD CONSTRAINT user_results_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table results_geo_big 
-- 
ALTER TABLE results_geo_big
    ADD CONSTRAINT results_geo_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT results_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo_big 
-- 
ALTER TABLE user_results_geo_big
    ADD CONSTRAINT user_results_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);