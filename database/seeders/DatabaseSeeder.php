<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\User;
use App\Models\FutureEvent;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Services\SeedImageService;

class DatabaseSeeder extends Seeder
{
    protected $seedImageService;
    public function run(): void
    {
        $this->seedImageService = app(SeedImageService::class);
        // 1. Crear Usuario ADMIN
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_admin' => true,
        ]);

        // 2. Crear TU Usuario (Fotógrafo Principal)
        $myUser = User::factory()->create([
            'name' => 'Yo Fotógrafo',
            'email' => 'foto@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'photographer',
        ]);

        $myPhotographer = Photographer::factory()->create([
            'user_id' => $myUser->id,
            'business_name' => 'Mi Estudio Pro',
            'status' => 'approved',
            'is_verified' => true,
            'region' => 'CABA',
            'profile_photo' => $this->seedImageService->getPhotographerImage(1),
            'banner_photo' => $this->seedImageService->getPhotographerBanner(1),
        ]);

        $otherPhotographers = User::factory(20)
            ->create([
                'role' => 'photographer',
                'password' => bcrypt('password'),
            ])
            ->map(function ($user, $index) {
                return Photographer::factory()->create([
                    'user_id' => $user->id,
                    'status' => 'approved',
                    'profile_photo' => $this->seedImageService->getPhotographerImage($index + 2),
                    'banner_photo' => $this->seedImageService->getPhotographerBanner($index + 2),
                ]);
            });


        // ----------------------------------------------------------------
// ESCENARIO A: MIS EVENTOS (Soy dueño, otros colaboran)
// ----------------------------------------------------------------

        echo "\n ESCENARIO A: Creando mis eventos con fotos...\n";

        $myEvents = Event::factory(5)->create([
            'photographer_id' => $myPhotographer->id,
            'is_active' => true,
            'is_private' => false,
        ]);

        foreach ($myEvents as $eventIndex => $event) {
            echo "  Evento {$eventIndex}/{$myEvents->count()}: {$event->name}";

            // Mis fotos (10 fotos con marca de agua)
            for ($i = 0; $i < 10; $i++) {
                try {
                    $photoData = $this->seedImageService->processSeedPhoto($myPhotographer->id);

                    Photo::create([
                        'event_id' => $event->id,
                        'photographer_id' => $myPhotographer->id,
                        'unique_id' => $photoData['unique_id'],
                        'original_path' => $photoData['original_path'],
                        'watermarked_path' => $photoData['watermarked_path'],
                        'thumbnail_path' => $photoData['thumbnail_path'],
                        'original_name' => $photoData['original_name'],
                        'file_size' => $photoData['file_size'],
                        'width' => $photoData['dimensions']['width'],
                        'height' => $photoData['dimensions']['height'],
                        'is_private' => false,
                        'is_active' => true,
                    ]);

                    echo ".";
                } catch (\Exception $e) {
                    echo "✗";
                    \Log::error("Error creando foto: " . $e->getMessage());
                }
            }

            // Colaboradores
            $collaborators = $otherPhotographers->random(2);

            foreach ($collaborators as $collab) {
                $event->collaborators()->attach($collab->id);

                // Fotos de colaboradores (5 fotos cada uno)
                for ($i = 0; $i < 5; $i++) {
                    try {
                        $photoData = $this->seedImageService->processSeedPhoto($collab->id);

                        Photo::create([
                            'event_id' => $event->id,
                            'photographer_id' => $collab->id,
                            'unique_id' => $photoData['unique_id'],
                            'original_path' => $photoData['original_path'],
                            'watermarked_path' => $photoData['watermarked_path'],
                            'thumbnail_path' => $photoData['thumbnail_path'],
                            'original_name' => $photoData['original_name'],
                            'file_size' => $photoData['file_size'],
                            'width' => $photoData['dimensions']['width'],
                            'height' => $photoData['dimensions']['height'],
                            'is_private' => false,
                            'is_active' => true,
                        ]);

                        echo ".";
                    } catch (\Exception $e) {
                        echo "✗";
                    }
                }
            }

            echo " \n";
        }

        // ----------------------------------------------------------------
