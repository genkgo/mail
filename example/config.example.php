<?php
// copy this file to config.php for the examples to work
return [
    'transport' => [
        'smtp' => 'smtp-starttls://mailbox%40domain.com:password@localhost:8025',
        'imap' => 'imap://user:pass@server',
        'from' => 'mailbox@domain.com',
        'from-name' => 'Sender',
        'to' => 'mailbox@domain.com',
        'to-name' => 'Recipient',
        'subject' => 'Subject',
        'html' => '<p>Hello World. <img src="cid:123456" width="100" height="100" /></p>',
        'image' => base64_decode('R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs='),
        'attachment' => '',
        'ehlo' => 'mail.local',
        'mailbox' => 'INBOX',
    ],
    'server' => [
        'smtp' => [
            'name' => 'mail.local',
            'address' => '0.0.0.0',
            'port' => 8025,
            'users' => [
                'mailbox@domain.com' =>'password',
            ],
            'spam' => [
                'forbidden' => [
                    'words' => ['viagra'],
                    'points' => 5,
                ],
                'ham' => 5,
                'spam' => 15,
            ]
        ],
    ],
    'protocol' => [
        'imap' => [
            'dsn' => 'imap://user:pass@server',
            'mailbox' => 'INBOX',
        ]
    ]
];