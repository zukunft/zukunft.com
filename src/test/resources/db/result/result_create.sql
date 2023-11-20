
-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard_prime
(
    phrase_id_1   smallint         NOT NULL,
    phrase_id_2   smallint         DEFAULT NULL,
    phrase_id_3   smallint         DEFAULT NULL,
    phrase_id_4   smallint         DEFAULT NULL,
    numeric_value double precision NOT NULL
);

COMMENT ON TABLE results_standard_prime                IS 'to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_prime.numeric_value IS 'the numeric value given by the user';

--
-- table structure to cache the formula public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard
(
    group_id      char(112)        PRIMARY KEY,
    numeric_value double precision NOT NULL
);

COMMENT ON TABLE results_standard                IS 'to cache the formula public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_standard.group_id      IS 'the 512-bit prime index to find the numeric result';
COMMENT ON COLUMN results_standard.numeric_value IS 'the numeric value given by the user';

-- --------------------------------------------------------

--
-- table structure to cache the formula numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results
(
    group_id        char(112)        PRIMARY KEY,
    source_group_id char(112)        DEFAULT NULL,
    numeric_value   double precision     NOT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE results                  IS 'to cache the formula numeric results related to up to 16 phrases';
COMMENT ON COLUMN results.group_id        IS 'the 512-bit prime index to find the numeric result';
COMMENT ON COLUMN results.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results.numeric_value   IS 'the numeric value given by the user';
COMMENT ON COLUMN results.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to cache the user specific changes of numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results
(
    group_id        char(112)            NOT NULL,
    source_group_id char(112)        DEFAULT NULL,
    user_id         bigint               NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_results                  IS 'to cache the user specific changes of numeric results related to up to 16 phrases';
COMMENT ON COLUMN user_results.group_id        IS 'the 512-bit prime index to find the user numeric result';
COMMENT ON COLUMN user_results.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results.user_id         IS 'the id of the user who has requested the change of the numeric result';
COMMENT ON COLUMN user_results.numeric_value   IS 'the user specific numeric value change';
COMMENT ON COLUMN user_results.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_prime
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT NULL,
    phrase_id_3     smallint         DEFAULT NULL,
    phrase_id_4     smallint         DEFAULT NULL,
    source_group_id bigint           DEFAULT NULL,
    numeric_value   double precision     NOT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE results_prime                  IS 'to cache the formula most often requested numeric results related up to four prime phrase';
COMMENT ON COLUMN results_prime.phrase_id_1     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_prime.phrase_id_2     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_prime.phrase_id_3     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_prime.phrase_id_4     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_prime.numeric_value   IS 'the numeric value given by the user';
COMMENT ON COLUMN results_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_prime.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_prime.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_prime
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT NULL,
    phrase_id_3     smallint         DEFAULT NULL,
    phrase_id_4     smallint         DEFAULT NULL,
    source_group_id bigint           DEFAULT NULL,
    user_id         bigint               NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_results_prime                  IS 'to store the user specific changes for the most often requested numeric results related up to four prime phrase';
COMMENT ON COLUMN user_results_prime.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_prime.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_prime.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_prime.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_prime.user_id         IS 'the id of the user who has requested the change of the numeric result';
COMMENT ON COLUMN user_results_prime.numeric_value   IS 'the user specific numeric value change';
COMMENT ON COLUMN user_results_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_prime.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_big
(
    group_id        text             PRIMARY KEY,
    source_group_id text             DEFAULT NULL,
    numeric_value   double precision     NOT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE results_big                  IS 'to cache the formula numeric results related to more than 16 phrases';
COMMENT ON COLUMN results_big.group_id        IS 'the variable text index to find numeric result';
COMMENT ON COLUMN results_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_big.numeric_value   IS 'the numeric value given by the user';
COMMENT ON COLUMN results_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_big.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_big.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_big
(
    group_id        text                 NOT NULL,
    source_group_id text             DEFAULT NULL,
    user_id         bigint               NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_results_big                  IS 'to store the user specific changes of numeric results related to more than 16 phrases';
COMMENT ON COLUMN user_results_big.group_id        IS 'the text index for more than 16 phrases to find the numeric result';
COMMENT ON COLUMN user_results_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_big.user_id         IS 'the id of the user who has requested the change of the numeric result';
COMMENT ON COLUMN user_results_big.numeric_value   IS 'the user specific numeric value change';
COMMENT ON COLUMN user_results_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_big.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard_prime
(
    phrase_id_1 smallint NOT NULL,
    phrase_id_2 smallint DEFAULT NULL,
    phrase_id_3 smallint DEFAULT NULL,
    phrase_id_4 smallint DEFAULT NULL,
    text_value text      NOT NULL
);

COMMENT ON TABLE results_text_standard_prime              IS 'to cache the formula public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_text_standard_prime.phrase_id_1 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_prime.phrase_id_2 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_prime.phrase_id_3 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_prime.phrase_id_4 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_prime.text_value  IS 'the text value given by the user';

--
-- table structure to cache the formula public unprotected text results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard
(
    group_id   char(112) PRIMARY KEY,
    text_value text      NOT NULL
);

COMMENT ON TABLE results_text_standard             IS 'to cache the formula public unprotected text results that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_text_standard.group_id   IS 'the 512-bit prime index to find the text result';
COMMENT ON COLUMN results_text_standard.text_value IS 'the text value given by the user';

-- --------------------------------------------------------

--
-- table structure to cache the formula text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text
(
    group_id        char(112) PRIMARY KEY,
    source_group_id char(112) DEFAULT NULL,
    text_value      text          NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_text                  IS 'to cache the formula text results related to up to 16 phrases';
COMMENT ON COLUMN results_text.group_id        IS 'the 512-bit prime index to find the text result';
COMMENT ON COLUMN results_text.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_text.text_value      IS 'the text value given by the user';
COMMENT ON COLUMN results_text.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_text.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_text.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_text.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_text.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_text.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to cache the user specific changes of text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text
(
    group_id        char(112)     NOT NULL,
    source_group_id char(112) DEFAULT NULL,
    user_id         bigint        NOT NULL,
    text_value      text      DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_text                  IS 'to cache the user specific changes of text results related to up to 16 phrases';
COMMENT ON COLUMN user_results_text.group_id        IS 'the 512-bit prime index to find the user text result';
COMMENT ON COLUMN user_results_text.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_text.user_id         IS 'the id of the user who has requested the change of the text result';
COMMENT ON COLUMN user_results_text.text_value      IS 'the user specific text value change';
COMMENT ON COLUMN user_results_text.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_text.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_text.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_text.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_text.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_text_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT NULL,
    phrase_id_3   smallint  DEFAULT NULL,
    phrase_id_4   smallint  DEFAULT NULL,
    source_group_id bigint    DEFAULT NULL,
    text_value      text          NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_text_prime                  IS 'to cache the formula most often requested text results related up to four prime phrase';
COMMENT ON COLUMN results_text_prime.phrase_id_1     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_prime.phrase_id_2     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_prime.phrase_id_3     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_prime.phrase_id_4     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_text_prime.text_value      IS 'the text value given by the user';
COMMENT ON COLUMN results_text_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_text_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_text_prime.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_text_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_text_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_text_prime.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_text_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT NULL,
    phrase_id_3   smallint  DEFAULT NULL,
    phrase_id_4   smallint  DEFAULT NULL,
    source_group_id bigint    DEFAULT NULL,
    user_id         bigint        NOT NULL,
    text_value      text      DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_text_prime                  IS 'to store the user specific changes for the most often requested text results related up to four prime phrase';
COMMENT ON COLUMN user_results_text_prime.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_prime.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_prime.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_prime.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_text_prime.user_id         IS 'the id of the user who has requested the change of the text result';
COMMENT ON COLUMN user_results_text_prime.text_value      IS 'the user specific text value change';
COMMENT ON COLUMN user_results_text_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_text_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_text_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_text_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_text_prime.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text_big
(
    group_id        text      PRIMARY KEY,
    source_group_id text      DEFAULT NULL,
    text_value      text          NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_text_big                  IS 'to cache the formula text results related to more than 16 phrases';
COMMENT ON COLUMN results_text_big.group_id        IS 'the variable text index to find text result';
COMMENT ON COLUMN results_text_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_text_big.text_value      IS 'the text value given by the user';
COMMENT ON COLUMN results_text_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_text_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_text_big.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_text_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_text_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_text_big.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text_big
(
    group_id        text          NOT NULL,
    source_group_id text      DEFAULT NULL,
    user_id         bigint        NOT NULL,
    text_value      text      DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_text_big                  IS 'to store the user specific changes of text results related to more than 16 phrases';
COMMENT ON COLUMN user_results_text_big.group_id        IS 'the text index for more than 16 phrases to find the text result';
COMMENT ON COLUMN user_results_text_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_text_big.user_id         IS 'the id of the user who has requested the change of the text result';
COMMENT ON COLUMN user_results_text_big.text_value      IS 'the user specific text value change';
COMMENT ON COLUMN user_results_text_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_text_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_text_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_text_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_text_big.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard_prime
(
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT NULL,
    phrase_id_3 smallint  DEFAULT NULL,
    phrase_id_4 smallint  DEFAULT NULL,
    time_value timestamp NOT NULL
);

COMMENT ON TABLE results_time_standard_prime              IS 'to cache the formula public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_time_standard_prime.phrase_id_1 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_prime.phrase_id_2 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_prime.phrase_id_3 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_prime.phrase_id_4 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_prime.time_value  IS 'the timestamp given by the user';

--
-- table structure to cache the formula public unprotected time results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard
(
    group_id   char(112) PRIMARY KEY,
    time_value timestamp NOT NULL
);

COMMENT ON TABLE results_time_standard             IS 'to cache the formula public unprotected time results that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_time_standard.group_id   IS 'the 512-bit prime index to find the time result';
COMMENT ON COLUMN results_time_standard.time_value IS 'the timestamp given by the user';

-- --------------------------------------------------------

--
-- table structure to cache the formula time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time
(
    group_id        char(112) PRIMARY KEY,
    source_group_id char(112) DEFAULT NULL,
    time_value      timestamp     NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_time                  IS 'to cache the formula time results related to up to 16 phrases';
COMMENT ON COLUMN results_time.group_id        IS 'the 512-bit prime index to find the time result';
COMMENT ON COLUMN results_time.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_time.time_value      IS 'the timestamp given by the user';
COMMENT ON COLUMN results_time.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_time.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_time.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_time.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_time.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_time.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to cache the user specific changes of time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time
(
    group_id        char(112)     NOT NULL,
    source_group_id char(112) DEFAULT NULL,
    user_id         bigint        NOT NULL,
    time_value      timestamp DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_time                  IS 'to cache the user specific changes of time results related to up to 16 phrases';
COMMENT ON COLUMN user_results_time.group_id        IS 'the 512-bit prime index to find the user time result';
COMMENT ON COLUMN user_results_time.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_time.user_id         IS 'the id of the user who has requested the change of the time result';
COMMENT ON COLUMN user_results_time.time_value      IS 'the user specific timestamp change';
COMMENT ON COLUMN user_results_time.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_time.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_time.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_time.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_time.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_time_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT NULL,
    phrase_id_3   smallint  DEFAULT NULL,
    phrase_id_4   smallint  DEFAULT NULL,
    source_group_id bigint    DEFAULT NULL,
    time_value      timestamp     NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_time_prime                  IS 'to cache the formula most often requested time results related up to four prime phrase';
COMMENT ON COLUMN results_time_prime.phrase_id_1     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_prime.phrase_id_2     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_prime.phrase_id_3     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_prime.phrase_id_4     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_time_prime.time_value      IS 'the timestamp given by the user';
COMMENT ON COLUMN results_time_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_time_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_time_prime.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_time_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_time_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_time_prime.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_time_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT NULL,
    phrase_id_3   smallint  DEFAULT NULL,
    phrase_id_4   smallint  DEFAULT NULL,
    source_group_id bigint    DEFAULT NULL,
    user_id         bigint        NOT NULL,
    time_value      timestamp DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_time_prime                  IS 'to store the user specific changes for the most often requested time results related up to four prime phrase';
COMMENT ON COLUMN user_results_time_prime.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_prime.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_prime.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_prime.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_time_prime.user_id         IS 'the id of the user who has requested the change of the time result';
COMMENT ON COLUMN user_results_time_prime.time_value      IS 'the user specific timestamp change';
COMMENT ON COLUMN user_results_time_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_time_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_time_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_time_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_time_prime.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time_big
(
    group_id        text      PRIMARY KEY,
    source_group_id text      DEFAULT NULL,
    time_value      timestamp     NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_time_big                  IS 'to cache the formula time results related to more than 16 phrases';
COMMENT ON COLUMN results_time_big.group_id        IS 'the variable text index to find time result';
COMMENT ON COLUMN results_time_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_time_big.time_value      IS 'the timestamp given by the user';
COMMENT ON COLUMN results_time_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_time_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_time_big.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_time_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_time_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_time_big.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time_big
(
    group_id        text          NOT NULL,
    source_group_id text      DEFAULT NULL,
    user_id         bigint        NOT NULL,
    time_value      timestamp DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_time_big                  IS 'to store the user specific changes of time results related to more than 16 phrases';
COMMENT ON COLUMN user_results_time_big.group_id        IS 'the text index for more than 16 phrases to find the time result';
COMMENT ON COLUMN user_results_time_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_time_big.user_id         IS 'the id of the user who has requested the change of the time result';
COMMENT ON COLUMN user_results_time_big.time_value      IS 'the user specific timestamp change';
COMMENT ON COLUMN user_results_time_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_time_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_time_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_time_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_time_big.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard_prime
(
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT NULL,
    phrase_id_3 smallint  DEFAULT NULL,
    phrase_id_4 smallint  DEFAULT NULL,
    geo_value  point     NOT NULL
);

COMMENT ON TABLE results_geo_standard_prime              IS 'to cache the formula public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_geo_standard_prime.phrase_id_1 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_prime.phrase_id_2 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_prime.phrase_id_3 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_prime.phrase_id_4 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_prime.geo_value   IS 'the geolocation given by the user';

--
-- table structure to cache the formula public unprotected geo results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard
(
    group_id   char(112) PRIMARY KEY,
    geo_value  point     NOT NULL
);

COMMENT ON TABLE results_geo_standard             IS 'to cache the formula public unprotected geo results that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_geo_standard.group_id   IS 'the 512-bit prime index to find the geo result';
COMMENT ON COLUMN results_geo_standard.geo_value  IS 'the geolocation given by the user';

-- --------------------------------------------------------

--
-- table structure to cache the formula geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo
(
    group_id        char(112) PRIMARY KEY,
    source_group_id char(112) DEFAULT NULL,
    geo_value       point         NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_geo                  IS 'to cache the formula geo results related to up to 16 phrases';
COMMENT ON COLUMN results_geo.group_id        IS 'the 512-bit prime index to find the geo result';
COMMENT ON COLUMN results_geo.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_geo.geo_value       IS 'the geolocation given by the user';
COMMENT ON COLUMN results_geo.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_geo.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_geo.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_geo.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_geo.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_geo.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to cache the user specific changes of geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo
(
    group_id        char(112)     NOT NULL,
    source_group_id char(112) DEFAULT NULL,
    user_id         bigint        NOT NULL,
    geo_value       point     DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_geo                  IS 'to cache the user specific changes of geo results related to up to 16 phrases';
COMMENT ON COLUMN user_results_geo.group_id        IS 'the 512-bit prime index to find the user geo result';
COMMENT ON COLUMN user_results_geo.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_geo.user_id         IS 'the id of the user who has requested the change of the geo result';
COMMENT ON COLUMN user_results_geo.geo_value       IS 'the user specific geolocation change';
COMMENT ON COLUMN user_results_geo.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_geo.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_geo.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_geo.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_geo.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_geo_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT NULL,
    phrase_id_3   smallint  DEFAULT NULL,
    phrase_id_4   smallint  DEFAULT NULL,
    source_group_id bigint    DEFAULT NULL,
    geo_value       point         NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_geo_prime                  IS 'to cache the formula most often requested geo results related up to four prime phrase';
COMMENT ON COLUMN results_geo_prime.phrase_id_1     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_prime.phrase_id_2     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_prime.phrase_id_3     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_prime.phrase_id_4     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_geo_prime.geo_value       IS 'the geolocation given by the user';
COMMENT ON COLUMN results_geo_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_geo_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_geo_prime.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_geo_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_geo_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_geo_prime.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_geo_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT NULL,
    phrase_id_3   smallint  DEFAULT NULL,
    phrase_id_4   smallint  DEFAULT NULL,
    source_group_id bigint    DEFAULT NULL,
    user_id         bigint        NOT NULL,
    geo_value       point     DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_geo_prime                  IS 'to store the user specific changes for the most often requested geo results related up to four prime phrase';
COMMENT ON COLUMN user_results_geo_prime.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_prime.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_prime.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_prime.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_geo_prime.user_id         IS 'the id of the user who has requested the change of the geo result';
COMMENT ON COLUMN user_results_geo_prime.geo_value       IS 'the user specific geolocation change';
COMMENT ON COLUMN user_results_geo_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_geo_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_geo_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_geo_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_geo_prime.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo_big
(
    group_id        text      PRIMARY KEY,
    source_group_id text      DEFAULT NULL,
    geo_value       point         NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_geo_big                  IS 'to cache the formula geo results related to more than 16 phrases';
COMMENT ON COLUMN results_geo_big.group_id        IS 'the variable text index to find geo result';
COMMENT ON COLUMN results_geo_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_geo_big.geo_value       IS 'the geolocation given by the user';
COMMENT ON COLUMN results_geo_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_geo_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_geo_big.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_geo_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_geo_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_geo_big.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo_big
(
    group_id        text          NOT NULL,
    source_group_id text      DEFAULT NULL,
    user_id         bigint        NOT NULL,
    geo_value       point     DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_geo_big                  IS 'to store the user specific changes of geo results related to more than 16 phrases';
COMMENT ON COLUMN user_results_geo_big.group_id        IS 'the text index for more than 16 phrases to find the geo result';
COMMENT ON COLUMN user_results_geo_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_geo_big.user_id         IS 'the id of the user who has requested the change of the geo result';
COMMENT ON COLUMN user_results_geo_big.geo_value       IS 'the user specific geolocation change';
COMMENT ON COLUMN user_results_geo_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_geo_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_geo_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_geo_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_geo_big.protect_id      IS 'to protect against unwanted changes';
