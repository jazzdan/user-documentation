<?hh // strict

use HHVM\UserDocumentation\GuidesIndex;

final class HomePageController extends WebPageController {
  protected async function getTitle(): Awaitable<string> {
    return 'HHVM and Hack Documentation';
  }
  
  protected function getInnerContent(string $product): XHPRoot {
    $guides = GuidesIndex::getGuides($product);

    $root = <ul class="guideList" />;
    foreach ($guides as $guide) {
      $pages = GuidesIndex::getPages($product, $guide);
      $url = sprintf(
        "/%s/%s/%s",
        $product,
        $guide,
        $pages[0],
      );

      $title = ucwords(strtr($guide, '-', ' '));

      $root->appendChild(
        <li>
          <h4><a href={$url}>{$title}</a></h4>
          <div class="guideDescription">
            {file_get_contents('http://loripsum.net/api/1/veryshort/plaintext')}
          </div>
        </li>
      );
    }
    return $root;
  }

  protected async function getBody(): Awaitable<XHPRoot> {
    return 
      <x:frag>
        <div class="guideListWrapper">
          <h2 class="listTitle">Hack</h2>
          <h3 class="listTitle">Learn</h3>
          {$this->getInnerContent('hack')}
          <h3 class="listTitle">
            <a href="/hack/reference/">Hack API Reference</a>
          </h3> 
          <p>Full reference docs for all functions, classes, interfaces, and traits in the Hack language.</p>
        </div>
        <div class="guideListWrapper">
          <h2 class="listTitle">HHVM</h2>
          <h3 class="listTitle">Learn</h3>
          {$this->getInnerContent('hhvm')}
        </div>
      </x:frag>;
  }
}
