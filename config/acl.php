<?php


// CONFIG
$config['session_acl_var'] = 'acl'; // ACL variable in session data
$config['redirect_uri'] = ''; // Landing page if unauthorized user (if empty, will load error_general with 403


// ROUTES
// If route have many permissions, use an array or concat them in a string with |

$config['welcome'] = 'user|admin';

$config['annuaire'] = 'admin';
$config['annuaire/index'] = 'user|admin';
$config['annuaire/view'] = 'user|admin';
$config['annuaire/edit'] = 'admin';
$config['annuaire/add'] = 'admin';

$config['fonction'] = 'admin';

$config['utilisateur'] = 'admin';


/* All routes unspecified here is will be public access */