<?php
$module = array (
  'name' => 'admin',
  'title' => 'System',
  'template' => 'module.tpl.htm',
  'startpage' => 'admin/users_list.php',
  'tab_width' => '60',
  'nav' => 
  array (
    0 => 
    array (
      'title' => 'CP Users',
      'open' => 1,
      'items' => 
      array (
        0 => 
        array (
          'title' => 'Add user',
          'target' => 'content',
          'link' => 'admin/users_edit.php',
        ),
        1 => 
        array (
          'title' => 'Edit user',
          'target' => 'content',
          'link' => 'admin/users_list.php',
        ),
      ),
    ),
    1 => 
    array (
      'title' => 'Groups',
      'open' => 1,
      'items' => 
      array (
        0 => 
        array (
          'title' => 'Add group',
          'target' => 'content',
          'link' => 'admin/groups_edit.php',
        ),
        1 => 
        array (
          'title' => 'Edit group',
          'target' => 'content',
          'link' => 'admin/groups_list.php',
        ),
      ),
    ),
  ),
)
?>