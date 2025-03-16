#!/usr/bin/env php
<?php
/**
 * Generate User CLI Script
 * 
 * Creates a new user with an AI-generated username in the Flarum database
 */

/**
 * Usage Examples:
 * 
 * Create a regular member (default):
 * php generate_user.php
 * 
 * Create a moderator:
 * php generate_user.php --group=mod
 * 
 * Additional options:
 * --email=user@example.com    Specify an email (otherwise auto-generated)
 * --password=secret123        Specify a password (otherwise auto-generated)
 * --domain=mysite.com         Specify domain for auto-generated emails (default: example.com)
 * --dry-run                   Show what would be created without actually creating the user
 * 
 * Examples with multiple options:
 * php generate_user.php --group=mod --email=moderator@example.com --dry-run
 * php generate_user.php --domain=myforum.com --password=securepass
 */

// Define the application path
define('APP_PATH', __DIR__ . '/..');

// Set up autoloading
spl_autoload_register(function ($className) {
    // Convert namespace separators to directory separators
    $className = str_replace('\\', '/', $className);
    $filePath = APP_PATH . '/' . $className . '.php';
    
    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    }
    return false;
});

// Initialize bootstrap
require_once APP_PATH . '/Core/Bootstrap.php';
\Core\Bootstrap::init();

// Import required classes
use Utils\OpenRouter;
use Models\User;

// Parse command line options
$options = getopt('', ['email::', 'password::', 'domain::', 'dry-run', 'group::']);
$email = $options['email'] ?? null;
$password = $options['password'] ?? null;
$domain = $options['domain'] ?? 'example.com';
$dryRun = isset($options['dry-run']);
$requestedGroup = isset($options['group']) ? strtolower($options['group']) : 'member';

echo "-----------------------------------------------------\n";
echo "User Generator Tool\n";
echo "-----------------------------------------------------\n";

// Initialize database connection
try {
    global $pdo;
    if (!isset($pdo) || !($pdo instanceof \PDO)) {
        throw new \Exception("No database connection available");
    }
    
    echo "✓ Database connection established\n";
} catch (\Exception $e) {
    echo "Error: Failed to connect to the database: " . $e->getMessage() . "\n";
    exit(1);
}

// Get group ID - look up by name_singular instead of hardcoding IDs
$groupName = ($requestedGroup === 'mod') ? 'Mod' : 'Member';

