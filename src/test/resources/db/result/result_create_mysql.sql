
-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard_prime
(
    formula_id    smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a numeric result',
    phrase_id_1   smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_2   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_3   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    numeric_value double     NOT NULL COMMENT 'the numeric value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected numeric results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard_main
(
    formula_id    smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a numeric result',
    phrase_id_1   smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_2   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_3   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_4   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_5   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_6   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_7   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    numeric_value double     NOT NULL COMMENT 'the numeric value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected numeric results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard
(
    group_id      char(112) NOT NULL COMMENT 'the 512-bit prime index to find the numeric result',
    numeric_value double    NOT NULL COMMENT 'the numeric value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure to cache the formula numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the numeric result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    numeric_value   double        NOT NULL COMMENT 'the numeric value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula numeric results related to up to 16 phrases';

--
-- table structure to cache the user specific changes of numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user numeric result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the numeric result',
    numeric_value   double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the user specific changes of numeric results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    numeric_value   double        NOT NULL COMMENT 'the numeric value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula most often requested numeric results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the numeric result',
    numeric_value   double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested numeric results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested numeric results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    numeric_value   double        NOT NULL COMMENT 'the numeric value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula second most often requested numeric results related up to eight prime phrase';

--
-- table structure to store the user specific changes to cache the formula second most often requested numeric results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the numeric result',
    numeric_value   double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes to cache the formula second most often requested numeric results related up to eight prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_big
(
    group_id        char(255)     NOT NULL COMMENT 'the variable text index to find numeric result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    numeric_value   double        NOT NULL COMMENT 'the numeric value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula numeric results related to more than 16 phrases';

--
-- table structure to store the user specific changes of numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_big
(
    group_id        char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the numeric result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the numeric result',
    numeric_value   double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of numeric results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard_prime
(
    formula_id smallint      NOT NULL COMMENT 'formula id that is part of the prime key for a text result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    text_value  text         NOT NULL COMMENT 'the text value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected text results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard_main
(
    formula_id smallint      NOT NULL COMMENT 'formula id that is part of the prime key for a text result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_5 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_6 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_7 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    text_value  text         NOT NULL COMMENT 'the text value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected text results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected text results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the text result',
    text_value text          NOT NULL COMMENT 'the text value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected text results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure to cache the formula text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the text result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    text_value      text          NOT NULL COMMENT 'the text value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula text results related to up to 16 phrases';

--
-- table structure to cache the user specific changes of text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user text result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the text result',
    text_value      text      DEFAULT NULL COMMENT 'the user specific text value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the user specific changes of text results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_text_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    text_value      text          NOT NULL COMMENT 'the text value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula most often requested text results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_text_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the text result',
    text_value      text      DEFAULT NULL COMMENT 'the user specific text value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested text results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested text results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_text_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    text_value      text          NOT NULL COMMENT 'the text value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula second most often requested text results related up to eight prime phrase';

--
-- table structure to store the user specific changes to cache the formula second most often requested text results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_text_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the text result',
    text_value      text      DEFAULT NULL COMMENT 'the user specific text value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes to cache the formula second most often requested text results related up to eight prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text_big
(
    group_id        char(255)     NOT NULL COMMENT 'the variable text index to find text result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    text_value      text          NOT NULL COMMENT 'the text value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula text results related to more than 16 phrases';

--
-- table structure to store the user specific changes of text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text_big
(
    group_id        char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the text result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the text result',
    text_value      text      DEFAULT NULL COMMENT 'the user specific text value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of text results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard_prime
(
    formula_id  smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a time result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    time_value  timestamp     NOT NULL COMMENT 'the timestamp given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected time results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard_main
(
    formula_id  smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a time result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_5 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_6 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_7 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    time_value  timestamp     NOT NULL COMMENT 'the timestamp given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected time results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected time results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time result',
    time_value timestamp     NOT NULL COMMENT 'the timestamp given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected time results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure to cache the formula time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    time_value      timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula time results related to up to 16 phrases';

--
-- table structure to cache the user specific changes of time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user time result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time result',
    time_value      timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the user specific changes of time results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_time_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    time_value      timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula most often requested time results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_time_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time result',
    time_value      timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested time results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested time results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_time_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    time_value      timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula second most often requested time results related up to eight prime phrase';

--
-- table structure to store the user specific changes to cache the formula second most often requested time results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_time_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time result',
    time_value      timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes to cache the formula second most often requested time results related up to eight prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time_big
(
    group_id        char(255)     NOT NULL COMMENT 'the variable text index to find time result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    time_value      timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula time results related to more than 16 phrases';

--
-- table structure to store the user specific changes of time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time_big
(
    group_id        char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the time result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time result',
    time_value      timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of time results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard_prime
(
    formula_id  smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a geo result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    geo_value   point         NOT NULL COMMENT 'the geolocation given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected geo results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard_main
(
    formula_id  smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a geo result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_5 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_6 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_7 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    geo_value   point         NOT NULL COMMENT 'the geolocation given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected geo results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected geo results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the geo result',
    geo_value  point         NOT NULL COMMENT 'the geolocation given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected geo results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure to cache the formula geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the geo result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    geo_value       point         NOT NULL COMMENT 'the geolocation given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula geo results related to up to 16 phrases';

--
-- table structure to cache the user specific changes of geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user geo result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the geo result',
    geo_value       point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the user specific changes of geo results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_geo_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    geo_value       point         NOT NULL COMMENT 'the geolocation given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula most often requested geo results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_geo_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the geo result',
    geo_value       point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested geo results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested geo results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_geo_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    geo_value       point         NOT NULL COMMENT 'the geolocation given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula second most often requested geo results related up to eight prime phrase';

--
-- table structure to store the user specific changes to cache the formula second most often requested geo results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_geo_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the geo result',
    geo_value       point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes to cache the formula second most often requested geo results related up to eight prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo_big
(
    group_id        char(255)     NOT NULL COMMENT 'the variable text index to find geo result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    geo_value       point         NOT NULL COMMENT 'the geolocation given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula geo results related to more than 16 phrases';

--
-- table structure to store the user specific changes of geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo_big
(
    group_id        char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the geo result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the geo result',
    geo_value       point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of geo results related to more than 16 phrases';
