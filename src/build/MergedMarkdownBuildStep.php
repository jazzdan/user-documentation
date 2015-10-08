<?hh // strict

namespace HHVM\UserDocumentation;

use FredEmmott\DefinitionFinder\FileParser;

final class MergedMarkdownBuildStep extends BuildStep {
  public function buildAll(): void {
    $sources = (Vector { })
      ->addAll(self::findSources(BuildPaths::MERGED_YAML, Set{'yml'}));
    if (!is_dir(BuildPaths::MERGED_MD)) {
      mkdir(BuildPaths::MERGED_MD, /* mode = */ 0755, /* recursive = */ true);
    }
    foreach ($sources as $source) {
      $filename = pathinfo($source)['filename'];
      $type = explode('.', $filename)[0];   
      $output_path = BuildPaths::MERGED_MD.'/'.$filename.'.md'; 
      switch ($type) {
        case "function":
          //$builder = new FunctionMarkdownBuilder($source);
          //$builder->build();
          break;
        case "class":
          $builder = new ClassMarkdownBuilder($source);
          file_put_contents($output_path, $builder->build());
          break;
        default:
          // Interface and Trait need MarkdownBuilders?
          break;
      }
    }
  }
}
