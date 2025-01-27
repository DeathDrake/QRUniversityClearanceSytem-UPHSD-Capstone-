<?php
  require_once __DIR__ . '/vendor/autoload.php';

  $csv = new \ParseCsv\Csv();
  $csv->parseFile('data.csv');
  foreach ($csv->data as $value) {
    echo $value['username'];
    $password = password_hash($value['password'], PASSWORD_BCRYPT);
    echo $password;

    // TODO: insert into db
  }