// ESCENARIO B: COLABORACIONES (Soy invitado, otros son dueños)
// ----------------------------------------------------------------

        echo "\n ESCENARIO B: Eventos donde soy colaborador...\n";

        $hosts = $otherPhotographers->random(3);

        foreach ($hosts as $hostIndex => $host) {
            $event = Event::factory()->create([
                'photographer_id' => $host->id,
                'name' => 'Evento de ' . $host->business_name,
                'is_active' => true,
            ]);

            echo "  Evento {$hostIndex}/3: {$event->name}";

            $event->collaborators()->attach($myPhotographer->id);

            // Mis fotos (8 fotos)
            for ($i = 0; $i < 8; $i++) {
                try {
                    $photoData = $this->seedImageService->processSeedPhoto($myPhotographer->id);

                    Photo::create([
                        'event_id' => $event->id,
                        'photographer_id' => $myPhotographer->id,
                        'unique_id' => $photoData['unique_id'],
                        'original_path' => $photoData['original_path'],
                        'watermarked_path' => $photoData['watermarked_path'],
                        'thumbnail_path' => $photoData['thumbnail_path'],
                        'original_name' => $photoData['original_name'],
                        'file_size' => $photoData['file_size'],
                        'width' => $photoData['dimensions']['width'],
                        'height' => $photoData['dimensions']['height'],
                        'is_private' => false,
                        'is_active' => true,
                    ]);

                    echo ".";
                } catch (\Exception $e) {
                    echo "✗";
                }
            }

            // Fotos del anfitrión (8 fotos)
            for ($i = 0; $i < 8; $i++) {
                try {
                    $photoData = $this->seedImageService->processSeedPhoto($host->id);

                    Photo::create([
                        'event_id' => $event->id,
                        'photographer_id' => $host->id,
                        'unique_id' => $photoData['unique_id'],
                        'original_path' => $photoData['original_path'],
                        'watermarked_path' => $photoData['watermarked_path'],
                        'thumbnail_path' => $photoData['thumbnail_path'],
                        'original_name' => $photoData['original_name'],
                        'file_size' => $photoData['file_size'],
                        'width' => $photoData['dimensions']['width'],
                        'height' => $photoData['dimensions']['height'],
                        'is_private' => false,
                        'is_active' => true,
                    ]);

                    echo ".";
                } catch (\Exception $e) {
                    echo "✗";
                }
            }

            echo " \n";
        }

        // ----------------------------------------------------------------
