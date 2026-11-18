<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;

class SoloGymController extends Controller
{
    /**
     * Display the SoloGym infographic & promotion landing page.
     */
    public function index()
    {
        $apkPath = public_path('downloads/sologym-v1.0.0.apk');
        $apkExists = File::exists($apkPath);
        $apkSize = $apkExists ? round(File::size($apkPath) / (1024 * 1024), 2) : 38.4;
        if ($apkSize < 1) {
            $apkSize = $apkExists ? round(File::size($apkPath) / 1024, 1) . ' KB' : '38.4 MB';
        } else {
            $apkSize = $apkSize . ' MB';
        }
        $apkSha256 = $apkExists ? hash_file('sha256', $apkPath) : '67d3d5f9b6213f1a96bb7a823e9789be1496470b457db2900225fce77457bb98';
        $downloadCount = Cache::get('sologym_apk_downloads', 1284);

        $literature = [
            [
                'author' => 'Dr. Brad Schoenfeld',
                'title' => 'Science and Development of Muscle Hypertrophy',
                'focus' => 'Volume & Rest Interval Dynamics',
                'badge' => 'Hypertrophy Core',
                'description' => 'Scientific guidelines demonstrating that 2-3 minute rest intervals between compound sets optimize mechanical tension and total volume load compared to rushed 60-second breaks.'
            ],
            [
                'author' => 'Dr. Eric Helms',
                'title' => 'The Muscle and Strength Training Pyramid',
                'focus' => 'Adherence, Volume & Progressive Overload',
                'badge' => 'Training Hierarchy',
                'description' => 'A structured hierarchy placing safety and adherence at the foundation, ensuring sustainable weight increments via double progression rather than risky sudden 1RM jumps.'
            ],
            [
                'author' => 'Dr. Bret Contreras',
                'title' => 'Biomechanics of Posterior Chain & Axial Loading',
                'focus' => 'Joint Safety & Machine Stabilization',
                'badge' => 'Biomechanics',
                'description' => 'Extensive electromyography (EMG) research showing machine-supported rows and thrusts provide maximal muscle activation with drastically lower lumbar shear forces.'
            ],
            [
                'author' => 'Mark Rippetoe',
                'title' => 'Starting Strength: Basic Barbell Training',
                'focus' => 'Kinematic Form & Barbell Physics',
                'badge' => 'Movement Quality',
                'description' => 'Mechanical moment-arm principles that SoloGym uses for its Tier 3 compound barbell cues (neutral spine, bar over mid-foot, active shoulder bracing).'
            ],
            [
                'author' => 'ACSM (American College of Sports Medicine)',
                'title' => 'Guidelines for Exercise Testing and Prescription',
                'focus' => 'Beginner Readiness & Adaptation',
                'badge' => 'Clinical Safety',
                'description' => 'Standardized physiological protocols recommending machine and guided cable foundations for the first 4-8 weeks before moving to free barbell axial loads.'
            ]
        ];

        $personas = [
            [
                'id' => 'rex',
                'name' => 'Coach Rex',
                'tag' => '🔥 Beast Mode',
                'title' => 'The High-Energy Hype Beast',
                'primaryColor' => '#00E676',
                'accentColor' => '#FF9100',
                'image' => 'assets/images/mascot/mascot_active.jpg',
                'greeting' => 'Rise and conquer! Today is a day for personal records. Let’s get after it!',
                'quote' => 'One more rep than your mind wants to give—that is where champions live!',
                'tip' => 'Explode on concentric, fight the negative on the way down!'
            ],
            [
                'id' => 'maya',
                'name' => 'Coach Maya',
                'tag' => '📐 Science & Safety',
                'title' => 'Biomechanics & Form Specialist',
                'primaryColor' => '#38BDF8',
                'accentColor' => '#818CF8',
                'image' => 'assets/images/mascot/mascot_analyzing.jpg',
                'greeting' => 'Welcome! A fresh session to practice optimal motor unit recruitment and joint safety.',
                'quote' => 'Quality over quantity. 8 clinical reps beats 12 ego curls every single time.',
                'tip' => 'Control the eccentric for 2 to 3 seconds for 40% greater hypertrophy stimulus.'
            ],
            [
                'id' => 'iron',
                'name' => 'Coach Iron',
                'tag' => '🛡️ Pure Discipline',
                'title' => 'Old-School Discipline Veteran',
                'primaryColor' => '#10B981',
                'accentColor' => '#94A3B8',
                'image' => 'assets/images/mascot/mascot_confident.jpg',
                'greeting' => 'The barbell doesn’t care about excuses. Time to show up and execute clean work.',
                'quote' => 'Discipline outlasts motivation every single day of the year. Respect the craft.',
                'tip' => 'Never compromise spinal neutrality for 5 extra kilos. Ever.'
            ],
            [
                'id' => 'zen',
                'name' => 'Coach Zen',
                'tag' => '🌿 Mind & Recovery',
                'title' => 'Mind-Muscle & Recovery Master',
                'primaryColor' => '#A855F7',
                'accentColor' => '#EC4899',
                'image' => 'assets/images/mascot/mascot_welcoming.jpg',
                'greeting' => 'Take a deep breath and connect your mind to your body. Today is about intentional movement.',
                'quote' => 'Your muscle cannot contract what your nervous system cannot feel. Focus inside.',
                'tip' => 'Growth happens during recovery and sleep, not during the workout. Nourish your body.'
            ],
        ];

        $mascotStates = [
            [
                'id' => 'welcoming',
                'name' => 'Greeting & Motivation',
                'tag' => 'App Launch Cues',
                'image' => 'assets/images/mascot/mascot_welcoming.jpg',
                'quote' => 'Welcome back to the gym! Your form is your shield today. Let’s make every single rep count!',
                'color' => '#00E676'
            ],
            [
                'id' => 'active',
                'name' => 'High-Energy Hype',
                'tag' => 'Working Set Cues',
                'image' => 'assets/images/mascot/mascot_active.jpg',
                'quote' => 'Lock in! Brace your core, drive through the floor, and own the eccentric phase!',
                'color' => '#00E5FF'
            ],
            [
                'id' => 'confident',
                'name' => 'Tactical Coaching',
                'tag' => 'Form & Interval Rest',
                'image' => 'assets/images/mascot/mascot_confident.jpg',
                'quote' => 'Smart recovery makes champions. 120s timer is rolling—let ATP resynthesize for max power.',
                'color' => '#FFD700'
            ],
            [
                'id' => 'celebrating',
                'name' => 'PR & Victory',
                'tag' => 'Milestone Unlocked',
                'image' => 'assets/images/mascot/mascot_celebrating.jpg',
                'quote' => 'NEW PERSONAL RECORD! That progressive overload just paid off! You earned this milestone!',
                'color' => '#10B981'
            ],
            [
                'id' => 'exhausted',
                'name' => 'Recovery & Deload',
                'tag' => 'Post-Workout Cool Down',
                'image' => 'assets/images/mascot/mascot_exhausted.jpg',
                'quote' => 'Tough session crushed. Hydrate, eat your protein, and let SQLite safely store your session.',
                'color' => '#FF9100'
            ],
            [
                'id' => 'analyzing',
                'name' => 'Scientific Analysis',
                'tag' => 'Navy Body Comp & Charts',
                'image' => 'assets/images/mascot/mascot_analyzing.jpg',
                'quote' => 'Checking your trend line: Lean muscle mass is up 0.4kg while waist circumference dropped 1cm. Pure recomp!',
                'color' => '#8B5CF6'
            ]
        ];

        return view('sologym.landing', compact(
            'apkExists',
            'apkSize',
            'apkSha256',
            'downloadCount',
            'literature',
            'personas',
            'mascotStates'
        ));
    }

