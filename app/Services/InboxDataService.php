<?php

namespace App\Services;

/**
 * Provides inbox conversation data.
 * Currently returns mock data — swap internals for real DB/API calls
 * without changing the public interface.
 */
class InboxDataService
{
    public function getConversations(): array
    {
        return [
            $this->conv('conv_001', '+447700900111', 'Sarah Mitchell', 'SM', 'c_001', 'sms', '60777', 'shortcode', 'sid_4', true, 2, 'When will my order arrive?', '10:32 AM', strtotime('today 10:32'), '15 Dec 2024', [
                ['direction' => 'outbound', 'content' => 'Hi Sarah, your order #12345 has been dispatched!', 'time' => '09:15 AM', 'date' => '22 Dec 2024'],
                ['direction' => 'inbound',  'content' => 'Thanks! How long will delivery take?', 'time' => '09:45 AM', 'date' => '22 Dec 2024'],
                ['direction' => 'outbound', 'content' => "Usually 2-3 business days. You'll receive tracking soon.", 'time' => '09:48 AM', 'date' => '22 Dec 2024'],
                ['direction' => 'inbound',  'content' => 'Great, thank you!', 'time' => '10:02 AM', 'date' => '23 Dec 2024'],
                ['direction' => 'inbound',  'content' => 'When will my order arrive?', 'time' => '10:32 AM', 'date' => '23 Dec 2024'],
            ]),
            $this->conv('conv_002', '+447700900222', 'James Wilson', 'JW', 'c_002', 'rcs', 'QuickSMS Brand', 'rcs_agent', null, true, 1, 'Can I change my delivery address?', '09:15 AM', strtotime('today 09:15'), '10 Dec 2024', [
                ['direction' => 'inbound',  'content' => 'Hi, I placed an order yesterday', 'time' => '08:30 AM'],
                ['direction' => 'outbound', 'content' => 'Hello James! How can we help you today?', 'time' => '08:45 AM'],
                ['direction' => 'inbound',  'content' => 'Can I change my delivery address?', 'time' => '09:15 AM'],
            ], 'agent_1'),
            $this->conv('conv_003', '+447700900333', '+44 7700 900333', '??', null, 'sms', '+447700900100', 'vmn', 'sid_3', false, 0, 'STOP', 'Yesterday', strtotime('yesterday 10:15'), '20 Dec 2024', [
                ['direction' => 'outbound', 'content' => 'Flash sale! 50% off everything today only. Shop now at example.com', 'time' => '10:00 AM'],
                ['direction' => 'inbound',  'content' => 'STOP', 'time' => '10:15 AM'],
            ]),
            $this->conv('conv_004', '+447700900444', 'Emma Thompson', 'ET', 'c_003', 'rcs', 'RetailBot', 'rcs_agent', null, false, 0, 'Perfect, see you then!', 'Yesterday', strtotime('yesterday 15:48'), '01 Nov 2024', [
                ['direction' => 'outbound', 'content' => 'Reminder: Your appointment is tomorrow at 2pm', 'time' => '3:00 PM'],
                ['direction' => 'inbound',  'content' => 'Thanks for the reminder!', 'time' => '3:30 PM'],
                ['direction' => 'inbound',  'content' => 'Can I reschedule to 3pm instead?', 'time' => '3:32 PM'],
                ['direction' => 'outbound', 'content' => 'Of course! Your appointment has been moved to 3pm.', 'time' => '3:45 PM'],
                ['direction' => 'inbound',  'content' => 'Perfect, see you then!', 'time' => '3:48 PM'],
            ], 'agent_2'),
            $this->conv('conv_005', '+447700900555', 'Michael Brown', 'MB', 'c_004', 'sms', '60777', 'shortcode', 'sid_4', false, 0, 'Your refund has been processed.', '2 days ago', strtotime('-2 days 16:30'), '05 Dec 2024', [
                ['direction' => 'inbound',  'content' => 'I need to return my order', 'time' => '11:00 AM'],
                ['direction' => 'outbound', 'content' => "Sorry to hear that. What's the issue?", 'time' => '11:15 AM'],
                ['direction' => 'inbound',  'content' => 'Wrong size', 'time' => '11:20 AM'],
                ['direction' => 'outbound', 'content' => 'No problem. Please return to our store or use the prepaid label in your package.', 'time' => '11:25 AM'],
                ['direction' => 'inbound',  'content' => 'Done, dropped it off today', 'time' => '2:00 PM'],
                ['direction' => 'outbound', 'content' => 'Your refund has been processed.', 'time' => '4:30 PM'],
            ]),
            $this->conv('conv_006', '+447700900666', 'Sophie Brown', 'SB', 'c_005', 'rcs', 'QuickSMS Brand', 'rcs_agent', null, false, 0, 'Can I reschedule to next week?', '15 Dec', strtotime('-14 days 11:00'), '01 Dec 2024', [
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
            ], 'agent_1'),
            $this->conv('conv_007', '+447700900777', 'Alice Henderson', 'AH', 'c_006', 'sms', '60777', 'shortcode', null, true, 3, 'Is my package still on the way?', '3 days ago', strtotime('-3 days 11:45'), '18 Dec 2024', [
                ['direction' => 'inbound', 'content' => 'Is my package still on the way?', 'time' => '11:45 AM', 'date' => '3 days ago'],
            ]),
            $this->conv('conv_008', '+447700900888', 'Benjamin Clarke', 'BC', 'c_007', 'rcs', 'RetailBot', 'rcs_agent', null, false, 0, 'Thanks for the quick response!', '3 days ago', strtotime('-3 days 14:20'), '10 Dec 2024', [
                ['direction' => 'inbound', 'content' => 'Thanks for the quick response!', 'time' => '2:20 PM'],
            ], 'agent_2'),
            $this->conv('conv_009', '+447700900999', 'Charlotte Davies', 'CD', 'c_008', 'sms', '+447700900100', 'vmn', null, true, 1, 'Can you call me back please?', '4 days ago', strtotime('-4 days 08:22'), '22 Dec 2024', [
                ['direction' => 'inbound', 'content' => 'Can you call me back please?', 'time' => '8:22 AM', 'date' => '4 days ago'],
            ]),
            $this->conv('conv_010', '+447700901010', 'Daniel Evans', 'DE', 'c_009', 'rcs', 'QuickSMS Brand', 'rcs_agent', null, false, 0, 'Order received, thank you!', '4 days ago', strtotime('-4 days 09:30'), '05 Dec 2024', [
                ['direction' => 'inbound', 'content' => 'Order received, thank you!', 'time' => '9:30 AM'],
            ], 'agent_1'),
            $this->conv('conv_011', '+447700901111', 'Eleanor Foster', 'EF', 'c_010', 'sms', '60777', 'shortcode', null, false, 0, "Perfect, I'll be there at 10am", '5 days ago', strtotime('-5 days 16:45'), '01 Dec 2024', [
                ['direction' => 'inbound', 'content' => "Perfect, I'll be there at 10am", 'time' => '4:45 PM'],
            ]),
            $this->conv('conv_012', '+447700901212', 'Frederick Grant', 'FG', 'c_011', 'rcs', 'RetailBot', 'rcs_agent', null, true, 2, 'Where is my refund?', '5 days ago', strtotime('-5 days 07:55'), '15 Dec 2024', [
                ['direction' => 'inbound', 'content' => 'Where is my refund?', 'time' => '7:55 AM'],
            ], 'agent_2'),
            $this->conv('conv_013', '+447700901313', 'Georgia Harris', 'GH', 'c_012', 'sms', '+447700900100', 'vmn', null, false, 0, 'Appointment confirmed for Friday', '1 week ago', strtotime('-7 days 11:00'), '28 Nov 2024', [
                ['direction' => 'outbound', 'content' => 'Appointment confirmed for Friday', 'time' => '11:00 AM'],
            ]),
            $this->conv('conv_014', '+447700901414', 'Henry Irving', 'HI', 'c_013', 'rcs', 'QuickSMS Brand', 'rcs_agent', null, false, 0, 'Got it, thanks!', '6 days ago', strtotime('-6 days 13:15'), '20 Nov 2024', [
                ['direction' => 'inbound', 'content' => 'Got it, thanks!', 'time' => '1:15 PM'],
            ], 'agent_1'),
            $this->conv('conv_015', '+447700901515', 'Isabelle Jones', 'IJ', 'c_014', 'sms', '60777', 'shortcode', null, true, 1, 'Do you have this in blue?', '12:10 PM', strtotime('today 12:10'), '19 Dec 2024', [
                ['direction' => 'inbound', 'content' => 'Do you have this in blue?', 'time' => '12:10 PM'],
            ]),
        ];
    }

