<?php

namespace App\Services;

use App\Mail\GuardianCommunicationMail;
use App\Models\AssignedTeacher;
use App\Models\Communication;
use App\Models\CommunicationRecipient;
use App\Models\InboundEmail;
use App\Models\Promotion;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Stevebauman\Purify\Facades\Purify;

class CommunicationService
{
    protected BulkSMSService $bulkSMSService;

    public function __construct(BulkSMSService $bulkSMSService)
    {
        $this->bulkSMSService = $bulkSMSService;
    }

    public function getTeacherStudents(int $teacherId, int $sessionId): Collection
    {
        $assignments = AssignedTeacher::query()
            ->where('teacher_id', $teacherId)
            ->where('session_id', $sessionId)
            ->sectionLeadership()
            ->get(['class_id', 'section_id']);

        if ($assignments->isEmpty()) {
            return collect();
        }

        return $this->basePromotionQuery($sessionId)
            ->where(function (Builder $query) use ($assignments) {
                foreach ($assignments as $assignment) {
                    $query->orWhere(function (Builder $inner) use ($assignment) {
                        $inner->where('class_id', $assignment->class_id);

                        if ($assignment->section_id) {
                            $inner->where('section_id', $assignment->section_id);
                        }
                    });
                }
            })
            ->orderBy('class_id')
            ->orderBy('section_id')
            ->get()
            ->unique('student_id')
            ->values();
    }

