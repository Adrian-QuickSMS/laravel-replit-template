<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuickSMSController extends Controller
{
    public function login()
    {
        return view('quicksms.auth.login', [
            'page_title' => 'Login'
        ]);
    }
    
    public function signup()
    {
        return view('quicksms.auth.signup', [
            'page_title' => 'Sign Up'
        ]);
    }
    
    public function verifyEmail()
    {
        return view('quicksms.auth.verify-email', [
            'page_title' => 'Verify Email'
        ]);
    }
    
    public function signupSecurity()
    {
        return view('quicksms.auth.security', [
            'page_title' => 'Security & Consent'
        ]);
    }
    
    public function dashboard()
    {
        // TODO: Replace with actual data from API
        return view('quicksms.dashboard', [
            'page_title' => 'Dashboard'
        ]);
    }

    public function messages()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Messages',
            'purpose' => 'Manage all messaging activities including sending, receiving, and campaign management.',
            'sub_modules' => [
                'Send Message',
                'Inbox',
                'Campaign History'
            ]
        ]);
    }

    public function sendMessage()
    {
        // TODO: Replace with database query - GET /api/sender-ids
        $sender_ids = [
            ['id' => 1, 'name' => 'QuickSMS', 'type' => 'alphanumeric'],
            ['id' => 2, 'name' => 'ALERTS', 'type' => 'alphanumeric'],
            ['id' => 3, 'name' => '+447700900100', 'type' => 'numeric'],
        ];

        // TODO: Replace with database query - GET /api/rcs-agents?status=approved
        // Only approved RCS agents are selectable in Send Message, Inbox, and RCS Wizard
        // Status lifecycle: Draft -> Submitted -> In Review -> Approved/Rejected
        // Draft/Rejected: editable | Submitted/In Review: locked | Approved: selectable across platform
        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => asset('images/rcs-agents/quicksms-brand.svg'), 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => asset('images/rcs-agents/promotions-agent.svg'), 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with database query - GET /api/templates (excludes API-triggered for portal UI)
        $templates = [
            ['id' => 1, 'name' => 'Welcome Message', 'content' => 'Welcome to QuickSMS! Reply STOP to opt out.', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Live', 'version' => 1],
            ['id' => 2, 'name' => 'Appointment Reminder', 'content' => 'Reminder: Your appointment is on {date} at {time}.', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Live', 'version' => 2],
            ['id' => 3, 'name' => 'Promotional Offer', 'content' => 'Special offer! Get 20% off with code {code}. T&Cs apply.', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Live', 'version' => 1],
            ['id' => 4, 'name' => 'RCS Welcome', 'content' => 'Welcome to our RCS experience! Enjoy rich messaging.', 'trigger' => 'Portal', 'channel' => 'Basic RCS + SMS', 'status' => 'Live', 'version' => 1],
            ['id' => 5, 'name' => 'Product Showcase', 'content' => '', 'trigger' => 'Portal', 'channel' => 'Rich RCS + SMS', 'status' => 'Live', 'version' => 1, 'rcs_payload' => [
                'type' => 'standalone',
                'card' => [
                    'media' => ['url' => '', 'height' => 'MEDIUM'],
                    'title' => 'New Product Launch',
                    'description' => 'Check out our latest product offering!',
                    'suggestions' => [
                        ['type' => 'url', 'text' => 'Learn More', 'url' => 'https://example.com']
                    ]
                ],
                'fallback' => 'New Product Launch! Check out our latest offering at https://example.com'
            ]],
            ['id' => 6, 'name' => 'Archived Welcome', 'content' => 'Old welcome message.', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Archived', 'version' => 1],
        ];

        // TODO: Replace with database query - GET /api/lists
        $lists = [
            ['id' => 1, 'name' => 'Marketing', 'count' => 1247],
            ['id' => 2, 'name' => 'Promotions', 'count' => 856],
            ['id' => 3, 'name' => 'Updates', 'count' => 2103],
            ['id' => 4, 'name' => 'Newsletter', 'count' => 3421],
        ];

        // TODO: Replace with database query - GET /api/tags
        $tags = [
            ['id' => 1, 'name' => 'VIP', 'color' => '#6f42c1', 'count' => 234],
            ['id' => 2, 'name' => 'Customer', 'color' => '#198754', 'count' => 1892],
            ['id' => 3, 'name' => 'Newsletter', 'color' => '#0d6efd', 'count' => 567],
        ];

        // TODO: Replace with database query - GET /api/opt-out-lists
        $opt_out_lists = [
            ['id' => 1, 'name' => 'Master Opt-Out List', 'count' => 2847, 'is_default' => true],
            ['id' => 2, 'name' => 'Marketing Opt-Outs', 'count' => 1245, 'is_default' => false],
            ['id' => 3, 'name' => 'Promotions Opt-Outs', 'count' => 892, 'is_default' => false],
        ];

        // TODO: Replace with database query - GET /api/virtual-numbers
        $virtual_numbers = [
            ['id' => 1, 'number' => '+447700900100', 'label' => 'Main'],
            ['id' => 2, 'number' => '+447700900200', 'label' => 'Marketing'],
        ];

        // TODO: Replace with database query - GET /api/optout-domains
        $optout_domains = [
            ['id' => 1, 'domain' => 'stop.uk', 'is_default' => true],
            ['id' => 2, 'domain' => 'unsubscribe.quicksms.uk', 'is_default' => false],
        ];

        return view('quicksms.messages.send-message', [
            'page_title' => 'Send Message',
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'templates' => $templates,
            'lists' => $lists,
            'tags' => $tags,
            'opt_out_lists' => $opt_out_lists,
            'virtual_numbers' => $virtual_numbers,
            'optout_domains' => $optout_domains,
        ]);
    }

    public function confirmCampaign()
    {
        // TODO: Replace with actual campaign data from session/database
        $campaign = [
            'name' => 'Summer Sale 2024',
            'created_by' => 'John Smith',
            'created_at' => '23/12/2024 14:30',
            'scheduled_time' => 'Immediate',
            'message_validity' => 'Default (48 hours)',
            'sending_window' => 'Respect unsociable hours (21:00 - 08:00)',
        ];

        // TODO: Replace with actual channel data from session
        $channel = [
            'type' => 'rich_rcs', // sms_only, basic_rcs, rich_rcs
            'sms_sender_id' => 'QuickSMS',
            'rcs_agent' => [
                'name' => 'QuickSMS Brand',
                'logo' => 'https://via.placeholder.com/222',
            ],
        ];

        // TODO: Replace with actual recipient data from validation
        $recipients = [
            'total_selected' => 5000,
            'valid' => 4823,
            'invalid' => 127,
            'opted_out' => 50,
            'sources' => [
                'manual_input' => 25,
                'file_upload' => 1500,
                'contacts' => 2000,
                'lists' => 1200,
                'dynamic_lists' => 200,
                'tags' => 75,
            ],
        ];

        // TODO: Replace with actual pricing data from pricing database
        $pricing = [
            'sms_unit_price' => 0.023,
            'rcs_basic_price' => 0.035,
            'rcs_single_price' => 0.045,
            'vat_applicable' => true,
            'vat_rate' => 20,
        ];

        // TODO: Replace with actual message content from session
        $message = [
            'type' => 'rich_rcs',
            'sms_content' => 'Summer Sale is here! Get 30% off all items. Shop now at example.com. Reply STOP to opt out.',
            'rcs_content' => [
                'title' => 'Summer Sale 2024',
                'description' => 'Get 30% off all items!',
                'media_url' => 'https://via.placeholder.com/400x200',
                'buttons' => [
                    ['label' => 'Shop Now', 'type' => 'url'],
                    ['label' => 'Call Us', 'type' => 'phone'],
                ],
            ],
        ];

        return view('quicksms.messages.confirm-campaign', [
            'page_title' => 'Confirm & Send Campaign',
            'campaign' => $campaign,
            'channel' => $channel,
            'recipients' => $recipients,
            'pricing' => $pricing,
            'message' => $message,
        ]);
    }

    public function inbox()
    {
        $sender_ids = [
            ['id' => 'sid_1', 'name' => 'QuickSMS', 'type' => 'Alpha'],
            ['id' => 'sid_2', 'name' => 'Alerts', 'type' => 'Alpha'],
            ['id' => 'sid_3', 'name' => '+447700900100', 'type' => 'VMN'],
            ['id' => 'sid_4', 'name' => '60777', 'type' => 'Shortcode'],
            ['id' => 'sid_5', 'name' => '82345', 'type' => 'Shortcode'],
        ];

        // TODO: Replace with database query - GET /api/rcs-agents?status=approved
        // Only approved RCS agents are selectable in Inbox compose
        $rcs_agents = [
            ['id' => 'agent_1', 'name' => 'QuickSMS Brand', 'status' => 'approved'],
            ['id' => 'agent_2', 'name' => 'RetailBot', 'status' => 'approved'],
        ];

        // TODO: Replace with database query - GET /api/templates (excludes API-triggered for portal UI)
        $templates = [
            ['id' => 'tpl_1', 'name' => 'Quick Reply', 'content' => 'Thank you for your message. We will get back to you shortly.', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Live', 'version' => 1],
            ['id' => 'tpl_2', 'name' => 'Order Update', 'content' => 'Hi {{firstName}}, your order #{{orderNumber}} is on its way!', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Live', 'version' => 2],
            ['id' => 'tpl_3', 'name' => 'Appointment Confirm', 'content' => 'Your appointment is confirmed for {{date}} at {{time}}. Reply YES to confirm or NO to cancel.', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Live', 'version' => 1],
            ['id' => 'tpl_4', 'name' => 'RCS Thank You', 'content' => 'Thanks for reaching out! Our team will respond shortly.', 'trigger' => 'Portal', 'channel' => 'Basic RCS + SMS', 'status' => 'Live', 'version' => 1],
            ['id' => 'tpl_5', 'name' => 'Rich Promo Card', 'content' => '', 'trigger' => 'Portal', 'channel' => 'Rich RCS + SMS', 'status' => 'Live', 'version' => 1, 'rcs_payload' => [
                'type' => 'standalone',
                'card' => [
                    'media' => ['url' => '', 'height' => 'MEDIUM'],
                    'title' => 'Special Offer',
                    'description' => 'Exclusive discount just for you!',
                    'suggestions' => [
                        ['type' => 'url', 'text' => 'Shop Now', 'url' => 'https://example.com/shop']
                    ]
                ],
                'fallback' => 'Special Offer! Exclusive discount at https://example.com/shop'
            ]],
        ];

        // Extended mock conversations dataset for filter/sort testing
        // TODO: Replace with API call to GET /api/conversations
        $conversations = [
            [
                'id' => 'conv_001',
                'phone' => '+447700900111',
                'phone_masked' => '+44 77** ***111',
                'name' => 'Sarah Mitchell',
                'initials' => 'SM',
                'contact_id' => 'c_001',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'sender_id' => 'sid_4',
                'unread' => true,
                'unread_count' => 2,
                'last_message' => 'When will my order arrive?',
                'last_message_time' => '10:32 AM',
                'timestamp' => strtotime('today 10:32'),
                'first_contact' => '15 Dec 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Hi Sarah, your order #12345 has been dispatched!', 'time' => '09:15 AM', 'date' => '22 Dec 2024'],
                    ['direction' => 'inbound', 'content' => 'Thanks! How long will delivery take?', 'time' => '09:45 AM', 'date' => '22 Dec 2024'],
                    ['direction' => 'outbound', 'content' => 'Usually 2-3 business days. You\'ll receive tracking soon.', 'time' => '09:48 AM', 'date' => '22 Dec 2024'],
                    ['direction' => 'inbound', 'content' => 'Great, thank you!', 'time' => '10:02 AM', 'date' => '23 Dec 2024'],
                    ['direction' => 'inbound', 'content' => 'When will my order arrive?', 'time' => '10:32 AM', 'date' => '23 Dec 2024'],
                ],
            ],
            [
                'id' => 'conv_002',
                'phone' => '+447700900222',
                'phone_masked' => '+44 77** ***222',
                'name' => 'James Wilson',
                'initials' => 'JW',
                'contact_id' => 'c_002',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'rcs_agent_id' => 'agent_1',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'Can I change my delivery address?',
                'last_message_time' => '09:15 AM',
                'timestamp' => strtotime('today 09:15'),
                'first_contact' => '10 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Hi, I placed an order yesterday', 'time' => '08:30 AM'],
                    ['direction' => 'outbound', 'content' => 'Hello James! How can we help you today?', 'time' => '08:45 AM'],
                    ['direction' => 'inbound', 'content' => 'Can I change my delivery address?', 'time' => '09:15 AM'],
                ],
            ],
            [
                'id' => 'conv_003',
                'phone' => '+447700900333',
                'phone_masked' => '+44 77** ***333',
                'name' => '+44 7700 900333',
                'initials' => '??',
                'contact_id' => null,
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'sender_id' => 'sid_3',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'STOP',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 10:15'),
                'first_contact' => '20 Dec 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Flash sale! 50% off everything today only. Shop now at example.com', 'time' => '10:00 AM'],
                    ['direction' => 'inbound', 'content' => 'STOP', 'time' => '10:15 AM'],
                ],
            ],
            [
                'id' => 'conv_004',
                'phone' => '+447700900444',
                'phone_masked' => '+44 77** ***444',
                'name' => 'Emma Thompson',
                'initials' => 'ET',
                'contact_id' => 'c_003',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'rcs_agent_id' => 'agent_2',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Perfect, see you then!',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 15:48'),
                'first_contact' => '01 Nov 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Reminder: Your appointment is tomorrow at 2pm', 'time' => '3:00 PM'],
                    ['direction' => 'inbound', 'content' => 'Thanks for the reminder!', 'time' => '3:30 PM'],
                    ['direction' => 'inbound', 'content' => 'Can I reschedule to 3pm instead?', 'time' => '3:32 PM'],
                    ['direction' => 'outbound', 'content' => 'Of course! Your appointment has been moved to 3pm.', 'time' => '3:45 PM'],
                    ['direction' => 'inbound', 'content' => 'Perfect, see you then!', 'time' => '3:48 PM'],
                ],
            ],
            [
                'id' => 'conv_005',
                'phone' => '+447700900555',
                'phone_masked' => '+44 77** ***555',
                'name' => 'Michael Brown',
                'initials' => 'MB',
                'contact_id' => 'c_004',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'sender_id' => 'sid_4',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Your refund has been processed.',
                'last_message_time' => '2 days ago',
                'timestamp' => strtotime('-2 days 16:30'),
                'first_contact' => '05 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'I need to return my order', 'time' => '11:00 AM'],
                    ['direction' => 'outbound', 'content' => 'Sorry to hear that. What\'s the issue?', 'time' => '11:15 AM'],
                    ['direction' => 'inbound', 'content' => 'Wrong size', 'time' => '11:20 AM'],
                    ['direction' => 'outbound', 'content' => 'No problem. Please return to our store or use the prepaid label in your package.', 'time' => '11:25 AM'],
                    ['direction' => 'inbound', 'content' => 'Done, dropped it off today', 'time' => '2:00 PM'],
                    ['direction' => 'outbound', 'content' => 'Your refund has been processed.', 'time' => '4:30 PM'],
                ],
            ],
            [
                'id' => 'conv_006',
                'phone' => '+447700900666',
                'phone_masked' => '+44 77** ***666',
                'name' => 'Sophie Brown',
                'initials' => 'SB',
                'contact_id' => 'c_005',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'rcs_agent_id' => 'agent_1',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Can I reschedule to next week?',
                'last_message_time' => '15 Dec',
                'timestamp' => strtotime('-14 days 11:00'),
                'first_contact' => '01 Dec 2024',
                'messages' => [
                    [
                        'direction' => 'outbound',
                        'type' => 'rich_card',
                        'time' => '15 Dec 09:00',
                        'rich_card' => [
                            'image' => '/images/placeholder-newsletter.jpg',
                            'title' => 'Weekly Newsletter',
                            'description' => 'Important updates about upcoming events and school closures.',
                            'button' => 'Read More',
                        ],
                        'caption' => 'School newsletter for this week',
                    ],
                    ['direction' => 'inbound', 'content' => 'Can I reschedule to next week?', 'time' => '15 Dec 11:00'],
                ],
            ],
            // Additional conversations for filter/sort testing
            [
                'id' => 'conv_007',
                'phone' => '+447700900777',
                'phone_masked' => '+44 77** ***777',
                'name' => 'Alice Henderson',
                'initials' => 'AH',
                'contact_id' => 'c_006',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => true,
                'unread_count' => 3,
                'last_message' => 'Is my package still on the way?',
                'last_message_time' => '3 days ago',
                'timestamp' => strtotime('-3 days 11:45'),
                'first_contact' => '18 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Is my package still on the way?', 'time' => '11:45 AM', 'date' => '3 days ago'],
                ],
            ],
            [
                'id' => 'conv_008',
                'phone' => '+447700900888',
                'phone_masked' => '+44 77** ***888',
                'name' => 'Benjamin Clarke',
                'initials' => 'BC',
                'contact_id' => 'c_007',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Thanks for the quick response!',
                'last_message_time' => '3 days ago',
                'timestamp' => strtotime('-3 days 14:20'),
                'first_contact' => '10 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Thanks for the quick response!', 'time' => '2:20 PM'],
                ],
            ],
            [
                'id' => 'conv_009',
                'phone' => '+447700900999',
                'phone_masked' => '+44 77** ***999',
                'name' => 'Charlotte Davies',
                'initials' => 'CD',
                'contact_id' => 'c_008',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'Can you call me back please?',
                'last_message_time' => '4 days ago',
                'timestamp' => strtotime('-4 days 08:22'),
                'first_contact' => '22 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Can you call me back please?', 'time' => '8:22 AM', 'date' => '4 days ago'],
                ],
            ],
            [
                'id' => 'conv_010',
                'phone' => '+447700901010',
                'phone_masked' => '+44 77** ***010',
                'name' => 'Daniel Evans',
                'initials' => 'DE',
                'contact_id' => 'c_009',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Order received, thank you!',
                'last_message_time' => '4 days ago',
                'timestamp' => strtotime('-4 days 09:30'),
                'first_contact' => '05 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Order received, thank you!', 'time' => '9:30 AM'],
                ],
            ],
            [
                'id' => 'conv_011',
                'phone' => '+447700901111',
                'phone_masked' => '+44 77** ***111',
                'name' => 'Eleanor Foster',
                'initials' => 'EF',
                'contact_id' => 'c_010',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Perfect, I\'ll be there at 10am',
                'last_message_time' => '5 days ago',
                'timestamp' => strtotime('-5 days 16:45'),
                'first_contact' => '01 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Perfect, I\'ll be there at 10am', 'time' => '4:45 PM'],
                ],
            ],
            [
                'id' => 'conv_012',
                'phone' => '+447700901212',
                'phone_masked' => '+44 77** ***212',
                'name' => 'Frederick Grant',
                'initials' => 'FG',
                'contact_id' => 'c_011',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => true,
                'unread_count' => 2,
                'last_message' => 'Where is my refund?',
                'last_message_time' => '5 days ago',
                'timestamp' => strtotime('-5 days 07:55'),
                'first_contact' => '15 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Where is my refund?', 'time' => '7:55 AM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_013',
                'phone' => '+447700901313',
                'phone_masked' => '+44 77** ***313',
                'name' => 'Georgia Harris',
                'initials' => 'GH',
                'contact_id' => 'c_012',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Appointment confirmed for Friday',
                'last_message_time' => '1 week ago',
                'timestamp' => strtotime('-7 days 11:00'),
                'first_contact' => '28 Nov 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Appointment confirmed for Friday', 'time' => '11:00 AM'],
                ],
            ],
            [
                'id' => 'conv_014',
                'phone' => '+447700901414',
                'phone_masked' => '+44 77** ***414',
                'name' => 'Henry Irving',
                'initials' => 'HI',
                'contact_id' => 'c_013',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Got it, thanks!',
                'last_message_time' => '6 days ago',
                'timestamp' => strtotime('-6 days 13:15'),
                'first_contact' => '20 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Got it, thanks!', 'time' => '1:15 PM'],
                ],
            ],
            [
                'id' => 'conv_015',
                'phone' => '+447700901515',
                'phone_masked' => '+44 77** ***515',
                'name' => 'Isabelle Jones',
                'initials' => 'IJ',
                'contact_id' => 'c_014',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'Do you have this in blue?',
                'last_message_time' => '12:10 PM',
                'timestamp' => strtotime('today 12:10'),
                'first_contact' => '19 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Do you have this in blue?', 'time' => '12:10 PM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_016',
                'phone' => '+447700901616',
                'phone_masked' => '+44 77** ***616',
                'name' => 'Jack Kelly',
                'initials' => 'JK',
                'contact_id' => 'c_015',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'I\'ll check and get back to you',
                'last_message_time' => '2 weeks ago',
                'timestamp' => strtotime('-14 days 10:30'),
                'first_contact' => '01 Nov 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'I\'ll check and get back to you', 'time' => '10:30 AM'],
                ],
            ],
            [
                'id' => 'conv_017',
                'phone' => '+447700901717',
                'phone_masked' => '+44 77** ***717',
                'name' => 'Katie Lewis',
                'initials' => 'KL',
                'contact_id' => 'c_016',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Thanks for letting me know',
                'last_message_time' => '8 days ago',
                'timestamp' => strtotime('-8 days 15:20'),
                'first_contact' => '10 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Thanks for letting me know', 'time' => '3:20 PM'],
                ],
            ],
            [
                'id' => 'conv_018',
                'phone' => '+447700901818',
                'phone_masked' => '+44 77** ***818',
                'name' => 'Liam Morgan',
                'initials' => 'LM',
                'contact_id' => 'c_017',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => true,
                'unread_count' => 4,
                'last_message' => 'URGENT: Need to speak to someone now',
                'last_message_time' => '06:30 AM',
                'timestamp' => strtotime('today 06:30'),
                'first_contact' => '25 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'URGENT: Need to speak to someone now', 'time' => '6:30 AM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_019',
                'phone' => '+447700901919',
                'phone_masked' => '+44 77** ***919',
                'name' => 'Mia Nelson',
                'initials' => 'MN',
                'contact_id' => 'c_018',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Wonderful, looking forward to it',
                'last_message_time' => '9 days ago',
                'timestamp' => strtotime('-9 days 12:00'),
                'first_contact' => '15 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Wonderful, looking forward to it', 'time' => '12:00 PM'],
                ],
            ],
            [
                'id' => 'conv_020',
                'phone' => '+447700902020',
                'phone_masked' => '+44 77** ***020',
                'name' => 'Noah Owen',
                'initials' => 'NO',
                'contact_id' => 'c_019',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Payment received',
                'last_message_time' => '10 days ago',
                'timestamp' => strtotime('-10 days 14:45'),
                'first_contact' => '01 Dec 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Payment received', 'time' => '2:45 PM'],
                ],
            ],
            [
                'id' => 'conv_021',
                'phone' => '+447700902121',
                'phone_masked' => '+44 77** ***121',
                'name' => 'Olivia Parker',
                'initials' => 'OP',
                'contact_id' => 'c_020',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'Is the store open on Boxing Day?',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 18:30'),
                'first_contact' => '20 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Is the store open on Boxing Day?', 'time' => '6:30 PM', 'date' => 'Yesterday'],
                ],
            ],
            [
                'id' => 'conv_022',
                'phone' => '+447700902222',
                'phone_masked' => '+44 77** ***222',
                'name' => '+44 7700 902222',
                'initials' => '??',
                'contact_id' => null,
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'INFO',
                'last_message_time' => '11 days ago',
                'timestamp' => strtotime('-11 days 09:00'),
                'first_contact' => '15 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'INFO', 'time' => '9:00 AM'],
                ],
            ],
            [
                'id' => 'conv_023',
                'phone' => '+447700902323',
                'phone_masked' => '+44 77** ***323',
                'name' => 'Peter Quinn',
                'initials' => 'PQ',
                'contact_id' => 'c_021',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'That works for me',
                'last_message_time' => '12 days ago',
                'timestamp' => strtotime('-12 days 16:00'),
                'first_contact' => '10 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'That works for me', 'time' => '4:00 PM'],
                ],
            ],
            [
                'id' => 'conv_024',
                'phone' => '+447700902424',
                'phone_masked' => '+44 77** ***424',
                'name' => 'Quinn Roberts',
                'initials' => 'QR',
                'contact_id' => 'c_022',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Delivery confirmed',
                'last_message_time' => '13 days ago',
                'timestamp' => strtotime('-13 days 11:30'),
                'first_contact' => '05 Dec 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Delivery confirmed', 'time' => '11:30 AM'],
                ],
            ],
            [
                'id' => 'conv_025',
                'phone' => '+447700902525',
                'phone_masked' => '+44 77** ***525',
                'name' => 'Rachel Smith',
                'initials' => 'RS',
                'contact_id' => 'c_023',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => true,
                'unread_count' => 2,
                'last_message' => 'Why hasn\'t anyone replied?',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 20:15'),
                'first_contact' => '18 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Why hasn\'t anyone replied?', 'time' => '8:15 PM', 'date' => 'Yesterday'],
                ],
            ],
            [
                'id' => 'conv_026',
                'phone' => '+447700902626',
                'phone_masked' => '+44 77** ***626',
                'name' => 'Samuel Taylor',
                'initials' => 'ST',
                'contact_id' => 'c_024',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'See you next week',
                'last_message_time' => '2 weeks ago',
                'timestamp' => strtotime('-14 days 17:45'),
                'first_contact' => '01 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'See you next week', 'time' => '5:45 PM'],
                ],
            ],
            [
                'id' => 'conv_027',
                'phone' => '+447700902727',
                'phone_masked' => '+44 77** ***727',
                'name' => 'Tina Underwood',
                'initials' => 'TU',
                'contact_id' => 'c_025',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Confirmed, thank you',
                'last_message_time' => '15 days ago',
                'timestamp' => strtotime('-15 days 10:00'),
                'first_contact' => '25 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Confirmed, thank you', 'time' => '10:00 AM'],
                ],
            ],
            [
                'id' => 'conv_028',
                'phone' => '+447700902828',
                'phone_masked' => '+44 77** ***828',
                'name' => '+44 7700 902828',
                'initials' => '??',
                'contact_id' => null,
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'HELP',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 22:00'),
                'first_contact' => '26 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'HELP', 'time' => '10:00 PM', 'date' => 'Yesterday'],
                ],
            ],
            [
                'id' => 'conv_029',
                'phone' => '+447700902929',
                'phone_masked' => '+44 77** ***929',
                'name' => 'Uma Vance',
                'initials' => 'UV',
                'contact_id' => 'c_026',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Great service as always',
                'last_message_time' => '16 days ago',
                'timestamp' => strtotime('-16 days 14:30'),
                'first_contact' => '20 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Great service as always', 'time' => '2:30 PM'],
                ],
            ],
            [
                'id' => 'conv_030',
                'phone' => '+447700903030',
                'phone_masked' => '+44 77** ***030',
                'name' => 'Victor Watson',
                'initials' => 'VW',
                'contact_id' => 'c_027',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Will do, cheers',
                'last_message_time' => '17 days ago',
                'timestamp' => strtotime('-17 days 09:15'),
                'first_contact' => '15 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Will do, cheers', 'time' => '9:15 AM'],
                ],
            ],
            [
                'id' => 'conv_031',
                'phone' => '+447700903131',
                'phone_masked' => '+44 77** ***131',
                'name' => 'Wendy Xavier',
                'initials' => 'WX',
                'contact_id' => 'c_028',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'What time do you close?',
                'last_message_time' => '01:15 PM',
                'timestamp' => strtotime('today 13:15'),
                'first_contact' => '22 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'What time do you close?', 'time' => '1:15 PM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_032',
                'phone' => '+447700903232',
                'phone_masked' => '+44 77** ***232',
                'name' => 'Xander Young',
                'initials' => 'XY',
                'contact_id' => 'c_029',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'No problem at all',
                'last_message_time' => '18 days ago',
                'timestamp' => strtotime('-18 days 16:20'),
                'first_contact' => '10 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'No problem at all', 'time' => '4:20 PM'],
                ],
            ],
            [
                'id' => 'conv_033',
                'phone' => '+447700903333',
                'phone_masked' => '+44 77** ***333',
                'name' => 'Yara Zane',
                'initials' => 'YZ',
                'contact_id' => 'c_030',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Perfect, thanks for confirming',
                'last_message_time' => '19 days ago',
                'timestamp' => strtotime('-19 days 11:45'),
                'first_contact' => '05 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Perfect, thanks for confirming', 'time' => '11:45 AM'],
                ],
            ],
            [
                'id' => 'conv_034',
                'phone' => '+447700903434',
                'phone_masked' => '+44 77** ***434',
                'name' => 'Zoe Adams',
                'initials' => 'ZA',
                'contact_id' => 'c_031',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => true,
                'unread_count' => 2,
                'last_message' => 'Still waiting for my order!',
                'last_message_time' => '02:30 PM',
                'timestamp' => strtotime('today 14:30'),
                'first_contact' => '23 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Still waiting for my order!', 'time' => '2:30 PM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_035',
                'phone' => '+447700903535',
                'phone_masked' => '+44 77** ***535',
                'name' => 'Aaron Baker',
                'initials' => 'AB',
                'contact_id' => 'c_032',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Got the package today',
                'last_message_time' => '20 days ago',
                'timestamp' => strtotime('-20 days 15:00'),
                'first_contact' => '01 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Got the package today', 'time' => '3:00 PM'],
                ],
            ],
        ];

        $unread_count = collect($conversations)->where('unread', true)->count();

        // Calculate awaiting_reply_48h for each conversation
        // Logic: unread AND timestamp older than 48 hours
        $now = time();
        $fortyEightHours = 48 * 60 * 60; // 48 hours in seconds
        
        foreach ($conversations as &$conv) {
            $conv['awaiting_reply_48h'] = false;
            
            // Only flag if conversation is unread AND over 48 hours old
            $isUnread = isset($conv['unread']) && $conv['unread'] === true;
            
            if ($isUnread && isset($conv['timestamp'])) {
                $timeDiff = $now - $conv['timestamp'];
                $conv['awaiting_reply_48h'] = $timeDiff >= $fortyEightHours;
            }
        }
        unset($conv); // Break reference

        return view('quicksms.messages.inbox', [
            'page_title' => 'Inbox',
            'conversations' => $conversations,
            'unread_count' => $unread_count,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'templates' => $templates,
        ]);
    }

    public function campaignHistory()
    {
        // TODO: Replace with API call to GET /api/campaigns
        $campaigns = [
            ['id' => 'camp_001', 'name' => 'New Year Flash Sale', 'channel' => 'rich_rcs', 'status' => 'scheduled', 'recipients_total' => 5200, 'recipients_delivered' => null, 'send_date' => '2025-01-01 00:00', 'sender_id' => 'QuickSMS', 'rcs_agent' => 'QuickSMS Brand', 'tags' => ['VIP', 'Promo'], 'template' => 'Sale Announcement', 'has_tracking' => true, 'has_optout' => true],
            ['id' => 'camp_002', 'name' => 'Holiday Greetings', 'channel' => 'sms_only', 'status' => 'sending', 'recipients_total' => 3150, 'recipients_delivered' => 1840, 'send_date' => '2024-12-24 09:00', 'sender_id' => 'Greetings', 'rcs_agent' => null, 'tags' => ['Newsletter'], 'template' => null, 'has_tracking' => false, 'has_optout' => true],
            ['id' => 'camp_003', 'name' => 'Boxing Day Deals', 'channel' => 'basic_rcs', 'status' => 'scheduled', 'recipients_total' => 2800, 'recipients_delivered' => null, 'send_date' => '2024-12-26 08:00', 'sender_id' => 'QuickSMS', 'rcs_agent' => 'QuickSMS Brand', 'tags' => ['Promo', 'Sale'], 'template' => 'Flash Deal', 'has_tracking' => true, 'has_optout' => true],
            ['id' => 'camp_004', 'name' => 'Christmas Eve Reminder', 'channel' => 'sms_only', 'status' => 'complete', 'recipients_total' => 1500, 'recipients_delivered' => 1487, 'send_date' => '2024-12-24 07:00', 'sender_id' => 'Reminders', 'rcs_agent' => null, 'tags' => ['Transactional'], 'template' => 'Reminder', 'has_tracking' => false, 'has_optout' => false],
            ['id' => 'camp_005', 'name' => 'Winter Clearance', 'channel' => 'rich_rcs', 'status' => 'complete', 'recipients_total' => 4200, 'recipients_delivered' => 4156, 'send_date' => '2024-12-23 14:30', 'sender_id' => 'QuickSMS', 'rcs_agent' => 'RetailBot', 'tags' => ['Clearance', 'VIP'], 'template' => 'Product Showcase', 'has_tracking' => true, 'has_optout' => true],
            ['id' => 'camp_006', 'name' => 'Last Minute Gifts', 'channel' => 'sms_only', 'status' => 'complete', 'recipients_total' => 890, 'recipients_delivered' => 885, 'send_date' => '2024-12-23 10:00', 'sender_id' => 'GiftShop', 'rcs_agent' => null, 'tags' => ['Promo'], 'template' => null, 'has_tracking' => true, 'has_optout' => true],
            ['id' => 'camp_007', 'name' => 'Delivery Update Batch', 'channel' => 'sms_only', 'status' => 'complete', 'recipients_total' => 2340, 'recipients_delivered' => 2338, 'send_date' => '2024-12-22 16:45', 'sender_id' => 'Logistics', 'rcs_agent' => null, 'tags' => ['Transactional', 'Delivery'], 'template' => 'Shipping Update', 'has_tracking' => true, 'has_optout' => false],
            ['id' => 'camp_008', 'name' => 'Weekend Special Offer', 'channel' => 'basic_rcs', 'status' => 'complete', 'recipients_total' => 1800, 'recipients_delivered' => 1756, 'send_date' => '2024-12-21 09:00', 'sender_id' => 'QuickSMS', 'rcs_agent' => 'QuickSMS Brand', 'tags' => ['Weekend', 'Promo'], 'template' => 'Weekend Deal', 'has_tracking' => false, 'has_optout' => true],
            ['id' => 'camp_009', 'name' => 'VIP Early Access', 'channel' => 'rich_rcs', 'status' => 'complete', 'recipients_total' => 520, 'recipients_delivered' => 518, 'send_date' => '2024-12-20 18:00', 'sender_id' => 'VIPClub', 'rcs_agent' => 'RetailBot', 'tags' => ['VIP', 'Exclusive'], 'template' => 'VIP Invitation', 'has_tracking' => true, 'has_optout' => true],
            ['id' => 'camp_010', 'name' => 'Store Opening Hours', 'channel' => 'sms_only', 'status' => 'complete', 'recipients_total' => 3400, 'recipients_delivered' => 3389, 'send_date' => '2024-12-20 08:00', 'sender_id' => 'StoreInfo', 'rcs_agent' => null, 'tags' => ['Info'], 'template' => null, 'has_tracking' => false, 'has_optout' => false],
            ['id' => 'camp_011', 'name' => 'Flash Sale Alert', 'channel' => 'sms_only', 'status' => 'complete', 'recipients_total' => 6100, 'recipients_delivered' => 6042, 'send_date' => '2024-12-19 12:00', 'sender_id' => 'QuickSMS', 'rcs_agent' => null, 'tags' => ['Flash', 'Sale'], 'template' => 'Flash Deal', 'has_tracking' => true, 'has_optout' => true],
            ['id' => 'camp_012', 'name' => 'Customer Survey', 'channel' => 'basic_rcs', 'status' => 'complete', 'recipients_total' => 1200, 'recipients_delivered' => 1145, 'send_date' => '2024-12-18 10:30', 'sender_id' => 'Feedback', 'rcs_agent' => 'SurveyBot', 'tags' => ['Survey', 'Feedback'], 'template' => 'Survey Request', 'has_tracking' => false, 'has_optout' => true],
            ['id' => 'camp_013', 'name' => 'Order Confirmation Batch', 'channel' => 'sms_only', 'status' => 'complete', 'recipients_total' => 450, 'recipients_delivered' => 450, 'send_date' => '2024-12-17 15:20', 'sender_id' => 'Orders', 'rcs_agent' => null, 'tags' => ['Transactional'], 'template' => 'Order Confirm', 'has_tracking' => true, 'has_optout' => false],
            ['id' => 'camp_014', 'name' => 'Appointment Reminders', 'channel' => 'sms_only', 'status' => 'complete', 'recipients_total' => 780, 'recipients_delivered' => 776, 'send_date' => '2024-12-16 09:00', 'sender_id' => 'Bookings', 'rcs_agent' => null, 'tags' => ['Reminder', 'Appointments'], 'template' => 'Appointment', 'has_tracking' => false, 'has_optout' => true],
            ['id' => 'camp_015', 'name' => 'Product Launch Teaser', 'channel' => 'rich_rcs', 'status' => 'complete', 'recipients_total' => 2500, 'recipients_delivered' => 2467, 'send_date' => '2024-12-15 11:00', 'sender_id' => 'QuickSMS', 'rcs_agent' => 'QuickSMS Brand', 'tags' => ['Launch', 'Product'], 'template' => 'Product Launch', 'has_tracking' => true, 'has_optout' => true],
        ];

        return view('quicksms.messages.campaign-history', [
            'page_title' => 'Campaign History',
            'campaigns' => $campaigns,
        ]);
    }
    
    public function campaignApprovals()
    {
        $pendingApprovals = [
            ['id' => 'camp_pa001', 'name' => 'January Promo Blast', 'sub_account' => 'Marketing Department', 'created_by' => 'Emma Thompson', 'message_volume' => 5200, 'estimated_cost' => 156.00, 'scheduled_time' => '2026-01-20 09:00', 'status' => 'pending', 'channel' => 'SMS', 'created_at' => '2026-01-15 14:32'],
            ['id' => 'camp_pa002', 'name' => 'Product Launch RCS', 'sub_account' => 'Marketing Department', 'created_by' => 'Michael Brown', 'message_volume' => 3800, 'estimated_cost' => 228.00, 'scheduled_time' => '2026-01-21 10:00', 'status' => 'pending', 'channel' => 'RCS', 'created_at' => '2026-01-16 09:15'],
            ['id' => 'camp_pa003', 'name' => 'Flash Sale Alert', 'sub_account' => 'Customer Support', 'created_by' => 'Chris Martinez', 'message_volume' => 1500, 'estimated_cost' => 45.00, 'scheduled_time' => '2026-01-18 12:00', 'status' => 'pending', 'channel' => 'SMS', 'created_at' => '2026-01-16 11:45'],
        ];
        
        $recentDecisions = [
            ['id' => 'camp_rd001', 'name' => 'Weekend Special', 'sub_account' => 'Marketing Department', 'created_by' => 'Emma Thompson', 'decision' => 'approved', 'approver' => 'Sarah Mitchell', 'decided_at' => '2026-01-14 16:20'],
            ['id' => 'camp_rd002', 'name' => 'Discount Code SMS', 'sub_account' => 'Marketing Department', 'created_by' => 'Michael Brown', 'decision' => 'rejected', 'approver' => 'James Wilson', 'decided_at' => '2026-01-13 10:05', 'rejection_reason' => 'Content requires compliance review before sending'],
        ];
        
        return view('quicksms.messages.campaign-approvals', [
            'page_title' => 'Campaign Approvals',
            'pending_approvals' => $pendingApprovals,
            'recent_decisions' => $recentDecisions,
        ]);
    }

    public function contacts()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Contact Book',
            'purpose' => 'Manage your contacts, organize them into lists, and handle opt-out preferences.',
            'sub_modules' => [
                'All Contacts',
                'Lists',
                'Tags',
                'Opt-Out Lists'
            ]
        ]);
    }

    public function allContacts()
    {
        $contacts = [
            [
                'id' => 1,
                'first_name' => 'Emma',
                'last_name' => 'Thompson',
                'initials' => 'ET',
                'email' => 'emma.thompson@example.com',
                'mobile' => '+44 7700 900123',
                'mobile_masked' => '+44 77** ***123',
                'tags' => ['VIP', 'Newsletter'],
                'lists' => ['Marketing'],
                'status' => 'active',
                'source' => 'UI',
                'created_at' => '2024-11-15',
            ],
            [
                'id' => 2,
                'first_name' => 'James',
                'last_name' => 'Wilson',
                'initials' => 'JW',
                'email' => 'james.wilson@example.com',
                'mobile' => '+44 7700 900456',
                'mobile_masked' => '+44 77** ***456',
                'tags' => ['Customer'],
                'lists' => ['Promotions', 'Updates'],
                'status' => 'active',
                'source' => 'Import',
                'created_at' => '2024-10-22',
            ],
            [
                'id' => 3,
                'first_name' => 'Sarah',
                'last_name' => 'Mitchell',
                'initials' => 'SM',
                'email' => 'sarah.m@example.com',
                'mobile' => '+44 7700 900789',
                'mobile_masked' => '+44 77** ***789',
                'tags' => ['VIP', 'Partner'],
                'lists' => ['Marketing'],
                'status' => 'active',
                'source' => 'API',
                'created_at' => '2024-12-01',
            ],
            [
                'id' => 4,
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'initials' => 'MB',
                'email' => 'michael.brown@example.com',
                'mobile' => '+44 7700 900321',
                'mobile_masked' => '+44 77** ***321',
                'tags' => [],
                'lists' => ['Updates'],
                'status' => 'opted-out',
                'source' => 'UI',
                'created_at' => '2024-09-10',
            ],
            [
                'id' => 5,
                'first_name' => 'Lisa',
                'last_name' => 'Anderson',
                'initials' => 'LA',
                'email' => 'lisa.anderson@example.com',
                'mobile' => '+44 7700 900654',
                'mobile_masked' => '+44 77** ***654',
                'tags' => ['Newsletter', 'Customer'],
                'lists' => ['Promotions'],
                'status' => 'active',
                'source' => 'Email-to-SMS',
                'created_at' => '2024-11-28',
            ],
            [
                'id' => 6,
                'first_name' => 'David',
                'last_name' => 'Taylor',
                'initials' => 'DT',
                'email' => '',
                'mobile' => '+44 7700 900987',
                'mobile_masked' => '+44 77** ***987',
                'tags' => ['VIP'],
                'lists' => [],
                'status' => 'active',
                'source' => 'Import',
                'created_at' => '2024-12-05',
            ],
            [
                'id' => 7,
                'first_name' => 'Jennifer',
                'last_name' => 'Davis',
                'initials' => 'JD',
                'email' => 'jen.davis@example.com',
                'mobile' => '+44 7700 900111',
                'mobile_masked' => '+44 77** ***111',
                'tags' => ['Partner'],
                'lists' => ['Marketing', 'Updates'],
                'status' => 'opted-out',
                'source' => 'UI',
                'created_at' => '2024-08-20',
            ],
            [
                'id' => 8,
                'first_name' => 'Robert',
                'last_name' => 'Garcia',
                'initials' => 'RG',
                'email' => 'r.garcia@example.com',
                'mobile' => '+44 7700 900222',
                'mobile_masked' => '+44 77** ***222',
                'tags' => ['Customer', 'Newsletter'],
                'lists' => ['Promotions'],
                'status' => 'active',
                'source' => 'API',
                'created_at' => '2024-12-10',
            ],
        ];

        $available_tags = ['VIP', 'Newsletter', 'Customer', 'Partner'];
        $available_lists = ['Marketing', 'Promotions', 'Updates'];

        return view('quicksms.contacts.all-contacts', [
            'page_title' => 'All Contacts',
            'contacts' => $contacts,
            'total_contacts' => 432,
            'available_tags' => $available_tags,
            'available_lists' => $available_lists,
        ]);
    }

    public function lists()
    {
        // TODO: Fetch lists from database via API
        $static_lists = [
            [
                'id' => 1,
                'name' => 'Marketing',
                'description' => 'Main marketing campaign recipients',
                'type' => 'static',
                'contact_count' => 1247,
                'created_at' => '2024-10-15',
                'updated_at' => '2024-12-20',
            ],
            [
                'id' => 2,
                'name' => 'Promotions',
                'description' => 'Customers interested in special offers',
                'type' => 'static',
                'contact_count' => 856,
                'created_at' => '2024-09-22',
                'updated_at' => '2024-12-18',
            ],
            [
                'id' => 3,
                'name' => 'Updates',
                'description' => 'Product and service updates subscribers',
                'type' => 'static',
                'contact_count' => 2103,
                'created_at' => '2024-08-10',
                'updated_at' => '2024-12-21',
            ],
            [
                'id' => 4,
                'name' => 'Newsletter',
                'description' => 'Monthly newsletter recipients',
                'type' => 'static',
                'contact_count' => 3421,
                'created_at' => '2024-07-05',
                'updated_at' => '2024-12-19',
            ],
        ];

        $dynamic_lists = [
            [
                'id' => 101,
                'name' => 'New Contacts (30 days)',
                'description' => 'Contacts added in the last 30 days',
                'type' => 'dynamic',
                'rules' => [
                    ['field' => 'created_date', 'operator' => 'last_n_days', 'value' => '30']
                ],
                'contact_count' => 89,
                'created_at' => '2024-11-01',
                'last_evaluated' => '2024-12-22',
            ],
            [
                'id' => 102,
                'name' => 'Active VIPs',
                'description' => 'VIP tagged contacts with active status',
                'type' => 'dynamic',
                'rules' => [
                    ['field' => 'tag', 'operator' => 'contains', 'value' => 'VIP'],
                    ['field' => 'status', 'operator' => 'equals', 'value' => 'Active']
                ],
                'contact_count' => 156,
                'created_at' => '2024-10-20',
                'last_evaluated' => '2024-12-22',
            ],
            [
                'id' => 103,
                'name' => 'London Area',
                'description' => 'Contacts with London postcodes',
                'type' => 'dynamic',
                'rules' => [
                    ['field' => 'postcode', 'operator' => 'starts_with', 'value' => 'L']
                ],
                'contact_count' => 421,
                'created_at' => '2024-09-15',
                'last_evaluated' => '2024-12-22',
            ],
        ];

        // TODO: Fetch contacts from database for adding to lists
        $available_contacts = [
            ['id' => 1, 'name' => 'Emma Thompson', 'mobile' => '+44 7700 900123'],
            ['id' => 2, 'name' => 'James Wilson', 'mobile' => '+44 7700 900456'],
            ['id' => 3, 'name' => 'Sarah Mitchell', 'mobile' => '+44 7700 900789'],
            ['id' => 4, 'name' => 'Michael Brown', 'mobile' => '+44 7700 900321'],
            ['id' => 5, 'name' => 'Lisa Anderson', 'mobile' => '+44 7700 900654'],
        ];

        $available_tags = ['VIP', 'Newsletter', 'Customer', 'Partner'];

        return view('quicksms.contacts.lists', [
            'page_title' => 'Lists',
            'static_lists' => $static_lists,
            'dynamic_lists' => $dynamic_lists,
            'available_contacts' => $available_contacts,
            'available_tags' => $available_tags,
        ]);
    }

    public function tags()
    {
        // TODO: Fetch tags from database via API
        $tags = [
            [
                'id' => 1,
                'name' => 'VIP',
                'color' => '#6f42c1',
                'contact_count' => 156,
                'created_at' => '2024-06-15',
                'last_used' => '2024-12-22',
                'source' => 'manual',
            ],
            [
                'id' => 2,
                'name' => 'Newsletter',
                'color' => '#0d6efd',
                'contact_count' => 1847,
                'created_at' => '2024-05-20',
                'last_used' => '2024-12-21',
                'source' => 'manual',
            ],
            [
                'id' => 3,
                'name' => 'Customer',
                'color' => '#198754',
                'contact_count' => 2341,
                'created_at' => '2024-04-10',
                'last_used' => '2024-12-22',
                'source' => 'api',
            ],
            [
                'id' => 4,
                'name' => 'Partner',
                'color' => '#fd7e14',
                'contact_count' => 89,
                'created_at' => '2024-07-22',
                'last_used' => '2024-12-18',
                'source' => 'manual',
            ],
            [
                'id' => 5,
                'name' => 'Flu Clinic 2025',
                'color' => '#dc3545',
                'contact_count' => 423,
                'created_at' => '2024-11-01',
                'last_used' => '2024-12-20',
                'source' => 'campaign',
            ],
            [
                'id' => 6,
                'name' => 'Black Friday 2024',
                'color' => '#212529',
                'contact_count' => 1256,
                'created_at' => '2024-11-15',
                'last_used' => '2024-11-29',
                'source' => 'campaign',
            ],
            [
                'id' => 7,
                'name' => 'Responded',
                'color' => '#20c997',
                'contact_count' => 567,
                'created_at' => '2024-08-05',
                'last_used' => '2024-12-22',
                'source' => 'api',
            ],
            [
                'id' => 8,
                'name' => 'Inactive',
                'color' => '#6c757d',
                'contact_count' => 234,
                'created_at' => '2024-09-12',
                'last_used' => '2024-12-15',
                'source' => 'api',
            ],
        ];

        // Available colors for tag creation
        $available_colors = [
            '#6f42c1' => 'Purple',
            '#0d6efd' => 'Blue',
            '#198754' => 'Green',
            '#fd7e14' => 'Orange',
            '#dc3545' => 'Red',
            '#212529' => 'Black',
            '#20c997' => 'Teal',
            '#6c757d' => 'Gray',
            '#0dcaf0' => 'Cyan',
            '#d63384' => 'Pink',
        ];

        return view('quicksms.contacts.tags', [
            'page_title' => 'Tags',
            'tags' => $tags,
            'available_colors' => $available_colors,
        ]);
    }

    public function optOutLists()
    {
        // TODO: Replace with database query - GET /api/opt-out-lists
        $opt_out_lists = [
            [
                'id' => 1,
                'name' => 'Master Opt-Out List',
                'description' => 'Global suppression list for all campaigns',
                'is_master' => true,
                'count' => 2847,
                'created_at' => '2024-01-15',
                'updated_at' => '2024-12-21',
            ],
            [
                'id' => 2,
                'name' => 'Marketing Opt-Outs',
                'description' => 'Contacts who opted out of marketing messages',
                'is_master' => false,
                'count' => 1245,
                'created_at' => '2024-03-10',
                'updated_at' => '2024-12-20',
            ],
            [
                'id' => 3,
                'name' => 'Promotions Opt-Outs',
                'description' => 'Contacts who opted out of promotional offers',
                'is_master' => false,
                'count' => 892,
                'created_at' => '2024-05-22',
                'updated_at' => '2024-12-18',
            ],
            [
                'id' => 4,
                'name' => 'Newsletter Opt-Outs',
                'description' => 'Unsubscribed from newsletter communications',
                'is_master' => false,
                'count' => 456,
                'created_at' => '2024-07-08',
                'updated_at' => '2024-12-15',
            ],
        ];

        // TODO: Replace with database query - GET /api/opt-outs
        $opt_outs = [
            [
                'id' => 1,
                'mobile' => '+447700900123',
                'source' => 'sms_reply',
                'timestamp' => '2024-12-21 14:32:15',
                'campaign_ref' => 'XMAS2024',
                'list_id' => 1,
                'list_name' => 'Master Opt-Out List',
            ],
            [
                'id' => 2,
                'mobile' => '+447700900456',
                'source' => 'url_click',
                'timestamp' => '2024-12-20 09:15:42',
                'campaign_ref' => 'WINTER_SALE',
                'list_id' => 2,
                'list_name' => 'Marketing Opt-Outs',
            ],
            [
                'id' => 3,
                'mobile' => '+447700900789',
                'source' => 'api',
                'timestamp' => '2024-12-19 16:45:00',
                'campaign_ref' => null,
                'list_id' => 1,
                'list_name' => 'Master Opt-Out List',
            ],
            [
                'id' => 4,
                'mobile' => '+447700900321',
                'source' => 'manual',
                'timestamp' => '2024-12-18 11:20:33',
                'campaign_ref' => 'BLACK_FRIDAY',
                'list_id' => 3,
                'list_name' => 'Promotions Opt-Outs',
            ],
            [
                'id' => 5,
                'mobile' => '+447700900654',
                'source' => 'sms_reply',
                'timestamp' => '2024-12-17 08:55:12',
                'campaign_ref' => 'WEEKLY_UPDATE',
                'list_id' => 4,
                'list_name' => 'Newsletter Opt-Outs',
            ],
            [
                'id' => 6,
                'mobile' => '+447700900987',
                'source' => 'url_click',
                'timestamp' => '2024-12-16 13:10:45',
                'campaign_ref' => 'LOYALTY_PROG',
                'list_id' => 2,
                'list_name' => 'Marketing Opt-Outs',
            ],
            [
                'id' => 7,
                'mobile' => '+447700900111',
                'source' => 'api',
                'timestamp' => '2024-12-15 17:30:00',
                'campaign_ref' => 'CRM_SYNC',
                'list_id' => 1,
                'list_name' => 'Master Opt-Out List',
            ],
            [
                'id' => 8,
                'mobile' => '+447700900222',
                'source' => 'manual',
                'timestamp' => '2024-12-14 10:05:22',
                'campaign_ref' => null,
                'list_id' => 1,
                'list_name' => 'Master Opt-Out List',
            ],
        ];

        return view('quicksms.contacts.opt-out-lists', [
            'page_title' => 'Opt-Out Lists',
            'opt_out_lists' => $opt_out_lists,
            'opt_outs' => $opt_outs,
            'total_opt_outs' => 2847,
        ]);
    }

    public function reporting()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Reporting',
            'purpose' => 'Access comprehensive reports and analytics for your messaging activities.',
            'sub_modules' => [
                'Dashboard',
                'Message Log',
                'Finance Data',
                'Invoices',
                'Download Area'
            ]
        ]);
    }

    public function reportingDashboard()
    {
        return view('quicksms.reporting.dashboard', [
            'page_title' => 'Reporting Dashboard'
        ]);
    }

    public function messageLog()
    {
        // TODO: Replace with database query - GET /api/messages?page=X&limit=Y&filters=Z
        return view('quicksms.reporting.message-log', [
            'page_title' => 'Message Log'
        ]);
    }

    public function financeData()
    {
        return view('quicksms.reporting.finance-data', [
            'page_title' => 'Finance Data'
        ]);
    }

    public function invoices()
    {
        return view('quicksms.reporting.invoices', [
            'page_title' => 'Invoices'
        ]);
    }

    public function downloadArea()
    {
        return view('quicksms.reporting.download-area', [
            'page_title' => 'Download Area'
        ]);
    }

    public function purchase()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Purchase',
            'purpose' => 'Purchase message credits, packages, and additional services.',
            'sub_modules' => []
        ]);
    }

    public function purchaseMessages()
    {
        return view('quicksms.purchase.messages', [
            'page_title' => 'Purchase Messages'
        ]);
    }

    public function purchaseNumbers()
    {
        return view('quicksms.purchase.numbers', [
            'page_title' => 'Purchase Numbers'
        ]);
    }

    public function management()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Management',
            'purpose' => 'Configure and manage your messaging infrastructure and integrations.',
            'sub_modules' => [
                'RCS Agent Registrations',
                'SMS SenderID Registration',
                'Templates',
                'API Connections',
                'Email-to-SMS',
                'Numbers'
            ]
        ]);
    }

    public function rcsAgentRegistrations()
    {
        // TODO: Replace with Auth::id() when authentication is integrated
        $currentUserId = auth()->id() ?? 1;
        
        return view('quicksms.management.rcs-agent', [
            'page_title' => 'RCS Agent Library',
            'currentUserId' => $currentUserId
        ]);
    }

    public function rcsAgentCreate()
    {
        return view('quicksms.management.rcs-agent-wizard', [
            'page_title' => 'Register RCS Agent'
        ]);
    }

    public function smsSenderIdRegistration()
    {
        return view('quicksms.management.sms-sender-id', [
            'page_title' => 'SMS SenderID Registration'
        ]);
    }

    public function smsSenderIdRegister()
    {
        return view('quicksms.management.sms-sender-id-wizard', [
            'page_title' => 'Register SenderID'
        ]);
    }

    public function templates()
    {
        // TODO: Replace with database query - GET /api/sender-ids
        $sender_ids = [
            ['id' => 1, 'name' => 'QuickSMS', 'type' => 'alphanumeric'],
            ['id' => 2, 'name' => 'ALERTS', 'type' => 'alphanumeric'],
            ['id' => 3, 'name' => '+447700900100', 'type' => 'numeric'],
        ];

        // TODO: Replace with database query - GET /api/rcs-agents?status=approved
        // Only approved RCS agents are selectable in Template wizard
        // Status lifecycle: Draft -> Submitted -> In Review -> Approved/Rejected
        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => asset('images/rcs-agents/quicksms-brand.svg'), 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => asset('images/rcs-agents/promotions-agent.svg'), 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with database query - GET /api/opt-out-lists
        $opt_out_lists = [
            ['id' => 1, 'name' => 'Master Opt-Out List', 'count' => 2847, 'is_default' => true],
            ['id' => 2, 'name' => 'Marketing Opt-Outs', 'count' => 1245, 'is_default' => false],
            ['id' => 3, 'name' => 'Promotions Opt-Outs', 'count' => 892, 'is_default' => false],
        ];

        // TODO: Replace with database query - GET /api/virtual-numbers
        $virtual_numbers = [
            ['id' => 1, 'number' => '+447700900100', 'label' => 'Main Number'],
            ['id' => 2, 'number' => '+447700900200', 'label' => 'Marketing'],
        ];

        // TODO: Replace with database query - GET /api/optout-domains
        $optout_domains = [
            ['id' => 1, 'domain' => 'qsms.uk', 'is_default' => true],
            ['id' => 2, 'domain' => 'optout.quicksms.com', 'is_default' => false],
        ];

        return view('quicksms.management.templates', [
            'page_title' => 'Message Templates',
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'opt_out_lists' => $opt_out_lists,
            'virtual_numbers' => $virtual_numbers,
            'optout_domains' => $optout_domains
        ]);
    }

    public function apiConnections()
    {
        return view('quicksms.management.api-connections', [
            'page_title' => 'API Connections'
        ]);
    }

    public function apiConnectionCreate()
    {
        return view('quicksms.management.api-connection-wizard', [
            'page_title' => 'Create API Connection'
        ]);
    }

    public function emailToSms()
    {
        return view('quicksms.management.email-to-sms', [
            'page_title' => 'Email-to-SMS'
        ]);
    }

    public function emailToSmsCreateMapping()
    {
        return view('quicksms.management.email-to-sms-mapping-wizard', [
            'page_title' => 'Create Email-to-SMS Mapping'
        ]);
    }

    public function emailToSmsStandardCreate()
    {
        return view('quicksms.management.email-to-sms-standard-wizard', [
            'page_title' => 'Create Standard Email-to-SMS'
        ]);
    }

    public function emailToSmsStandardEdit($id)
    {
        return view('quicksms.management.standard-email-to-sms-form', [
            'page_title' => 'Edit Standard Email-to-SMS',
            'id' => $id
        ]);
    }

    public function numbers()
    {
        return view('quicksms.management.numbers', [
            'page_title' => 'Numbers'
        ]);
    }

    public function numbersConfigure(Request $request)
    {
        // Get selected number IDs from query string
        $selectedIds = $request->query('ids', '');
        
        return view('quicksms.management.numbers-configure', [
            'page_title' => 'Configure Numbers',
            'selectedIds' => $selectedIds
        ]);
    }

    public function account()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Account',
            'purpose' => 'Manage your account settings, users, and security preferences.',
            'sub_modules' => [
                'Details',
                'Users and Access',
                'Sub Accounts',
                'Audit Logs',
                'Security Settings'
            ]
        ]);
    }

    public function accountDetails()
    {
        return view('quicksms.account.details', [
            'page_title' => 'Account Details'
        ]);
    }
    
    public function accountActivate()
    {
        return view('quicksms.account.activate', [
            'page_title' => 'Activate Your Account'
        ]);
    }

    public function usersAndAccess()
    {
        return view('quicksms.account.users-access', [
            'page_title' => 'Users and Access'
        ]);
    }

    public function subAccounts()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Sub Accounts',
            'purpose' => 'Create and manage sub-accounts for organizational units.',
            'sub_modules' => []
        ]);
    }
    
    public function subAccountDetail($id)
    {
        $subAccounts = [
            'sub-001' => [
                'id' => 'sub-001',
                'name' => 'Marketing Department',
                'status' => 'live',
                'created_at' => '2024-06-15',
                'user_count' => 8,
                'monthly_spend' => 1250.00,
                'monthly_messages' => 42500,
                'limits' => [
                    'spend_cap' => 5000.00,
                    'message_cap' => 100000,
                    'daily_limit' => 5000,
                    'enforcement_type' => 'block',
                    'hard_stop' => false
                ]
            ],
            'sub-002' => [
                'id' => 'sub-002',
                'name' => 'Customer Support',
                'status' => 'live',
                'created_at' => '2024-08-22',
                'user_count' => 5,
                'monthly_spend' => 875.50,
                'monthly_messages' => 28000,
                'limits' => [
                    'spend_cap' => 2000.00,
                    'message_cap' => 50000,
                    'daily_limit' => 2000,
                    'enforcement_type' => 'warn',
                    'hard_stop' => false
                ]
            ],
            'sub-003' => [
                'id' => 'sub-003',
                'name' => 'Sales Team',
                'status' => 'suspended',
                'created_at' => '2024-09-10',
                'user_count' => 3,
                'monthly_spend' => 0,
                'monthly_messages' => 0,
                'limits' => [
                    'spend_cap' => 1000.00,
                    'message_cap' => 25000,
                    'daily_limit' => 1000,
                    'enforcement_type' => 'approval',
                    'hard_stop' => true
                ]
            ]
        ];
        
        $subAccount = $subAccounts[$id] ?? null;
        
        if (!$subAccount) {
            abort(404, 'Sub-Account not found');
        }
        
        return view('quicksms.account.sub-account-detail', [
            'page_title' => $subAccount['name'],
            'sub_account' => $subAccount
        ]);
    }

    public function auditLogs()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Audit Logs',
            'purpose' => 'Review account activity and security events.',
            'sub_modules' => []
        ]);
    }

    public function securitySettings()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Security Settings',
            'purpose' => 'Configure security options including 2FA and session management.',
            'sub_modules' => []
        ]);
    }

    public function support()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Support',
            'purpose' => 'Access support resources, submit tickets, and browse documentation.',
            'sub_modules' => [
                'Dashboard',
                'Create a Ticket',
                'Knowledge Base'
            ]
        ]);
    }

    public function supportDashboard()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Support Dashboard',
            'purpose' => 'Overview of support tickets and help resources.',
            'sub_modules' => []
        ]);
    }

    public function createTicket()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Create a Ticket',
            'purpose' => 'Submit a new support request or issue.',
            'sub_modules' => []
        ]);
    }

    public function knowledgeBase()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Knowledge Base',
            'purpose' => 'Browse help articles, guides, and FAQs.',
            'sub_modules' => []
        ]);
    }
    
    public function knowledgeBaseTestMode()
    {
        return view('quicksms.support.knowledge-base-test-mode', [
            'page_title' => 'Understanding Test Mode'
        ]);
    }

    public function rcsPreviewDemo()
    {
        return view('rcs.preview', [
            'page_title' => 'RCS Preview Demo'
        ]);
    }

    /**
     * API: Get numbers pricing from HubSpot
     * Returns setup and monthly fees for VMNs and keywords
     */
    public function getNumbersPricing(Request $request)
    {
        $currency = $request->query('currency', 'GBP');
        
        $hubspotService = new \App\Services\HubSpotProductService();
        $pricing = $hubspotService->fetchNumbersPricing($currency);
        
        return response()->json($pricing);
    }

    /**
     * API: Lock numbers/keywords for purchase
     * Prevents race conditions during checkout
     */
    public function lockNumbersForPurchase(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:vmn,keyword',
            'items.*.identifier' => 'required|string',
            'purchase_type' => 'required|in:vmn,keyword',
        ]);

        $purchaseService = new \App\Services\NumberPurchaseService();
        
        try {
            $sessionId = \Illuminate\Support\Str::uuid()->toString();
            $userId = 1;

            $result = $purchaseService->acquireLocks(
                $request->input('items'),
                $sessionId,
                $userId
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * API: Process numbers/keywords purchase
     * Atomic transaction with audit logging
     */
    public function processNumbersPurchase(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'sub_account_id' => 'required|string',
            'sub_account_name' => 'nullable|string',
            'purchase_type' => 'required|in:vmn,keyword',
            'items' => 'required|array|min:1',
            'items.*.identifier' => 'required|string',
        ]);

        $purchaseService = new \App\Services\NumberPurchaseService();

        try {
            $result = $purchaseService->processPurchase([
                'session_id' => $request->input('session_id'),
                'user_id' => 1,
                'user_email' => 'demo@quicksms.com',
                'user_name' => 'Demo User',
                'sub_account_id' => $request->input('sub_account_id'),
                'sub_account_name' => $request->input('sub_account_name'),
                'purchase_type' => $request->input('purchase_type'),
                'items' => $request->input('items'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * API: Release locked numbers/keywords
     * Called when user cancels or times out
     */
    public function releaseNumberLocks(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $purchaseService = new \App\Services\NumberPurchaseService();
        
        try {
            $purchaseService->releaseLocks($request->input('session_id'));
            
            return response()->json([
                'success' => true,
                'message' => 'Locks released successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
