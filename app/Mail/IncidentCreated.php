<?php

namespace App\Mail;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IncidentCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $incident;

    /**
     * Create a new message instance.
     */
    public function __construct(Incident $incident)
    {
        $this->incident = $incident;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $incidentNo = $this->incident->incident_no ?? $this->incident->incident_code ?? 'N/A';
        $title = $this->incident->title ?? $this->incident->subject ?? 'Incident';
        
        return $this->subject('Incident ' . $incidentNo . ' - Registered Successfully')
                    ->view('emails.incident-created')
                    ->with(['incident' => $this->incident]);
    }
}


