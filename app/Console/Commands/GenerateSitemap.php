<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Event;
use App\Models\Photographer;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Genera el sitemap.xml de F33';

    public function handle()
    {
        $sitemap = Sitemap::create();

    
        $sitemap->add(Url::create('/')->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));
        $sitemap->add(Url::create('/nosotros')->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));
        $sitemap->add(Url::create('/contacto')->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));
        $sitemap->add(Url::create('/fotografos')->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));
        $sitemap->add(Url::create('/eventos')->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        
        Event::where('is_active', true)->where('is_private', false)->get()->each(function (Event $event) use ($sitemap) {
            $sitemap->add(
                Url::create("/eventos/{$event->slug}")
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        });

        
        Photographer::where('status', 'approved')->get()->each(function (Photographer $photographer) use ($sitemap) {
            $sitemap->add(
                Url::create("/fotografos/{$photographer->slug}")
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        });

        
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('¡Sitemap generado exitosamente en public/sitemap.xml!');
    }
}