    /**
     * Download the APK file directly.
     */
    public function downloadApk()
    {
        $apkPath = public_path('downloads/sologym-v1.0.0.apk');

        if (!File::exists($apkPath)) {
            abort(404, 'SoloGym APK package is temporarily unavailable.');
        }

        Cache::increment('sologym_apk_downloads');

        return response()->download($apkPath, 'SoloGym-v1.0.0.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-cache, must-revalidate'
        ]);
    }

    /**
     * API: Plate Calculator for standard Olympic barbell loading.
     */
    public function calculatePlates(Request $request)
    {
        $target = floatval($request->query('weight', 60.0));
        $barbell = floatval($request->query('barbell', 20.0));
        $unit = $request->query('unit', 'kg');

        if ($target < $barbell) {
            return response()->json([
                'success' => false,
                'message' => "Target weight ({$target} {$unit}) cannot be lighter than the bar ({$barbell} {$unit}).",
                'platesPerSide' => [],
                'totalLoaded' => $barbell,
                'remainder' => 0
            ]);
        }

        $neededWeight = $target - $barbell;
        $perSide = $neededWeight / 2.0;

        // Standard plate denominations in KG and LBS
        $denominations = $unit === 'lbs'
            ? [
                ['weight' => 45.0, 'color' => '#EF4444', 'label' => '45 lbs (Red)'],
                ['weight' => 35.0, 'color' => '#3B82F6', 'label' => '35 lbs (Blue)'],
                ['weight' => 25.0, 'color' => '#EAB308', 'label' => '25 lbs (Yellow)'],
                ['weight' => 10.0, 'color' => '#22C55E', 'label' => '10 lbs (Green)'],
                ['weight' => 5.0,  'color' => '#FFFFFF', 'label' => '5 lbs (White)', 'textDark' => true],
                ['weight' => 2.5,  'color' => '#1F2937', 'label' => '2.5 lbs (Black)'],
              ]
            : [
                ['weight' => 25.0, 'color' => '#EF4444', 'label' => '25 kg (Red)'],
                ['weight' => 20.0, 'color' => '#3B82F6', 'label' => '20 kg (Blue)'],
                ['weight' => 15.0, 'color' => '#EAB308', 'label' => '15 kg (Yellow)'],
                ['weight' => 10.0, 'color' => '#22C55E', 'label' => '10 kg (Green)'],
                ['weight' => 5.0,  'color' => '#F3F4F6', 'label' => '5 kg (White)', 'textDark' => true],
                ['weight' => 2.5,  'color' => '#1F2937', 'label' => '2.5 kg (Black)'],
                ['weight' => 1.25, 'color' => '#9CA3AF', 'label' => '1.25 kg (Chrome)', 'textDark' => true],
              ];

        $platesPerSide = [];
        $remaining = $perSide;

        foreach ($denominations as $denom) {
            $count = (int) floor(($remaining + 0.001) / $denom['weight']);
            if ($count > 0) {
                $platesPerSide[] = [
                    'weight' => $denom['weight'],
                    'count' => $count,
                    'color' => $denom['color'],
                    'label' => $denom['label'],
                    'textDark' => $denom['textDark'] ?? false,
                ];
                $remaining -= ($count * $denom['weight']);
            }
        }

        $totalLoaded = $barbell + (($perSide - $remaining) * 2);

        return response()->json([
            'success' => true,
            'target' => $target,
            'barbell' => $barbell,
            'unit' => $unit,
            'platesPerSide' => $platesPerSide,
            'totalLoaded' => round($totalLoaded, 2),
            'remainder' => round($remaining * 2, 2)
        ]);
    }

    /**
     * API: Navy Body Composition Calculator simulation.
     */
    public function calculateBodyComp(Request $request)
    {
        $gender = $request->query('gender', 'male');
        $height = floatval($request->query('height', 175));
        $weight = floatval($request->query('weight', 75));
        $waist = floatval($request->query('waist', 82));
        $neck = floatval($request->query('neck', 38));
        $hip = floatval($request->query('hip', 95));

        if ($height <= 0 || $weight <= 0 || $neck <= 0) {
            return response()->json(['error' => 'Invalid biometric measurements.'], 422);
        }

        if ($gender === 'male') {
            $denom = $waist - $neck;
            if ($denom <= 0) $denom = 1;
            $bf = 495 / (1.0324 - 0.19077 * log10($denom) + 0.15456 * log10($height)) - 450;
        } else {
            $denom = ($waist + $hip) - $neck;
            if ($denom <= 0) $denom = 1;
            $bf = 495 / (1.29579 - 0.35004 * log10($denom) + 0.22100 * log10($height)) - 450;
        }

        $bf = max(4.0, min(50.0, round($bf, 1)));
        $fatMass = round($weight * ($bf / 100), 1);
        $leanMass = round($weight - $fatMass, 1);

        $category = 'Fitness';
        if ($gender === 'male') {
            if ($bf < 6) $category = 'Essential Fat';
            elseif ($bf < 14) $category = 'Athletes';
            elseif ($bf < 18) $category = 'Fitness (Optimal)';
            elseif ($bf < 25) $category = 'Average';
            else $category = 'Overweight';
        } else {
            if ($bf < 14) $category = 'Essential Fat';
            elseif ($bf < 21) $category = 'Athletes';
            elseif ($bf < 25) $category = 'Fitness (Optimal)';
            elseif ($bf < 32) $category = 'Average';
            else $category = 'Overweight';
        }

        return response()->json([
            'bodyFatPct' => $bf,
            'fatMassKg' => $fatMass,
            'leanMassKg' => $leanMass,
            'category' => $category,
            'weightKg' => $weight
        ]);
    }
}
