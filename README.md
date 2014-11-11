SimpleChurchCRM PHP API Wrapper
=======

Currently, this does not support all of the functions of the API. If you add to it, please submit a pull request.

## Usage

```
<?php

require 'SimpleChurchAPI.php'

$sccrm = new SimpleChurchAPI([
   'sessionId' => 'Your session ID', //Optional. You can generate one from the API documentation page, 
   'subDomain' => 'mychurch', //Required. the subdomain of simplechurchcrm.com that points to your account.
]);

?>
```
If you don't have a sessionId, you can get one by the following:

```
$sccrm->login('Username', 'Password'); //This will return some info about the user, but it also sets the sessionId
```
