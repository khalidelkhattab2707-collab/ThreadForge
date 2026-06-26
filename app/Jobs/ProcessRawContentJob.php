<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Enums\RawContentStatusEnum;
use App\Models\RawContent;
use App\Services\GrokRepurposingService;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessRawContentJob implements ShouldQueue
{
    use Queueable;
    
      public int $tries   = 3;
    public int $backoff = 60;
    /**
     * Create a new job instance.
     */
   public function __construct(
        public readonly RawContent $rawContent

    ) {}


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = app(GrokRepurposingService::class);

         // 1. Marque comme "en cours" pour éviter les doubles traitements
        $this->rawContent->update([
            'status' => RawContentStatusEnum::Analyzing,
        ]);

        // 2. Charge le Blueprint — un seul appel SQL (pas de N+1)
        $blueprint = $this->rawContent->campaignBlueprint;

        // 3. Appel Grok via le Service
        $result = $service->repurpose(
            $this->rawContent->content,
            $blueprint
        );

        // 4. Persiste le GeneratedPost
        // Les Eloquent Casts gèrent body_points et suggested_hashtags automatiquement
        $this->rawContent->generatedPost()->create([
            'hook_propose'                  => $result['hook_propose'],
            'body_points'                   => $result['body_points'],
            'technical_readability_score'   => $result['technical_readability_score'],
            'suggested_hashtags'            => $result['suggested_hashtags'],
            'tone_compliance_justification' => $result['tone_compliance_justification'],
        ]);

        // 5. Marque comme terminé
        $this->rawContent->update([
            'status' => RawContentStatusEnum::Done,
        ]);
    }
    public function failed(Throwable $exception): void
    {
        // Toujours implémenter failed() — critère jury obligatoire
        $this->rawContent->update([
            'status' => RawContentStatusEnum::Failed,
        ]);

        Log::error('ProcessRawContentJob failed', [
            'raw_content_id' => $this->rawContent->id,
            'error'          => $exception->getMessage(),
            'trace'          => $exception->getTraceAsString(),
        ]);
    }
}
