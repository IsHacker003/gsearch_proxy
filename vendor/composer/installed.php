<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => null,
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => null,
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'mikepultz/netdns2' => array(
            'pretty_version' => 'v2.0.8',
            'version' => '2.0.8.0',
            'reference' => '37dcffabf099a33871a9870834a6976f92d4b2ec',
            'type' => 'library',
            'install_path' => __DIR__ . '/../mikepultz/netdns2',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'pear/net_dns2' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
