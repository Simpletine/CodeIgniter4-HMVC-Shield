<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) 2021 CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Simpletine\HMVCShield\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Publishes package views into the user's app directory.
 *
 * Two distinct operations (can be combined):
 *   1. --copy-views  : Copies the entire package Views/ directory to the target
 *                      path, then records the path in HMVCPaths so stn_view()
 *                      resolves views from the published location.
 *   2. --update-auth : Patches app/Config/Auth.php to use AdminLTE login/register
 *                      views (legacy behaviour, on by default when no flags given).
 *
 * Usage:
 *   php spark publish:views                        Both operations (default)
 *   php spark publish:views --copy-views           Views copy only
 *   php spark publish:views --update-auth          Auth.php patch only
 *   php spark publish:views --path=app/Views/stn   Custom destination path
 *   php spark publish:views --force                Overwrite without prompt
 *   php spark publish:views --backup               Backup Auth.php before modifying
 */
class PublishViews extends BaseCommand
{
    /**
     * @var string
     */
    protected $group = 'SimpleTine';

    /**
     * @var string
     */
    protected $name = 'publish:views';

    /**
     * @var string
     */
    protected $description = 'Publishes package views to app/ and optionally patches Auth.php for AdminLTE views.';

    /**
     * @var string
     */
    protected $usage = 'publish:views [options]';

