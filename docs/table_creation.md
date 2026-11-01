Create a table
==============

This guide shows step by step how to get this table:

> the solutions of the problem **global warming** with the
> **potential gain** in trillion EUR, the **initial effort** in person years
> and the **reward ratio**, the best ratio on top

The start page data already has all these numbers. So steps 2 to 4 are
enough. Steps 5 and 6 show how to add a number or a formula of your own.

Icons used in this guide:

| icon            | what it does                    |
|-----------------|---------------------------------|
| person in a circle | user menu: log in, sign up, settings |
| **+**           | add something                   |
| pencil          | change or rename something      |
| circled **x**   | remove something                |
| chain           | assign a formula                |
| **...**         | more choices                    |


1) Log in
---------

You only need to log in if you want to add or change something. Reading and
sorting a table works without logging in.

1. Click the **person icon** at the top right.
2. Click **"Log in"**.
3. Enter your **username or email** and your **password**.
4. Click **"Login"**.

If you have no account yet, click **"signup"** below the button instead.
Fill in **username**, **email**, **password** and **re-type password**, then
click **"Sign Up"**.

After logging in, the person icon turns dark blue. Hover over it to see your
name.


2) Select the 'table editor' view
---------------------------------

3) Choose the columns
---------------------

4) Select the default sorting
-----------------------------

5) Fill the data
----------------

A column appears when the solutions have a number for it. Add, for example,
the expected return of *district cooling*:

1. Open the word *district cooling*.
2. In the part **Values**, click the **+** with the tooltip **"add new
   value"**.
3. The form **"Add a new value for ..."** opens. *district cooling* is
   already selected.
4. For each further word that describes the number:
   1. Type it in the field **"word or triple"**, e.g. *expected return*.
   2. Pick it from the suggestions.
   3. Click **"find and next"**.

   Then do the same for *assumed*. To remove a word that you selected by
   mistake, click the circled **x** next to it.
5. Enter the number in **"Value"**.
6. Click **"add"**.

To add a source, a description or a range, click **"... more details ..."**
before you click "add".


6) Add a formula
----------------

The potential gain and the reward ratio of each solution are not typed in.
Two formulas calculate them:

| formula                      | calculation                                   |
|------------------------------|-----------------------------------------------|
| avoided loss in trillion EUR | potential loss × loss reduction               |
| reward ratio (formula)       | potential loss × loss reduction ÷ initial effort |

To add a formula of your own, e.g. the reward ratio from the gain:

1. Open the word that the formula is for, e.g. *reward ratio*.
2. In the part **Formulas**, click the **+** with the tooltip **"add new
   formula"**.
3. The form **"Add a new formula"** opens. Fill in:
   - **Name**: *reward ratio from the gain*
   - **Description**: what the formula calculates and why
   - **Expression**: the result on the left of the `=`, the calculation on
     the right. Each number is a list of its words in quotes, separated by
     commas:

     ```
     "reward ratio","trillion","EUR per person year" = "potential gain","trillion","EUR" / "initial effort","person year"
     ```

4. Click **"Validate"** next to the expression. Every word must be found.
   If one is not found, create it first: type it in the search field at the
   top, click **"Get numbers"** and then the green button **"add '...' as a
   new word"**.
5. Tick **"calculate only if all values used in the formula exist"**, so
   that no ratio is calculated for a solution without an effort.
6. Click **"save"**.

The formula is calculated for everything that belongs to the word you
started from.

To use an existing formula for another word, open that word and click the
**chain icon** "assign a formula" in the part **Formulas**. Select the
formula and click **"link"**.

> The results are calculated in the background. It can take a moment until
> the new numbers appear in the table.