// ESCENARIO C: RELLENO (Eventos random)
// ----------------------------------------------------------------

        echo "\n ESCENARIO C: Eventos de relleno (otros fotógrafos)...\n";

        $total = $otherPhotographers->count();
        $current = 0;

        foreach ($otherPhotographers as $photographer) {
            $current++;

            if (!$photographer || !$photographer->id) {
                echo "    Fotógrafo inválido detectado, saltando...\n";
                continue;
            }

            echo "  Fotógrafo {$current}/{$total}: {$photographer->business_name}";

            try {
                // 2 eventos por fotógrafo
                for ($e = 0; $e < 2; $e++) {

                    $event = null;

                    try {
                        $event = Event::factory()->create([
                            'photographer_id' => $photographer->id,
                        ]);
                    } catch (\Throwable $eventError) {
                        echo " [E-ERR]";
                        \Log::error("Error creando evento", [
                            'photographer_id' => $photographer->id,
                            'error' => $eventError->getMessage(),
                            'file' => $eventError->getFile(),
                            'line' => $eventError->getLine(),
                        ]);
                        continue; // Saltar este evento y continuar con el siguiente
                    }

                    if (!$event || !$event->id) {
                        echo " [E-NULL]";
                        continue;
                    }

                    // 5 fotos por evento
                    for ($i = 0; $i < 5; $i++) {
                        $attempts = 0;
                        $maxAttempts = 3;
                        $photoCreated = false;

                        while ($attempts < $maxAttempts && !$photoCreated) {
                            try {
                                $photoData = $this->seedImageService->processSeedPhoto($photographer->id);

                                if (!$photoData || !isset($photoData['unique_id'])) {
                                    throw new \Exception("Datos de foto inválidos");
                                }

                                Photo::create([
                                    'event_id' => $event->id,
                                    'photographer_id' => $photographer->id,
                                    'unique_id' => $photoData['unique_id'],
                                    'original_path' => $photoData['original_path'],
                                    'watermarked_path' => $photoData['watermarked_path'],
                                    'thumbnail_path' => $photoData['thumbnail_path'],
                                    'original_name' => $photoData['original_name'],
                                    'file_size' => $photoData['file_size'],
                                    'width' => $photoData['dimensions']['width'],
                                    'height' => $photoData['dimensions']['height'],
                                    'is_private' => false,
                                    'is_active' => true,
                                ]);

                                echo ".";
                                $photoCreated = true;

                            } catch (\Throwable $photoError) {
                                $attempts++;

                                if ($attempts >= $maxAttempts) {
                                    echo "✗";
                                    \Log::warning("Foto omitida tras {$maxAttempts} intentos", [
                                        'event_id' => $event->id ?? 'unknown',
                                        'photographer_id' => $photographer->id,
                                        'error' => $photoError->getMessage(),
                                    ]);
                                } else {
                                    echo "↻";
                                    sleep(1);
                                }
                            }
                        }
                    }
                }

                echo " \n";

            } catch (\Throwable $criticalError) {
                echo "  CRÍTICO\n";
                \Log::error("Error crítico en fotógrafo", [
                    'photographer_id' => $photographer->id ?? 'unknown',
                    'business_name' => $photographer->business_name ?? 'unknown',
                    'error' => $criticalError->getMessage(),
                    'file' => $criticalError->getFile(),
                    'line' => $criticalError->getLine(),
                    'trace' => $criticalError->getTraceAsString(),
                ]);

                // NO re-lanzar, solo continuar con el siguiente fotógrafo
                continue;
            }
        }

        echo "\n";





        

        $eventImageMap = [
            'Tech Summit Argentina 2025' => 'tech2025',
            'Festival Lollapalooza Argentina' => 'lolla2025',
            'Feria del Libro Buenos Aires' => 'feria2025',
            'Maratón de Buenos Aires' => 'maraton2025',
            'Comic Con Argentina' => 'comic2025',
        ];
        // ================================================================
        //  EVENTOS FUTUROS CON COORDENADAS
        // ================================================================

        $allPhotographers = $otherPhotographers->push($myPhotographer);

        // ----------------------------------------------------------------
        //  EVENTOS DESTACADOS EN CABA Y GBA
        // ----------------------------------------------------------------
        $specificFutureEvents = [
            [
                'photographer_id' => $allPhotographers->random()->id,
                'title' => 'Tech Summit Argentina 2025',
                'description' => 'La mayor conferencia de tecnología del año. Miles de asistentes, startups e inversores.',
                'location' => 'Centro Costa Salguero, Buenos Aires',
                'latitude' => -34.5633,
                'longitude' => -58.3816,
                'event_date' => Carbon::now()->addDays(15)->setTime(9, 0),
                'expiry_date' => Carbon::now()->addDays(22)->setTime(9, 0),
                'cover_image' => 'https://picsum.photos/seed/tech2025/1280/720',
                'status' => 'upcoming',
            ],
            [
                'photographer_id' => $allPhotographers->random()->id,
                'title' => 'Festival Lollapalooza Argentina',
                'description' => 'Tres días de música con artistas internacionales. Rock, pop, electrónica y más.',
                'location' => 'Hipódromo de San Isidro, Buenos Aires',
                'latitude' => -34.4682,
                'longitude' => -58.5135,
                'event_date' => Carbon::now()->addDays(30)->setTime(14, 0),
                'expiry_date' => Carbon::now()->addDays(37)->setTime(14, 0),
                'cover_image' => 'https://picsum.photos/seed/lolla2025/1280/720',
                'status' => 'upcoming',
            ],
            [
                'photographer_id' => $allPhotographers->random()->id,
                'title' => 'Feria del Libro Buenos Aires',
                'description' => 'La feria del libro más grande de Latinoamérica. Autores, conferencias y firmas.',
                'location' => 'La Rural, Palermo',
                'latitude' => -34.5831,
                'longitude' => -58.4353,
                'event_date' => Carbon::now()->addDays(45)->setTime(11, 0),
                'expiry_date' => Carbon::now()->addDays(52)->setTime(11, 0),
                'cover_image' => 'https://picsum.photos/seed/feria2025/1280/720',
                'status' => 'upcoming',
            ],
            [
                'photographer_id' => $allPhotographers->random()->id,
                'title' => 'Maratón de Buenos Aires',
                'description' => '42K por las calles más emblemáticas. Más de 15,000 corredores.',
                'location' => 'Obelisco (Largada), CABA',
                'latitude' => -34.6037,
                'longitude' => -58.3816,
                'event_date' => Carbon::now()->addDays(60)->setTime(7, 0),
                'expiry_date' => Carbon::now()->addDays(67)->setTime(7, 0),
                'cover_image' => 'https://picsum.photos/seed/maraton2025/1280/720',
                'status' => 'upcoming',
            ],
            [
                'photographer_id' => $allPhotographers->random()->id,
                'title' => 'Comic Con Argentina',
                'description' => 'Convención de comics, videojuegos, anime y cultura pop.',
                'location' => 'Estadio Obras Sanitarias, CABA',
                'latitude' => -34.5645,
                'longitude' => -58.4283,
                'event_date' => Carbon::now()->addDays(25)->setTime(10, 0),
                'expiry_date' => Carbon::now()->addDays(32)->setTime(10, 0),
                'cover_image' => 'https://picsum.photos/seed/comic2025/1280/720',
                'status' => 'upcoming',
            ],
        ];

        foreach ($specificFutureEvents as $eventData) {
            $imageName = $eventImageMap[$eventData['title']] ?? 'test';

            FutureEvent::create([
                'photographer_id' => $eventData['photographer_id'],
                'title' => $eventData['title'],
                'description' => $eventData['description'],
                'location' => $eventData['location'],
                'latitude' => $eventData['latitude'],
                'longitude' => $eventData['longitude'],
                'event_date' => $eventData['event_date'],
                'expiry_date' => $eventData['expiry_date'],
                'cover_image' => $this->seedImageService->getFutureEventImage($imageName), // Usar servicio para obtener imagen
                'status' => $eventData['status'],
            ]);
        }


        // ----------------------------------------------------------------
        //  EVENTOS POR PROVINCIA (2 eventos por provincia)
        // ----------------------------------------------------------------
        $provinceEvents = [
            // BUENOS AIRES (GBA)
            [
                'title' => 'Fiesta de la Cerveza Artesanal',
                'description' => 'Más de 50 cervecerías artesanales de todo el país. Música en vivo y food trucks.',
                'location' => 'La Plata, Buenos Aires',
                'latitude' => -34.9215,
                'longitude' => -57.9545,
            ],
            [
                'title' => 'Feria de Diseño y Arte',
                'description' => 'Artistas locales, diseñadores independientes y emprendedores creativos.',
                'location' => 'San Isidro, Buenos Aires',
                'latitude' => -34.4708,
                'longitude' => -58.5128,
            ],

            // CÓRDOBA
            [
                'title' => 'Festival de Jazz Córdoba',
                'description' => 'El festival de jazz más importante del interior. Artistas nacionales e internacionales.',
                'location' => 'Teatro del Libertador, Córdoba',
                'latitude' => -31.4201,
                'longitude' => -64.1888,
            ],
            [
                'title' => 'Rally de las Sierras',
                'description' => 'Competencia de automovilismo deportivo por las sierras cordobesas.',
                'location' => 'Villa Carlos Paz, Córdoba',
                'latitude' => -31.4241,
                'longitude' => -64.4978,
            ],

            // SANTA FE
            [
                'title' => 'Festival Internacional de Cine',
                'description' => 'Proyecciones, charlas con directores y premiación de cortometrajes.',
                'location' => 'Rosario, Santa Fe',
                'latitude' => -32.9442,
                'longitude' => -60.6505,
            ],
            [
                'title' => 'Feria del Libro Regional',
                'description' => 'Autores locales, editoriales independientes y talleres literarios.',
                'location' => 'Santa Fe Capital',
                'latitude' => -31.6107,
                'longitude' => -60.6973,
            ],

            // MENDOZA
            [
                'title' => 'Fiesta Nacional de la Vendimia',
                'description' => 'Celebración de la cosecha de uva con desfile, espectáculos y elección de reina.',
                'location' => 'Mendoza Capital',
                'latitude' => -32.8895,
                'longitude' => -68.8458,
            ],
            [
                'title' => 'Maratón de Alta Montaña',
                'description' => 'Carrera de montaña por los Andes mendocinos. 21K y 42K.',
                'location' => 'Las Cuevas, Mendoza',
                'latitude' => -32.8064,
                'longitude' => -70.0378,
            ],

            // SALTA
            [
                'title' => 'Festival Folclórico del Norte',
                'description' => 'Música folclórica, danzas tradicionales y artesanías regionales.',
                'location' => 'Salta Capital',
                'latitude' => -24.7821,
                'longitude' => -65.4232,
            ],
            [
                'title' => 'Feria de Artesanías Andinas',
                'description' => 'Artesanos de pueblos originarios exponen sus tejidos, cerámicas y productos regionales.',
                'location' => 'Cafayate, Salta',
                'latitude' => -26.0730,
                'longitude' => -65.9787,
            ],

            // TUCUMÁN
            [
                'title' => 'Festival Nacional del Folclore',
                'description' => 'El festival folclórico más antiguo del país. Peñas, zambas y chacareras.',
                'location' => 'San Miguel de Tucumán',
                'latitude' => -26.8083,
                'longitude' => -65.2176,
            ],
            [
                'title' => 'Fiesta del Limón',
                'description' => 'Celebración de la producción citrícola con desfiles, música y gastronomía.',
                'location' => 'Tafí Viejo, Tucumán',
                'latitude' => -26.7306,
                'longitude' => -65.2619,
            ],

            // NEUQUÉN
            [
                'title' => 'Festival de Música Electrónica Patagónico',
                'description' => 'DJs internacionales en el marco de la Patagonia. Techno, house y trance.',
                'location' => 'Neuquén Capital',
                'latitude' => -38.9516,
                'longitude' => -68.0591,
            ],
            [
                'title' => 'Fiesta Nacional del Pehuen',
                'description' => 'Celebración de la cultura mapuche con ceremonias, música y artesanías.',
                'location' => 'Villa Pehuenia, Neuquén',
                'latitude' => -38.8833,
                'longitude' => -71.1667,
            ],

            // RÍO NEGRO
            [
                'title' => 'Festival de Cine de la Patagonia',
                'description' => 'Proyecciones, workshops y encuentros con cineastas de la región.',
                'location' => 'San Carlos de Bariloche',
                'latitude' => -41.1335,
                'longitude' => -71.3103,
            ],
            [
                'title' => 'Fiesta Nacional de la Manzana',
                'description' => 'Celebración de la cosecha con desfiles, música y productos regionales.',
                'location' => 'General Roca, Río Negro',
                'latitude' => -39.0333,
                'longitude' => -67.5833,
            ],

            // CHUBUT
            [
                'title' => 'Festival del Pingüino',
                'description' => 'Celebración de la fauna patagónica con actividades educativas y turismo sustentable.',
                'location' => 'Puerto Madryn, Chubut',
                'latitude' => -42.7692,
                'longitude' => -65.0386,
            ],
            [
                'title' => 'Festival Nacional del Salmón',
                'description' => 'Competencia de pesca deportiva y gastronomía patagónica.',
                'location' => 'Esquel, Chubut',
                'latitude' => -42.9114,
                'longitude' => -71.3103,
            ],

            // SANTA CRUZ
            [
                'title' => 'Maratón Glaciar Perito Moreno',
                'description' => 'Carrera de aventura con vistas al Glaciar Perito Moreno.',
                'location' => 'El Calafate, Santa Cruz',
                'latitude' => -50.3375,
                'longitude' => -72.2647,
            ],
            [
                'title' => 'Festival de Tango Austral',
                'description' => 'Milongas, clases de tango y espectáculos en el fin del mundo.',
                'location' => 'Río Gallegos, Santa Cruz',
                'latitude' => -51.6226,
                'longitude' => -69.2181,
            ],

            // TIERRA DEL FUEGO
            [
                'title' => 'Festival del Fin del Mundo',
                'description' => 'Música, arte y cultura en la ciudad más austral del planeta.',
                'location' => 'Ushuaia, Tierra del Fuego',
                'latitude' => -54.8019,
                'longitude' => -68.3029,
            ],
            [
                'title' => 'Maratón de los Andes Fueguinos',
                'description' => 'Trail running por los bosques y montañas del fin del mundo.',
                'location' => 'Ushuaia, Tierra del Fuego',
                'latitude' => -54.7833,
                'longitude' => -68.3,
            ],

            // JUJUY
            [
                'title' => 'Fiesta de la Pachamama',
                'description' => 'Ceremonia ancestral de agradecimiento a la Madre Tierra.',
                'location' => 'Humahuaca, Jujuy',
                'latitude' => -23.2048,
                'longitude' => -65.3500,
            ],
            [
                'title' => 'Carnaval Jujeño',
                'description' => 'El carnaval más importante del NOA con comparsas, música y desfiles.',
                'location' => 'San Salvador de Jujuy',
                'latitude' => -24.1858,
                'longitude' => -65.2995,
            ],

            // MISIONES
            [
                'title' => 'Festival de la Música Misionera',
                'description' => 'Chamamé, polka y música guaranítica en la tierra colorada.',
                'location' => 'Posadas, Misiones',
                'latitude' => -27.3671,
                'longitude' => -55.8961,
            ],
            [
                'title' => 'Fiesta Nacional del Inmigrante',
                'description' => 'Celebración de las diferentes colectividades con gastronomía y danzas típicas.',
                'location' => 'Oberá, Misiones',
                'latitude' => -27.4870,
                'longitude' => -55.1197,
            ],

            // CORRIENTES
            [
                'title' => 'Carnaval de Corrientes',
                'description' => 'Uno de los carnavales más importantes de Argentina. Comparsas y batucadas.',
                'location' => 'Corrientes Capital',
                'latitude' => -27.4806,
                'longitude' => -58.8341,
            ],
            [
                'title' => 'Festival del Chamamé',
                'description' => 'El ritmo del litoral argentino con los mejores exponentes del género.',
                'location' => 'Mercedes, Corrientes',
                'latitude' => -29.1840,
                'longitude' => -58.0732,
            ],

            // ENTRE RÍOS
            [
                'title' => 'Festival de Jineteada y Folclore',
                'description' => 'Destreza gaucha, doma de potros y música folclórica.',
                'location' => 'Diamante, Entre Ríos',
                'latitude' => -32.0667,
                'longitude' => -60.6333,
            ],
            [
                'title' => 'Fiesta Nacional del Asado con Cuero',
                'description' => 'Gastronomía tradicional entrerriana y espectáculos criollos.',
                'location' => 'Gualeguaychú, Entre Ríos',
                'latitude' => -33.0093,
                'longitude' => -58.5173,
            ],

            // CHACO
            [
                'title' => 'Fiesta Nacional del Algodón',
                'description' => 'Celebración de la producción algodonera con elección de reina y festivales.',
                'location' => 'Resistencia, Chaco',
                'latitude' => -27.4511,
                'longitude' => -58.9868,
            ],
            [
                'title' => 'Festival de Esculturas en Madera',
                'description' => 'Artistas de todo el mundo tallan esculturas gigantes en plena plaza.',
                'location' => 'Resistencia, Chaco',
                'latitude' => -27.4606,
                'longitude' => -58.9878,
            ],

            // FORMOSA
            [
                'title' => 'Fiesta Nacional del Pomelo',
                'description' => 'Celebración de la producción citrícola con música y gastronomía.',
                'location' => 'Formosa Capital',
                'latitude' => -26.1858,
                'longitude' => -58.1756,
            ],
            [
                'title' => 'Festival Folclórico del Litoral',
                'description' => 'Chamamé, chacareras y polkas del litoral argentino.',
                'location' => 'Clorinda, Formosa',
                'latitude' => -25.2894,
                'longitude' => -57.7189,
            ],

            // SANTIAGO DEL ESTERO
            [
                'title' => 'Festival Nacional de la Chacarera',
                'description' => 'El ritmo más tradicional de Santiago en su máxima expresión.',
                'location' => 'Santiago del Estero Capital',
                'latitude' => -27.7834,
                'longitude' => -64.2642,
            ],
            [
                'title' => 'Fiesta del Caballo',
                'description' => 'Jineteadas, domas y destrezas gauchas.',
                'location' => 'Frías, Santiago del Estero',
                'latitude' => -28.6333,
                'longitude' => -65.1333,
            ],

            // LA RIOJA
            [
                'title' => 'Festival del Poncho',
                'description' => 'Artesanías, tejidos tradicionales y folclore riojano.',
                'location' => 'La Rioja Capital',
                'latitude' => -29.4130,
                'longitude' => -66.8558,
            ],
            [
                'title' => 'Fiesta Nacional de la Chaya',
                'description' => 'Celebración de carnaval con música, danzas y albahaca.',
                'location' => 'Chilecito, La Rioja',
                'latitude' => -29.1639,
                'longitude' => -67.4981,
            ],

            // CATAMARCA
            [
                'title' => 'Fiesta Nacional del Poncho',
                'description' => 'Tejidos artesanales, música folclórica y danzas tradicionales.',
                'location' => 'San Fernando del Valle de Catamarca',
                'latitude' => -28.4696,
                'longitude' => -65.7795,
            ],
            [
                'title' => 'Festival de la Virgen del Valle',
                'description' => 'Peregrinación religiosa y festividades patronales.',
                'location' => 'Catamarca Capital',
                'latitude' => -28.4710,
                'longitude' => -65.7794,
            ],

            // SAN JUAN
            [
                'title' => 'Festival Nacional del Sol',
                'description' => 'Espectáculo de luces, danzas y música en homenaje al sol.',
                'location' => 'San Juan Capital',
                'latitude' => -31.5375,
                'longitude' => -68.5364,
            ],
            [
                'title' => 'Vuelta Ciclística Internacional',
                'description' => 'Competencia de ciclismo profesional por los valles sanjuaninos.',
                'location' => 'Valle Fértil, San Juan',
                'latitude' => -30.6333,
                'longitude' => -67.4667,
            ],

            // SAN LUIS
            [
                'title' => 'Festival Internacional de Tango',
                'description' => 'Milongas, clases magistrales y espectáculos de tango.',
                'location' => 'San Luis Capital',
                'latitude' => -33.3017,
                'longitude' => -66.3378,
            ],
            [
                'title' => 'Rally de las Sierras Puntanas',
                'description' => 'Competencia de rally por los caminos serranos.',
                'location' => 'Merlo, San Luis',
                'latitude' => -32.3439,
                'longitude' => -65.0128,
            ],

            // LA PAMPA
            [
                'title' => 'Fiesta Nacional del Ternero',
                'description' => 'Exposición ganadera, remates y espectáculos criollos.',
                'location' => 'Santa Rosa, La Pampa',
                'latitude' => -36.6167,
                'longitude' => -64.2833,
            ],
            [
                'title' => 'Festival Folclórico Pampeano',
                'description' => 'Folclore pampeano con artistas locales y nacionales.',
                'location' => 'General Pico, La Pampa',
                'latitude' => -35.6567,
                'longitude' => -63.7567,
            ],
        ];



        // Mapeo de eventos por provincia a imágenes locales
        $provinceEventsImages = [
            'Fiesta de la Cerveza Artesanal' => 'cerveza',
            'Feria de Diseño y Arte' => 'arte',
            'Festival de Jazz Córdoba' => 'jazz',
            'Rally de las Sierras' => 'rally',
            'Festival Internacional de Cine' => 'cine',
            'Feria del Libro Regional' => 'feria2025',
            'Fiesta Nacional de la Vendimia' => 'vendimia',
            'Maratón de Alta Montaña' => 'montana',
            'Festival Folclórico del Norte' => 'folclore',
            'Feria de Artesanías Andinas' => 'artesanias',
            'Festival Nacional del Folclore' => 'folclore',
            'Fiesta del Limón' => 'vendimia',
            'Festival de Música Electrónica Patagónico' => 'electronica',
            'Fiesta Nacional del Pehuen' => 'pachamama',
            'Festival de Cine de la Patagonia' => 'cine',
            'Fiesta Nacional de la Manzana' => 'vendimia',
            'Festival del Pingüino' => 'pinguino',
            'Festival Nacional del Salmón' => 'montana',
            'Maratón Glaciar Perito Moreno' => 'glaciar',
            'Festival de Tango Austral' => 'tango',
            'Festival del Fin del Mundo' => 'ushuaia',
            'Maratón de los Andes Fueguinos' => 'montana',
            'Fiesta de la Pachamama' => 'pachamama',
            'Carnaval Jujeño' => 'carnaval',
            'Festival de la Música Misionera' => 'chamame',
            'Fiesta Nacional del Inmigrante' => 'carnaval',
            'Carnaval de Corrientes' => 'carnaval',
            'Festival del Chamamé' => 'chamame',
            'Festival de Jineteada y Folclore' => 'gaucho',
            'Fiesta Nacional del Asado con Cuero' => 'gaucho',
            'Fiesta Nacional del Algodón' => 'algodon',
            'Festival de Esculturas en Madera' => 'artesanias',
            'Fiesta Nacional del Pomelo' => 'vendimia',
            'Festival Folclórico del Litoral' => 'folclore',
            'Festival Nacional de la Chacarera' => 'folclore',
            'Fiesta del Caballo' => 'gaucho',
            'Festival del Poncho' => 'artesanias',
            'Fiesta Nacional de la Chaya' => 'carnaval',
            'Fiesta Nacional del Poncho' => 'artesanias',
            'Festival de la Virgen del Valle' => 'pachamama',
            'Festival Nacional del Sol' => 'folclore',
            'Vuelta Ciclística Internacional' => 'rally',
            'Festival Internacional de Tango' => 'tango',
            'Rally de las Sierras Puntanas' => 'rally',
            'Fiesta Nacional del Ternero' => 'gaucho',
            'Festival Folclórico Pampeano' => 'folclore',
        ];


        // Crear eventos distribuidos por provincia
        $dayOffset = 10;
        foreach ($provinceEvents as $eventData) {
            $eventDate = Carbon::now()->addDays($dayOffset)->setTime(rand(9, 20), 0);

            $imageName = $provinceEventsImages[$eventData['title']] ?? 'test'; // 

            FutureEvent::create([
                'photographer_id' => $allPhotographers->random()->id,
                'title' => $eventData['title'],
                'description' => $eventData['description'],
                'location' => $eventData['location'],
                'latitude' => $eventData['latitude'],
                'longitude' => $eventData['longitude'],
                'event_date' => $eventDate,
                'expiry_date' => $eventDate->copy()->addDays(7),
                'cover_image' => $this->seedImageService->getFutureEventImage($imageName), // 
                'status' => 'upcoming',
            ]);

            $dayOffset += rand(3, 7);
        }

        //  1 Evento de prueba que ya pasó
        FutureEvent::create([
            'photographer_id' => $myPhotographer->id,
            'title' => '🧪 EVENTO DE PRUEBA (Ya Pasó)',
            'description' => 'Este evento ya pasó. Ejecuta "php artisan events:convert-future" para convertirlo.',
            'location' => 'Test Location, CABA',
            'latitude' => -34.6037,
            'longitude' => -58.3816,
            'event_date' => Carbon::now()->subDays(2),
            'expiry_date' => Carbon::now()->addDays(5),
            'cover_image' => $this->seedImageService->getFutureEventImage('test'), // 
            'status' => 'upcoming',
        ]);

        // ================================================================
        //  RESUMEN EN CONSOLA
        // ================================================================

        $totalEvents = Event::count();
        $totalFutureEvents = FutureEvent::count();
        $totalPhotos = Photo::count();
        $totalPhotographers = Photographer::count();

        echo "\n=============================================\n";
        echo "   Base de datos poblada con éxito\n";
        echo "=============================================\n";
        echo " CREDENCIALES:\n";
        echo "   Admin:      admin@empresa.com / password\n";
        echo "   Fotógrafo:  foto@empresa.com / password\n";
        echo "---------------------------------------------\n";
        echo " ESTADÍSTICAS:\n";
        echo "   • Fotógrafos: {$totalPhotographers}\n";
        echo "   • Eventos pasados: {$totalEvents}\n";
        echo "   • Eventos futuros: {$totalFutureEvents}\n";
        echo "   • Fotos: {$totalPhotos}\n";
        echo "---------------------------------------------\n";
        echo " DISTRIBUCIÓN DE EVENTOS:\n";
        echo "   • 5 eventos destacados (CABA/GBA)\n";
        echo "   • " . count($provinceEvents) . " eventos por provincia\n";
        echo "   • 1 evento de prueba (pasado)\n";
        echo "   • Total: {$totalFutureEvents} marcadores en el mapa\n";
        echo "---------------------------------------------\n";
        echo "🇦🇷 PROVINCIAS CUBIERTAS:\n";
        echo "   Buenos Aires, CABA, Córdoba, Santa Fe,\n";
        echo "   Mendoza, Salta, Tucumán, Neuquén, Río Negro,\n";
        echo "   Chubut, Santa Cruz, Tierra del Fuego, Jujuy,\n";
        echo "   Misiones, Corrientes, Entre Ríos, Chaco,\n";
        echo "   Formosa, Santiago del Estero, La Rioja,\n";
        echo "   Catamarca, San Juan, San Luis, La Pampa\n";
        echo "---------------------------------------------\n";
        echo " PRUEBAS:\n";
        echo "1. Home > Mapa: Deberías ver {$totalFutureEvents} marcadores\n";
        echo "2. Dashboard > Mis Oportunidades\n";
        echo "3. php artisan events:convert-future\n";
        echo "=============================================\n";
    }
}
