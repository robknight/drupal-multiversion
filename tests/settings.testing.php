<?php

if (!empty($test_class)) {
  $namespaces['multiversion'] = 'modules/multiversion';
  $config['core.extension']['module']['multiversion'] = 0;

  $module = 'multiversion_' . explode('\\', $test_class)[1];
  if (file_exists("modules/multiversion/modules/$module/$module.info.yml")) {
    $namespaces[$module] = "modules/multiversion/modules/$module";
    $config['core.extension']['module'][$module] = 0;
  }
}
