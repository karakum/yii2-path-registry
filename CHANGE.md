version 0.4.0
=============

**Date:** 26-Aug-2016

- Namespace can have url as array(like `['controller/action']`) to provide 
  download through controller. Thereby now possible to upload to outside web 
  root folder and then download more securely.
- remove PathOrganizer:getBaseUrl() method.

version 0.3.0
=============

**Date:** 13-Aug-2016

- bug fix: loop never end on mkdir fail;
- improve error explanation in migration.

version 0.2.0
=============

**Date:** 08-Jun-2016

- base path & url getters;
- Native MYSQL timestamp behavior for `created` and `updated` columns.

version 0.1.0
=============

**Date:** 06-Jun-2016

- Initial release
