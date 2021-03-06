<?hh // strict

namespace HHVM\UserDocumentation;

final class GuidesHTMLBuildStep extends BuildStep {
  const string SOURCE_ROOT = __DIR__.'/../../guides';
  const string RENDERER = __DIR__.'/../../md-render/render.rb';

  public function buildAll(): void {
    $sources = self::findSources(self::SOURCE_ROOT, Set{'md'})
      ->filter($path ==> basename($path) !== 'README.md')
      ->filter($path ==> strpos($path, '-examples') === false)
      ->map($path ==> substr($path, strlen(self::SOURCE_ROOT) + 1));
    sort($sources);

    $list = Vector { };
    foreach ($sources as $input) {
      $output = $this->renderFile($input);
      $list[] = $output;
    }
    $index = $this->createIndex($list);
    file_put_contents(
      BuildPaths::GUIDES_INDEX,
      '<?hh return '.var_export($index, true).";",
    );
    
    $summaries = self::findSources(self::SOURCE_ROOT, Set{'txt'})
      ->filter($path ==> strpos($path, '-summary') !== false)
      ->map($path ==> substr($path, strlen(self::SOURCE_ROOT) + 1));
    sort($summaries);
    $summary_index = $this->createSummaryIndex($summaries);
    file_put_contents(
      BuildPaths::GUIDES_SUMMARY,
      '<?hh return '.var_export($summary_index, true).";",
    );
  }

  private function renderFile(string $input): string {
    $parts = (new Vector(explode('/', $input)))
      ->map(
        $part ==> preg_match('/^[0-9]{2}-/', $part) ? substr($part, 3) : $part
      );

    $output = implode('/', $parts);
    $output = dirname($output).'/'.basename($output, '.md').'.html';
    $output = BuildPaths::GUIDES_HTML.'/'.$output;

    $output_dir = dirname($output);
    if (!is_dir($output_dir)) {
      mkdir($output_dir, /* mode = */ 0755, /* recursive = */ true);
    }

    $input = realpath(self::SOURCE_ROOT.'/'.$input);
    $args = (Vector { self::RENDERER, $input, $output })
      ->map($raw ==> escapeshellarg($raw));
    shell_exec(sprintf("%s %s > %s", ...$args));
    return $output;
  }

  private function createIndex(
    Iterable<string> $list,
  ): Map<string, Map<string, Map<string, string>>> {
    $out = Map { };
    foreach ($list as $path) {
      $path = str_replace(BuildPaths::GUIDES_HTML.'/', '', $path);
      $parts = explode('/', $path);
      if (count($parts) !== 3) {
        continue;
      }

      list($product, $section, $page) = $parts;
      $page = basename($page, '.html');
      if (!$out->contains($product)) {
        $out[$product] = Map {};
      }
      if (!$out[$product]->contains($section)) {
        $out[$product][$section] = Map { };
      }
      $out[$product][$section][$page] = $path;
    }
    return $out;
  }
  
  private function createSummaryIndex(
    Iterable<string> $summaries,
  ): Map<string, Map<string, string>> {
    $out = Map { };
    foreach ($summaries as $summary) {
      $parts = (new Vector(explode('/', $summary)))
        ->map(
          $part ==> preg_match('/^[0-9]{2}-/', $part) ? substr($part, 3) : $part
        );
      if (count($parts) !== 3) {
        continue;
      }
      list($product, $section, $page) = $parts;      
      if (!$out->contains($product)) {
        $out[$product] = Map {};
      }
      $out[$product][$section] = $summary;
    }
    return $out;
  }
}
