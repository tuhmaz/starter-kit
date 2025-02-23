<?php return array (
  'App\\Providers\\EventServiceProvider' => 
  array (
    'Illuminate\\Auth\\Events\\Login' => 
    array (
      0 => 'App\\Listeners\\UpdateUserStatus@handleLogin',
    ),
    'Illuminate\\Auth\\Events\\Logout' => 
    array (
      0 => 'App\\Listeners\\UpdateUserStatus@handleLogout',
    ),
  ),
  'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider' => 
  array (
    'Illuminate\\Auth\\Events\\Login' => 
    array (
      0 => 'App\\Listeners\\UpdateUserStatus@handleLogin',
    ),
    'Illuminate\\Auth\\Events\\Logout' => 
    array (
      0 => 'App\\Listeners\\UpdateUserStatus@handleLogout',
    ),
  ),
);