-- --------------------------------------------------------

--
-- constraints for table results
--
ALTER TABLE results
    ADD CONSTRAINT results_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results
--
ALTER TABLE user_results
    ADD CONSTRAINT user_results_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_prime
--
ALTER TABLE results_prime
    ADD CONSTRAINT results_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_prime
--
ALTER TABLE user_results_prime
    ADD CONSTRAINT user_results_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_main
--
ALTER TABLE results_main
    ADD CONSTRAINT results_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_main
--
ALTER TABLE user_results_main
    ADD CONSTRAINT user_results_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_big
--
ALTER TABLE results_big
    ADD CONSTRAINT results_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_big
--
ALTER TABLE user_results_big
    ADD CONSTRAINT user_results_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table results_text
--
ALTER TABLE results_text
    ADD CONSTRAINT results_text_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text
--
ALTER TABLE user_results_text
    ADD CONSTRAINT user_results_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_text_prime
--
ALTER TABLE results_text_prime
    ADD CONSTRAINT results_text_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text_prime
--
ALTER TABLE user_results_text_prime
    ADD CONSTRAINT user_results_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_text_main
--
ALTER TABLE results_text_main
    ADD CONSTRAINT results_text_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_text_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text_main
--
ALTER TABLE user_results_text_main
    ADD CONSTRAINT user_results_text_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_text_big
--
ALTER TABLE results_text_big
    ADD CONSTRAINT results_text_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text_big
--
ALTER TABLE user_results_text_big
    ADD CONSTRAINT user_results_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table results_time
--
ALTER TABLE results_time
    ADD CONSTRAINT results_time_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time
--
ALTER TABLE user_results_time
    ADD CONSTRAINT user_results_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_prime
--
ALTER TABLE results_time_prime
    ADD CONSTRAINT results_time_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_prime
--
ALTER TABLE user_results_time_prime
    ADD CONSTRAINT user_results_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_main
--
ALTER TABLE results_time_main
    ADD CONSTRAINT results_time_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_main
--
ALTER TABLE user_results_time_main
    ADD CONSTRAINT user_results_time_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_big
--
ALTER TABLE results_time_big
    ADD CONSTRAINT results_time_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_big
--
ALTER TABLE user_results_time_big
    ADD CONSTRAINT user_results_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table results_geo
--
ALTER TABLE results_geo
    ADD CONSTRAINT results_geo_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo
--
ALTER TABLE user_results_geo
    ADD CONSTRAINT user_results_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_geo_prime
--
ALTER TABLE results_geo_prime
    ADD CONSTRAINT results_geo_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo_prime
--
ALTER TABLE user_results_geo_prime
    ADD CONSTRAINT user_results_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_geo_main
--
ALTER TABLE results_geo_main
    ADD CONSTRAINT results_geo_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_geo_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo_main
--
ALTER TABLE user_results_geo_main
    ADD CONSTRAINT user_results_geo_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_geo_big
--
ALTER TABLE results_geo_big
    ADD CONSTRAINT results_geo_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo_big
--
ALTER TABLE user_results_geo_big
    ADD CONSTRAINT user_results_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table results_time_series
--
ALTER TABLE results_time_series
    ADD CONSTRAINT results_time_series_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_series_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_series
--
ALTER TABLE user_results_time_series
    ADD CONSTRAINT user_results_time_series_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_series_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_series_prime
--
ALTER TABLE results_time_series_prime
    ADD CONSTRAINT results_time_series_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_series_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_series_prime
--
ALTER TABLE user_results_time_series_prime
    ADD CONSTRAINT user_results_time_series_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_series_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_series_big
--
ALTER TABLE results_time_series_big
    ADD CONSTRAINT results_time_series_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_series_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_series_big
--
ALTER TABLE user_results_time_series_big
    ADD CONSTRAINT user_results_time_series_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_series_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);
