<?php
$prefix = 'wilcity_';

return [
  'metaBoxes' => [
    'id'           => 'wilcity_sms_content',
    'title'        => 'SMS Settings',
    'object_types' => ['product'],
    'context'      => 'normal',
    'priority'     => 'low',
    'show_names'   => true, // Show field names on the left
    'fields'       => [
      [
        'name'        => 'SMS Content (To Customer)',
        'description' => 'This is SMS content that will be sent to customer who purchased this product',
        'type'        => 'textarea',
        'id'          => 'wilcity_custom_sms_content'
      ],
      [
        'name'        => 'SMS Content (To Product Author)',
        'description' => 'This is SMS content that will be sent to customer who purchased this product',
        'type'        => 'textarea',
        'id'          => 'wilcity_product_author_sms_content'
      ],
    ]
  ]
];
