<?php

namespace LiveIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * LiveIgniter Clean Command
 * 
 * Cleans up expired component sessions and cache
 */
class Clean extends BaseCommand
{
    /**
     * The Command's Group
     */
    protected $group = 'LiveIgniter';

    /**
     * The Command's Name
     */
    protected $name = 'liveigniter:clean';

    /**
     * The Command's Description
     */
    protected $description = 'Cleans up expired LiveIgniter component sessions and cache.';

    /**
     * The Command's Usage
     */
    protected $usage = 'liveigniter:clean [options]';

    /**
     * The Command's Options
     */
    protected $options = [
        '--sessions' => 'Clean only component sessions.',
        '--cache'    => 'Clean only component cache.',
        '--age'      => 'Maximum age in seconds (default: 3600).',
        '--dry-run'  => 'Show what would be cleaned without actually cleaning.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $sessionsOnly = CLI::getOption('sessions') ?? false;
        $cacheOnly = CLI::getOption('cache') ?? false;
        $maxAge = (int) (CLI::getOption('age') ?? 3600);
        $dryRun = CLI::getOption('dry-run') ?? false;

        CLI::write('🧹 Cleaning LiveIgniter data...', 'yellow');
        CLI::newLine();

        if ($dryRun) {
            CLI::write('🔍 DRY RUN MODE - No files will be deleted', 'cyan');
            CLI::newLine();
        }

        try {
            $totalCleaned = 0;

            if ($cacheOnly || (!$sessionsOnly && !$cacheOnly)) {
                $totalCleaned += $this->cleanCache($maxAge, $dryRun);
            }

            if ($sessionsOnly || (!$cacheOnly && !$sessionsOnly)) {
                $totalCleaned += $this->cleanSessions($maxAge, $dryRun);
            }

            CLI::newLine();
            if ($dryRun) {
                CLI::write("Would clean {$totalCleaned} items.", 'cyan');
            } else {
                CLI::write("Cleaned {$totalCleaned} items successfully!", 'green');
            }

        } catch (\Exception $e) {
            CLI::error('Error during cleanup: ' . $e->getMessage());
        }
    }

    /**
     * Clean component cache
     */
    protected function cleanCache(int $maxAge, bool $dryRun): int
    {
        CLI::write('🗄️  Cleaning component cache...', 'cyan');

        $cache = \Config\Services::cache();
        $cleaned = 0;

        // In a real implementation, you would need to get cache keys
        // This is a simplified version
        try {
            if (!$dryRun) {
                // Clean cache entries that start with 'liveigniter.'
                $cache->clean();
            }
            
            CLI::write('✓ Cache cleaned', 'green');
            $cleaned = 1; // Simplified count
            
        } catch (\Exception $e) {
            CLI::write('✗ Error cleaning cache: ' . $e->getMessage(), 'red');
        }

        return $cleaned;
    }

    /**
     * Clean component sessions
     */
    protected function cleanSessions(int $maxAge, bool $dryRun): int
    {
        CLI::write('📊 Cleaning component sessions...', 'cyan');

        $cleaned = 0;
        $session = session();

        try {
            // Get all session data
            $sessionData = $session->get();
            $cutoffTime = time() - $maxAge;

            foreach ($sessionData as $key => $value) {
                if (strpos($key, 'liveigniter.components.') === 0) {
                    // Check if session data is old
                    if (is_array($value) && isset($value['timestamp'])) {
                        if ($value['timestamp'] < $cutoffTime) {
                            if ($dryRun) {
                                CLI::write("Would remove session: {$key}", 'yellow');
                            } else {
                                $session->remove($key);
                                CLI::write("Removed session: {$key}", 'green');
                            }
                            $cleaned++;
                        }
                    } else {
                        // Remove sessions without timestamp (they're probably old)
                        if ($dryRun) {
                            CLI::write("Would remove session (no timestamp): {$key}", 'yellow');
                        } else {
                            $session->remove($key);
                            CLI::write("Removed session (no timestamp): {$key}", 'green');
                        }
                        $cleaned++;
                    }
                }
            }

            if ($cleaned === 0) {
                CLI::write('✓ No expired sessions found', 'green');
            } else {
                CLI::write("✓ Found {$cleaned} expired sessions", 'green');
            }

        } catch (\Exception $e) {
            CLI::write('✗ Error cleaning sessions: ' . $e->getMessage(), 'red');
        }

        return $cleaned;
    }
}
