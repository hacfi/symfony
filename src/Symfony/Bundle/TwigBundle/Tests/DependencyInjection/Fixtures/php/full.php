<?php

$container->loadFromExtension('twig', [
    'form_themes' => [
        'MyBundle::form.html.twig',
    ],
    'globals' => [
        'foo' => '@bar',
        'baz' => '@@qux',
        'pi' => 3.14,
        'bad' => ['key' => 'foo'],
    ],
    'auto_reload' => false,
    'charset' => 'ISO-8859-1',
    'debug' => true,
    'strict_variables' => true,
    'default_path' => '%kernel.project_dir%/Fixtures/templates',
    'paths' => [
        'path1',
        'path2',
        'namespaced_path1' => 'namespace1',
        'namespaced_path2' => 'namespace2',
    ],
]);
