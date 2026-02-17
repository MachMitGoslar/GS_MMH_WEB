<?php

/**
 * Site Hooks Configuration
 *
 * Define custom hooks for the MachMit!Haus website
 */

use Kirby\Cms\App;
use Kirby\Cms\Page;

return [
    /**
     * Auto-update parent project status when project_step is updated
     *
     * When a project step sets a new status, automatically update
     * the parent project's status to match.
     */
    'page.update:after' => function (Page $newPage, Page $oldPage) {
        if ($oldPage->intendedTemplate()->name() == 'project_step') {
            if ($newPage->project_status_to()->isNotEmpty() && ($newPage->project_status_to() != $newPage->parent()->project_status())) {
                $newPage->parent()->update([
                    "project_status" => $newPage->project_status_to(),
                ]);
            }
        }
    },

    /**
     * Auto-set publish date when content is first published
     *
     * Automatically sets the publish date for newsletters and notes
     * when they are published (listed) for the first time.
     */
    'page.changeStatus:after' => function (Page $newPage, Page $oldPage) {
        // Auto-set publish date for newsletters when published for the first time
        if ($newPage->intendedTemplate()->name() === 'newsletter') {
            // Check if page is being published (listed) and doesn't have a publish date yet
            if ($newPage->status() === 'listed' &&
                $oldPage->status() !== 'listed' &&
                $newPage->published()->isEmpty()) {
                $newPage->update([
                    'published' => date('Y-m-d'),
                ]);
            }
        }

        // Auto-set publish date for notes when published for the first time
        if ($newPage->intendedTemplate()->name() === 'notes') {
            // Check if page is being published (listed) and doesn't have a publish date yet
            if ($newPage->status() === 'listed' &&
                $oldPage->status() !== 'listed' &&
                $newPage->published()->isEmpty()) {
                $newPage->update([
                    'published' => date('Y-m-d'),
                ]);
            }
        }

        // Handle booking request status changes
        if ($newPage->intendedTemplate()->name() === 'booking-request') {
            $kirby = App::instance();
            $oldStatus = $oldPage->status();
            $newStatus = $newPage->status();

            // Only process if status actually changed
            if ($oldStatus !== $newStatus) {
                // Update processed timestamp and user
                $newPage->update([
                    'processed_at' => date('Y-m-d H:i:s'),
                    'processed_by' => '- ' . ($kirby->user() ? $kirby->user()->id() : 'system'),
                ]);

                // Get room names for email
                $roomNames = [];
                $roomIds = $newPage->requested_rooms()->toPages();
                foreach ($roomIds as $room) {
                    $roomNames[] = $room->title()->value();
                }

                $recurrenceLabels = [
                    'weekly' => 'Wöchentlich',
                    'biweekly' => 'Alle 2 Wochen',
                    'monthly' => 'Monatlich',
                ];

                $emailData = [
                    'requester_name' => $newPage->requester_name()->value(),
                    'requester_email' => $newPage->requester_email()->value(),
                    'room_names' => implode(', ', $roomNames),
                    'request_date' => $newPage->request_date()->toDate('d.m.Y'),
                    'request_time_start' => $newPage->request_time_start()->value(),
                    'request_time_end' => $newPage->request_time_end()->value(),
                    'is_recurring' => $newPage->is_recurring()->toBool(),
                    'recurrence_pattern' => $newPage->recurrence_pattern()->value(),
                    'recurrence_end_date' => $newPage->recurrence_end_date()->isNotEmpty() ? $newPage->recurrence_end_date()->toDate('d.m.Y') : '',
                    'expected_attendees' => $newPage->expected_attendees()->toInt(),
                    'admin_notes' => $newPage->admin_notes()->value(),
                    'denial_reason' => $newPage->denial_reason()->value(),
                ];

                $roomsPage = $kirby->site()->find('rooms');
                $fromEmail = $roomsPage ? $roomsPage->notification_email()->or('noreply@' . $kirby->url('host'))->value() : 'noreply@' . $kirby->url('host');

                // Send approval email and create calendar events
                if ($newStatus === 'listed' && $oldStatus !== 'listed') {
                    // Send confirmation email
                    // try {
                    //     $kirby->email([
                    //         'from' => $fromEmail,
                    //         'replyTo' => $fromEmail,
                    //         'to' => $emailData['requester_email'],
                    //         'subject' => 'Buchungsanfrage genehmigt: ' . $emailData['room_names'] . ' am ' . $emailData['request_date'],
                    //         'body' => snippet('content-types/rooms/emails/booking-approved', $emailData, true)
                    //     ]);
                    // } catch (Exception $e) {
                    //     error_log('Booking approval email failed: ' . $e->getMessage());
                    // }
                    // Create Google Calendar events if configured
                    $calendarCredentials = $kirby->option('google.calendar.credentials');
                    if ($calendarCredentials && file_exists($calendarCredentials)) {
                        try {
                            require_once $kirby->root('snippets') . '/content-types/rooms/googleCalendarIntegration.php';
                            $calendarResults = createBookingCalendarEvents($newPage);

                            // Check if all events were created successfully
                            $allSuccess = true;
                            foreach ($calendarResults as $result) {
                                if (! ($result['success'] ?? false)) {
                                    $allSuccess = false;
                                    error_log('Calendar event creation failed for ' . ($result['room'] ?? 'unknown') . ': ' . ($result['message'] ?? 'Unknown error'));
                                }
                            }

                            // Update the calendar_event_added field
                            if ($allSuccess && ! empty($calendarResults)) {

                                // $newPage->update([
                                //     'calendar_event_added' => true
                                // ]);
                            }
                        } catch (Exception $e) {
                            error_log('Google Calendar integration failed: ' . $e->getMessage());
                        }
                    } else {
                        error_log('Google Calendar credentials not configured or file not found.');
                    }
                }

                // Send denial email
                if ($newStatus === 'unlisted' && $oldStatus !== 'unlisted') {
                    try {
                        $kirby->email([
                            'from' => $fromEmail,
                            'replyTo' => $fromEmail,
                            'to' => $emailData['requester_email'],
                            'subject' => 'Buchungsanfrage abgelehnt: ' . $emailData['room_names'] . ' am ' . $emailData['request_date'],
                            'body' => snippet('content-types/rooms/emails/booking-denied', $emailData, true),
                        ]);
                    } catch (Exception $e) {
                        error_log('Booking denial email failed: ' . $e->getMessage());
                    }
                }
            }
        }
    },
    'dreamform.submitted:after' => function ($submission, $form) {

        // The page under which DreamForm stores submissions (adjust as needed!)
        $parent = page('forms');

        if (! $parent) {
            return;
        }

        // DreamForm creates the latest entry as a Draft
        $entry = $parent->drafts()->sortBy('created', 'desc')->first();

        if (! $entry) {
            return;
        }

        // Set the status directly
        $entry->changeStatus('unlisted');

    },

];