    public function findConversation(string $id): ?array
    {
        foreach ($this->getConversations() as $conv) {
            if ($conv['id'] === $id) {
                return $conv;
            }
        }
        return null;
    }

    public function getUnreadCount(): int
    {
        return collect($this->getConversations())
            ->where('unread', true)
            ->sum('unread_count');
    }

    /**
     * Build a normalised conversation array.
     */
    private function conv(
        string $id, string $phone, string $name, string $initials,
        ?string $contactId, string $channel, string $source, string $sourceType,
        ?string $senderId, bool $unread, int $unreadCount,
        string $lastMessage, string $lastMessageTime, int $timestamp,
        string $firstContact, array $messages, ?string $rcsAgentId = null
    ): array {
        $masked = preg_replace('/(\+\d{2})\d{4}(\d{3})/', '$1 **** ***$2', $phone);
        $data = [
            'id' => $id,
            'phone' => $phone,
            'phone_masked' => $masked,
            'name' => $name,
            'initials' => $initials,
            'contact_id' => $contactId,
            'channel' => $channel,
            'source' => $source,
            'source_type' => $sourceType,
            'sender_id' => $senderId,
            'unread' => $unread,
            'unread_count' => $unreadCount,
            'last_message' => $lastMessage,
            'last_message_time' => $lastMessageTime,
            'timestamp' => $timestamp,
            'first_contact' => $firstContact,
            'messages' => $messages,
        ];
        if ($rcsAgentId) {
            $data['rcs_agent_id'] = $rcsAgentId;
        }

        // Calculate awaiting_reply_48h flag
        $lastInbound = null;
        $lastOutbound = null;
        foreach (array_reverse($messages) as $m) {
            if ($m['direction'] === 'inbound' && !$lastInbound) {
                $lastInbound = $m;
            }
            if ($m['direction'] === 'outbound' && !$lastOutbound) {
                $lastOutbound = $m;
            }
            if ($lastInbound && $lastOutbound) break;
        }
        $data['awaiting_reply_48h'] = false;
        if ($lastInbound && (!$lastOutbound || array_search($lastInbound, $messages) > array_search($lastOutbound, $messages))) {
            $data['awaiting_reply_48h'] = ($timestamp < strtotime('-48 hours'));
        }

        return $data;
    }
}
