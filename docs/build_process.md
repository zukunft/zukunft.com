Build Process (planned)
-------------

The automatic build process starts these steps if the development branch has been updated:

1. after each update of the development branch a full test is started
7. if the full test is fine the code changes will be merged to the release branch and deployed to the user acceptance servers that contains a copy of the production data. Developers have no privileged access to the release deployment, so they must test using the development release that contains only public data.
8. if all relevant users have accepted the new version the update will automatically be deployed to the production pods.