    /**
     * @var array<string, string>
     */
    protected $arguments = [];

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--copy-views'  => 'Copy the entire package Views/ directory to the destination path',
        '--update-auth' => 'Patch Auth.php to use AdminLTE login/register views',
        '--path'        => 'Destination directory for views (relative to ROOTPATH, default: app/Views/simpletine)',
        '--force'       => 'Overwrite existing files without asking',
        '--backup'      => 'Create a backup of Auth.php before modifying it',
    ];

    /**
     * Execute the command.
     *
     * @param list<string> $params
     */
    public function run(array $params): void
    {
        $doCopyViews  = CLI::getOption('copy-views') !== null;
        $doUpdateAuth = CLI::getOption('update-auth') !== null;

        // When neither flag is given, perform both operations
        if (! $doCopyViews && ! $doUpdateAuth) {
            $doCopyViews  = true;
            $doUpdateAuth = true;
        }

        if ($doCopyViews) {
            $this->copyViews();
        }

        if ($doUpdateAuth) {
            $this->updateAuthConfig();
        }
    }

    // -----------------------------------------------------------------------
    // Copy views
    // -----------------------------------------------------------------------

    /**
     * Copies all files from the package Views/ directory to the target path.
     * Records the destination in HMVCPaths config so stn_view() uses it.
     */
    private function copyViews(): void
    {
        CLI::write('=== Publishing Views ===', 'cyan');

        // Determine destination
        $relPath = CLI::getOption('path');
        if (! is_string($relPath) || $relPath === '') {
            $relPath = 'app/Views';
        }

        $destAbs = rtrim(ROOTPATH, '/\\') . DIRECTORY_SEPARATOR
            . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relPath, '/\\'));

        CLI::write("Destination: {$destAbs}", 'white');

        $sourceAbs = realpath(__DIR__ . '/../Views');
        if ($sourceAbs === false || ! is_dir($sourceAbs)) {
            CLI::error('Package Views/ directory not found. Cannot publish views.');

            return;
        }

        $force = CLI::getOption('force') !== null;

        if (is_dir($destAbs) && ! $force) {
            $confirm = CLI::prompt(
                "Directory already exists: {$destAbs}\nOverwrite files?",
                ['y', 'n'],
            );
            if (strtolower($confirm) !== 'y') {
                CLI::write('View publishing skipped.', 'yellow');

                return;
            }
        }

        $this->copyDirectory($sourceAbs, $destAbs);

        // Record the override path in the user-level HMVCPaths config (if published)
        $this->recordViewsOverridePath($relPath . '/');

        CLI::write('Views published successfully.', 'green');
        CLI::newLine();
        CLI::write('  To activate the override, ensure HMVCPaths::$viewsOverridePath is set:', 'yellow');
        CLI::write("  public string \$viewsOverridePath = '{$relPath}/';", 'white');
        CLI::write('  Run `php spark publish:config` to publish Config/HMVCPaths.php to app/Config/.', 'yellow');
        CLI::newLine();
    }

    /**
     * Recursively copies a directory tree.
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        /** @var SplFileInfo $item */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        ) as $item) {
            $targetPath = $dest . DIRECTORY_SEPARATOR . substr($item->getPathname(), strlen($source) + 1);

            if ($item->isDir()) {
                if (! is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                if (copy($item->getPathname(), $targetPath)) {
                    CLI::write('  Copied: ' . substr($item->getPathname(), strlen($source) + 1), 'green');
                } else {
                    CLI::error('  Failed: ' . substr($item->getPathname(), strlen($source) + 1));
                }
            }
        }
    }

    /**
     * Updates the user-level HMVCPaths config (if it exists in app/Config/) with
     * the new viewsOverridePath value so stn_view() can pick it up.
     */
    private function recordViewsOverridePath(string $relPath): void
    {
        $configFile = APPPATH . 'Config/HMVCPaths.php';

        if (! file_exists($configFile)) {
            // Config not yet published — skip silently; user can publish:config later
            return;
        }

        $content = file_get_contents($configFile);
        if ($content === false) {
            return;
        }

        // Replace the viewsOverridePath value
        $updated = preg_replace(
            '/public string \$viewsOverridePath\s*=\s*\'[^\']*\';/',
            "public string \$viewsOverridePath = '" . addslashes($relPath) . "';",
            $content,
        );

        if ($updated !== null && $updated !== $content) {
            file_put_contents($configFile, $updated);
            CLI::write('Updated HMVCPaths::$viewsOverridePath in app/Config/HMVCPaths.php', 'green');
        }
    }

    // -----------------------------------------------------------------------
    // Auth.php patch (legacy behaviour, kept for backward compatibility)
    // -----------------------------------------------------------------------

    /**
     * Patches app/Config/Auth.php to use AdminLTE login/register views.
     */
    private function updateAuthConfig(): void
    {
        CLI::write('=== Patching Auth.php for AdminLTE views ===', 'cyan');

        $filePath = APPPATH . 'Config/Auth.php';

        if (! file_exists($filePath)) {
            CLI::error("Auth.php not found: {$filePath}");
            CLI::write('Ensure Shield is installed and Auth.php exists in app/Config/.', 'yellow');

            return;
        }

        if (CLI::getOption('backup') !== null) {
            $backup = $filePath . '.backup.' . date('Y-m-d_His');
            if (copy($filePath, $backup)) {
                CLI::write("Backup: {$backup}", 'green');
            }
        }

        $authConfig = config('Simpletine\HMVCShield\Config\Auth');
        if ($authConfig === null) {
            CLI::error('Could not load SimpleTine Auth configuration.');

            return;
        }

        $search = [
            '\CodeIgniter\Shield\Views\login',
            '\CodeIgniter\Shield\Views\register',
        ];
        $replace = array_values($authConfig->views);

        $content = file_get_contents($filePath);
        if ($content === false) {
            CLI::error("Failed to read: {$filePath}");

            return;
        }

        $updated = str_replace($search, $replace, $content);

        if ($updated === $content) {
            CLI::write('No changes needed. Auth.php already uses AdminLTE views.', 'yellow');

            return;
        }

        if (file_put_contents($filePath, $updated) !== false) {
            CLI::write('Auth.php patched successfully.', 'green');
            CLI::write("File: {$filePath}", 'white');
        } else {
            CLI::error('Failed to write Auth.php.');
        }

        CLI::newLine();
    }
}