    public function buildPreview(array $data, User $sender): array
    {
        $channel = $data['channel'];
        $scope = $data['scope'];
        $messageText = $this->normalizeMessageText($data['message']);
        $messageHtml = $channel === 'email' ? Purify::clean($data['message']) : null;
        $subject = $channel === 'email' ? trim((string) ($data['subject'] ?? '')) : null;
        $sessionId = (int) $data['session_id'];

        $promotions = $scope === 'bulk'
            ? $this->getBulkPromotions($sessionId, $data['class_id'] ?? null, $data['section_id'] ?? null)
            : $this->getTeacherStudents($sender->id, $sessionId)->where('student_id', (int) $data['student_id'])->values();

        $qualifiedRecipients = [];
        $invalidRecipients = [];

        foreach ($promotions as $promotion) {
            $resolved = $this->resolvePromotionRecipient($promotion, $channel);

            if ($resolved['valid']) {
                $qualifiedRecipients[] = $resolved;
                continue;
            }

            $invalidRecipients[] = $resolved;
        }

        return [
            'channel' => $channel,
            'scope' => $scope,
            'subject' => $subject,
            'message_html' => $messageHtml,
            'message_text' => $messageText,
            'session_id' => $sessionId,
            'class_id' => $data['class_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
            'student_id' => $data['student_id'] ?? null,
            'qualified_recipients' => $qualifiedRecipients,
            'invalid_recipients' => $invalidRecipients,
            'qualified_count' => count($qualifiedRecipients),
            'invalid_count' => count($invalidRecipients),
        ];
    }

    public function send(array $preview, User $sender): Communication
    {
        $communication = Communication::create([
            'channel' => $preview['channel'],
            'audience_type' => $preview['scope'],
            'session_id' => $preview['session_id'],
            'class_id' => $preview['class_id'],
            'section_id' => $preview['section_id'],
            'created_by' => $sender->id,
            'sender_role' => $sender->role,
            'subject' => $preview['subject'],
            'message' => $preview['message_text'],
            'message_html' => $preview['message_html'],
            'status' => 'processing',
            'total_recipients' => $preview['qualified_count'],
            'successful_recipients' => 0,
            'failed_recipients' => 0,
            'metadata' => [
                'invalid_count' => $preview['invalid_count'],
                'student_id' => $preview['student_id'],
            ],
        ]);

        $successful = 0;
        $failed = 0;

        foreach ($preview['qualified_recipients'] as $recipient) {
            $history = CommunicationRecipient::create([
                'communication_id' => $communication->id,
                'student_id' => $recipient['student_id'],
                'channel' => $preview['channel'],
                'recipient_name' => $recipient['student_name'],
                'destination' => $recipient['destination'],
                'status' => 'pending',
            ]);

            try {
                if ($preview['channel'] === 'email') {
                    Mail::to($recipient['destination'])->send(new GuardianCommunicationMail(
                        $preview['subject'],
                        $preview['message_html'] ?? nl2br(e($preview['message_text'])),
                        $recipient['student_name'],
                        $sender->first_name . ' ' . $sender->last_name,
                        SiteSetting::query()->value('school_name') ?? config('app.name')
                    ));

                    $providerResponse = [];
                    $providerMessageId = null;
                } else {
                    $providerResponse = $this->bulkSMSService->sendSMS($recipient['destination'], $preview['message_text']);
                    $providerMessageId = data_get($providerResponse, 'data.id');
                }

                $history->update([
                    'status' => 'sent',
                    'sent_at' => Carbon::now(),
                    'provider_message_id' => $providerMessageId,
                    'provider_response' => $providerResponse,
                ]);
                $successful++;
            } catch (\Throwable $e) {
                $history->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $communication->update([
            'successful_recipients' => $successful,
            'failed_recipients' => $failed,
            'status' => $failed === 0 ? 'completed' : ($successful === 0 ? 'failed' : 'partial'),
        ]);

        return $communication->fresh([
            'creator',
            'session',
            'schoolClass',
            'section',
            'recipients.student.parent_info',
        ]);
    }

    public function sendReply(Communication $communication, CommunicationRecipient $recipient, array $data, User $sender): Communication
    {
        $destination = trim((string) $recipient->destination);

        if (!filter_var($destination, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Recipient email format is invalid.');
        }

        $messageHtml = Purify::clean($data['message']);
        $messageText = $this->normalizeMessageText($data['message']);

        $replyCommunication = Communication::create([
            'channel' => 'email',
            'audience_type' => 'reply',
            'session_id' => $communication->session_id,
            'class_id' => $communication->class_id,
            'section_id' => $communication->section_id,
            'created_by' => $sender->id,
            'sender_role' => $sender->role,
            'subject' => $data['subject'],
            'message' => $messageText,
            'message_html' => $messageHtml,
            'status' => 'processing',
            'total_recipients' => 1,
            'successful_recipients' => 0,
            'failed_recipients' => 0,
            'metadata' => [
                'reply_to_communication_id' => $communication->id,
                'reply_to_recipient_id' => $recipient->id,
            ],
        ]);

        $history = CommunicationRecipient::create([
            'communication_id' => $replyCommunication->id,
            'student_id' => $recipient->student_id,
            'channel' => 'email',
            'recipient_name' => $recipient->recipient_name,
            'destination' => $destination,
            'status' => 'pending',
        ]);

        try {
            Mail::to($destination)->send(new GuardianCommunicationMail(
                $data['subject'],
                $messageHtml,
                $recipient->recipient_name ?: 'Student',
                $sender->first_name . ' ' . $sender->last_name,
                SiteSetting::query()->value('school_name') ?? config('app.name')
            ));

            $history->update([
                'status' => 'sent',
                'sent_at' => Carbon::now(),
                'provider_response' => [],
            ]);

            $replyCommunication->update([
                'successful_recipients' => 1,
                'status' => 'completed',
            ]);
        } catch (\Throwable $e) {
            $history->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $replyCommunication->update([
                'failed_recipients' => 1,
                'status' => 'failed',
            ]);
        }

        return $replyCommunication->fresh([
            'creator',
            'session',
            'schoolClass',
            'section',
            'recipients.student.parent_info',
        ]);
    }

    public function sendInboundReply(InboundEmail $inboundEmail, array $data, User $sender): Communication
    {
        $destination = trim((string) $inboundEmail->from_email);

        if (!filter_var($destination, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Inbound sender email is invalid.');
        }

        $messageHtml = Purify::clean($data['message']);
        $messageText = $this->normalizeMessageText($data['message']);

        $communication = Communication::create([
            'channel' => 'email',
            'audience_type' => 'inbox_reply',
            'created_by' => $sender->id,
            'sender_role' => $sender->role,
            'subject' => $data['subject'],
            'message' => $messageText,
            'message_html' => $messageHtml,
            'status' => 'processing',
            'total_recipients' => 1,
            'successful_recipients' => 0,
            'failed_recipients' => 0,
            'metadata' => [
                'inbound_email_id' => $inboundEmail->id,
                'reply_target' => $destination,
            ],
        ]);

        $history = CommunicationRecipient::create([
            'communication_id' => $communication->id,
            'channel' => 'email',
            'recipient_name' => $inboundEmail->from_name ?: $destination,
            'destination' => $destination,
            'status' => 'pending',
        ]);

        try {
            Mail::to($destination)->send(new GuardianCommunicationMail(
                $data['subject'],
                $messageHtml,
                $inboundEmail->from_name ?: 'Guardian',
                $sender->first_name . ' ' . $sender->last_name,
                SiteSetting::query()->value('school_name') ?? config('app.name')
            ));

            $history->update([
                'status' => 'sent',
                'sent_at' => Carbon::now(),
                'provider_response' => [],
            ]);

            $communication->update([
                'successful_recipients' => 1,
                'status' => 'completed',
            ]);
        } catch (\Throwable $e) {
            $history->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $communication->update([
                'failed_recipients' => 1,
                'status' => 'failed',
            ]);
        }

        return $communication->fresh([
            'creator',
            'recipients',
        ]);
    }

    protected function getBulkPromotions(int $sessionId, $classId = null, $sectionId = null): Collection
    {
        return $this->basePromotionQuery($sessionId)
            ->when($classId, function (Builder $query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->when($sectionId, function (Builder $query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->orderBy('class_id')
            ->orderBy('section_id')
            ->get();
    }

    protected function basePromotionQuery(int $sessionId): Builder
    {
        return Promotion::query()
            ->with(['student.parent_info', 'schoolClass', 'section'])
            ->where('session_id', $sessionId);
    }

    protected function resolvePromotionRecipient(Promotion $promotion, string $channel): array
    {
        $student = $promotion->student;
        $parentInfo = optional($student)->parent_info;
        $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
        $className = optional($promotion->schoolClass)->class_name ?? 'N/A';
        $sectionName = optional($promotion->section)->section_name ?? 'N/A';

        if (!$student || !$parentInfo) {
            return $this->invalidRecipient($promotion, $studentName, $className, $sectionName, 'Parent/guardian details are missing.');
        }

        if ($channel === 'email') {
            $destination = trim((string) $parentInfo->guardian_email);

            if ($destination === '') {
                return $this->invalidRecipient($promotion, $studentName, $className, $sectionName, 'Guardian email is missing.');
            }

            if (!filter_var($destination, FILTER_VALIDATE_EMAIL)) {
                return $this->invalidRecipient($promotion, $studentName, $className, $sectionName, 'Guardian email format is invalid.', $destination);
            }
        } else {
            $rawPhone = trim((string) $parentInfo->guardian_phone);

            if ($rawPhone === '') {
                return $this->invalidRecipient($promotion, $studentName, $className, $sectionName, 'Guardian phone number is missing.');
            }

            $destination = $this->normalizePhoneNumber($rawPhone);

            if (!$destination) {
                return $this->invalidRecipient($promotion, $studentName, $className, $sectionName, 'Guardian phone number format is invalid.', $rawPhone);
            }
        }

        return [
            'valid' => true,
            'student_id' => $student->id,
            'student_name' => $studentName,
            'class_name' => $className,
            'section_name' => $sectionName,
            'destination' => $destination,
        ];
    }

    protected function invalidRecipient(Promotion $promotion, string $studentName, string $className, string $sectionName, string $reason, ?string $destination = null): array
    {
        return [
            'valid' => false,
            'student_id' => $promotion->student_id,
            'student_name' => $studentName ?: 'Unknown Student',
            'class_name' => $className,
            'section_name' => $sectionName,
            'destination' => $destination,
            'reason' => $reason,
        ];
    }

    protected function normalizePhoneNumber(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (!$digits) {
            return null;
        }

        if (strlen($digits) === 11 && strpos($digits, '0') === 0) {
            $digits = '234' . substr($digits, 1);
        } elseif (strlen($digits) === 13 && strpos($digits, '234') === 0) {
            $digits = $digits;
        } elseif (strlen($digits) === 10 && preg_match('/^[789]\d{9}$/', $digits)) {
            $digits = '234' . $digits;
        } else {
            return null;
        }

        return preg_match('/^234[789]\d{9}$/', $digits) ? '+' . $digits : null;
    }

    protected function normalizeMessageText(string $message): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($message)));

        return $text === '' ? trim($message) : $text;
    }
}