// Look up the group ID from the database
try {
    $stmt = $pdo->prepare("SELECT id FROM groups WHERE name_singular = :name");
    $stmt->bindValue(':name', $groupName);
    $stmt->execute();
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($result && isset($result['id'])) {
        $groupId = (int)$result['id'];
        echo "Found group ID for {$groupName}: {$groupId}\n";
    } else {
        // Fail if the group doesn't exist
        throw new \Exception("Group '{$groupName}' not found in the database. Cannot continue.");
    }
} catch (\Exception $e) {
    // Exit with error message
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "User will be assigned to group: {$groupName}\n";

// Initialize models
$userModel = new User($pdo);
$openRouter = new OpenRouter();

// If email is not provided, generate one
if (empty($email)) {
    // Generate a random string for the email
    $randomString = bin2hex(random_bytes(4));
    $email = "user_{$randomString}@{$domain}";
    echo "Generated email: {$email}\n";
}

// If password is not provided, generate one
if (empty($password)) {
    // Generate a random password
    $password = bin2hex(random_bytes(8));
    echo "Generated password: {$password}\n";
}

// Get forum description from settings table for context
$forumDescription = "a general forum community";
try {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'forum_description'");
    $stmt->execute();
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($result && !empty($result['value'])) {
        $forumDescription = $result['value'];
        echo "Forum description: {$forumDescription}\n";
    } else {
        echo "No forum description found, using default.\n";
    }
} catch (\Exception $e) {
    echo "Error fetching forum description: " . $e->getMessage() . "\n";
    // Continue with default description
}

// Define username format examples for Members
$memberUsernameFormats = [
    // Original formats
    "Simple word combinations (e.g., HappyCat, WildTiger)",
    "Adjective + Noun related to the theme (e.g., CoolCat, SleepyKitten)",
    "Verb + Noun (e.g., PurringCat, LeapingTiger)",
    "Playful abbreviations (e.g., CatLvr, KitFan)",
    "Words with underscores (e.g., cool_cat, happy_kitten)",
    "Letter/number substitutions (e.g., C4tL0v3r, F3lin3Fun)",
    "Theme-related puns (e.g., PawsomeKitty, CatsMeow)",
    "Alliterative names (e.g., CuriousCat, MeowingMittens)",
    "Fantasy-inspired (e.g., WhiskerWizard, FurryFighter)",
    "Pop culture references that fit the theme (e.g., GarfieldLover, SimbaStyle)",
    "Profession + theme-related object (e.g., CatChef, KittenDoctor)",
    "Fictional character inspired (e.g., GarfieldFan, SimbaLover)",
    "Location + theme-related noun (e.g., JungleCat, ForestFeline)",
    "Emotion + theme-related action (e.g., HappyPurr, JoyfulMeow)",
    "Color + theme-related object (e.g., RedPaw, BlackWhiskers)",
    "Season + theme-related term (e.g., SummerCat, WinterKitten)",
    "Food + theme-related term (e.g., PizzaCat, TacoKitten)",
    "Hobby + theme-related term (e.g., GamerCat, RunnerKitten)",
    "Mythical creature hybrid (e.g., DragonCat, PhoenixKitten)",
    "Time-related + theme (e.g., NightCat, DawnPurr)",
    
    // New formats with personal names
    "First name + Theme (e.g., JaneCatLover, BobTheCat)",
    "Name initials + Theme word (e.g., JDKitty, ABFeline)",
    "Shortened name + Theme (e.g., SamThePurr, AlexCatNip)",
    "Person's nickname + Theme element (e.g., BuddyCatTales, DudeWithWhiskers)",
    "Name pun + Theme (e.g., CatherinePurr, TomCat)",
    
    // Additional creative formats
    "Musical terms + Theme (e.g., MelodyCat, RhythmPaws)",
    "Scientific terms + Theme (e.g., QuantumKitten, AtomicPurr)",
    "Historical references + Theme (e.g., CleopatraCat, NapoleonPaws)",
    "Sports terminology + Theme (e.g., FastballFeline, GoalkeeperKitty)",
    "Technology + Theme (e.g., CyberKitten, DigitalWhiskers)",
    "Astronomical terms + Theme (e.g., GalaxyCat, CosmicPurr)",
    "Literary references + Theme (e.g., HamletKitty, MacbethPaws)",
    "Weather phenomena + Theme (e.g., ThunderCat, StormyPurr)",
    "Royal/Noble titles + Theme (e.g., KingOfCats, LordWhiskers)",
    "Architectural terms + Theme (e.g., CastleCat, SkyscraperKitty)",
    "Geographical features + Theme (e.g., MountainCat, RiverKitten)",
    "Transportation + Theme (e.g., RocketCat, SailboatKitty)",
    "Holiday + Theme (e.g., ChristmasCat, HalloweenPurr)",
    "Magical elements + Theme (e.g., EnchantedWhiskers, SpellboundCat)",
    "Plant life + Theme (e.g., CactusCat, RosePaws)",
    "Famous landmarks + Theme (e.g., EiffelCat, PyramidPaws)",
    "Art movements + Theme (e.g., SurrealCat, ImpressionistKitten)",
    "Currency + Theme (e.g., BitcoinCat, DollarWhiskers)",
    "Elements + Theme (e.g., FireCat, WaterKitten)",
    "Fabrics/materials + Theme (e.g., SilkPaws, VelvetKitty)",
    "Precious stones + Theme (e.g., DiamondCat, RubyWhiskers)",
    "Tools + Theme (e.g., HammerCat, WrenchKitten)",
    "Dance styles + Theme (e.g., TangoCat, BalletPaws)",
    "Coffee types + Theme (e.g., EspressoCat, LattePurr)",
    "Mathematical terms + Theme (e.g., AlgebraCat, CalculusPaws)",
    
    // Common/typical username formats
    "First name + Last name (e.g., JohnSmith, MaryJones)",
    "First name + Numbers (e.g., Mark123, Susan456)",
    "First name + Birth year (e.g., David1987, Jessica1995)",
    "Single common noun (e.g., Reader, Explorer)",
    "Adjective + Number (e.g., Happy247, Cool365)",
    "Simple descriptive term (e.g., Catlover, Kittenowner)",
    "Generic term + Underscore + Number (e.g., user_42, member_123)",
    "Common animal name (e.g., Tiger, Fox)",
    "Hobby or interest + Number (e.g., Fishing22, Cooking101)",
    "Job or profession (e.g., Teacher, Engineer)",
    "Color + Animal (e.g., BlueBird, RedFox)",
    "Random word + Random numbers (e.g., Banana57, Basket42)",
    "Two common words (e.g., WaterBottle, SunGlasses)",
    "Single letter + Random numbers (e.g., J12345, M98765)",
    "Common phrase abbreviation (e.g., OMG123, LOLplayer)"
];

// Define username format examples for Moderators
$modUsernameFormats = [
    // Authority-themed usernames
    "Guardian + Theme (e.g., GuardianOfCats, FelineProtector)",
    "Moderator terms (e.g., CatModerator, FelineMod)",
    "Leadership terms + Theme (e.g., CatLeader, FelineGuide)",
    "Referee/Judge terms + Theme (e.g., CatReferee, PawJustice)",
    "Law enforcement + Theme (e.g., PawPatrol, WhiskerWatch)",
    "Superhero + Theme (e.g., CatHero, SuperWhiskers)",
    "Security terms + Theme (e.g., SecureCat, WhiskerGuard)",
    "Authority titles + Theme (e.g., CaptainCat, CommanderPaws)",
    "Diplomatic terms + Theme (e.g., AmbassadorCat, PawDiplomat)",
    "Guardian creatures + Theme (e.g., SphinxGuardian, CerberusCat)",
    
    // Professional-themed usernames
    "Academic titles + Theme (e.g., ProfessorCat, DrWhiskers)",
    "Executive titles + Theme (e.g., CEOofCats, DirectorFeline)",
    "Scientific titles + Theme (e.g., DrFelix, ResearcherCat)",
    "Tech expertise + Theme (e.g., TechCat, FelineEngineer)",
    "Wisdom terms + Theme (e.g., WiseCat, SagePaws)",
    "Expert terms + Theme (e.g., CatExpert, FelineAuthority)",
    "Mentor terms + Theme (e.g., CatMentor, WhiskerGuru)",
    "Knight/Noble terms + Theme (e.g., KnightOfCats, LordPaws)",
    "Guide/Helper terms + Theme (e.g., FelineGuide, CatHelper)",
    
    // Community-themed usernames
    "Community terms + Theme (e.g., CatCommunity, FelineFriends)",
    "Support terms + Theme (e.g., CatSupport, WhiskerHelper)",
    "Service terms + Theme (e.g., ServingCats, FelineService)",
    "Welcome terms + Theme (e.g., WelcomingPaws, GreeterCat)",
    "Harmony terms + Theme (e.g., HarmonyCat, PeacefulWhiskers)",
    "Unity terms + Theme (e.g., UnitedPaws, FelineUnity)",
    
    // Power/Authority metaphors
    "Celestial bodies + Theme (e.g., SunCat, MoonWhiskers)",
    "Mythical leaders + Theme (e.g., ZeusCat, OdinPaws)",
    "Majestic animals + Theme (e.g., LionMod, EagleCat)",
    "Elemental power + Theme (e.g., ThunderMod, StormCat)",
    "Legendary figures + Theme (e.g., ArthurCat, ExcaliburPaws)",
    "Ancient rulers + Theme (e.g., PharaohCat, EmperorWhiskers)",
    
    // Professional and formal patterns
    "Formal prefix + Name (e.g., Mod_James, Admin_Sarah)",
    "Official + Random name (e.g., OfficialTiger, VerifiedEagle)",
    "Staff + Random term (e.g., StaffLion, TeamFalcon)",
    "Formal title abbreviation (e.g., Mod_JS, ADM_Taylor)",
    "Forum + Role (e.g., ForumModerator, CommunityAdmin)",
    "Color + Staff title (e.g., BlueModCat, GreenTeamPaws)"
];

// Select the appropriate username format array based on the group
$usernameFormats = ($groupName === 'Mod') ? $modUsernameFormats : $memberUsernameFormats;

// Randomly select 3 format examples
shuffle($usernameFormats);
$selectedFormats = array_slice($usernameFormats, 0, 3);

// Generate a username using AI
try {
    echo "Generating username using AI...\n";
    
    $prompt = "Generate THREE creative, unique usernames for a forum user on a forum about: \"{$forumDescription}\".

Each username should be:
1. Between 5-15 characters long
2. Not contain any spaces (can use underscores instead)
3. Not include any offensive or inappropriate terms
4. Be memorable and distinct
5. Be thematically appropriate for the forum topic

Consider using one of these username formats as inspiration (but create completely original usernames, DO NOT use the example usernames provided below):
- {$selectedFormats[0]}
- {$selectedFormats[1]}
- {$selectedFormats[2]}

IMPORTANT: Your usernames must be completely original. DO NOT return any of the example usernames like \"HappyCat\", \"CoolCat\", etc. Create new, unique usernames based on the format patterns but with different words.

Please provide your response in valid JSON format as follows:
{
  \"usernames\": [
    \"username1\",
    \"username2\",
    \"username3\"
  ]
}

Do not include any explanation or additional text, just the JSON.";
    
    $response = $openRouter->generate($prompt, 'content', [
        'temperature' => 1,  // Higher temperature for more creativity and variety
        'max_tokens' => 1000,    // We need enough tokens for the JSON response
        'request_source' => 'generate_user_cli'
    ]);
    
    // Extract the generated content
    $content = $openRouter->extractContent($response);
    
    // Parse JSON response
    $jsonData = json_decode($content, true);
    $usernameOptions = [];
    
    if ($jsonData && isset($jsonData['usernames']) && is_array($jsonData['usernames'])) {
        $usernameOptions = $jsonData['usernames'];
        echo "Generated " . count($usernameOptions) . " username options.\n";
    } else {
        // Fallback if JSON parsing fails - extract any string that looks like a username
        preg_match_all('/"([a-zA-Z0-9_]{5,15})"/', $content, $matches);
        if (!empty($matches[1])) {
            $usernameOptions = array_slice($matches[1], 0, 3);
            echo "JSON parsing failed. Extracted " . count($usernameOptions) . " username options from text.\n";
        } else {
            throw new \Exception("Failed to parse usernames from AI response.");
        }
    }
    
    if (empty($usernameOptions)) {
        throw new \Exception("No valid usernames found in AI response.");
    }
    
    // Clean up each username and check if it's available
    $username = null;
    $tried = [];
    
    foreach ($usernameOptions as $option) {
        // Clean up the username
        $cleanUsername = trim($option);
        $cleanUsername = preg_replace('/["\'`]/', '', $cleanUsername);  // Remove quotes
        $cleanUsername = preg_replace('/\s+/', '_', $cleanUsername);    // Replace spaces with underscores
        
        // Ensure length requirements
        if (strlen($cleanUsername) < 5) {
            $cleanUsername .= '_' . bin2hex(random_bytes(2));
        } elseif (strlen($cleanUsername) > 15) {
            $cleanUsername = substr($cleanUsername, 0, 15);
        }
        
        $tried[] = $cleanUsername;
        
        // Check if username is available
        $isAvailable = $userModel->isUsernameAvailable($cleanUsername);
        
        echo "Checking username: {$cleanUsername} - " . ($isAvailable ? "Available" : "Already taken") . "\n";
        
        if ($isAvailable) {
            $username = $cleanUsername;
            break; // Found an available username
        }
    }
    
    // If none of the AI-generated usernames are available, generate a random one
    if ($username === null) {
        $username = 'user_' . bin2hex(random_bytes(4));
        echo "All generated usernames are taken. Using fallback: {$username}\n";
    } else {
        echo "Using username: {$username}\n";
    }
    
    // For debugging, show all options that were tried
    if (count($tried) > 1) {
        echo "All generated options: " . implode(", ", $tried) . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error generating username: " . $e->getMessage() . "\n";
    echo "Using fallback username instead.\n";
    
    // Fallback: generate a simple username without AI
    $username = 'user_' . bin2hex(random_bytes(4));
    echo "Fallback username: {$username}\n";
}

// Display the user information
echo "\nUser Information:\n";
echo "- Username: {$username}\n";
echo "- Email: {$email}\n";
echo "- Password: {$password}\n";
echo "- Joined Date: " . date('Y-m-d H:i:s') . "\n";

// If dry run, don't create the user
if ($dryRun) {
    echo "\nDry run mode: User will not be created in the database.\n";
    exit(0);
}

// Create the user in the database
try {
    $userData = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'is_email_confirmed' => 1,  // Set email as confirmed by default
        'joined_at' => date('Y-m-d H:i:s')
    ];
    
    $userId = $userModel->createUser($userData);
    
    if ($userId) {
        // Assign user to the specified group
        try {
            // Get current date/time for created_at
            $now = date('Y-m-d H:i:s');
            
            $stmt = $pdo->prepare("INSERT INTO group_user (user_id, group_id, created_at) VALUES (:user_id, :group_id, :created_at)");
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':group_id', $groupId, \PDO::PARAM_INT);
            $stmt->bindValue(':created_at', $now);
            $stmt->execute();
            
            echo "\n✓ User created successfully with ID: {$userId}\n";
            echo "✓ User assigned to group: {$groupName}\n";
        } catch (\Exception $e) {
            echo "\nWarning: User created but failed to assign to group: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\nError: Failed to create user.\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "\nError creating user: " . $e->getMessage() . "\n";
    exit(1);
} 