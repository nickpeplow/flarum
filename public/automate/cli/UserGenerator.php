<?php
/**
 * UserGenerator Class
 * 
 * A class for generating users with AI-created usernames
 * Can be used as a standalone CLI or included in other scripts
 */

namespace cli;

use Utils\OpenRouter;
use Models\User;

class UserGenerator {
    private $pdo;
    private $openRouter;
    private $dryRun = false;
    
    /**
     * Constructor
     *
     * @param \PDO $pdo Database connection
     * @param bool $dryRun Whether to run in dry-run mode
     */
    public function __construct($pdo, $dryRun = false) {
        $this->pdo = $pdo;
        $this->dryRun = $dryRun;
        
        // Initialize OpenRouter
        $this->openRouter = new OpenRouter();
    }
    
    /**
     * Get group ID by name
     *
     * @param string $groupName Name of the group
     * @return int Group ID
     */
    public function getGroupId($groupName) {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM groups WHERE name_singular = :name");
            $stmt->bindValue(':name', $groupName);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result && isset($result['id'])) {
                $groupId = (int)$result['id'];
                echo "Found group ID for {$groupName}: {$groupId}\n";
                return $groupId;
            } else {
                throw new \Exception("Group '{$groupName}' not found in the database.");
            }
        } catch (\Exception $e) {
            throw new \Exception("Error getting group ID: " . $e->getMessage());
        }
    }
    
    /**
     * Get forum description from settings
     *
     * @return string Forum description
     */
    public function getForumDescription() {
        $forumDescription = "a general forum community";
        try {
            $stmt = $this->pdo->prepare("SELECT value FROM settings WHERE `key` = 'forum_description'");
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
        
        return $forumDescription;
    }
    
    /**
     * Generate a username using AI
     *
     * @param string $userType Type of user (member or mod)
     * @return string Generated username
     */
    public function generateUsername($userType = 'member') {
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

        // Select the appropriate username format array based on the user type
        $usernameFormats = ($userType === 'mod') ? $modUsernameFormats : $memberUsernameFormats;

        // Randomly select 3 format examples
        shuffle($usernameFormats);
        $selectedFormats = array_slice($usernameFormats, 0, 3);
        
        $forumDescription = $this->getForumDescription();
        
        // Generate a username using AI
        try {
            echo "Generating username using AI...\n";
            
            // Load the prompt template
            $templatePath = __DIR__ . '/templates/username_prompt.tpl';
            
            if (!file_exists($templatePath)) {
                throw new \Exception("Template file not found: $templatePath");
            }
            
            // Load the template
            $prompt = file_get_contents($templatePath);
            
            // Replace placeholders
            $replacements = [
                '{{forum_description}}' => $forumDescription,
                '{{format_1}}' => $selectedFormats[0],
                '{{format_2}}' => $selectedFormats[1],
                '{{format_3}}' => $selectedFormats[2]
            ];
            
            $prompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);
            
            $response = $this->openRouter->generate($prompt, 'content', [
                'temperature' => 1,  // Higher temperature for more creativity and variety
                'max_tokens' => 1000,    // We need enough tokens for the JSON response
                'request_source' => 'generate_user_cli'
            ]);
            
            // Extract the generated content
            $content = $this->openRouter->extractContent($response);
            
            // Parse JSON response
            $jsonData = json_decode($content, true);
            $usernameOptions = [];
            
            if ($jsonData && isset($jsonData['usernames']) && is_array($jsonData['usernames'])) {
                $usernameOptions = $jsonData['usernames'];
                echo "Generated " . count($usernameOptions) . " username options.\n";
            } else {
                // Clean up JSON if needed - first strip markdown code blocks if present
                if (preg_match('/```(?:json)?\s*({.*})\s*```/s', $content, $matches)) {
                    $content = $matches[1];
                    // Try parsing again
                    $jsonData = json_decode($content, true);
                    if ($jsonData && isset($jsonData['usernames']) && is_array($jsonData['usernames'])) {
                        $usernameOptions = $jsonData['usernames'];
                        echo "Generated " . count($usernameOptions) . " username options after cleanup.\n";
                    } else {
                        // As a last resort, try regex extraction
                        preg_match_all('/\"usernames\":\s*\[\s*\"([a-zA-Z0-9_]{5,15})\"/', $content, $matches1);
                        preg_match_all('/\"([a-zA-Z0-9_]{5,15})\"\s*,?\s*\"([a-zA-Z0-9_]{5,15})\"/', $content, $matches2);
                        preg_match_all('/\"([a-zA-Z0-9_]{5,15})\"\s*\]/', $content, $matches3);
                        
                        $allMatches = array_merge(
                            $matches1[1] ?? [],
                            $matches2[1] ?? [], 
                            $matches2[2] ?? [], 
                            $matches3[1] ?? []
                        );
                        
                        // Remove duplicates and limit to 3
                        $usernameOptions = array_slice(array_unique($allMatches), 0, 3);
                        echo "JSON parsing failed. Extracted " . count($usernameOptions) . " username options from text.\n";
                        
                        if (empty($usernameOptions)) {
                            throw new \Exception("Failed to parse usernames from AI response.");
                        }
                    }
                }
            }
            
            if (empty($usernameOptions)) {
                throw new \Exception("No valid usernames found in AI response.");
            }
            
            // Clean up each username and check if it's available
            $userModel = new User($this->pdo);
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
            
            return $username;
            
        } catch (\Exception $e) {
            echo "Error generating username: " . $e->getMessage() . "\n";
            echo "Using fallback username instead.\n";
            
            // Fallback: generate a simple username without AI
            $username = 'user_' . bin2hex(random_bytes(4));
            echo "Fallback username: {$username}\n";
            
            return $username;
        }
    }
    
    /**
     * Create a user with an AI-generated username
     *
     * @param array $options User creation options
     * @return array Result with user data
     */
    public function createUser($options = []) {
        // Default options
        $email = $options['email'] ?? null;
        $password = $options['password'] ?? null;
        $domain = $options['domain'] ?? 'example.com';
        $requestedGroup = isset($options['group']) ? strtolower($options['group']) : 'member';
        
        // Get group ID
        $groupName = ($requestedGroup === 'mod') ? 'Mod' : 'Member';
        $groupId = $this->getGroupId($groupName);
        
        echo "User will be assigned to group: {$groupName}\n";
        
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
        
        // Generate username
        $username = $this->generateUsername($requestedGroup);
        
        // Display the user information
        echo "\nUser Information:\n";
        echo "- Username: {$username}\n";
        echo "- Email: {$email}\n";
        echo "- Password: {$password}\n";
        echo "- Joined Date: " . date('Y-m-d H:i:s') . "\n";
        
        // If dry run, don't create the user
        if ($this->dryRun) {
            echo "\nDry run mode: User will not be created in the database.\n";
            return [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'group' => $groupName,
                'dry_run' => true,
                'success' => true
            ];
        }
        
        // Create the user in the database
        try {
            $userModel = new User($this->pdo);
            
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
                    
                    $stmt = $this->pdo->prepare("INSERT INTO group_user (user_id, group_id, created_at) VALUES (:user_id, :group_id, :created_at)");
                    $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
                    $stmt->bindValue(':group_id', $groupId, \PDO::PARAM_INT);
                    $stmt->bindValue(':created_at', $now);
                    $stmt->execute();
                    
                    echo "\n✓ User created successfully with ID: {$userId}\n";
                    echo "✓ User assigned to group: {$groupName}\n";
                    
                    return [
                        'user_id' => $userId,
                        'username' => $username,
                        'email' => $email,
                        'password' => $password,
                        'group' => $groupName,
                        'success' => true
                    ];
                    
                } catch (\Exception $e) {
                    echo "\nWarning: User created but failed to assign to group: " . $e->getMessage() . "\n";
                    
                    return [
                        'user_id' => $userId,
                        'username' => $username,
                        'email' => $email,
                        'password' => $password,
                        'error' => "User created but not assigned to group: " . $e->getMessage(),
                        'success' => false
                    ];
                }
            } else {
                echo "\nError: Failed to create user.\n";
                
                return [
                    'error' => "Failed to create user",
                    'success' => false
                ];
            }
        } catch (\Exception $e) {
            echo "\nError creating user: " . $e->getMessage() . "\n";
            
            return [
                'error' => $e->getMessage(),
                'success' => false
            ];
        }
    }
} 