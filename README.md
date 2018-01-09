SimpleChurchCRM PHP API Wrapper
=======

Currently, this does not support all of the functions of the API. If you add to it, please submit a pull request.

## Usage

### Authenticate
```
<?php

require 'SimpleChurchApi.php'

$sccrm = new SimpleChurchApi([
   'sessionId' => 'Your session ID', //Optional. You can generate one from the API documentation page, 
   'subDomain' => 'mychurch', //Required. the subdomain of simplechurchcrm.com that points to your account.
]);

?>
```
If you don't have a sessionId, you can get one by the following:

```
$sccrm->login('Username', 'Password'); //This will return some info about the user, but it also sets the sessionId
```
### Create a Person

```
$person = $sccrm->createPerson([
   'phoneWork' => '123-345-6789',
   'mail' => 'my@address.com',
   'fname' => 'Frank', //Is only required field.
   'lname' => 'Jones',
   'date2' => date('Y-m-d'),
   'text4' => 'Visitor',
   'notifyAdmin' => 1,
]);
```

Any and all of the fields that are documented can be passed in here, including custom fields you have created (date2 and text4 in this example).  You will get back a person object, which will have a `uid` - the unique person id. We'll use that below.

### Put a Person In A Group

```
$sccrm->addPersonToGroup($person->uid, 2); //2 is the ID of the group we're adding the new person to.
```

### Assign an Interaction

```
$sccrm->assignInteraction([
   'instructions' => 'Please follow up with the person. They just registed on the website.',
   'assignedUid' => 261, //Uid of Person to complete the interaction
   'uid' => $person->uid,
   'aid' => 4, //Type of interaction
   'dateCompleteBy' => date('Y-m-d', strtotime('+7 days')),
]);
```

### Or, Make any GET or POST Request

```
$sccrm->get('calendar/properties');

$sccrm->post('interactions', $params);
```
