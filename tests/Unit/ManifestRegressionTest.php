<?php

declare(strict_types=1);

namespace hypeJunction\Maps\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Static regression guards for hypeMaps 7.x migration fixes that the generic
 * failure-catalog guard (MigrationRegressionTest) does not express.
 *
 * These are source-level assertions — no Elgg boot required — because the
 * defects they guard are compile/parse-time or removed-constant fatals that
 * would crash a booted test before it could assert.
 */
final class ManifestRegressionTest extends TestCase
{
    private static function pluginRoot(): string
    {
        // tests/Unit -> tests -> plugin root
        return \dirname(__DIR__, 2);
    }

    private static function read(string $relative): string
    {
        $path = self::pluginRoot() . '/' . ltrim($relative, '/');
        self::assertFileExists($path);
        return (string) file_get_contents($path);
    }

    /**
     * 46d2ac6: the removed REFERER constant was renamed to REFERRER in both
     * actions. A bare `REFERER` (single R) resolves to the string "REFERER" on
     * 7.x (undefined-constant fatal under strict PHP 8), silently breaking the
     * redirect target of every action response.
     *
     * @return void
     */
    public function testActionsUseReferrerConstantNotReferer(): void
    {
        foreach (['actions/hypemaps/settings/save.php', 'actions/maps/geopositioning/update.php'] as $action) {
            $src = self::read($action);
            $this->assertMatchesRegularExpression(
                '/\bREFERRER\b/',
                $src,
                "{$action} should reference the REFERRER constant for its response redirect target",
            );
            $this->assertDoesNotMatchRegularExpression(
                '/\bREFERER\b/',
                $src,
                "{$action} still references the removed single-R REFERER constant (renamed to REFERRER)",
            );
        }
    }

    /**
     * 3dcf9d9: lib/{functions,hooks,events}.php must be require_once'd from the
     * TOP of elgg-plugin.php (above the `return [ ... ]`), so the namespaced
     * handler functions named in the 'events' array resolve at registration
     * time. composer autoload.files / Bootstrap require_once are both too late.
     *
     * @return void
     */
    public function testManifestRequiresLibFilesBeforeReturn(): void
    {
        $src = self::read('elgg-plugin.php');
        $returnPos = strpos($src, 'return [');
        $this->assertNotFalse($returnPos, 'elgg-plugin.php must return the plugin config array');

        foreach (['functions', 'hooks', 'events'] as $lib) {
            $needle = "require_once __DIR__ . '/lib/{$lib}.php';";
            $pos = strpos($src, $needle);
            $this->assertNotFalse($pos, "elgg-plugin.php must require_once lib/{$lib}.php");
            $this->assertLessThan(
                $returnPos,
                $pos,
                "require_once lib/{$lib}.php must appear before the return so handler functions resolve at registration time",
            );
        }
    }

    /**
     * 1afdb10: the 4.x/5.x hooks→events migration moved every handler under the
     * 'events' key and dropped the legacy 'hooks' key entirely. The manifest
     * must expose 'events' and must NOT carry a top-level 'hooks' key (which
     * 7.x ignores, silently dropping every handler).
     *
     * @return void
     */
    public function testManifestUsesEventsKeyNotHooks(): void
    {
        $src = self::read('elgg-plugin.php');
        $this->assertMatchesRegularExpression(
            "/^\s*'events'\s*=>\s*\[/m",
            $src,
            "elgg-plugin.php must register handlers under the 'events' key on 7.x",
        );
        $this->assertDoesNotMatchRegularExpression(
            "/^\s*'hooks'\s*=>\s*\[/m",
            $src,
            "elgg-plugin.php still carries a legacy top-level 'hooks' key — 7.x ignores it, dropping every handler",
        );
    }
}
