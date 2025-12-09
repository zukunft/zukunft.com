System views
------------

System views are the frontend pages used by the system used for be the [CRUD](https://de.wikipedia.org/wiki/CRUD) functions, which implies that the users cannot remove or add components to any system view and the system view cannot be assigned by the user to an object.  
The pages of this system is configured by json file src/main/resources/messages/system_views.json.
All frontend pages of this system are defined by json files in the [zukunft data exchange json](https://github.com/zukunft/zukunft.com/blob/develop/docs/exchange_json.md) format.

The system view are used to
- add, update and delete
  - words 
  - verbs
  - triples
  - sources
  - references
  - values
  - formulas
  - results
  - views
  - components
- confirm or undo changes
- link
  - views to words, verbs, triples or formulas
  - components to views
  - words or triples to formulas

additional there are system views for the
- initial setup
- user signup
- user login and activations
- password reset
- user settings
- error log
- search
- value details
- explain results
- formula tests
- im- and export
- backend processes
- about page



