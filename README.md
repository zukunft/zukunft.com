# zukunft.com 

## Calculating with RDF data.

This program should
- be a GGG browser
- make the Giant Global Graph usable for the real-time delphi-method
- an exoskeleton for the brain
- make slow thinking (Kahneman) faster
- allow each user have her/his own OLAP cube
- make efficient community learning easy by connecting all user OLAP cubes point to point
- allow double-sided tree structures within the cubes by using phrases
- use common sense by using opencyc via conceptnet.io
- enable data exchange to wikidata and other interlinking databases

## Development installation

To install this version 0.0.3 on a debian system (https://wiki.debian.org/) do as root e.g. in /home/your_user/:

1. Clone the repository:
   ```bash
   git clone -b develop https://github.com/zukunft/zukunft.com.git
   ```
   ```bash
   chmod 777 zukunft.com/install.sh
   ```

2. (Optional) Adjust the `.env.sample` file e.g. for customize database credentials:
   ```bash
   nano zukunft.com/.env.sample
   ```

   ```env
   OS=debian (or "docker")
   ENV=dev (or "test", "prod")
   BRANCH=develop (or "release", "master")
   DB=postgres (or "mysql")
   PGSQL_USERNAME=zukunft
   PGSQL_DATABASE=zukunft
   PGSQL_PASSWORD=your_password_here
   PGSQL_ADMIN_USERNAME=postgres
   PGSQL_ADMIN_PASSWORD=admin_password_here
   PGSQL_HOST=localhost (or "db")
   ```
   
3. Start the application:
   ```bash
   sudo ./zukunft.com/install.sh
   ```

## Planned changes

For versions 0.0.3 these changes are planned
- JSON import
- system mask

and for versions 0.0.4
- fix the unit and integration tests

and for versions 0.0.5
- improve the setup and update script


## Coding Guidelines

this code follows the principle of Antoine de Saint-Exupéry

"Il semble que la perfection soit atteinte non quand il n'y a plus rien à ajouter,
mais quand il n'y a plus rien à retrancher."

Or in English: "reduce to the max"

The code use for zukunft.com should be as simple as possible and have only a few dependencies and each part as capsuled as possible,
so basically follow the Zen of Python https://www.python.org/dev/peps/pep-0020/
The minimal requirements are a LAMP server (https://wiki.debian.org/LaMp) and an HTML (using some HTML5 features) browser.
If you see anything that does not look simple to you, please request a change on https://github.com/zukunft/zukunft.com or write an email to timon@zukunft.com


Target user experience:
- **one-to-one**: business logic as you would explain it to a human
  each formula should have 3 to 5, max 8 elements due to the limitation of the human work memory
- **user sandbox**: the look and feel should never change without confirmation by the user
- **don't disturb**: suggested changes should never prevent the user from continuing
- **always sorted**: the messages to the user should be sorted by criticality but taking the reaktion time into account
- prevent duplicates in the values or formulas to force user to social interaction

General coding principles:
1. **Don't repeat yourself**: one point of change (https://en.wikipedia.org/wiki/Don%27t_repeat_yourself) (but coded repeating by intention can be used)
2. **test**: each facade function should have a unit test called from test_units.php or test_unit_db.php
  with zukunft.com/test a complete unit and integration test
  best: first write the test and then the code
3. **only needed dependencies**: use the least external code possible because https://archive.fosdem.org/2021/schedule/event/dep_as_strong_as_the_weakest_link/
4. **best guess**: assume almost everything can happen and in case of incomplete data use best guess assumptions to complete the process but report the assumption to the calling function and create the message to the user if all assumptions are collected
5. **never change** a running system (until you have a very, very good reason)
6. **one click update**: allow to update a pod with one click on the fly (https://en.wikipedia.org/wiki/Continuous_delivery)
7. **log in/out**: all user changes and data im- and export are logged with an undo and redo option
8. **small code package**: split if function and classes are getting too big or at least the most important functions within a class should be on top of each class
9. **error detection** and tracking: in case something unexpected happens the code should try to create an internal error message to enable later debugging
10. **self speaking** error messages
11. **shared api** with in code auto check
12. capsule: each class and method should check the consistency of the input parameters at the beginning

Coding team suggestions
- daily max 15 min physical **standup** where all member confirm the daily target
- improve the **definition of done** of a story (ticket) until all team members understand it
- all team members **vote** simultaneously for 1, 2, 3, 5, 8 or max 13 story-points
- if a story has more points it is split
- when all agree on the story-points the story is assigned to one member
- critical: if there is a delay other team member **offer to help** (no blaming)  
- at the sprint retro one selects a perspective that the other done not know for spontaneous answers
- **one tool** (not two or more) per purpose: git, tickets, wiki, message e.g. element.io 

Decisions
- use this program for a mind map with all arguments where each has a weight and value and all changes are logged

Deployment process
1. do the changes and commits in the feature branch related to the issue e.g. "feature/134-create-a-docker-script-and-a-docu-how-to-use"
2. review the code and merge it to "develop"
3. test it and if it is fine, merge it to the staging branch "release"
4. if the public test is fine, merge it to master and update the production system using the CI/CD Process, which needs to be created

More in the detailed [coding guidelines](docs/code_guidelines.md)

## main objects

the logical order of the main objects is
- word - use single words for better assignments
- verb - a predicate to connect two words
- triple - combine two words or triples with a verb
- source - import only data source
- ref - im- and export to external systems
- value - a number for calculation 
- group - list of words or triples
- formula - expression for calculation
- result - numeric result of a formula
- view - named display mask
- component - parts of a display mask

More in the detailed [object description](docs/code_objects.md)
